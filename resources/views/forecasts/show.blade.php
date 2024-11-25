@extends('layouts.admin', ['heading' => isset($helper) ? $helper->title(): ''])

@section('content')
    <h1 class="mb-4 mt-4 text-2xl font-extrabold leading-none tracking-tight text-gray-900 md:text-2xl lg:text-3xl  text-center ">
        {!!$forecast?->competition->getTitle()!!}</h1>
    @include('forecasts.partials.show-content')
@endsection
