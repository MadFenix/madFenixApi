<?php

namespace App\Console\Commands;

use App\Modules\Blockchain\Block\Domain\HederaQueue as HederaQueueDomain;
use Illuminate\Console\Command;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class QueueHedera extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:hedera';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consume Hedera queue';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $messagesQueueHedera = HederaQueueDomain::where('done', '=', false)->where('transaction_id', '=', null)->get();

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

            if ($messageQueueHedera->piezas_de_oro_ft > 0 && empty($messageQueueHedera->transaction_id)) {
                $this->line('Run hedera transfer');
                $this->line('User id: ' . $messageQueueHedera->user_id);
                $this->line('Id hedera: ' . $messageQueueHedera->id_hedera);
                $this->line('Oro: ' . $messageQueueHedera->piezas_de_oro_ft);
                $process = new Process([$node, base_path() . '/node_scripts/hedera_transfer_oro_out.cjs', 'receiver_account_id=' . $messageQueueHedera->id_hedera, 'oro=' . $messageQueueHedera->piezas_de_oro_ft, 'queue_hedera_id=' . $messageQueueHedera->id]);

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

            if ($messageQueueHedera->nft_identification_id > 0 && empty($messageQueueHedera->transaction_id)) {
                $nftIdentification = $messageQueueHedera->nft_identification();
                if (empty($nftIdentification)) {
                    $messageQueueHedera->transaction_id = 'Error';
                    $messageQueueHedera->attempts += 1;
                    $messageQueueHedera->save();
                }
                $nft = $nftIdentification->nft();
                if (empty($nft)) {
                    $messageQueueHedera->transaction_id = 'Error';
                    $messageQueueHedera->attempts += 1;
                    $messageQueueHedera->save();
                }
                $this->line('Run hedera transfer');
                $this->line('User id: ' . $messageQueueHedera->user_id);
                $this->line('Id hedera: ' . $messageQueueHedera->id_hedera);
                $this->line('NFT: ' . $nft->short_description . ' - ' . $nft->token_props . '.' . $nft->token_realm . '.' . $nft->token_number . ' - ' . $nftIdentification->nft_identification);
                $process = new Process([$node, base_path() . '/node_scripts/hedera_transfer_nft_out.cjs', 'receiver_account_id=' . $messageQueueHedera->id_hedera, 'token_props=' . $nft->token_props, 'token_realm=' . $nft->token_realm, 'token_number=' . $nft->token_number, 'nft_identification=' . $nftIdentification->nft_identification, 'queue_hedera_id=' . $messageQueueHedera->id]);

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
