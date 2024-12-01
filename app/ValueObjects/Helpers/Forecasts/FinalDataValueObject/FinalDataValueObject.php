<?php

namespace App\ValueObjects\Helpers\Forecasts\FinalDataValueObject;

use App\Models\User;
use App\ValueObjects\Helpers\Forecasts\ForecastDataAbstractionValueObject;

class FinalDataValueObject extends ForecastDataAbstractionValueObject
{
    /**
     * @param AthleteValueObject[] $results
     * @param UserValueObject[] $users
     */
    public function __construct(
        public array $results = [],
        public array $users = []
    ) {
    }

    public function updateUser(UserValueObject $user): void
    {
        foreach ($this->users as &$existingUser){
            if($user->id === $existingUser->id){
                $existingUser = $user;
                return;
            }
        }

        $this->users[] = $user;
    }

    public function getUserByUserModel(User $user): ?UserValueObject
    {
        foreach ($this->users as $existingUser){
            if($existingUser->id === $user->id){
                return $existingUser;
            }
        }
        return new UserValueObject(
            id: $user->id,
            name: $user->name,
        );
    }

    public function export(): array
    {
        return [
            'results' => collect($this->results)->map(fn(AthleteValueObject $athlete) => $athlete->export())->toArray(),
            'users' => collect($this->users)->map(fn(UserValueObject $user) => $user->export())->toArray(),
        ];
    }
}
