@extends('layouts.admin', ['heading' => isset($helper) ? $helper->title(): ''])

@section('content')

    <style>
        p {
            padding: 15px;
        }
    </style>



    <h2 class="mb-4 mt-4 text-2xl font-extrabold leading-none tracking-tight text-gray-900 md:text-2xl lg:text-3xl  text-center ">
    {{/* @var \App\Models\Event $event */ $event->description }} season {{ implode('/', str_split($season->name, 2)) }}<br><br>
        starts at: {{$event->short_description}}, {{$event->first_competition_date->tz('Europe/Riga')->format('H:i \o\n d F Y.')}}
    </h2>


    <br>
    <br>
    <br>
    <br>
    <h1 class="mb-4 mt-4 text-4xl leading-none tracking-tight text-gray-900 md:text-2xl lg:text-3xl  text-center" style="color: #020288">
        <p>Only
        <p id="countdown"></p>
        <p>or</p>
        <p id="countdown3"></p>
        <p>or</p>
        <p id="countdown2"></p>
        left...
        </p>

    </h1>


    <script>
        // Set target date
        // const targetDate = new Date("2025-12-31T23:59:59").getTime();
        const targetDate = new Date("{{$event->first_competition_date->tz('Europe/Riga')->toDateTimeString()}}").getTime();

        const countdown = setInterval(() => {
            const now = new Date();
            let totalSeconds = Math.floor((targetDate - now) / 1000);

            if (totalSeconds < 0) {
                document.getElementById("countdown").innerHTML = "Countdown finished!";
                clearInterval(countdown);
                document.getElementById("countdown2").innerText = "0";
                document.getElementById("countdown3").innerText = "0 days, 0 seconds";
                return;
            }

            // Approximations: 1 month = 30 days, 1 week = 7 days
            const secondsInMonth = 30 * 24 * 60 * 60;
            const secondsInWeek = 7 * 24 * 60 * 60;
            const secondsInDay = 24 * 60 * 60;
            const secondsInHour = 60 * 60;
            const secondsInMinute = 60;

            const months = Math.floor(totalSeconds / secondsInMonth);
            totalSeconds %= secondsInMonth;

            const weeks = Math.floor(totalSeconds / secondsInWeek);
            totalSeconds %= secondsInWeek;

            const days = Math.floor(totalSeconds / secondsInDay);
            totalSeconds %= secondsInDay;

            const hours = Math.floor(totalSeconds / secondsInHour);
            totalSeconds %= secondsInHour;

            const minutes = Math.floor(totalSeconds / secondsInMinute);
            const seconds = totalSeconds % secondsInMinute;

            document.getElementById("countdown").innerHTML =
                `${months} months ${weeks} weeks ${days} days ${hours} hours ${minutes} minutes ${seconds} seconds`;


            const totalSeconds2 = Math.floor((targetDate - now) / 1000);
            document.getElementById("countdown2").innerText = totalSeconds2.toLocaleString() + ' seconds';



            const days3 = Math.floor(totalSeconds2 / (60 * 60 * 24));
            const seconds3 = totalSeconds % (60 * 60 * 24); // seconds remaining after full days

            document.getElementById("countdown3").innerText =
                `${days3} day${days3 !== 1 ? 's' : ''} and ${seconds3} second${seconds3 !== 1 ? 's' : ''}`;


        }, 1000);


        // const countdown = setInterval(() => {
        //     const now = new Date();
        //     const totalSeconds = Math.floor((targetDate - now) / 1000);
        //
        //     if (totalSeconds <= 0) {
        //         clearInterval(countdown);
        //         document.getElementById("countdown").innerText = "0";
        //     } else {
        //         document.getElementById("countdown").innerText = totalSeconds.toLocaleString();
        //     }
        // }, 1000);
    </script>
@endsection
