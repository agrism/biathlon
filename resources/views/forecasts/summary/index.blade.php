@extends('layouts.admin', ['heading' => isset($helper) ? $helper->title(): ''])

<?php

$summary_table = array();
$winners_table = array();

$row = 0;
foreach($data['users'] as $user) {

	$column = 0;
	$summary_table[$row] = array();

	foreach($user['events'] as $userEvent) {

		$summary_table[$row][$column] = ($userEvent['regular'] ?? 0) + ($userEvent['bonus'] ?? 0);
		$column++;
	}

$row++;
}

$row = 0;
foreach($data['users'] as $user) {

	$column = 0;
	$winners_table[$user['name']] = array();

	foreach($user['events'] as $userEvent) {

	$winner = true;

	for ($i = 0; $i < count($summary_table); $i++) {
		if ($summary_table[$row][$column] < $summary_table[$i][$column]) { $winner = false; }
	}
	$winners_table[$user['name']][$userEvent['eventId']] = $winner;

	$column++;
	}
$row++;
}

?>

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
                                <a hx-get="{{route('forecasts.summary.user-event', ['userId' => $user['id'] ?? 'y', 'eventId' => $userEvent['eventId'] ?? 'x'])}}"
                                    hx-target="#user-event"
                                   class="cursor-pointer"
                                >
                				@if ($isWinner = $winners_table[$user['name']][$userEvent['eventId']] == true)
                                <u>
                                @endif

                                {{$userEvent['regular'] ?? 0}} <small>+{{$userEvent['bonus'] ?? 0}}</small>

                				@if ($isWinner)
                                </u>
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
