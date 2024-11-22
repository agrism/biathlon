<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'x') }}</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
{{--    <script src="https://unpkg.com/htmx.org@1.9.12"></script>--}}
</head>

<body hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'>
<div class="flex flex-col justify-center min-h-screen antialiased">
    <header class="bg-gray-100 container mx-auto">
        <nav
            class="bg-white border border-gray-200 dark:border-gray-700 px-2 sm:px-4 py-2.5  dark:bg-gray-800 shadow">
            <div class="container flex flex-wrap justify-between items-center mx-auto">
                <a href="/" class="flex items-center">
                  <span class="self-center text-xl font-semibold whitespace-nowrap dark:text-white">
                    &#9737;&#9737;&#9737;&#9737;&#9737;
                  </span>
                </a>

                <div class="flex items-center">
                    <button
                        id="menu-toggle"
                        type="button"
                        class="inline-flex items-center p-2 ml-3 text-sm text-gray-500 rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600 md:hidden"
                    >
                        <span class="sr-only">Open main menu</span>
                        <!-- Hamburger icon -->
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M4 6h16M4 12h16m-7 6h7"
                            />
                        </svg>
                    </button>
                </div>

                <div
                    class="w-full md:block md:w-auto hidden"
                    id="mobile-menu"
                >
                    <ul class="flex flex-col mt-4 md:flex-row md:space-x-8 md:mt-0 md:text-sm md:font-medium">
                        <li>
                            <a
                                href="/"
                                class="block py-2 pr-4 pl-3 text-gray-700 border-b border-gray-100 hover:bg-gray-50 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 dark:text-gray-400 md:dark:hover:text-white dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent dark:border-gray-700"
                            >
                                Games
                            </a>
                        </li>
                        <li>
                            <a
                                href="{{route('profile')}}"
                                class="block py-2 pr-4 pl-3 text-gray-700 border-b border-gray-100 hover:bg-gray-50 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 dark:text-gray-400 md:dark:hover:text-white dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent dark:border-gray-700"
                            >
                                {{ auth()->user()->name }}
                            </a>
                        </li>
                        <li>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <button type="submit"
                                        class="block py-2 pr-4 pl-3 text-gray-700 border-b border-gray-100 hover:bg-gray-50 md:hover:bg-transparent md:border-0 md:hover:text-blue-700 md:p-0 dark:text-gray-400 md:dark:hover:text-white dark:hover:bg-gray-700 dark:hover:text-white md:dark:hover:bg-transparent dark:border-gray-700"
                                >
                                    {{ __('Logout') }}
                                </button>

                            </form>
                        </li>
{{--                        @include('admin.menu', ['ignoreHome' => true])--}}
                    </ul>
                </div>

            </div>
        </nav>
    </header>
    <x-alerts.errors/>
    <x-alerts.status/>
    <div class="container mx-auto top-600 h-screen">
        {{--        <div class="right-0 flex items-center justify-end px-4 py-2 space-x-4">--}}
        {{--            <div class="text-gray-800 capitalize">--}}
        {{--                {{ auth()->user()->name }}--}}
        {{--            </div>--}}

        {{--            <form method="POST" action="{{ route('logout') }}">--}}
        {{--                @csrf--}}

        {{--                <button type="submit" class="text-gray-700 uppercase hover:underline hover:text-blue-700">--}}
        {{--                    {{ __('Logout') }}--}}
        {{--                </button>--}}

        {{--            </form>--}}
        {{--        </div>--}}
        {{ $slot }}
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
