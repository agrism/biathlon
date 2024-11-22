<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'x') }}</title>
    <style>
        table, th, td {
            border: 1px solid;
        }
    </style>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
{{--    <script src="https://unpkg.com/htmx.org@1.9.12"></script>--}}
</head>

<body hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'>
<div class="flex flex-col justify-center min-h-screen antialiased">
    <ul>
        @include('admin.menu')
    </ul>
    <x-alerts.errors/>
    <x-alerts.status/>
    <div class="container mx-auto top-600 h-screen">
        {{ $slot }}
    </div>
</div>
</body>

</html>
