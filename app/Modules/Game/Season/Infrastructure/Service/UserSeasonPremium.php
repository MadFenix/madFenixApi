<?php

namespace App\Modules\Game\Season\Infrastructure\Service;

use App\Modules\Blockchain\Block\Domain\NftIdentification;

class UserSeasonPremium
{
    static public function isUserSeasonPremium($profile) {
        if ($profile->season_premium) {
            return true;
        }

        $dragonCustodioHolder = NftIdentification::where('nft_id', 3)
            ->where(function ($query) use($profile) {
                $query->where('user_id', '=', $profile->user_id)
                    ->orWhere('user_id_hedera', '=', $profile->user_id);
            })
            ->first();
        if ($dragonCustodioHolder) {
            return true;
        }

        return false;
    }
}
