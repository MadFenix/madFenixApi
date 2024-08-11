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

class HoldersAndQuantityHederaNFTId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hedera-holders-quantity {nft_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get holders and quantity from NFT id Hedera';

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

        $holders = [];
        foreach ($holdersId as $holderId) {
            $holderFind = false;
            foreach ($holders as $holder) {
                if ($holder->id == $holderId) {
                    $holderFind = true;
                    $holder->quantity++;
                }
            }
            if (!$holderFind) {
                $newHolder = new \stdClass();
                $newHolder->id = $holderId;
                $newHolder->quantity = 1;
                $holders[] = $newHolder;
            }
        }

        echo json_encode($holders);
    }
}
