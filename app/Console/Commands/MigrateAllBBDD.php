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

class MigrateAllBBDD extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate-all-bbdd';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate all BBDD';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $identifications = Identification::all();

        foreach ($identifications as $identification) {
            $isConnected = AccountManager::connectToAccount(new Request(), $identification->account);
            if ($isConnected) {
                $this->info("Migrate account: {$identification->account}");
                Artisan::call('migrate', [
                    '--database' => 'tenant',
                    '--force'    => true, // Obligatorio para evitar confirmaciones en producción
                ]);
            }
        }
    }
}
