<?php

namespace App\Jobs;

use App\Enums\SystemTenantEnum;
use App\Models\User;
use App\Models\UserLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UserLogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(protected string $requestPath, protected  string $requestMethod, protected User $user)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if($this->user->tenant === SystemTenantEnum::ADMIN){
            return;
        }
        $userLog = new UserLog;
        $userLog->user_id = $this->user->id;
        $userLog->method = $this->requestMethod;
        $userLog->path = substr($this->requestPath, 0, 255);
        $userLog->save();
    }
}
