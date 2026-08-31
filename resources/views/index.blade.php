@extends('layouts.admin', ['heading' => ''])

@section('content')
    <!-- Home Hero Banner -->
    <div class="mb-8 text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100/90 text-sky-900 text-xs font-bold uppercase tracking-wider mb-3">
            ❄️ Trackside Lounge & News
        </div>
        <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">
            Biathlon Stories & Trackside News
        </h1>
        <p class="text-sm text-slate-500 max-w-xl mx-auto mt-2 leading-relaxed">
            Grab a warm coffee and catch up on the latest race drama, shooting range breakdowns, and stories straight from the snow.
        </p>
    </div>

    <!-- Biathlon Story Carousel -->
    <div
        class="mb-10 max-w-4xl mx-auto overflow-hidden rounded-3xl border border-slate-200/90 shadow-md bg-slate-950 relative group select-none"
        x-data="{
            active: 0,
            slides: [
                {
                    src: '{{ asset('images/carousel/slide-1.jpg') }}',
                    title: 'BMW IBU World Cup Holmenkollen',
                    subtitle: 'Celebrating the season crystal globe champions'
                },
                {
                    src: '{{ asset('images/carousel/slide-2.jpg') }}',
                    title: 'BMW IBU World Cup Oslo',
                    subtitle: 'Sprint speed & marksmanship on the tracks'
                },
                {
                    src: '{{ asset('images/carousel/slide-3.jpg') }}',
                    title: 'Milano-Cortina 2026',
                    subtitle: 'Olympic Winter Games biathlon individual battles'
                },
                {
                    src: '{{ asset('images/carousel/slide-4.jpg') }}',
                    title: 'BMW IBU World Cup Ruhpolding',
                    subtitle: 'Chiemgau Arena range focus and trackside preparations'
                },
                {
                    src: '{{ asset('images/carousel/slide-5.jpg') }}',
                    title: 'BMW IBU World Cup Contenders',
                    subtitle: 'Rising stars and podium contenders of the season'
                },
                {
                    src: '{{ asset('images/carousel/slide-6.jpg') }}',
                    title: 'BMW IBU World Cup Elite Women',
                    subtitle: 'Top athletes & crystal globe contenders'
                }
            ],
            autoplay: true,
            timer: null,
            init() {
                this.timer = setInterval(() => {
                    if (this.autoplay) {
                        this.next();
                    }
                }, 5000);
            },
            next() {
                this.active = (this.active + 1) % this.slides.length;
            },
            prev() {
                this.active = (this.active - 1 + this.slides.length) % this.slides.length;
            },
            goTo(index) {
                this.active = index;
            }
        }"
        @mouseenter="autoplay = false"
        @mouseleave="autoplay = true"
    >
        <!-- Slides Container -->
        <div class="relative w-full h-72 sm:h-96 md:h-[480px] overflow-hidden">
            <template x-for="(slide, index) in slides" :key="index">
                <div
                    x-show="active === index"
                    x-transition:enter="transition ease-out duration-700"
                    x-transition:enter-start="opacity-0 scale-105"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-500"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute inset-0 w-full h-full"
                >
                    <img
                        :src="slide.src"
                        :alt="slide.title"
                        class="w-full h-full object-cover object-center"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-950/20 to-transparent flex flex-col justify-end p-6 sm:p-8">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-sky-500/90 text-white text-xs font-black uppercase tracking-wider w-max mb-2 shadow-sm">
                            ❄️ Biathlon Stories
                        </span>
                        <h3 class="text-lg sm:text-2xl font-black text-white drop-shadow-md" x-text="slide.title"></h3>
                        <p class="text-xs sm:text-sm font-medium text-slate-200 mt-1 drop-shadow" x-text="slide.subtitle"></p>
                    </div>
                </div>
            </template>
        </div>

        <!-- Navigation Arrows -->
        <button
            type="button"
            @click="prev()"
            class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-slate-900/60 hover:bg-slate-900/90 backdrop-blur-md text-white flex items-center justify-center border border-white/20 transition-all hover:scale-110 cursor-pointer shadow-md z-20"
            aria-label="Previous Slide"
        >
            <i class="fa-solid fa-chevron-left text-sm"></i>
        </button>

        <button
            type="button"
            @click="next()"
            class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-slate-900/60 hover:bg-slate-900/90 backdrop-blur-md text-white flex items-center justify-center border border-white/20 transition-all hover:scale-110 cursor-pointer shadow-md z-20"
            aria-label="Next Slide"
        >
            <i class="fa-solid fa-chevron-right text-sm"></i>
        </button>

        <!-- Slide Indicators & Counter -->
        <div class="absolute bottom-4 right-6 z-20 flex items-center gap-3">
            <!-- Counter -->
            <span class="px-2.5 py-1 rounded-full bg-slate-900/70 backdrop-blur-md text-white text-[11px] font-bold border border-white/15">
                <span x-text="active + 1"></span> / <span x-text="slides.length"></span>
            </span>

            <!-- Dots -->
            <div class="flex items-center gap-1.5 bg-slate-900/70 backdrop-blur-md px-3 py-1.5 rounded-full border border-white/15">
                <template x-for="(slide, index) in slides" :key="index">
                    <button
                        type="button"
                        @click="goTo(index)"
                        class="h-2 rounded-full transition-all duration-300 cursor-pointer"
                        :class="active === index ? 'w-6 bg-sky-400' : 'w-2 bg-white/40 hover:bg-white/70'"
                        :aria-label="'Go to slide ' + (index + 1)"
                    ></button>
                </template>
            </div>
        </div>
    </div>

    @if(isset($event) && $event->first_competition_date)
        <!-- Upcoming Stage Countdown Banner -->
        <div class="mb-10 p-6 rounded-3xl bg-gradient-to-r from-slate-900 via-slate-800 to-sky-950 text-white shadow-md relative overflow-hidden">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6 relative z-10">
                <div>
                    <span class="px-2.5 py-1 rounded-full bg-amber-500/20 text-amber-300 text-xs font-bold uppercase tracking-wider">
                        Next World Cup Stage
                    </span>
                    <h2 class="text-xl sm:text-2xl font-black mt-2 text-white">
                        {{ $event->description }}
                    </h2>
                    <p class="text-xs text-slate-300 mt-1">
                        {{ $event->short_description }} • {{ $event->first_competition_date->tz('Europe/Riga')->format('d F Y, H:i') }}
                    </p>
                </div>

                <div class="text-center bg-white/10 backdrop-blur-md px-6 py-3 rounded-2xl border border-white/15">
                    <span class="text-[11px] uppercase tracking-wider text-slate-300 font-semibold block">Starts In</span>
                    <span id="countdown" class="text-lg sm:text-xl font-black text-amber-400 tracking-tight"></span>
                </div>
            </div>
        </div>

        <script>
            (function() {
                const targetDate = new Date("{{ $event->first_competition_date->tz('Europe/Riga')->toDateTimeString() }}").getTime();
                const el = document.getElementById("countdown");
                if (!el) return;

                const timer = setInterval(() => {
                    const now = new Date().getTime();
                    let distance = targetDate - now;

                    if (distance < 0) {
                        el.innerText = "Event Underway!";
                        clearInterval(timer);
                        return;
                    }

                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    el.innerText = `${days}d ${hours}h ${minutes}m ${seconds}s`;
                }, 1000);
            })();
        </script>
    @endif

    <!-- Penalty Loop Biathlon Insights & Telemetry Feed Widget -->
    @include('components.widgets.penalty-loop-feed', ['tweets' => $tweets])

@endsection
