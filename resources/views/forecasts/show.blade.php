@extends('layouts.admin', ['heading' => isset($helper) ? $helper->title(): ''])

@section('content')
    @include('forecasts.partials.show-content')
@endsection
