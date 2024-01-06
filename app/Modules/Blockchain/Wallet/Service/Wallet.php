<?php


namespace App\Modules\Blockchain\Wallet\Service;


use App\Modules\Blockchain\Block\Domain\Block;
use App\Modules\Blockchain\Block\Service\Blockchain;
use App\Modules\User\Domain\User;

class Wallet
{
    /**
     * Store transfer to bd
     *
     * @param User $user
     * @param User $userToTransfer
     * @param $amount
     * @throws \Exception
     */
    public function storeTransferZenToBd(User $user, User $userToTransfer, $amount)
    {
        $userWaller = $user->ownWallets()->first();
        $userToTransferWaller = $userToTransfer->ownWallets()->first();

        $userWaller->balance -= $amount;
        if ($userWaller->balance < 0) {
            throw new \Exception('User have not enough balance');
        }
        $userToTransferWaller->balance += $amount;

        $userWaller->save();
        $userToTransferWaller->save();
    }

    /**
     * Store transfer to blockchain
     *
     * @param User $user
     * @param User $userToTransfer
     * @param $amount
     */
    public function storeTransferZenToBlockchain(User $user, User $userToTransfer, $amount)
    {
        $userWaller = $user->ownWallets()->first();
        $userToTransferWaller = $userToTransfer->ownWallets()->first();

        $blockchain = new Blockchain();

        $transfer = new \stdClass();
        $transfer->user_id = $user->id;
        $transfer->user_id_receiving_transfer = $userToTransfer->id;
        $transfer->amount = $amount;
        $transfer->user_balance = $userWaller->balance;
        $transfer->user_balance_receiving_transfer = $userToTransferWaller->balance;
        $block = new Block($transfer);

        $blockchain->addBlock($block);
    }
}
