@extends('layouts.admin', ['heading' => isset($helper) ? $helper->title(): ''])

@section('content')

    <h1 class="mb-4 mt-4 text-2xl font-extrabold leading-none tracking-tight text-gray-900 md:text-2xl lg:text-3xl  text-center ">
        Prediction totals</h1>

    <h2 class="mb-4 mt-4 text-2xl font-extrabold leading-none tracking-tight text-gray-900 md:text-2xl lg:text-3xl  text-center ">
        BMW IBU World Cup Biathlon, season 24/25</h2>

    <div class="px-2 py-2">
        <div id="totals">

            <table border="1" style="border-collapse: collapse;"
                   class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700"
            >
                <thead>
                <tr>
                    @foreach($data['events'] ?? [] as $dataItem)
                        <th scope="col"
                            class="px-1 py-2 text-start text-xs font-medium  text-gray-500 uppercase dark:text-neutral-500"
                        >
                            {{$dataItem}}
                        </th>
                    @endforeach
                </tr>
                </thead>
                <tbody>

                @foreach($data['users'] as $user)
                    <tr class="odd:bg-white even:bg-gray-100">
                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">{{$user['name'] ?? '-'}}</td>
                        @foreach($user['events'] as $userEvent)
                            <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                                <div hx-get="{{route('forecasts.summary.user-event', ['userId' => $user['id'] ?? 'y', 'eventId' => $userEvent['eventId'] ?? 'x'])}}"
                                    hx-target="#user-event"
                                   class="cursor-pointer"
                                >
                				@if ($isWinner = (($userEvent['winner'] ?? false) == true))
                                <div class="relative inline-block group">
                                    <span class="inline-flex items-center rounded-md border border-yellow-400 bg-yellow-100 px-1 py-0.5 -ml-1">
                                @endif
                                    <span class="underline decoration-gray-500 hover:decoration-blue-600">{{$userEvent['regular'] ?? 0}} <small>+{{$userEvent['bonus'] ?? 0}}</small></span>
                				@if ($isWinner)
                                    </span>
                                    <div class="absolute invisible group-hover:visible opacity-0 group-hover:opacity-100 transition bottom-full left-1/2 -translate-x-1/2 mb-2 px-3 py-1 bg-gray-900 text-white text-sm rounded whitespace-nowrap">
                                        Winner
                                        <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                    </div>
                                </div>
                                @endif

                                </a>
                            </td>
                        @endforeach

                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium"><strong>{{$user['total']['regular'] ?? 0 }}</strong></td>
                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium"><strong>{{$user['total']['bonus'] ?? 0 }}</strong></td>
                        <td class="px-2 py-2 whitespace-nowrap text-sm font-medium"><strong>{{$user['total']['total'] ?? 0 }}</strong></td>
                    </tr>

                @endforeach
                </tbody>
            </table>

        </div>

        <div id="user-event">

        </div>
    </div>
@endsection
