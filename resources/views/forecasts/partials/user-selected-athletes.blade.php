@if(auth()->check())
    <div class="grid xs:grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-6 gap-1">
        @foreach($forecast->final_data->getUserByUserModel(auth()?->user())->getAthletes() ?? [] as $index => $athlete)
                <x-cards.athlete :athlete="$athlete" :index="$index" :forecast="$forecast" :favoriteAthleteIds="$favoriteAthleteIds"></x-cards.athlete>
        @endforeach
    </div>
@endif
