<!doctype html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Biathlon Prediction League</title>
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @yield('style')
</head>
<body hx-boost="true" hx-indicator="#status"
      class="min-h-full flex flex-col bg-slate-50 text-slate-800 antialiased selection:bg-sky-500 selection:text-white @yield('body-class')"
>

<!-- Modern Biathlon Target Loading Indicator -->
<div id="status" class="indicator fixed inset-0 z-50 flex items-center justify-center bg-slate-900/30 backdrop-blur-sm transition-all duration-300">
    <div class="rounded-2xl px-6 py-4 flex items-center gap-3 bg-white/95 border border-slate-200/80 shadow-2xl">
        <div class="flex items-center gap-2">
            <div class="target-disc"></div>
            <div class="target-disc"></div>
            <div class="target-disc"></div>
            <div class="target-disc"></div>
            <div class="target-disc"></div>
        </div>
        <span class="text-xs font-semibold uppercase tracking-wider text-slate-600 ml-2">Loading...</span>
    </div>
</div>

@include('menu', ['ignoreHome' => true])

<main class="flex-1 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
    <div class="mb-4">
        {!! \App\Helpers\BreadCrumbHelper::instance()->render() !!}
    </div>

    @isset($heading)
    <div class="mb-6 text-center">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">{!! $heading !!}</h1>
    </div>
    @endif

    <div class="cont" style="opacity: 1;">
        @yield('content')
    </div>
</main>

<footer class="w-full border-t border-slate-200/80 bg-white/60 backdrop-blur-sm py-4 mt-12 text-center text-xs text-slate-400">
    <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-2">
        <div class="flex items-center gap-2">
            <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
            <span>IBU World Cup Live Sync Active</span>
        </div>
        <div>
            Biathlon Prediction Game &bull; Server time: {{ round(microtime(true) - LARAVEL_START, 3) }}s
        </div>
    </div>
</footer>

<script>
    function closeAthleteModal() {
        const show = document.getElementById('show');
        if (show) {
            show.innerHTML = '';
            show.classList.add('hidden');
        }
    }
    window.closeAthleteModal = closeAthleteModal;

    document.addEventListener('htmx:afterSwap', function (evt) {
        if (evt.detail.target && evt.detail.target.id === 'show') {
            if (evt.detail.target.innerHTML.trim() !== '') {
                evt.detail.target.classList.remove('hidden');
            } else {
                evt.detail.target.classList.add('hidden');
            }
        }
    });

    document.addEventListener('keydown', function (evt) {
        if (evt.key === 'Escape') {
            closeAthleteModal();
        }
    });

    try {
        const menuToggle = document.getElementById('menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');

        if (menuToggle && mobileMenu) {
            menuToggle.addEventListener('click', function () {
                mobileMenu.classList.toggle('hidden');
            });
        }
    } catch(e) {}
</script>
</body>
</html>
