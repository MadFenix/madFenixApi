<?php

namespace App\Console\Commands;

use App\Modules\Game\Ranking\Domain\Tournament;
use App\Modules\Game\Ranking\Domain\TournamentUser;
use App\Modules\Twitch\Infrastructure\ApiHelix;
use App\Modules\User\Domain\User;
use Illuminate\Console\Command;

class TwitchApiGetUserFollowers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get-twitch-user-followers {user_access_token?} {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Twitch user followers';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $userAccessToken = $this->argument('user_access_token');
        $user_id = $this->argument('user_id');

        $apiHelix = new ApiHelix();

        if ($userAccessToken) {
            $apiHelix->setTwitchAccessToken($userAccessToken);
        }

        if ($user_id) {
            $apiHelix->setTwitchUserId($user_id);
        }

        echo json_encode($apiHelix->getUserFollowers());
    }
}
