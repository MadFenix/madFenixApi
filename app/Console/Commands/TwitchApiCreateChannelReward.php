<?php

namespace App\Console\Commands;

use App\Modules\Game\Ranking\Domain\Tournament;
use App\Modules\Game\Ranking\Domain\TournamentUser;
use App\Modules\Twitch\Infrastructure\ApiHelix;
use App\Modules\User\Domain\User;
use Illuminate\Console\Command;

class TwitchApiCreateChannelReward extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get-twitch-create-channel-reward {user_access_token?} {user_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create Twitch channel reward';

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

        echo json_encode($apiHelix->createChannelReward());
    }
}
