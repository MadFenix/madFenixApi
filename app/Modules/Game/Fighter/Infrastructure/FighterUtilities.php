<?php
namespace App\Modules\Twitch\Infrastructure;

use App\Modules\Blockchain\Block\Domain\NftIdentification;
use App\Modules\Game\Fighter\Domain\FighterUser;
use App\Modules\User\Domain\User;
use Carbon\Carbon;

class FighterUtilities
{
    static function getExtraNFTs(User $user): string
    {
        $fighterNFTsExtra = NftIdentification::
            where(function ($query) use($user) {
                $query->where('user_id', '=', $user->id)
                    ->orWhere('user_id_hedera', '=', $user->id);
            })
            ->where('nft_id', '=', 2)
            ->get();
        $fighterNFTsExtraString = '';
        foreach ($fighterNFTsExtra as $fighterNFTExtra) {
            $fighterNFTsExtraString .= $fighterNFTExtra->nft_identification . ',';
        }
        if ($fighterNFTsExtraString) {
            $fighterNFTsExtraString = substr($fighterNFTsExtraString, 0, -1);
        }

        return $fighterNFTsExtraString;
    }

    static function getFighterUserTransformer(User $user, FighterUser $fighterUser): \stdClass
    {
        $fighterNFTsExtraString = FighterUtilities::getExtraNFTs($user);

        $returnFighterUser = new \stdClass();
        $returnFighterUser->ownership_cards = $fighterNFTsExtraString;
        $returnFighterUser->avatar_image = $fighterUser->avatar_image;
        $returnFighterUser->avatar_frame = $fighterUser->avatar_frame;
        $returnFighterUser->action_frame = $fighterUser->action_frame;
        $returnFighterUser->card_frame = $fighterUser->card_frame;
        $returnFighterUser->game_arena = $fighterUser->game_arena;
        $returnFighterUser->cups = $fighterUser->cups;
        $returnFighterUser->rank = $fighterUser->rank;
        $returnFighterUser->decks_available = $fighterUser->decks_available;
        $returnFighterUser->deck_current = $fighterUser->deck_current;
        $returnFighterUser->deck_1 = $fighterUser->deck_1;
        $returnFighterUser->deck_2 = $fighterUser->deck_2;
        $returnFighterUser->deck_3 = $fighterUser->deck_3;
        $returnFighterUser->deck_4 = $fighterUser->deck_4;
        $returnFighterUser->deck_5 = $fighterUser->deck_5;
        $returnFighterUser->deck_6 = $fighterUser->deck_6;
        $returnFighterUser->deck_7 = $fighterUser->deck_7;
        $returnFighterUser->deck_8 = $fighterUser->deck_8;
        $returnFighterUser->deck_9 = $fighterUser->deck_9;
        $returnFighterUser->deck_10 = $fighterUser->deck_10;
        $returnFighterUser->ready_to_play = $fighterUser->ready_to_play;
        $returnFighterUser->ready_to_play_last = $fighterUser->ready_to_play_last;
        $returnFighterUser->playing_with_user = $fighterUser->playing_with_user;
        $returnFighterUser->playing_deck = $fighterUser->playing_deck;
        $returnFighterUser->playing_hand = $fighterUser->playing_hand;
        $returnFighterUser->playing_shift = $fighterUser->playing_shift;
        $returnFighterUser->playing_hp = $fighterUser->playing_hp;
        $returnFighterUser->playing_pa = $fighterUser->playing_pa;
        $returnFighterUser->playing_card_left = $fighterUser->playing_card_left;
        $returnFighterUser->playing_card_center = $fighterUser->playing_card_center;
        $returnFighterUser->playing_card_right = $fighterUser->playing_card_right;
        $returnFighterUser->playing_card_left_back = $fighterUser->playing_card_left_back;
        $returnFighterUser->playing_card_center_back = $fighterUser->playing_card_center_back;
        $returnFighterUser->playing_card_right_back = $fighterUser->playing_card_right_back;

        return $returnFighterUser;
    }

    static function getFighterUserDeck($fighterUserInfo): string
    {
        $deckNumber = 'deck_' . $fighterUserInfo->deck_current;

        return $fighterUserInfo->$deckNumber;
    }

    static function getCommonCards(): array
    {
        return [2003,2164,2197,2284,2334,2517,2747,2893,2954,3053,3091,3163,3204,
            3258,3326,3389,3469,3541,3813,3916,3950,4060,4132,4380,4524,4604,
            4766,4988,5261,5308,5580,5612,5676,5746,5789,5828,5939,6171,6235,
            6259,6381,6445,6735,6858,7122];
    }

    static function getUserIdBots(): array
    {
        return [258,259,260,261,262,263,264,265,266,267,268,269,270,271,272,273,274,275,276,277];
    }
}