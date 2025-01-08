<?php

namespace App\Casts;

use App\Models\Athlete;
use App\ValueObjects\Athletes\AthleteDetailsValueObject;
use App\ValueObjects\Athletes\Details\BadgeItemValueObject;
use App\ValueObjects\Athletes\Details\BibItemValueObject;
use App\ValueObjects\Athletes\Details\ItemValueObject;
use App\ValueObjects\Athletes\Details\OwgItemValueObject;
use App\ValueObjects\Athletes\Details\RaceItemValueObject;
use App\ValueObjects\Athletes\Details\RNKItemValueObject;
use App\ValueObjects\Helpers\Forecasts\ForecastFirstSixPlacesDataValueObject;
use Exception;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class AthleteDetailsCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param array<string, mixed> $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $details = json_decode(data_get($attributes, 'details'), true);

        $details = is_array($details) ? $details : [];

        return  static::createDetails($details);

    }

    /**
     * Prepare the given value for storage.
     *
     * @param array<string, mixed> $attributes
     * @throws Exception
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value instanceof AthleteDetailsValueObject) {
            return json_encode($value->export());
        }

        throw new Exception('incorrect instance passed 345');
    }

    public static function createDetails(array $details): AthleteDetailsValueObject
    {
        $owgFactory = function ($data): OwgItemValueObject {
            return new OwgItemValueObject(
                Year: data_get($data, 'Year'),
                SeasonId: data_get($data, 'SeasonId'),
                Place: data_get($data, 'Place'),
                Ind: data_get($data, 'Ind'),
                Spr: data_get($data, 'Spr'),
                Pur: data_get($data, 'Pur'),
                Mas: data_get($data, 'Mas'),
                Rel: data_get($data, 'Rel'),
                MxRel: data_get($data, 'MxRel'),
                SxRel: data_get($data, 'SxRel'),
                Tot_Id: data_get($data, 'Tot_Id'),
                Ind_Id: data_get($data, 'Ind_Id'),
                Spr_Id: data_get($data, 'Spr_Id'),
                Pur_Id: data_get($data, 'Pur_Id'),
                Mas_Id: data_get($data, 'Mas_Id'),
                Rel_Id: data_get($data, 'Rel_Id'),
                MxRel_Id: data_get($data, 'MxRel_Id'),
                SxRel_Id: data_get($data, 'SxRel_Id'),
                Tot: data_get($data, 'Tot'),
                Tot_Score: data_get($data, 'Tot_Score'),
            );
        };

        return new AthleteDetailsValueObject(
            IBUId: data_get($details, 'IBUId'),
            FullName: data_get($details, 'FullName'),
            FamilyName: data_get($details, 'FamilyName'),
            GivenName: data_get($details, 'GivenName'),
            otherFamilyNames: data_get($details, 'otherFamilyNames'),
            otherGivenNames: data_get($details, 'otherGivenNames'),
            NAT: data_get($details, 'NAT'),
            NF: data_get($details, 'NF'),
            Birthdate: data_get($details, 'Birthdate'),
            BirthYear: data_get($details, 'BirthYear'),
            Age: data_get($details, 'Age'),
            GenderId: data_get($details, 'GenderId'),
            Functions: data_get($details, 'Functions'),
            PhotoURI: data_get($details, 'PhotoURI'),
            FlagURI: data_get($details, 'FlagURI'),
            Bibs: collect(data_get($details, 'Bibs', []))->map(fn($d) => new BibItemValueObject(
                Code: data_get($d, 'Code'),
                Color: data_get($d, 'Color'),
                Description: data_get($d, 'Description'),
            ))->toArray(),
            Personal: collect(data_get($details, 'Personal', []))->map(fn($d) => new ItemValueObject(
                id: data_get($d, 'id'),
                Description: data_get($d, 'Description'),
                Value: data_get($d, 'Value'),
            ))->toArray(),
            Sport: collect(data_get($details, 'Sport', []))->map(fn($d) => new ItemValueObject(
                id: data_get($d, 'id'),
                Description: data_get($d, 'Description'),
                Value: data_get($d, 'Value'),
            ))->toArray(),
            Equipment: collect(data_get($details, 'Equipment', []))->map(fn($d) => new ItemValueObject(
                id: data_get($d, 'id'),
                Description: data_get($d, 'Description'),
                Value: data_get($d, 'Value'),
            ))->toArray(),
            Stats: collect(data_get($details, 'Stats', []))->map(fn($d) => new ItemValueObject(
                id: data_get($d, 'id'),
                Description: data_get($d, 'Description'),
                Value: data_get($d, 'Value'),
            ))->toArray(),
            Stories: collect(data_get($details, 'Stories', []))->map(fn($d) => new ItemValueObject(
                id: data_get($d, 'id'),
                Description: data_get($d, 'Description'),
                Value: data_get($d, 'Value'),
            ))->toArray(),
            Recent: collect(data_get($details, 'Recent', []))->map(fn($d) => new RaceItemValueObject(
                RaceId: data_get($d, 'RaceId'),
                SeasonId: data_get($d, 'SeasonId'),
                Season: data_get($d, 'Season'),
                Comp: data_get($d, 'Comp'),
                Competition: data_get($d, 'Competition'),
                Level: data_get($d, 'Level'),
                Place: data_get($d, 'Place'),
                PlaceNat: data_get($d, 'PlaceNat'),
                Rank: data_get($d, 'Rank'),
                SO: data_get($d, 'SO'),
                Pen: data_get($d, 'Pen'),
                Shootings: data_get($d, 'Shootings'),
            ))->toArray(),
            OWG: collect(data_get($details, 'OWG', []))->map(fn($d) => $owgFactory($d))->toArray(),
            WCH: collect(data_get($details, 'WCH', []))->map(fn($d) => $owgFactory($d))->toArray(),
            JWCH: collect(data_get($details, 'JWCH', []))->map(fn($d) => $owgFactory($d))->toArray(),
            WC: collect(data_get($details, 'WC', []))->map(fn($d) => $owgFactory($d))->toArray(),
            IC: collect(data_get($details, 'IC', []))->map(fn($d) => $owgFactory($d))->toArray(),
            JC: collect(data_get($details, 'JC', []))->map(fn($d) => $owgFactory($d))->toArray(),
            Podiums: data_get($details, 'Podiums', []),
            CompetitionRankings: data_get($details, 'CompetitionRankings'),
            IBUCupScores: data_get($details, 'IBUCupScores'),
            TopResults: collect(data_get($details, 'TopResults', []))->map(fn($d) => new RaceItemValueObject(
                RaceId: data_get($d, 'RaceId'),
                SeasonId: data_get($d, 'SeasonId'),
                Season: data_get($d, 'Season'),
                Comp: data_get($d, 'Comp'),
                Competition: data_get($d, 'Competition'),
                Level: data_get($d, 'Level'),
                Place: data_get($d, 'Place'),
                PlaceNat: data_get($d, 'PlaceNat'),
                Rank: data_get($d, 'Rank'),
                SO: data_get($d, 'SO'),
                Pen: data_get($d, 'Pen'),
                Shootings: data_get($d, 'Shootings'),
            ))->toArray(),
            StatSeasons: data_get($details, 'StatSeasons', []),
            StatShooting: data_get($details, 'StatShooting', []),
            StatShootingProne: data_get($details, 'StatShootingProne', []),
            StatShootingStanding: data_get($details, 'StatShootingStanding', []),
            StatSkiing: data_get($details, 'StatSkiing', []),
            StatSkiKMB: data_get($details, 'StatSkiKMB', []),
            StatStarts: data_get($details, 'StatStarts', []) ?: [],
            StatLevel: (array)data_get($details, 'StatLevel', []) ?: [],
            RNKS: collect(data_get($details, 'RNKS', []))->map(fn($d) => new RNKItemValueObject(
                Description: data_get($d, 'Description'),
                Individual: data_get($d, 'Individual'),
                Sprint: data_get($d, 'Sprint'),
                Pursuit: data_get($d, 'Pursuit'),
                MassStart: data_get($d, 'MassStart'),
                IndividualTotal: data_get($d, 'IndividualTotal'),
                Team: data_get($d, 'Team'),
                Relay: data_get($d, 'Relay'),
                MxRelay: data_get($d, 'MxRelay'),
                SingleMxRelay: data_get($d, 'SingleMxRelay'),
                Total: data_get($d, 'Total'),
                Total_WC: data_get($d, 'Total_WC'),
                Total_WCH: data_get($d, 'Total_WCH'),
                Total_OWG: data_get($d, 'Total_OWG'),
                All_WC: data_get($d, 'All_WC'),
                All_WCH: data_get($d, 'All_WCH'),
                All_OWG: data_get($d, 'All_OWG'),
            ))->toArray(),
            Badges: collect(data_get($details, 'Badges', []))->map(fn($d) => new BadgeItemValueObject(
                Code: data_get($d, 'Code'),
                Description: data_get($d, 'Description'),
                Value: data_get($d, 'Value'),
            ))->toArray(),
        );
    }
}
