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
                $process->run(function ($type, $buffer):void {
                    if (Process::ERR === $type) {
                        $this->line('ERR > '.$buffer);
                    }
                });

                $buffer = $process->getOutput();
                $this->line($buffer);
                $arguments = explode("\n", $buffer);
                if (count($arguments) >= 3 && $arguments[0] == 'SUCCESS') {
                    $hederaQueue = HederaQueueDomain::find(trim($arguments[1]));
                    $transactionId = trim($arguments[2]);
                    $hederaQueue->transaction_id = $transactionId;
                    $hederaQueue->done = true;
                    $hederaQueue->save();
                } elseif (count($arguments) >= 3 && $arguments[0] == 'FAIL') {
                    $hederaQueue = HederaQueueDomain::find(trim($arguments[1]));
                    $transactionId = trim($arguments[2]);
                    $hederaQueue->transaction_id = $transactionId;
                    $hederaQueue->attempts += 1;
                    $hederaQueue->save();
                }
            }
        }
    }
}
