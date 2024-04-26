<?php

namespace App\Console\Commands;

use App\Modules\Game\Ranking\Domain\Tournament;
use App\Modules\Game\Ranking\Domain\TournamentUser;
use App\Modules\Twitch\Infrastructure\ApiHelix;
use App\Modules\User\Domain\User;
use Illuminate\Console\Command;

class TwitchApiGetUserToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get-twitch-user-token {code}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Twitch user auth information';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        /*
         * Authorizations
         * https://id.twitch.tv/oauth2/authorize?response_type=code&client_id=TWITCH_API_KEY&redirect_uri=http://localhost:8000&scope=channel%3Amanage%3Apolls+channel%3Aread%3Apolls+channel%3Amoderate+chat%3Aedit+chat%3Aread+channel%3Amanage%3Aredemptions+moderator%3Aread%3Afollowers
         */
        $code = $this->argument('code');

        $apiHelix = new ApiHelix();

        var_dump($apiHelix->createOAuthTokenUser($code));
    }
}
