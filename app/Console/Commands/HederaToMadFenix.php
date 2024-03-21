<?php

namespace App\Console\Commands;

use App\Modules\Blockchain\Block\Domain\BlockchainHistorical;
use App\Modules\Blockchain\Block\Domain\HederaQueue as HederaQueueDomain;
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
                    $blockchainHistorical = BlockchainHistorical::where('memo', '=', $transaction->transaction_id)->first();
                    if ($blockchainHistorical) {
                        break;
                    }

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
                $oroASumar = (int) ($oroASumar / 1000);
                if ($oroASumar > 0 && $totalTokens == 0) {
                    $blockchainHistorical = BlockchainHistorical::where('memo', '=', $transaction->transaction_id)->first();
                    if ($blockchainHistorical) {
                        break;
                    }

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
            }
            $transactionsExecuted++;
        }

        if ($transactionsExecuted != count($hederaTransactions->transactions) && !empty($hederaTransactions->links->next)) {
            $this->consumePageFromHederaAccountTransactions('https://mainnet-public.mirrornode.hedera.com' . $hederaTransactions->links->next, $accountId, $tokenIdPlumas, $tokenIdOro);
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
    }
}
