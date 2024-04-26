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

class HederaToMadFenix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hedera-to-mad-fenix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trnasfer from Hedera to Mad Fénix';

    protected function consumePageFromHederaAccountTransactions($url, $accountId, $tokenIdPlumas, $tokenIdOro) {
        $hederaTransactions = json_decode(file_get_contents($url));

        $transactionsExecuted = 0;
        foreach ($hederaTransactions->transactions as $transaction) {
            $transactionMemo = strtolower(base64_decode($transaction->memo_base64));
            $transactionMemo = explode(':', $transactionMemo);
            if (count($transactionMemo) == 2 && (trim($transactionMemo[0]) == 'deposito' || trim($transactionMemo[0]) == 'depósito')) {
                $blockchainHistorical = BlockchainHistorical::where('memo', '=', $transaction->transaction_id)->first();
                if ($blockchainHistorical) {
                    break;
                }

                $plumasASumar = 0;
                $oroASumar = 0;
                $totalTokens = 0;
                foreach ($transaction->token_transfers as $token_transfer) {
                    if ($token_transfer->token_id == $tokenIdPlumas) {
                        $totalTokens += $token_transfer->amount;

                        if ($token_transfer->amount > 0 && $token_transfer->account == $accountId) {
                            $plumasASumar += $token_transfer->amount;
                        }
                    }
                    if ($token_transfer->token_id == $tokenIdOro) {
                        $totalTokens += $token_transfer->amount;

                        if ($token_transfer->amount > 0 && $token_transfer->account == $accountId) {
                            $oroASumar += $token_transfer->amount;
                        }
                    }
                }
                $plumasASumar = (int) $plumasASumar;
                if ($plumasASumar > 0 && $totalTokens == 0) {
                    $profile = Profile::where('user_id', '=', trim($transactionMemo[1]))->first();
                    if ($profile) {
                        $profile->plumas += $plumasASumar;
                        $profile->save();
                    }

                    $newBlockchainHistorical = new BlockchainHistorical();
                    $newBlockchainHistorical->user_id = $transactionMemo[1];
                    $newBlockchainHistorical->plumas = $plumasASumar;
                    $newBlockchainHistorical->memo = $transaction->transaction_id;
                    $newBlockchainHistorical->save();

                    $this->line('Ingreso. Usuario: ' . trim($transactionMemo[1]) . '. Plumas: ' . $plumasASumar);
                }
                $oroASumar = (int) ($oroASumar / 10000);
                if ($oroASumar > 0 && $totalTokens == 0) {
                    $profile = Profile::where('user_id', '=', trim($transactionMemo[1]))->first();
                    if ($profile) {
                        $profile->oro += $oroASumar;
                        $profile->save();
                    }

                    $newBlockchainHistorical = new BlockchainHistorical();
                    $newBlockchainHistorical->user_id = $transactionMemo[1];
                    $newBlockchainHistorical->piezas_de_oro_ft = $oroASumar;
                    $newBlockchainHistorical->memo = $transaction->transaction_id;
                    $newBlockchainHistorical->save();

                    $this->line('Ingreso. Usuario: ' . trim($transactionMemo[1]) . '. Oro: ' . $oroASumar);
                }

                foreach ($transaction->nft_transfers as $nft_transfer) {
                    $tokenIdArray = explode('.', $nft_transfer->token_id);
                    if (count($tokenIdArray) < 3) {
                        continue;
                    }
                    $nft = Nft::where('token_props', '=', trim($tokenIdArray[0]))
                        ->where('token_realm', '=', trim($tokenIdArray[1]))
                        ->where('token_number', '=', trim($tokenIdArray[2]))
                        ->first();
                    if (!$nft) {
                        continue;
                    }
                    $nftIdentification = NftIdentification::where('nft_identification', '=', $nft_transfer->serial_number)
                        ->where('nft_id', '=', $nft->id)
                        ->first();
                    if (!$nftIdentification) {
                        continue;
                    }
                    $nftIdentification->user_id = $transactionMemo[1];
                    $nftIdentification->madfenix_ownership = false;
                    $nftIdentification->save();

                    $newBlockchainHistorical = new BlockchainHistorical();
                    $newBlockchainHistorical->user_id = $transactionMemo[1];
                    $newBlockchainHistorical->nft_identification_id = $nftIdentification->id;
                    $newBlockchainHistorical->memo = $transaction->transaction_id;
                    $newBlockchainHistorical->save();

                    $this->line('Ingreso. Usuario: ' . trim($transactionMemo[1]) . '. NFT: ' . $nftIdentification->name . '. Serial: ' . $nftIdentification->nft_identification . '.');
                }
            }
            $transactionsExecuted++;
        }

        if ($transactionsExecuted != count($hederaTransactions->transactions) && !empty($hederaTransactions->links->next)) {
            $this->consumePageFromHederaAccountTransactions('https://mainnet-public.mirrornode.hedera.com' . $hederaTransactions->links->next, $accountId, $tokenIdPlumas, $tokenIdOro);
        }
    }

    protected function consumePageFromHederaFindNftTransaction($url, $hederaNft, $nftIdentification, $nftTokenId, $accountId) {
        $hederaTransactionNft = json_decode(file_get_contents($url));

        if (
            !isset($hederaTransactionNft->transactions) ||
            count($hederaTransactionNft->transactions) < 1 ||
            !isset($hederaTransactionNft->transactions[0]->transaction_id)
        ) {
            return false;
        }

        $transaction = json_decode(file_get_contents('https://mainnet-public.mirrornode.hedera.com/api/v1/transactions/' . $hederaTransactionNft->transactions[0]->transaction_id));

        $transactionMemo = strtolower(base64_decode($transaction->memo_base64));
        $transactionMemo = explode(':', $transactionMemo);
        if (count($transactionMemo) == 2 && (trim($transactionMemo[0]) == 'deposito' || trim($transactionMemo[0]) == 'depósito')) {
            $blockchainHistorical = BlockchainHistorical::where('memo', '=', $transaction->transaction_id)->first();
            if ($blockchainHistorical) {
                return false;
            }

            foreach ($transaction->nft_transfers as $nft_transfer) {
                if ($nft_transfer->token_id != $nftTokenId) {
                    continue;
                }
                if ($nft_transfer->serial_number != $nftIdentification->nft_identification) {
                    continue;
                }
                $nftIdentification->user_id = $transactionMemo[1];
                $nftIdentification->madfenix_ownership = false;
                $nftIdentification->save();

                $newBlockchainHistorical = new BlockchainHistorical();
                $newBlockchainHistorical->user_id = $transactionMemo[1];
                $newBlockchainHistorical->nft_identification_id = $nftIdentification->id;
                $newBlockchainHistorical->memo = $transaction->transaction_id;
                $newBlockchainHistorical->save();

                $this->line('Ingreso. Usuario: ' . trim($transactionMemo[1]) . '. NFT: ' . $nftIdentification->name . '. Serial: ' . $nftIdentification->nft_identification . '.');
            }
        }
    }

    protected function consumePageFromHederaAccountListNft($url, $nftIdentifications, $nftTokenId, $accountId) {
        $hederaNfts = json_decode(file_get_contents($url));

        $nftsExecuted = 0;
        foreach ($hederaNfts->nfts as $hederaNft) {
            foreach ($nftIdentifications as $nftIdentification) {
                if ($hederaNft->serial_number == $nftIdentification->nft_identification) {
                    $this->consumePageFromHederaFindNftTransaction(
                        'https://mainnet-public.mirrornode.hedera.com/api/v1/tokens/' . $nftTokenId . '/nfts/' . $nftIdentification->nft_identification . '/transactions?limit=1&order=desc',
                        $hederaNft,
                        $nftIdentification,
                        $nftTokenId,
                        $accountId
                    );
                }
            }
            $nftsExecuted++;
        }

        if ($nftsExecuted != count($hederaNfts->nfts) && !empty($hederaTransactions->links->next)) {
            $this->consumePageFromHederaAccountListNft('https://mainnet-public.mirrornode.hedera.com' . $hederaTransactions->links->next, $nftIdentifications, $nftTokenId, $accountId);
        }
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $accountId = env('HEDERA_ACCOUNT_ID');
        $tokenIdPlumas = env('HEDERA_TOKEN_ID_PLUMAS_PROPS') . '.' . env('HEDERA_TOKEN_ID_PLUMAS_REALM') . '.' . env('HEDERA_TOKEN_ID_PLUMAS_NUMBER');
        $tokenIdOro = env('HEDERA_TOKEN_ID_ORO_PROPS') . '.' . env('HEDERA_TOKEN_ID_ORO_REALM') . '.' . env('HEDERA_TOKEN_ID_ORO_NUMBER');

        $this->consumePageFromHederaAccountTransactions('https://mainnet-public.mirrornode.hedera.com/api/v1/transactions?account.id=' . $accountId . '&transactiontype=CRYPTOTRANSFER&result=success', $accountId, $tokenIdPlumas, $tokenIdOro);

        $nfts = Nft::all();
        foreach ($nfts as $nft) {
            $nftTokenId = $nft->token_props . '.' . $nft->token_realm . '.' . $nft->token_number;
            $nftIdentifications = NftIdentification::where('nft_id', '=', $nft->id)
                ->whereNull('user_id')
                ->where('madfenix_ownership', '=', '0')
                ->get();

            $this->consumePageFromHederaAccountListNft(
                'https://mainnet-public.mirrornode.hedera.com/api/v1/tokens/' . $nftTokenId . '/nfts?account.id=' . $accountId . '&limit=50',
                $nftIdentifications,
                $nftTokenId,
                $accountId
            );
        }
    }
}
