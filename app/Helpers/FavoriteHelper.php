<?php

namespace App\Helpers;

use App\Enums\FavoriteTypeEnum;
use App\Models\Athlete;
use App\Models\Favorite;
use App\Models\User;

class FavoriteHelper
{
    use InstanceTrait;

    protected bool $wasAdded = false;

    public function toogle(
        User $user,
        FavoriteTypeEnum $type,
        int $typeId,
    ): self {
        if (!$favorite = Favorite::query()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->where('type_id', $typeId)
            ->first()
        ) {
            return $this->add(user: $user, type: $type, typeId: $typeId);
        }

        $this->remove(favorite: $favorite);

        return $this;
    }

    public function isFavoriteAfterAction(): bool
    {
        return $this->wasAdded;
    }

    public function getUserFavoriteAthletesId(User $user): array
    {
        return $this->getUserFavoriteIds($user, FavoriteTypeEnum::ATHLETE);
    }

    public function getUserFavoriteIds(User $user, FavoriteTypeEnum $type): array
    {
        return match ($type) {
            FavoriteTypeEnum::ATHLETE => Favorite::query()
                ->where('user_id', $user->id)
                ->where('type', $type)
                ->pluck('type_id')
                ->all(),
            default => [],
        };
    }

    protected function add(
        User $user,
        FavoriteTypeEnum $type,
        mixed $typeId,
    ): self {
        $favorite = new Favorite;
        $favorite->user_id = $user->id;
        $favorite->type = $type;
        $favorite->type_id = $typeId;
        $favorite->save();
        $this->wasAdded = true;
        return $this;
    }

    protected function remove(Favorite $favorite): self
    {
        $favorite->delete();
        $this->wasAdded = false;
        return $this;
    }
}
