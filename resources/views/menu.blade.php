<nav class="sticky top-0 z-40 w-full bg-white/90 backdrop-blur-md border-b border-slate-200/80 shadow-sm transition-all">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Brand Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-sky-600 to-sky-400 flex items-center justify-center text-white shadow-md shadow-sky-500/20 group-hover:scale-105 transition-transform">
                    <span class="text-base font-black tracking-tighter">🎯</span>
                </div>
                <div class="flex flex-col">
                    <div class="flex items-center gap-1.5">
                        <span class="font-extrabold text-lg text-slate-900 tracking-tight leading-none group-hover:text-sky-600 transition-colors">BIATHLON</span>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-sky-100 text-sky-700 uppercase tracking-wide">PRO</span>
                    </div>
                    <span class="text-[10px] text-slate-400 font-medium tracking-wider uppercase leading-tight mt-0.5">IBU Prediction League</span>
                </div>
            </a>

            <!-- Mobile Hamburger Toggle -->
            <div class="flex md:hidden">
                <button
                    id="menu-toggle"
                    type="button"
                    class="inline-flex items-center justify-center p-2 rounded-xl text-slate-500 hover:text-slate-700 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-sky-500 transition-colors"
                    aria-controls="mobile-menu"
                    aria-expanded="false"
                >
                    <span class="sr-only">Open main menu</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                    </svg>
                </button>
            </div>

            <!-- Desktop Nav Links -->
            <div class="hidden md:flex md:items-center md:space-x-1 lg:space-x-2">
                @php
                    $navItems = [
                        ['route' => 'events.index', 'name' => 'Events', 'icon' => 'fa-solid fa-calendar-days', 'active' => request()->routeIs('events.*')],
                        ['route' => 'forecasts.summary.index', 'name' => 'Leaderboard', 'icon' => 'fa-solid fa-trophy', 'active' => request()->routeIs('forecasts.summary.*')],
                        ['route' => 'forecasts.index', 'name' => 'Predictions', 'icon' => 'fa-solid fa-crosshairs', 'active' => request()->routeIs('forecasts.index') || request()->routeIs('forecasts.show') || request()->routeIs('forecasts.select-athlete')],
                        ['route' => 'athletes.index', 'name' => 'Athletes', 'icon' => 'fa-solid fa-person-skiing', 'active' => request()->routeIs('athletes.*')],
                    ];
                @endphp

                @foreach($navItems as $item)
                    <a
                        href="{{ route($item['route']) }}"
                        class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-medium transition-all {{ $item['active'] ? 'bg-sky-50 text-sky-600 font-semibold shadow-xs' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-50' }}"
                    >
                        <i class="{{ $item['icon'] }} text-xs {{ $item['active'] ? 'text-sky-600' : 'text-slate-400' }}"></i>
                        <span>{{ $item['name'] }}</span>
                    </a>
                @endforeach
            </div>

            <!-- User Auth Capsule (Desktop) -->
            <div class="hidden md:flex md:items-center md:gap-3">
                @if(auth()->check())
                    <div class="flex items-center gap-2 pl-3 border-l border-slate-200">
                        <a
                            href="{{ route('private.profile') }}"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-100/80 hover:bg-slate-200/80 border border-slate-200 text-slate-800 text-sm font-medium transition-all"
                        >
                            <span class="w-6 h-6 rounded-full bg-gradient-to-tr from-sky-600 to-indigo-600 text-white flex items-center justify-center text-xs font-bold uppercase shadow-xs">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </span>
                            <span>{{ auth()->user()->name }}</span>
                        </a>

                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button
                                type="submit"
                                title="Logout"
                                class="p-2 rounded-xl text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                            >
                                <i class="fa-solid fa-arrow-right-from-bracket text-sm"></i>
                            </button>
                        </form>
                    </div>
                @else
                    <div class="flex items-center gap-2 pl-3 border-l border-slate-200">
                        <a
                            href="{{ route('login') }}"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold shadow-md shadow-sky-500/20 hover:shadow-sky-500/30 transition-all"
                        >
                            <i class="fa-solid fa-user text-xs"></i>
                            <span>Sign In</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Mobile Drawer Menu -->
    <div class="md:hidden hidden border-t border-slate-200 bg-white/95 px-4 pt-3 pb-4 space-y-1" id="mobile-menu">
        @foreach($navItems ?? [] as $item)
            <a
                href="{{ route($item['route']) }}"
                class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-base font-medium {{ $item['active'] ? 'bg-sky-50 text-sky-600 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}"
            >
                <i class="{{ $item['icon'] }} w-5 text-sm {{ $item['active'] ? 'text-sky-600' : 'text-slate-400' }}"></i>
                <span>{{ $item['name'] }}</span>
            </a>
        @endforeach

        <div class="pt-3 mt-3 border-t border-slate-200">
            @if(auth()->check())
                <div class="flex items-center justify-between px-3 py-2">
                    <a href="{{ route('private.profile') }}" class="flex items-center gap-2 font-medium text-slate-800">
                        <span class="w-7 h-7 rounded-full bg-sky-600 text-white flex items-center justify-center text-xs font-bold uppercase">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </span>
                        <span>{{ auth()->user()->name }}</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-xs font-semibold text-rose-600 px-2.5 py-1 rounded-lg bg-rose-50">
                            Logout
                        </button>
                    </form>
                </div>
            @else
                <a
                    href="{{ route('login') }}"
                    class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-sky-600 text-white font-semibold text-center shadow-md"
                >
                    <i class="fa-solid fa-user text-xs"></i>
                    <span>Sign In</span>
                </a>
            @endif
        </div>
    </div>
</nav>
