<?php

namespace App\Console\Commands;

use App\Modules\Blockchain\Block\Domain\HederaQueue as HederaQueueDomain;
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

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $accountId = env('HEDERA_ACCOUNT_ID');
        $tokenId = env('HEDERA_TOKEN_ID_PLUMAS_PROPS') . '.' . env('HEDERA_TOKEN_ID_PLUMAS_REALM') . '.' . env('HEDERA_TOKEN_ID_PLUMAS_NUMBER');
        $hederaTransactions = file_get_contents('https://mainnet-public.mirrornode.hedera.com/api/v1/transactions?account.id=' . $accountId . '&transactiontype=CRYPTOTRANSFER&result=success');

        $executableFinder = new ExecutableFinder();
        $node = $executableFinder->find('node');

        foreach ($messagesQueueHedera as $messageQueueHedera) {
            if ($messageQueueHedera->plumas > 0 && empty($messageQueueHedera->transaction_id)) {
                $this->line('Run hedera transfer');
                $this->line('User id: ' . $messageQueueHedera->user_id);
                $this->line('Id hedera: ' . $messageQueueHedera->id_hedera);
                $this->line('Plumas: ' . $messageQueueHedera->plumas);
                $process = new Process([$node, base_path() . '/node_scripts/hedera_transfer_out.cjs', 'receiver_account_id=' . $messageQueueHedera->id_hedera, 'plumas=' . $messageQueueHedera->plumas, 'queue_hedera_id=' . $messageQueueHedera->id]);

                try {
                    $process->run(function ($type, $buffer):void {
                        if (Process::ERR === $type) {
                            $this->line('ERR > '.$buffer);
                        }
                    });
                } catch (\Exception $e) {
                    $this->line($e->getMessage());
                }

                $buffer = $process->getOutput();
                $this->line($buffer);
                $arguments = explode("\n", $buffer);
                if (count($arguments) >= 3 && $arguments[0] == 'SUCCESS') {
                    $transactionId = trim($arguments[2]);
                    $messageQueueHedera->transaction_id = $transactionId;
                    $messageQueueHedera->attempts += 1;
                    $messageQueueHedera->done = true;
                    $messageQueueHedera->save();
                } elseif (count($arguments) >= 3 && $arguments[0] == 'FAIL') {
                    $transactionId = trim($arguments[2]);
                    $messageQueueHedera->transaction_id = $transactionId;
                    $messageQueueHedera->attempts += 1;
                    $messageQueueHedera->save();
                } else {
                    $messageQueueHedera->transaction_id = 'Error';
                    $messageQueueHedera->attempts += 1;
                    $messageQueueHedera->save();
                }
            }
        }
    }
}
