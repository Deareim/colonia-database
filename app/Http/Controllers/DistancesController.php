<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\System;
use App\Models\Faction;
use App\Models\Influences;

class DistancesController extends Controller
{
    
    const MISSION_LIMIT = 20;
    
    public function index() {
        $systems = System::with('phase', 'stations', 'stations.faction')->get();
        $systems = $systems->sortBy(function ($s) {
            return str_pad($s->phase->sequence, 3, "0", STR_PAD_LEFT).
                ":".$s->displayName();
        });

        $fakefaction = new Faction; // don't save this!
        $fakefaction->name = "?";
        $fakefaction->id = -1;

        $maxphase = 0;
        foreach ($systems as $idx => $system) {
            if ($system->inhabited() && $system->phase->sequence > $maxphase) {
                $maxphase = $system->phase->sequence;
            }
        }

        $presents = []; // cache

        $this->loadFactionCache();
        
        $grid = [];
        foreach ($systems as $idx => $system) {
            $line = [];
            if ($system->inhabited()) {
                $faction = $system->controllingFaction();
            } else {
                $faction = $fakefaction;
            }

            foreach ($systems as $idx2 => $system2) {
                if ($idx == $idx2) {
                    $line[$system2->id] = [
                        'distance' => 0,
                        'expansion' => false,
                        'present' => true,
                        'full' => false,
                        'available' => false,
                        'candidate' => false,
                        'target' => false
                    ];
                } else {
                    if (!isset($presents[$system2->id])) {
                        $presents[$system2->id] = $this->currentFactions($system2);
                    }
                    
                    $details = [
                        'distance' => $system->distanceTo($system2),
                        'expansion' => $system->expansionCube($system2),
                        'present' => isset($presents[$system2->id][$faction->id]),
                        'full' => count($presents[$system2->id]) >= 7 || $system2->bgslock,
                        'available' => (($system2->phase->sequence <= $maxphase) || ($system->phase->sequence >= $system2->phase->sequence)) && $system2->population > 0,
                        'candidate' => false,
                        'target' => false
                    ];
                    $line[$system2->id] = $details;
                }
            }
            
            uasort($line, function($a, $b) {
                return $a['distance'] - $b['distance'];
            });
            $targets = 0;
            foreach ($line as $key => $properties) {
                if (!$properties['present']) {
                    if (!$properties['expansion']) {
                        break; // that's it...
                    }
                    if ($properties['full'] || !$properties['available']) {
                        // can't expand right now but might later?
                        $line[$key]['candidate'] = true;
                    } else {
                        $line[$key]['candidate'] = true;
                        $line[$key]['target'] = true;
                        $targets++;
                        if ($targets == 3) {
                            break; // enough
                        }
                    }
                }
            }
            
            $grid[$system->id] = $line;
        }

        

        return view('distances/index', [
            'systems' => $systems,
            'grid' => $grid,
            'presents' => $presents,
            'missions' => self::MISSION_LIMIT,
        ]);
    }

    private $factioncache;
    private function loadFactionCache() {
        $factions = Faction::all();
        foreach ($factions as $faction) {
            $this->factioncache[$faction->id] = $faction;
        }
    }
    
    private function currentFactions(System $system) {
        $influences = $system->latestFactionsWithoutEagerLoad();
        $factions = [];
        foreach ($influences as $influence) {
            $factions[$influence->faction_id] = $this->factioncache[$influence->faction_id];
        }
        return $factions;
    }
    
}
