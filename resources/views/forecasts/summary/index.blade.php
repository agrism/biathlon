@extends('layouts.admin', ['heading' => isset($helper) ? $helper->title(): ''])

@section('content')
    <h1 class="mb-4 mt-4 text-2xl font-extrabold leading-none tracking-tight text-gray-900 md:text-2xl lg:text-3xl  text-center ">
        Prediction totals
    </h1>


    @foreach($seasons as $index => $season)
        <h2 class="mb-4 mt-4 text-2xl font-extrabold leading-none tracking-tight text-gray-900 md:text-2xl lg:text-3xl  text-center ">
            {{data_get($season, 'title')}}
        </h2>


        <div class="relative" x-data="{ showDetails: false }">
            <input type="checkbox" id="{{data_get($season, 'id')}}" name="{{data_get($season, 'id')}}" class="hidden" x-model="showDetails" >
            <label for="{{data_get($season, 'id')}}" class="cursor-pointer">
                <span class="inline-block">
                    <x-tooltip position="right" text="click to show/hide point details">
                            <i class="fa fa-info-circle text-gray-400"></i>
                    </x-tooltip>

                </span>
            </label>

            <div class="px-2 py-2">
                <div id="totals">

                    <table border="1" style="border-collapse: collapse;"
                           class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700"
                    >
                        <thead>
                        <tr>
                            @foreach($season['data']['events'] ?? [] as $dataItem)
                                <th scope="col"
                                    class="px-1 py-2 text-start text-xs font-medium  text-gray-500 uppercase dark:text-neutral-500"
                                >
                                    {{$dataItem}}
                                </th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>

                        @foreach($season['data']['users'] ?? [] as $user)
                            <tr class="odd:bg-white even:bg-gray-100">
                                <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                                    <strong class="user">{{$user['name'] ?? '-'}}</strong>
                                </td>
                                @foreach($user['events'] as $userEvent)
                                    <td class="px-2 py-1 whitespace-nowrap text-sm font-medium">
                                        <div
                                            hx-get="{{route('forecasts.summary.user-event', ['userId' => $user['id'] ?? 'y', 'eventId' => $userEvent['eventId'] ?? 'x'])}}"
                                            hx-target="#user-event"
                                            class="cursor-pointer py-1 px-1"
                                        >
                                            <x-tooltip text="Winner" :show="$isWinner = ($userEvent['winner'] ?? false) == true">
                                                <span class="underline decoration-gray-500 hover:decoration-blue-600 @if($isWinner) rounded-md border border-yellow-400 bg-yellow-100 px-1 py-1 @endif">
                                                    {{($userEvent['regular'] ?? 0) + ($userEvent['bonus'] ?? 0)}}
                                                </span>
                                            </x-tooltip>
                                            <small x-show="showDetails" class="block pt-[2px] text-gray-400">{{$userEvent['regular'] ?? 0}}+{{$userEvent['bonus'] ?? 0}}</small>
                                        </div>

                                        </a>
                                    </td>
                                @endforeach

                                <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                                    <strong>{{$user['total']['regular'] ?? 0 }}</strong></td>
                                <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                                    <strong>{{$user['total']['bonus'] ?? 0 }}</strong></td>
                                <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                                    <strong>{{$user['total']['total'] ?? 0 }}</strong></td>
                            </tr>

                        @endforeach
                        </tbody>
                    </table>

                </div>

                @if($index===0)
                <div id="user-event">

                </div>
                @endif
            </div>
        </div>

    @endforeach
@endsection
