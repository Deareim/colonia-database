@extends('layout/layout')

@section('title')
Factions
@endsection

@section('content')

@if ($userrank > 1)
<p><a class='edit' href='{{route('factions.create')}}'>New faction</a></p>
@endif
    
<table class='table table-bordered datatable' data-order='[[0, "asc"]]'>
  <thead>
	<tr>
	  <th>Name</th>
	  <th>Government</th>
	  <th>Player?</th>
	</tr>
  </thead>
  <tbody>
	@foreach ($factions as $faction)
	<tr>
	  <td><a href='{{route("factions.show", $faction->id)}}'>{{$faction->name}}</a></td>
      <td>
		@include($faction->government->icon)
		{{$faction->government->name}}
	  </td>
      <td>
		@if ($faction->player)
		@include('layout/yes')
		@else
		@include('layout/no')
		@endif
	  </td>
	</tr>
	@endforeach
  </tbody>
</table>
    
@endsection
