<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    public function influences() {
        return $this->hasMany('App\Models\Influence');
    }

    public function reserves() {
        return $this->hasMany('App\Models\Reserve');
    }

    public function effects()
    {
        return $this->hasMany('App\Models\Effect');
    }
    
    public function tradebalances() {
        return $this->hasMany('App\Models\Tradebalance');
    }

    // pending states
    public function factions() {
        return $this->belongsToMany('App\Models\Faction')->withPivot('date');
    }

    // current states
    public function currentFactions() {
        return $this->belongsToMany(
            'App\Models\Faction',
            'influences'
        )->wherePivot('current', 1)->distinct();
    }
//
}
