<?php

namespace App\Console\Commands;

use App\Modules\Game\Fighter\Domain\FighterUser;
use App\Modules\Game\Fighter\Infrastructure\FighterUtilities;
use App\Modules\User\Domain\User;
use Illuminate\Console\Command;

class CreateFighterUserBots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create-fighter-user-bots';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create fighter user bots';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $fighterUserBots = FighterUser::
            whereIn('user_id', FighterUtilities::getUserIdBots())
            ->get();

        $userIdBots = FighterUtilities::getUserIdBots();

        foreach ($userIdBots as $userIdBot) {
            $haveFighterUser = false;
            foreach ($fighterUserBots as $fighterUserBot) {
                if ($userIdBot == $fighterUserBot->user_id) {
                    $haveFighterUser = true;
                }
            }
            if (!$haveFighterUser) {
                FighterUtilities::createFighterUser($userIdBot);
            }
        }
    }
}
