<?php

namespace App\Console\Commands;

use App\Models\Season;
use App\Services\BiathlonResultApi;
use Illuminate\Console\Command;

class ReadSeasonsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:read-seasons';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach (range(1957, now()->format('Y')) as $value){

            $current = $value;
            $next = $value + 1;

            $seasonRemoteId = sprintf('%s%s', substr(strval($current), 2), substr($next, 2));

            /** @var Season $season */
            $season = Season::query()->where('name_remote', $seasonRemoteId)->first();

            if(!$season){
                $season = new Season;
                $season->name_remote = $seasonRemoteId;
                $season->name = $seasonRemoteId;
                $season->save();
            }
        }
    }
}
