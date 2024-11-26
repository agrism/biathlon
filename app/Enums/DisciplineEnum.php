<?php

namespace App\Enums;


/** data in event_competitions.discipline_remote_id table */
enum DisciplineEnum: string
{
    case INDIVIDUAL_CLASSIC_COMPETITION = 'IN';
    case RELAY_COMPETITION = 'RL';
    case SPRINT_COMPETITION = 'SP';
    case PATROL_COMPETITION = 'PT';
    case TEAM_COMPETITION = 'TM';
    case PURSUIT_COMPETITION = 'PU';
    case MASS_START = 'MS';
    case SINGLE_RELAY_MIXED = 'SR';
    case INDIVIDUAL_SHORT_COMPETITION = 'SI';
    case SUPER_SPRINT_QUALIFICATION_HEAT1 = 'H1';
    case SUPER_SPRINT_QUALIFICATION_HEAT2 = 'H2';
    case SUPER_SPRINT_QUALIFICATION_HEAT3 = 'H3';
    case SUPER_SPRINT_QUALIFICATION_HEAT4 = 'H4';
    case MASS_START_60 = 'M6';
    case MASS_START_HEAT_B = 'MB';
    case MASS_START_GALA = 'MG';
    case SUPER_SPRINT_QUALIFICATION1 = 'Q1';
    case SUPER_SPRINT_QUALIFICATION2 = 'Q2';
    case SUPER_SPRINT_QUALIFICATION3 = 'Q3';

    case SUPER_SPRINT_SEMIFINAL1 = 'S1';
    case SUPER_SPRINT_SEMIFINAL2 = 'S2';
    case SUPER_SPRINT_FINAL = 'SF';
    case SUPER_SPRINT_QUALIFICATION = 'SQ';

    public function isTeamDiscipline(): bool
    {
        return match ($this){
            self::RELAY_COMPETITION,
            self::TEAM_COMPETITION,
            self::SINGLE_RELAY_MIXED => true,
            default => false,
        };
    }
}
