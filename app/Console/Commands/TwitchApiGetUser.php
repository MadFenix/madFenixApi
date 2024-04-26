<?php

namespace App\Console\Commands;

use App\Modules\Game\Ranking\Domain\Tournament;
use App\Modules\Game\Ranking\Domain\TournamentUser;
use App\Modules\Twitch\Infrastructure\ApiHelix;
use App\Modules\User\Domain\User;
use Illuminate\Console\Command;

class TwitchApiGetUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get-twitch-user {user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Twitch user information';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $user = $this->argument('user');

        $apiHelix = new ApiHelix();

        $oAuthTokenCreated = $apiHelix->createOAuthTokenApp();

        if ($oAuthTokenCreated) {
            var_dump($apiHelix->getUser($user));
        }
    }
}
