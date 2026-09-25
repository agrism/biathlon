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

    <!-- Modern Editorial Biathlon Story Carousel (Full Width) -->
    <div
        class="mb-12 w-full overflow-hidden rounded-2xl border border-slate-800/90 shadow-md bg-slate-950 relative select-none"
        x-data="{
            active: 0,
            slides: [
                {
                    src: '{{ asset('images/carousel/slide-1.jpg') }}',
                    tag: 'Holmenkollen Stage',
                    title: 'BMW IBU World Cup Holmenkollen',
                    subtitle: 'Celebrating the season crystal globe champions on the iconic Holmenkollen tracks.'
                },
                {
                    src: '{{ asset('images/carousel/slide-2.jpg') }}',
                    tag: 'Sprint Speed',
                    title: 'BMW IBU World Cup Oslo',
                    subtitle: 'Sprint speed & marksmanship on the tracks as world cup battles intensify.'
                },
                {
                    src: '{{ asset('images/carousel/slide-3.jpg') }}',
                    tag: 'Winter Games 2026',
                    title: 'Milano-Cortina 2026',
                    subtitle: 'Olympic Winter Games biathlon individual battles and podium chases.'
                },
                {
                    src: '{{ asset('images/carousel/slide-4.jpg') }}',
                    tag: 'Ruhpolding Arena',
                    title: 'BMW IBU World Cup Ruhpolding',
                    subtitle: 'Chiemgau Arena range focus and trackside preparations in the Bavarian Alps.'
                },
                {
                    src: '{{ asset('images/carousel/slide-5.jpg') }}',
                    tag: 'Podium Contenders',
                    title: 'BMW IBU World Cup Contenders',
                    subtitle: 'Rising stars and podium contenders of the biathlon world cup season.'
                },
                {
                    src: '{{ asset('images/carousel/slide-6.jpg') }}',
                    tag: 'Elite Women',
                    title: 'BMW IBU World Cup Elite Women',
                    subtitle: 'Top athletes & crystal globe contenders battling down to the final shooting bout.'
                }
            ],
            autoplay: true,
            timer: null,
            init() {
                this.timer = setInterval(() => {
                    if (this.autoplay) {
                        this.next();
                    }
                }, 6000);
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
        <div class="grid grid-cols-1 lg:grid-cols-12 min-h-[380px] sm:min-h-[420px] lg:min-h-[460px] xl:min-h-[480px]">
            <!-- Left Side: Editorial Content & Controls (5 cols on lg, 4 cols on xl) -->
            <div class="lg:col-span-5 xl:col-span-4 p-6 sm:p-8 lg:p-10 xl:p-12 flex flex-col justify-between bg-slate-950 text-white z-10 border-b lg:border-b-0 lg:border-r border-slate-800/60">
                <!-- Top Tag & Counter -->
                <div class="flex items-center justify-between gap-3 mb-4">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-sky-500/20 text-sky-400 border border-sky-500/30 text-xs font-black uppercase tracking-wider">
                        <i class="fa-solid fa-snowflake text-[10px]"></i>
                        <span x-text="slides[active].tag"></span>
                    </span>
                    <span class="text-xs font-bold text-slate-400 bg-slate-800/80 px-2.5 py-1 rounded-full border border-slate-700">
                        <span class="text-white font-extrabold" x-text="active + 1"></span> / <span x-text="slides.length"></span>
                    </span>
                </div>

                <!-- Middle Headline & Subtitle -->
                <div class="my-auto py-3">
                    <h2 class="text-xl sm:text-2xl lg:text-3xl font-black text-white tracking-tight leading-tight" x-text="slides[active].title"></h2>
                    <p class="text-xs sm:text-sm text-slate-300 mt-2.5 leading-relaxed font-medium" x-text="slides[active].subtitle"></p>
                </div>

                <!-- Bottom Navigation Toolbar -->
                <div class="flex items-center justify-between gap-4 pt-4 border-t border-slate-800/80 mt-4">
                    <!-- Dots -->
                    <div class="flex items-center gap-1.5">
                        <template x-for="(slide, index) in slides" :key="index">
                            <button
                                type="button"
                                @click="goTo(index)"
                                class="h-2.5 rounded-full transition-all duration-300 cursor-pointer"
                                :class="active === index ? 'w-7 bg-sky-400' : 'w-2.5 bg-slate-700 hover:bg-slate-500'"
                                :aria-label="'Go to slide ' + (index + 1)"
                            ></button>
                        </template>
                    </div>

                    <!-- Arrows -->
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="prev()"
                            class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-slate-700 active:scale-95 text-slate-200 hover:text-white flex items-center justify-center border border-slate-700 transition-all cursor-pointer shadow-xs"
                            aria-label="Previous Slide"
                        >
                            <i class="fa-solid fa-chevron-left text-xs"></i>
                        </button>
                        <button
                            type="button"
                            @click="next()"
                            class="w-9 h-9 rounded-xl bg-slate-800 hover:bg-slate-700 active:scale-95 text-slate-200 hover:text-white flex items-center justify-center border border-slate-700 transition-all cursor-pointer shadow-xs"
                            aria-label="Next Slide"
                        >
                            <i class="fa-solid fa-chevron-right text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Side: Full-Bleed Visual with Ambient Backdrop (7 cols on lg, 8 cols on xl) -->
            <div class="lg:col-span-7 xl:col-span-8 relative bg-slate-950 overflow-hidden min-h-[280px] sm:min-h-[340px] lg:min-h-full flex items-center justify-center">
                <template x-for="(slide, index) in slides" :key="index">
                    <div
                        x-show="active === index"
                        x-transition:enter="transition ease-out duration-600"
                        x-transition:enter-start="opacity-0 scale-102"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-400"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-98"
                        class="absolute inset-0 w-full h-full flex items-center justify-center lg:justify-end"
                    >
                        <!-- Blurred ambient backdrop to seamlessly fill any screen aspect ratio with matching colors -->
                        <img
                            :src="slide.src"
                            :alt="slide.title"
                            class="absolute inset-0 w-full h-full object-cover blur-3xl opacity-35 scale-110 pointer-events-none"
                            aria-hidden="true"
                        >

                        <!-- Foreground uncropped crisp photo -->
                        <img
                            :src="slide.src"
                            :alt="slide.title"
                            class="relative h-full w-full max-h-[520px] object-cover lg:object-contain object-center lg:object-right z-10"
                        >

                        <!-- Left-edge subtle vignette connecting smoothly into dark editorial block -->
                        <div class="hidden lg:block absolute inset-y-0 left-0 w-32 xl:w-48 bg-gradient-to-r from-slate-950 via-slate-950/80 to-transparent pointer-events-none z-20"></div>

                        <!-- Right-edge subtle vignette -->
                        <div class="hidden xl:block absolute inset-y-0 right-0 w-16 bg-gradient-to-l from-slate-950 to-transparent pointer-events-none z-20"></div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    @if(isset($event) && $event->first_competition_date)
        <!-- Upcoming Stage Countdown Banner -->
        <div class="mb-10 w-full p-6 rounded-2xl bg-gradient-to-r from-slate-950 via-slate-900 to-sky-950 text-white shadow-md relative overflow-hidden border border-slate-800/80">
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
