<?php

namespace App\Console\Commands;

use App\Modules\Game\Ranking\Domain\Tournament;
use App\Modules\Game\Ranking\Domain\TournamentUser;
use App\Modules\Twitch\Infrastructure\ApiHelix;
use App\Modules\User\Domain\User;
use Illuminate\Console\Command;

class TwitchApiGetUserFromToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get-twitch-user-from-token {user_access_token?}';

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

        $apiHelix = new ApiHelix();

        if ($userAccessToken) {
            $apiHelix->setTwitchAccessToken($userAccessToken);
        }

        echo json_encode($apiHelix->getUserFromToken());
    }
}
