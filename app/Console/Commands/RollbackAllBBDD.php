<?php

namespace App\Console\Commands;

use App\Modules\Base\Infrastructure\Service\AccountManager;
use App\Modules\Blockchain\Block\Domain\BlockchainHistorical;
use App\Modules\Game\Profile\Domain\Profile;
use App\Modules\Game\Ranking\Domain\Tournament;
use App\Modules\Game\Ranking\Domain\TournamentUser;
use App\Modules\User\Domain\Identification;
use App\Modules\User\Domain\User;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class RollbackAllBBDD extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rollback-all-bbdd';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback all BBDD';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $identifications = Identification::all();

        foreach ($identifications as $identification) {
            $isConnected = AccountManager::connectToAccount(new Request(), $identification->account);
            if ($isConnected) {
                Artisan::call('migrate:rollback', [
                    '--database' => 'tenant',
                    '--force'    => true, // Obligatorio para evitar confirmaciones en producción
                ]);
            }
        }
    }
}
