<?php

namespace App\Modules\Blockchain\Block\Infrastructure\Service;

use App\Modules\Blockchain\Block\Domain\Nft;

class UserDragonCustodio
{
    static public function tokenMultiplier($profile) {
        $record = Nft::query()
            ->join('nft_identifications', 'nft_identifications.nft_id', '=', 'nfts.id')
            ->where('nfts.multiplier', '!=', '')
            ->where(function ($query) use ($profile) {
                $query->where('nft_identifications.user_id', $profile->user_id)
                    ->orWhere('nft_identifications.user_id_hedera', $profile->user_id);
            })
            ->orderByDesc('nfts.multiplier')
            ->select('nfts.multiplier')
            ->first();

        $multiplier = $record?->multiplier;

        $multiplierToReturn = 1;
        if ($multiplier) {
            $multiplierToReturn = $multiplier;
        }

        return $multiplierToReturn;
    }
}
