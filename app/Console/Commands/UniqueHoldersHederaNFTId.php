<?php

namespace App\Console\Commands;

use App\Modules\Blockchain\Block\Domain\BlockchainHistorical;
use App\Modules\Blockchain\Block\Domain\HederaQueue as HederaQueueDomain;
use App\Modules\Blockchain\Block\Domain\Nft;
use App\Modules\Blockchain\Block\Domain\NftIdentification;
use App\Modules\Game\Profile\Domain\Profile;
use Illuminate\Console\Command;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class UniqueHoldersHederaNFTId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hedera-unique-holders {nft_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get unique holders from NFT id Hedera';

    protected function consumePageFromHederaHoldersNFTId($url) {
        $hederaNfts = json_decode(file_get_contents($url));

        $holdersId = [];
        foreach ($hederaNfts->nfts as $hederaNft) {
            $holdersId[] = $hederaNft->account_id;
        }

        if (!empty($hederaNfts->links->next)) {
            $holdersId = array_merge($holdersId, $this->consumePageFromHederaHoldersNFTId('https://mainnet-public.mirrornode.hedera.com' . $hederaNfts->links->next));
        }

        return $holdersId;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $nftId = $this->argument('nft_id');

        $holdersId = $this->consumePageFromHederaHoldersNFTId('https://mainnet-public.mirrornode.hedera.com/api/v1/tokens/' . $nftId . '/nfts');
        $holdersId = array_values(array_unique($holdersId));

        foreach ($holdersId as $holderId) {
            echo $holderId . "\n";
        }
    }
}
