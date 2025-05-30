@extends('layouts.admin', ['heading' => isset($helper) ? $helper->title(): ''])

@section('content')



    <h2 class="mb-4 mt-4 text-2xl font-extrabold leading-none tracking-tight text-gray-900 md:text-2xl lg:text-3xl  text-center ">
    {{/* @var \App\Models\Event $event */ $event->description }} season {{ implode('/', str_split($season->name, 2)) }}<br><br>
        starts at: {{$event->short_description}}, {{$event->first_competition_date->tz('Europe/Riga')->format('H:i \o\n d F Y.')}}
    </h2>


    <h1 class="mb-4 mt-4 text-4xl font-extrabold leading-none tracking-tight text-gray-900 md:text-2xl lg:text-3xl  text-center text-red-500" id="countdown">

    </h1>

    <script>
        // Set target date
        // const targetDate = new Date("2025-12-31T23:59:59").getTime();
        const targetDate = new Date("{{$event->first_competition_date->tz('Europe/Riga')->toDateTimeString()}}").getTime();

        const countdown = setInterval(() => {
            const now = new Date().getTime();
            const distance = targetDate - now;

            if (distance < 0) {
                clearInterval(countdown);
                document.getElementById("countdown").innerHTML = "Countdown finished!";
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            document.getElementById("countdown").innerHTML =
                `${days}d ${hours}h ${minutes}m ${seconds}s`;
        }, 1000);
    </script>
@endsection
