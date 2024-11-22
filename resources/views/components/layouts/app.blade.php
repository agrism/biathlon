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

<body hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'>

<div class="container" style="margin: 0 auto">
    @include('menu', ['ignoreHome' => true])
</div>

<script>
    const menuToggle = document.getElementById('menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');

    menuToggle.addEventListener('click', function () {
        mobileMenu.classList.toggle('hidden');
    });
</script>
</body>

</html>
