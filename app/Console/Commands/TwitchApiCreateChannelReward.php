<?php

namespace App\Console\Commands;

use App\Modules\Game\Profile\Domain\Profile;
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
    protected $signature = 'get-twitch-create-channel-reward {profile_id?}';

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
        $profileId = $this->argument('profile_id');

        $apiHelix = new ApiHelix();

        if ($profileId) {
            $profile = Profile::where('user_id', '=', $profileId)->first();
            if (!$profile) {
                throw new \Exception('Profile not found');
            }
            $apiHelix->setProfile($profile);
        }

        echo json_encode($apiHelix->createChannelReward());
    }
}
