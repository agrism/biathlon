@php
    $selectedUserObj = null;
    if (isset($displayUser)) {
        if ($displayUser instanceof \App\Models\User) {
            $selectedUserObj = $forecast->final_data->getUserByUserModel($displayUser);
        } elseif ($displayUser instanceof \App\ValueObjects\Helpers\Forecasts\FinalDataValueObject\UserValueObject) {
            $selectedUserObj = $displayUser;
        }
    } elseif (auth()->check()) {
        $selectedUserObj = $forecast->final_data->getUserByUserModel(auth()->user());
    } elseif (!empty($forecast->final_data->users)) {
        $selectedUserObj = $forecast->final_data->users[0];
    }
@endphp

@if($selectedUserObj)
    <div class="grid xs:grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-6 gap-1" id="selected-athletes">
        @foreach($selectedUserObj->getAthletes() ?? [] as $index => $athlete)
            <x-cards.athlete
                :athlete="$athlete"
                :index="$index"
                :forecast="$forecast"
                :favoriteAthleteIds="$favoriteAthleteIds"
            ></x-cards.athlete>
        @endforeach
    </div>
@else
    <div class="p-8 rounded-3xl bg-slate-50 border border-slate-200/80 text-center" id="selected-athletes">
        <p class="text-xs text-slate-500 font-medium">
            <a href="{{ route('login') }}" class="font-bold text-sky-600 hover:text-sky-700 hover:underline">Sign in</a> to view and manage your prediction podium.
        </p>
    </div>
@endif
