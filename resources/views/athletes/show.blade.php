@extends('layouts.admin', ['heading' => $athlete->family_name .' '.$athlete->given_name])

@section('content')

    <style>

    </style>

    <div>
        @php
            try {
                getimagesize($athlete->photo_uri);
                echo '<img src="'.$athlete->photo_uri .'" alt="'.$athlete->family_name.'" style="height: 200px;margin: 0 auto" />';
            } catch (Exception){
            }
        @endphp

        <div class="relative flex flex-col w-full h-full overflow-scroll text-gray-700">
            <table class="w-full text-left table-auto min-w-max">
                <tbody>
                <tr class="hover:bg-slate-50">
                    <td class="p-4 border-b border-slate-200">
                        <p class="block text-sm text-slate-800 text-right">
                            Name:
                        </p>
                    </td>
                    <td class="p-4 border-b border-slate-200">
                        <p class="block text-sm text-slate-800">
                            {{$athlete->family_name}} {{$athlete->given_name}}
                        </p>
                    </td>
                </tr>
                <tr class="hover:bg-slate-50">
                    <td class="p-4 border-b border-slate-200">
                        <p class="block text-sm text-slate-800 text-right">
                            Country:
                        </p>
                    </td>
                    <td class="p-4 border-b border-slate-200">
                        <p class="block text-sm text-slate-800">
                            <img src="{!! $athlete->flag_uri !!}" style="height: 20px;">
                        </p>
                    </td>
                </tr>
                <tr class="hover:bg-slate-50">
                    <td class="p-4 border-b border-slate-200">
                        <p class="block text-sm text-slate-800 text-right">
                            Nationality:
                        </p>
                    </td>
                    <td class="p-4 border-b border-slate-200">
                        <p class="block text-sm text-slate-800">
                            {{$athlete->nat}}
                        </p>
                    </td>
                </tr>
                <tr class="hover:bg-slate-50">
                    <td class="p-4 border-b border-slate-200">
                        <p class="block text-sm text-slate-800 text-right">
                            DOB:
                        </p>
                    </td>
                    <td class="p-4 border-b border-slate-200">
                        <p class="block text-sm text-slate-800">
                            {{$athlete->birth_date?->format('d.m.Y')}}
                        </p>
                    </td>
                </tr>
                <tr class="hover:bg-slate-50">
                    <td class="p-4 border-b border-slate-200">
                        <p class="block text-sm text-slate-800 text-right">
                            Age
                        </p>
                    </td>
                    <td class="p-4 border-b border-slate-200">
                        <p class="block text-sm text-slate-800">
                            {{$athlete->birth_date?->age}}
                        </p>
                    </td>
                </tr>

                </tbody>
            </table>
        </div>

        <h1 class="mb-4 mt-4 text-1xl font-extrabold leading-none tracking-tight text-gray-900 md:text-1xl lg:text-2xl  text-center">Results</h1>

        <div class="relative flex flex-col w-full h-full overflow-scroll text-gray-700">
            <table class="w-full text-left table-auto min-w-max">
                <thead>
                <tr>
                    <th>Rank</th>
                    <th>Competition</th>
                    <th>Shooting</th>
                    <th>Behind</th>
                </tr>
                </thead>
                <tbody>
                @php /** @var $result \App\Models\EventCompetitionResult */ @endphp
                @php /** @var $linkHelper \App\Helpers\LinkHelper */ @endphp
                @foreach($athlete->results as $i => $result)

                <tr class="hover:bg-slate-50">
                    <td class="p-4 border-b border-slate-200">
                        <p class="block text-sm text-slate-800">
                            @if($result->rank == 1) {{\App\Enums\RankEnum::PLACE1->getMedal()}}
                            @elseif($result->rank == 2) {{\App\Enums\RankEnum::PLACE2->getMedal()}}
                            @elseif($result->rank == 3) {{\App\Enums\RankEnum::PLACE3->getMedal()}}
                            @elseif($result->rank)
                                {!! $linkHelper->getLink(route: route('competitions.show', $result->competition->race_remote_id),name: $result->rank) !!}
                            @else
                              DNF
                            @endif
                        </p>
                    </td>
                    <td class="p-4 border-b border-slate-200">
                        <p class="block text-sm text-slate-800">
                            {!! $linkHelper->getLink(route: route('competitions.show', $result->competition->race_remote_id),name: $result->competition->getTitle()) !!}
                        </p>
                    </td>
                    <td class="p-4 border-b border-slate-200">
                        <p class="block text-sm text-slate-800">
                            {!! $linkHelper->getLink(route: route('competitions.show', $result->competition->race_remote_id),name: $result->shootings) !!}

                        </p>
                    </td>
                    <td class="p-4 border-b border-slate-200">
                        <p class="block text-sm text-slate-800">
                            {!! $linkHelper->getLink(route: route('competitions.show', $result->competition->race_remote_id),name: $result->behind) !!}
                        </p>
                    </td>
                </tr>

                @endforeach
                </tbody>
            </table>
        </div>


    </div>

@endsection
