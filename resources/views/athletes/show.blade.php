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


    </div>

@endsection
