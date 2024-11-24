<?php

namespace App\Enums;


enum RankEnum: int
{
    case PLACE1 = 1;
    case PLACE2 = 2;
    case PLACE3 = 3;
    case PLACE4 = 4;
    case PLACE5 = 5;
    case PLACE6 = 6;

    public function getMedal():string
    {
        return match ($this){
          self::PLACE1 =>   '🥇',
          self::PLACE2 =>   '🥈',
          self::PLACE3 =>   '🥉',
          self::PLACE4,
          self::PLACE5,
          self::PLACE6 =>   '💐',
          default =>   '-',
        };
    }
}
