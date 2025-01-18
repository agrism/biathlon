<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Biathlon +</title>
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body hx-boost="true" hx-indicator="#status">

<div id="status" class="indicator fixed inset-0 z-50 flex items-center justify-center">
    <div class="rounded-md px-6 py-4 flex items-center gap-4 bg-white border-2 border-grey">
        <div class="circle w-5 h-5 rounded-full border-2 border-gray-300 bg-black"></div>
        <div class="circle w-5 h-5 rounded-full border-2 border-gray-300 bg-black"></div>
        <div class="circle w-5 h-5 rounded-full border-2 border-gray-300 bg-black"></div>
        <div class="circle w-5 h-5 rounded-full border-2 border-gray-300 bg-black"></div>
        <div class="circle w-5 h-5 rounded-full border-2 border-gray-300 bg-black"></div>
    </div>
</div>

@include('menu', ['ignoreHome' => true])
<div class="container" style="margin: 0 auto">
    {!! \App\Helpers\BreadCrumbHelper::instance()->render()!!}

    @isset($heading)
    <h1 class="mb-4 mt-4 text-2xl font-extrabold leading-none tracking-tight text-gray-900 md:text-2xl lg:text-3xl  text-center ">{!! $heading !!}</h1>
    @endif

    <div class="cont">
        @yield('content')
    </div>

    <div class="text-gray-400 px-2 text-sm">
        Response time: {{round(microtime(true) - LARAVEL_START, 3)}}s
    </div>
</div>

<script>
    try{
        const menuToggle = document.getElementById('menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');

        menuToggle.addEventListener('click', function () {
            mobileMenu.classList.toggle('hidden');
        });
    } catch {
    }
</script>
</body>
</html>
