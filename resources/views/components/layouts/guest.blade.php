<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'x') }}</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
</head>

<body>
<div class="container" style="margin: 0 auto">
    @include('menu', ['ignoreHome' => true])
    <div class="flex flex-col justify-center min-h-screen antialiased bg-gray1-100">

        {{ $slot }}
    </div>
</div>
</body>

</html>
