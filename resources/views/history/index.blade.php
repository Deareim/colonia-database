@extends('layout/layout')

@section('title', 'Colonia Region History Log')

@section('content')

@if ($userrank > 1)
<p><a class='edit' href='{{route('history.create')}}'>New history</a></p>
@endif

<table class='table table-bordered datatable' data-page-length='50' data-order='[[0,"desc"]]'>
  <thead>
	<tr><th>Date</th><th>Faction</th><th>Event</th><th>Location</th></tr>
  </thead>
  <tbody>
	@foreach ($historys as $history)
	<tr>
	  <td data-sort='{{$history->date->format('Y-m-d')}}'>{{\App\Util::displayDate($history->date)}}</td>
	  <td>
		@include($history->faction->government->icon)
		<a href='{{route('factions.show', $history->faction->id)}}'>
		  {{$history->faction->name}}
		</a>
	  </td>
	  <td>
		{{$history->description}}
	  </td>
	  @if ($history->location_type == 'App\Models\System')
	  <td>
		@include($history->location->economy->icon)
		<a href='{{route('systems.show', $history->location->id)}}'>
		  {{$history->location->displayName()}}
		</a>
	  </td>
	  @elseif ($history->location_type == 'App\Models\Station')
	  <td>
		@include($history->location->economy->icon)
		<a href='{{route('stations.show', $history->location->id)}}'>
		  {{$history->location->name}}
		</a>
	  </td>
	  @endif 
	</tr>
	@endforeach
  </tbody>
</table>

<p>'Faction' refers to the faction controlling the location at the time of the event, and does not imply intent.</p>

@endsection
