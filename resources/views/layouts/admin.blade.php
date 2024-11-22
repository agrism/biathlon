<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    {{--    <script src="https://cdn.tailwindcss.com"></script>--}}
    <title>&#10052;</title>
{{--    <script--}}
{{--        src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio,line-clamp,container-queries"></script>--}}
{{--    <script src="https://unpkg.com/htmx.org@1.9.12"--}}
{{--            integrity="sha384-ujb1lZYygJmzgSwoxRggbCHcjc0rB2XoQrxeTUQyRjrOnlCoYta87iKBWq3EsdM2"--}}
{{--            crossorigin="anonymous"></script>--}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
{{--    <script src="https://unpkg.com/htmx.org@1.9.12"></script>--}}
</head>
<body>
{{--@if(auth()->check())--}}
{{--    @include('admin.nav.nav')--}}
{{--@endif--}}

<div class="container" style="margin: 0 auto">

    @include('menu', ['ignoreHome' => true])


    {!! \App\Helpers\BreadCrumbHelper::instance()->render()!!}

    @isset($heading)
    <h1 class="mb-4 mt-4 text-2xl font-extrabold leading-none tracking-tight text-gray-900 md:text-2xl lg:text-3xl  text-center ">{!! $heading !!}</h1>
    @endif

    @yield('content')

    <div class="text-gray-400 px-2 text-sm">
        Response time: {{round(microtime(true) - LARAVEL_START, 3)}}s
    </div>
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
