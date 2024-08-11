<?php

namespace App\Modules\Blockchain\Block\Infrastructure\Service;

use App\Modules\Blockchain\Block\Domain\NftIdentification;

class UserDragonCustodio
{
    static public function tokenMultiplier($profile) {
        $numberDragonesCustodioHolder = NftIdentification::where('nft_id', 3)
            ->where(function ($query) use($profile) {
                $query->where('user_id', '=', $profile->user_id)
                    ->orWhere('user_id_hedera', '=', $profile->user_id);
            })
            ->count();
        $toAdd = $numberDragonesCustodioHolder * 0.5;
        if ($toAdd > 2) {
            $toAdd = 2;
        }

        return 1 + $toAdd;
    }
}
