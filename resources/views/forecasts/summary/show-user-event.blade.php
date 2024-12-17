<div>
    <h3 class="mb-4 mt-4 text-lg font-extrabold leading-none tracking-tight text-gray-900  text-center ">{{$user->name}}: {{$event->title()}}</h3>
    <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
        <thead>
            <tr>
                <th>Competition</th>
                <th>Points</th>
                <th>Bonus</th>
                <th>Total</th>
            </tr>
        </thead>

        <tbody>
        @php
            $totalRegularPoints = 0;
            $totalBonusPoints = 0;
            $totalPoints = 0;
        @endphp
        @foreach($event->competitions as $competition)
            <tr class="odd:bg-white even:bg-gray-100">
                <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">{!! $competition->getTitle() !!}</td>
                <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">{!! $competition->forecast->awards->first()?->user?->name !!}</td>
                <td class="px-2 py-2 whitespace-nowrap text-sm font-medium text-right">{{$regular = $competition->forecast->awards->where('type', \App\Enums\Forecast\AwardPointEnum::REGULAR_POINT)->first()?->points}}</td>
                <td class="px-2 py-2 whitespace-nowrap text-sm font-medium text-right">{{$bonus = $competition->forecast->awards->where('type', \App\Enums\Forecast\AwardPointEnum::BONUS_POINT)->first()?->points}}</td>
                <td class="px-2 py-2 whitespace-nowrap text-sm font-medium text-right">{{$total = $regular + $bonus}}</td>
                @php
                    $totalRegularPoints += $regular;
                    $totalBonusPoints += $bonus;
                    $totalPoints += $total;
                @endphp
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th></th>
                <th class="text-right">{{$totalRegularPoints}}</th>
                <th class="text-right">{{$totalBonusPoints}}</th>
                <th class="text-right">{{$totalPoints}}</th>
            </tr>
        </tfoot>
    </table>
</div>
