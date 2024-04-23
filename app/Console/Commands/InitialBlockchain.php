<?php

namespace App\Console\Commands;

use App\Modules\Blockchain\Block\Domain\BlockchainHistorical;
use App\Modules\Game\Profile\Domain\Profile;
use Illuminate\Console\Command;

class InitialBlockchain extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'initial-blockchain';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initial Blockchain';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $profiles = Profile::all();

        foreach ($profiles as $profile) {
            $newBlockchainHistorical = new BlockchainHistorical();
            $newBlockchainHistorical->user_id = $profile->user_id;
            $newBlockchainHistorical->plumas = $profile->plumas;
            $newBlockchainHistorical->piezas_de_oro_ft = $profile->oro;
            $newBlockchainHistorical->memo = "Early user";
            //$newBlockchainHistorical->save();
        }
    }
}
