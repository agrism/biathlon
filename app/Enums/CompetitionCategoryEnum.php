<?php

namespace App\Enums;

/** data in event_competitions.cat_remote_id table */
enum CompetitionCategoryEnum: string
{
    case SENIOR_MAN = 'SM';
    case SENIOR_WOMAN = 'SW';
    case JUNIOR_MAN = 'JM';
    case JUNIOR_WOMAN = 'JW';

    case YOUTH_MAN = 'YM';
    case YOUTH_WOMAN = 'YW';
    case MIX = 'MX';
    case MIX_JUNIOR = 'JX';
    case BOYS_GIRLS = 'BG';
    case BOYS_MAN = 'BM';
    case GIRLS_WOMAN = 'GW';

    case MIX_YOUTH = 'YX';

    public function getGender(): GenderEnum
    {
        return match ($this){
            self::SENIOR_MAN,
            self::JUNIOR_MAN,
            self::BOYS_MAN,
            self::YOUTH_MAN => GenderEnum::MAN,
            self::SENIOR_WOMAN,
            self::JUNIOR_WOMAN,
            self::GIRLS_WOMAN,
            self::YOUTH_WOMAN => GenderEnum::WOMAN,
            self::MIX,
            self::MIX_JUNIOR,
            self::MIX_YOUTH => GenderEnum::MIXED,
        };
    }

}
