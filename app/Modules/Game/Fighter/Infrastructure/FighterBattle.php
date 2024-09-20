<?php
namespace App\Modules\Game\Fighter\Infrastructure;

use App\Modules\Game\Fighter\Domain\FighterPast;
use App\Modules\Game\Fighter\Domain\FighterUser;
use App\Modules\User\Domain\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class FighterBattle
{
    static function checkFighterUserDeck(FighterUser $fighterUser, User $user): bool
    {
        $fighterUserInfo = FighterUtilities::getFighterUserTransformer($user, $fighterUser);

        $currentDeck = FighterUtilities::getFighterUserDeck($fighterUserInfo);
        $currentDeckArray = explode(',', $currentDeck);

        if (count($currentDeckArray) != 30) {
            return false;
        }

        $commonCardsArray = FighterUtilities::getCommonCards();
        $fighterUserCardsArray = explode(',', $fighterUserInfo->ownership_cards);
        $availableCardsArray = array_merge($commonCardsArray, $fighterUserCardsArray);

        $return = true;
        foreach ($currentDeckArray as $currentDeckCard) {
            if (!in_array($currentDeckCard, $availableCardsArray)) {
                $return = false;
            }
        }

        return $return;
    }

    static function findFighterUserToBattle($user)
    {
        $fighterUserToBattle = FighterUser::
            where('ready_to_play', '=', true)
            ->where('user_id', '!=', $user->id)
            ->whereNull('playing_with_user')
            ->where('ready_to_play_last', '>', Carbon::now()->subSeconds(45))
            ->orderBy('ready_to_play_last', 'ASC')
            ->first();

        return $fighterUserToBattle;
    }

    static function findFighterUserBotToBattle()
    {
        $fighterUserToBattle = FighterUser::
            whereNull('playing_with_user')
            ->whereIn('user_id', FighterUtilities::getUserIdBots())
            ->first();

        return $fighterUserToBattle;
    }

    static function saveNewFighterPast(FighterUser $fighterUser1, $gameHash)
    {
        $fighterPast = new FighterPast();
        $fighterPast->user_id = $fighterUser1->user_id;
        $fighterPast->game_hash = $gameHash;
        $fighterPast->avatar_image = $fighterUser1->avatar_image;
        $fighterPast->avatar_frame = $fighterUser1->avatar_frame;
        $fighterPast->action_frame = $fighterUser1->action_frame;
        $fighterPast->card_frame = $fighterUser1->card_frame;
        $fighterPast->game_arena = $fighterUser1->game_arena;
        $fighterPast->decks_available = $fighterUser1->decks_available;
        $fighterPast->deck_current = $fighterUser1->deck_current;
        $fighterPast->ready_to_play = $fighterUser1->ready_to_play;
        $fighterPast->playing_with_user = $fighterUser1->playing_with_user;
        $fighterPast->playing_deck = $fighterUser1->playing_deck;
        $fighterPast->playing_hand = $fighterUser1->playing_hand;
        $fighterPast->playing_shift = $fighterUser1->playing_shift;
        $fighterPast->playing_hp = $fighterUser1->playing_hp;
        $fighterPast->playing_pa = $fighterUser1->playing_pa;
        $fighterPast->playing_card_left = $fighterUser1->playing_card_left;
        $fighterPast->playing_card_center = $fighterUser1->playing_card_center;
        $fighterPast->playing_card_right = $fighterUser1->playing_card_right;
        $fighterPast->playing_card_left_back = $fighterUser1->playing_card_left_back;
        $fighterPast->playing_card_center_back = $fighterUser1->playing_card_center_back;
        $fighterPast->playing_card_right_back = $fighterUser1->playing_card_right_back;

        return $fighterPast->save();
    }

    static function prepareFighterUserToBattle(FighterUser $fighterUser1, FighterUser $fighterUser2, $gameHash, Carbon $battleDate)
    {
        $deckNumber = 'deck_' . $fighterUser1->deck_current;

        $currentDeckArray = explode(',', $fighterUser1->$deckNumber);
        shuffle($currentDeckArray);
        $currentDeck = '';
        foreach ($currentDeckArray as $currentCard) {
            $currentDeck .= $currentCard . ',';
        }
        if ($currentDeck) {
            $currentDeck = substr($currentDeck, 0, -1);
        }

        $fighterUser1->playing_with_user = $fighterUser2->user_id;
        $fighterUser1->playing_deck = $currentDeck;
        $fighterUser1->playing_hand = '';
        $fighterUser1->playing_shift = 1;
        $fighterUser1->playing_shift_resolved = 1;
        $fighterUser1->playing_shift_date = $battleDate;
        $fighterUser1->playing_hp = 17;
        $fighterUser1->playing_pa = 1;
        $fighterUser1->playing_card_left = '0';
        $fighterUser1->playing_card_center = '0';
        $fighterUser1->playing_card_right = '0';
        $fighterUser1->playing_card_left_back = '0';
        $fighterUser1->playing_card_center_back = '0';
        $fighterUser1->playing_card_right_back = '0';

        FighterBattle::drawCardsDeck($fighterUser1, 7);

        return FighterBattle::saveNewFighterPast($fighterUser1, $gameHash);
    }

    static function prepareFighterUsersToBattle(FighterUser $fighterUser1, FighterUser $fighterUser2)
    {
        $battleTime = time();
        $battleDate = new Carbon();
        $gameHash = hash('sha256', $fighterUser1->user_id . '_' . $fighterUser2->user_id . '_' . $battleTime);
        $fighterPast1Save = FighterBattle::prepareFighterUserToBattle($fighterUser1, $fighterUser2, $gameHash, $battleDate);
        $fighterPast2Save = FighterBattle::prepareFighterUserToBattle($fighterUser2, $fighterUser1, $gameHash, $battleDate);

        return $fighterPast1Save && $fighterPast2Save;
    }

    static function checkPlayedCards(FighterUser $fighterUser, $dataPlayedCards): bool
    {
        $currentHand = $fighterUser->playing_hand;
        $currentHandArray = explode(',', $currentHand);

        if ($dataPlayedCards['card_left'] == $fighterUser->playing_card_left) {
            $dataPlayedCards['card_left'] = '0';
        }
        if (!empty($dataPlayedCards['card_left']) && !in_array($dataPlayedCards['card_left'], $currentHandArray)) {
            return false;
        }

        if ($dataPlayedCards['card_center'] == $fighterUser->playing_card_center) {
            $dataPlayedCards['card_center'] = '0';
        }
        if (!empty($dataPlayedCards['card_center']) && !in_array($dataPlayedCards['card_center'], $currentHandArray)) {
            return false;
        }

        if ($dataPlayedCards['card_right'] == $fighterUser->playing_card_right) {
            $dataPlayedCards['card_right'] = '0';
        }
        if (!empty($dataPlayedCards['card_right']) && !in_array($dataPlayedCards['card_right'], $currentHandArray)) {
            return false;
        }

        return true;
    }

    static function drawCardsDeck(FighterUser $fighterUser, $quantityCards)
    {
        $currentHand = $fighterUser->playing_hand;
        $currentHandArray = explode(',', $currentHand);
        if (7 - count($currentHandArray) > $quantityCards) {
            $quantityCards = 7 - count($currentHandArray);
        }

        if ($quantityCards <= 0) {
            return;
        }

        $playingDeckArray = explode(',', $fighterUser->playing_deck);
        if (count($playingDeckArray) < $quantityCards) {
            $quantityCards = count($playingDeckArray);
        }
        if ($quantityCards <= 0) {
            return;
        }
        if ($currentHand) {
            $currentHand .= ',';
        }
        for ($i = 0; $i < $quantityCards; $i++) {
            if (count($playingDeckArray) > 0) {
                $currentHand .= array_shift($playingDeckArray) . ',';
            }
        }
        if ($currentHand) {
            $currentHand = substr($currentHand, 0, -1);
        }
        $fighterUser->playing_hand = $currentHand;

        $playingDeck = '';
        foreach ($playingDeckArray as $cardDeck) {
            $playingDeck .= $cardDeck . ',';
        }
        if ($playingDeck) {
            $playingDeck = substr($playingDeck, 0, -1);
        }
        $fighterUser->playing_deck = $playingDeck;

        $currentHandArray = explode(',', $fighterUser->playing_hand);
        while (count($currentHandArray) > 7) {
            array_shift($currentHandArray);
        }
        $currentHand = '';
        $quantityCardsInHand = count($currentHandArray);
        for ($i = 0; $i < $quantityCardsInHand; $i++) {
            $currentHand .= array_shift($currentHandArray) . ',';
        }
        if ($currentHand) {
            $currentHand = substr($currentHand, 0, -1);
        }
        $fighterUser->playing_hand = $currentHand;
    }

    static function returnCardsToDeck(FighterUser $fighterUser, $quantityCards)
    {
        $currentHand = $fighterUser->playing_hand;
        $currentHandArray = explode(',', $currentHand);
        if (7 - count($currentHandArray) > $quantityCards) {
            $quantityCards = 7 - count($currentHandArray);
        }

        if ($quantityCards <= 0) {
            return;
        }

        $playingDeckArray = explode(',', $fighterUser->playing_deck);
        $playingDeck = '';
        foreach ($playingDeckArray as $cardDeck) {
            $playingDeck .= $cardDeck . ',';
        }
        for ($i = 0; $i < $quantityCards; $i++) {
            if (count($currentHandArray) > 0) {
                $playingDeck .= array_shift($currentHandArray) . ',';
            }
        }
        if ($playingDeck) {
            $playingDeck = substr($playingDeck, 0, -1);
        }
        $fighterUser->playing_deck = $playingDeck;

        $currentHand = '';
        $quantityCardsInHand = count($currentHandArray);
        for ($i = 0; $i < $quantityCardsInHand; $i++) {
            $currentHand .= array_shift($currentHandArray) . ',';
        }
        if ($currentHand) {
            $currentHand = substr($currentHand, 0, -1);
        }
        $fighterUser->playing_hand = $currentHand;

        if (7 - $quantityCardsInHand > 0) {
            FighterBattle::drawCardsDeck($fighterUser, 7 - $quantityCardsInHand);
        }
    }

    static function discardFighterUserDeckCards(FighterUser $fighterUser, $quantity)
    {
        $playingDeckArray = explode(',', $fighterUser->playing_deck);
        for ($i = 0; $i < $quantity; $i++) {
            if (count($playingDeckArray) > 0) {
                array_shift($playingDeckArray);
            }
        }

        $playingDeck = '';
        foreach ($playingDeckArray as $cardDeck) {
            $playingDeck .= $cardDeck . ',';
        }
        if ($playingDeck) {
            $playingDeck = substr($playingDeck, 0, -1);
        }
        $fighterUser->playing_deck = $playingDeck;
    }

    static function discardFighterUserHandCards(FighterUser $fighterUser, $quantity)
    {
        $currentHand = $fighterUser->playing_hand;
        $currentHandArray = explode(',', $currentHand);
        for ($i = 0; $i < $quantity; $i++) {
            if (count($currentHandArray) > 0) {
                array_shift($currentHandArray);
            }
        }

        $quantityCardsInHand = count($currentHandArray);
        $playingHand = '';
        foreach ($currentHandArray as $cardHand) {
            $playingHand .= $cardHand . ',';
        }
        if ($playingHand) {
            $playingHand = substr($playingHand, 0, -1);
        }
        $fighterUser->playing_hand = $playingHand;

        if (7 - $quantityCardsInHand > 0) {
            FighterBattle::drawCardsDeck($fighterUser, 7 - $quantityCardsInHand);
        }
    }

    static function saveFighterUserBattleTurn(FighterUser $fighterUser, $dataPlayedCards)
    {
        $fighterPast = FighterPast::
            where('user_id', '=', $fighterUser->user_id)
            ->where('playing_shift', '=', $fighterUser->playing_shift)
            ->where('created_at', '<', Carbon::now()->subSeconds(8))
            ->orderBy('created_at', 'DESC')
            ->first();

        if (!$fighterPast || $fighterPast->playing_shift != $fighterUser->playing_shift || $fighterPast->playing_with_user != $fighterUser->playing_with_user) {
            return false;
        }

        if (!FighterBattle::checkPlayedCards($fighterUser, $dataPlayedCards)) {
            return false;
        }

        $fighterUser->playing_shift += 1;

        if ($fighterUser->playing_shift == 2) {
            $fighterUser->playing_pa = 2;
        }

        if ($fighterUser->playing_shift >= 3) {
            $fighterUser->playing_pa = 3;
        }

        $fighterUserHandArray = explode(',', $fighterUser->playing_hand);
        if (!empty($dataPlayedCards['card_left']) && in_array($dataPlayedCards['card_left'], $fighterUserHandArray)) {
            foreach ($fighterUserHandArray as $key => $fighterUserCardHand) {
                if ($dataPlayedCards['card_left'] == $fighterUserCardHand) {
                    unset($fighterUserHandArray[$key]);
                }
            }
            $fighterUser->playing_card_left_back = $fighterUser->playing_card_left;
            $fighterUser->playing_card_left = $dataPlayedCards['card_left'];
        } else {
            $fighterUser->playing_card_left_back = $fighterUser->playing_card_left;
        }
        if (!empty($dataPlayedCards['card_center']) && in_array($dataPlayedCards['card_center'], $fighterUserHandArray)) {
            foreach ($fighterUserHandArray as $key => $fighterUserCardHand) {
                if ($dataPlayedCards['card_center'] == $fighterUserCardHand) {
                    unset($fighterUserHandArray[$key]);
                }
            }
            $fighterUser->playing_card_center_back = $fighterUser->playing_card_center;
            $fighterUser->playing_card_center = $dataPlayedCards['card_center'];
        } else {
            $fighterUser->playing_card_center_back = $fighterUser->playing_card_center;
        }
        if (!empty($dataPlayedCards['card_right']) && in_array($dataPlayedCards['card_right'], $fighterUserHandArray)) {
            foreach ($fighterUserHandArray as $key => $fighterUserCardHand) {
                if ($dataPlayedCards['card_right'] == $fighterUserCardHand) {
                    unset($fighterUserHandArray[$key]);
                }
            }
            $fighterUser->playing_card_right_back = $fighterUser->playing_card_right;
            $fighterUser->playing_card_right = $dataPlayedCards['card_right'];
        } else {
            $fighterUser->playing_card_right_back = $fighterUser->playing_card_right;
        }

        if ($fighterUser->playing_deck) {
            if ($fighterUser->playing_shift >= 2 && $fighterUser->playing_shift <= 5) {
                FighterBattle::drawCardsDeck($fighterUser, 2);
            } else if ($fighterUser->playing_shift >= 6) {
                FighterBattle::drawCardsDeck($fighterUser, 3);
            }
        }

        $fighterUserSave = $fighterUser->save();

        if ($fighterUserSave) {
            return FighterBattle::saveNewFighterPast($fighterUser, $fighterPast->game_hash);
        }

        return false;
    }

    static function restoreFighterUserFromFighterPast(FighterUser $fighterUser, FighterPast $fighterPast)
    {
        $fighterUser->playing_deck = $fighterPast->playing_deck;
        $fighterUser->playing_hand = $fighterPast->playing_hand;
        $fighterUser->playing_shift = $fighterPast->playing_shift;
        $fighterUser->playing_hp = $fighterPast->playing_hp;
        $fighterUser->playing_pa = $fighterPast->playing_pa;
        $fighterUser->playing_card_left = $fighterPast->playing_card_left;
        $fighterUser->playing_card_center = $fighterPast->playing_card_center;
        $fighterUser->playing_card_right = $fighterPast->playing_card_right;
    }

    static function checkFighterPastsToBattleTurn(FighterPast $fighterPast1, FighterPast $fighterPast2, FighterUser $fighterUser1, FighterUser $fighterUser2)
    {
        if ($fighterUser1->playing_shift < $fighterUser2->playing_shift) {
            $fighterBattleTurnSave = FighterBattle::saveFighterUserBattleTurn($fighterUser1, [
                'card_left' => 0,
                'card_center' => 0,
                'card_right' => 0
            ]);
            if (!$fighterBattleTurnSave) {
                return false;
            }
            $fighterPast1 = FighterPast::
                where('user_id', '=', $fighterUser1->user_id)
                ->orderBy('created_at', 'DESC')
                ->first();
            if (!$fighterPast1) {
                return false;
            }
        }
        if ($fighterUser1->playing_shift < $fighterUser2->playing_shift) {
            return false;
        }
        if ($fighterUser2->playing_shift < $fighterUser1->playing_shift) {
            $fighterBattleTurnSave = FighterBattle::saveFighterUserBattleTurn($fighterUser2, [
                'card_left' => 0,
                'card_center' => 0,
                'card_right' => 0
            ]);
            if (!$fighterBattleTurnSave) {
                return false;
            }
            $fighterPast2 = FighterPast::
                where('user_id', '=', $fighterUser2->user_id)
                ->orderBy('created_at', 'DESC')
                ->first();
            if (!$fighterPast2) {
                return false;
            }
        }
        if ($fighterUser2->playing_shift < $fighterUser1->playing_shift) {
            return false;
        }

        if ($fighterPast1->playing_shift > $fighterUser1->playing_shift) {
            FighterBattle::restoreFighterUserFromFighterPast($fighterUser1, $fighterPast1);
        }
        if ($fighterPast2->playing_shift > $fighterUser2->playing_shift) {
            FighterBattle::restoreFighterUserFromFighterPast($fighterUser2, $fighterPast2);
        }

        if ($fighterPast1->playing_shift < $fighterUser1->playing_shift) {
            $fighterBattleTurnSave = FighterBattle::saveFighterUserBattleTurn($fighterUser1, [
                'card_left' => 0,
                'card_center' => 0,
                'card_right' => 0
            ]);
            if (!$fighterBattleTurnSave) {
                return false;
            }
            $fighterPast1 = FighterPast::
                where('user_id', '=', $fighterUser1->user_id)
                ->orderBy('created_at', 'DESC')
                ->first();
            if (!$fighterPast1) {
                return false;
            }
        }
        if ($fighterPast1->playing_shift < $fighterUser1->playing_shift) {
            return false;
        }
        if ($fighterPast2->playing_shift < $fighterUser2->playing_shift) {
            $fighterBattleTurnSave = FighterBattle::saveFighterUserBattleTurn($fighterUser2, [
                'card_left' => 0,
                'card_center' => 0,
                'card_right' => 0
            ]);
            if (!$fighterBattleTurnSave) {
                return false;
            }
            $fighterPast2 = FighterPast::
                where('user_id', '=', $fighterUser2->user_id)
                ->orderBy('created_at', 'DESC')
                ->first();
            if (!$fighterPast2) {
                return false;
            }
        }
        if ($fighterPast2->playing_shift < $fighterUser2->playing_shift) {
            return false;
        }

        return true;
    }

    static function resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, &$currentActionPoints, &$damgePoints, $abilityModificator, $damageModificator, $lineDiscardEffect, $discardEffect, $invulnerability, $save = 0)
    {
        if ($lineDiscardEffect || $discardEffect) {
            $abilityModificator = 0;
            $damageModificator = 0;
            $save = 0;
        }
        if (!$onlyModificators) {
            $currentCardParameter = 'playing_card_' . $lane;
            $backCardParameter = 'playing_card_' . $lane . '_back';
            if ($fighterUser1->$currentCardParameter == $fighterUser1->$backCardParameter) {
                return true;
            }
            if ($currentActionPoints < ((int) $contentCard->action_points - $abilityModificator)) {
                if ($lane == 'left') {
                    $fighterUser1->playing_card_left = $fighterUser1->playing_card_left_back;
                }
                if ($lane == 'center') {
                    $fighterUser1->playing_card_center = $fighterUser1->playing_card_center_back;
                }
                if ($lane == 'right') {
                    $fighterUser1->playing_card_right = $fighterUser1->playing_card_right_back;
                }
                return false;
            }
            $currentActionPoints -= (int) $contentCard->action_points - $abilityModificator;
            if (!$invulnerability) {
                $damgePoints += (int) $contentCard->damage + $damageModificator;
            }
            if ($save > 0) {
                $fighterUser1->playing_hp += $save;
            }
        }

        return true;
    }

    static function AbilityWithout($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$invulnerability, &$currentActionPoints, &$currentActionPoints2, &$currentActionPointsModificator,  $onlyModificators)
    {
        if (!$onlyModificators) {
            if ($lane == 'left') {
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $leftAbilityModificator, $leftDamageModificator, $leftDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            if ($lane == 'center') {
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $centerAbilityModificator, $centerDamageModificator, $centerDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            if ($lane == 'right') {
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $rightAbilityModificator, $rightDamageModificator, $rightDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
        }

        return true;
    }

    static function AbilityAdjacentDamagePlusOne($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$invulnerability, &$currentActionPoints, &$currentActionPoints2, &$currentActionPointsModificator,  $onlyModificators)
    {
        if ($onlyModificators) {
            if ($lane == 'left') {
                $centerDamageModificator += 1;
            }
            if ($lane == 'center') {
                $leftDamageModificator += 1;
                $rightDamageModificator += 1;
            }
            if ($lane == 'right') {
                $centerDamageModificator += 1;
            }
        }

        if (!$onlyModificators) {
            if ($lane == 'left') {
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $leftAbilityModificator, $leftDamageModificator, $leftDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            if ($lane == 'center') {
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $centerAbilityModificator, $centerDamageModificator, $centerDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            if ($lane == 'right') {
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $rightAbilityModificator, $rightDamageModificator, $rightDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
        }

        return true;
    }

    static function AbilityAdjacentActionMinusOne($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$invulnerability, &$currentActionPoints, &$currentActionPoints2, &$currentActionPointsModificator,  $onlyModificators)
    {
        if ($onlyModificators) {
            if ($lane == 'left') {
                $centerAbilityModificator += 1;
            }
            if ($lane == 'center') {
                $leftAbilityModificator += 1;
                $rightAbilityModificator += 1;
            }
            if ($lane == 'right') {
                $centerAbilityModificator += 1;
            }
        }

        if (!$onlyModificators) {
            if ($lane == 'left') {
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $leftAbilityModificator, $leftDamageModificator, $leftDiscardOppentEffect, $discardOpponentEffects, $invulnerability, $invulnerability);
            }
            if ($lane == 'center') {
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $centerAbilityModificator, $centerDamageModificator, $centerDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            if ($lane == 'right') {
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $rightAbilityModificator, $rightDamageModificator, $rightDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
        }

        return true;
    }

    static function AbilitySaveOne($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$invulnerability, &$currentActionPoints, &$currentActionPoints2, &$currentActionPointsModificator,  $onlyModificators)
    {
        if (!$onlyModificators) {
            if ($lane == 'left') {
                if ($fighterUser1->playing_card_left == $fighterUser1->playing_card_left_back) {
                    $save = 0;
                } else {
                    $save = 1;
                }
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $leftAbilityModificator, $leftDamageModificator, $leftDiscardOppentEffect, $discardOpponentEffects, $invulnerability, $invulnerability, $save);
            }
            if ($lane == 'center') {
                if ($fighterUser1->playing_card_center == $fighterUser1->playing_card_center_back) {
                    $save = 0;
                } else {
                    $save = 1;
                }
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $centerAbilityModificator, $centerDamageModificator, $centerDiscardOppentEffect, $discardOpponentEffects, $invulnerability, $save);
            }
            if ($lane == 'right') {
                if ($fighterUser1->playing_card_right == $fighterUser1->playing_card_right_back) {
                    $save = 0;
                } else {
                    $save = 1;
                }
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $rightAbilityModificator, $rightDamageModificator, $rightDiscardOppentEffect, $discardOpponentEffects, $invulnerability, $save);
            }
        }

        return true;
    }

    static function AbilityMinusOneToOpponent($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$invulnerability, &$currentActionPoints, &$currentActionPoints2, &$currentActionPointsModificator,  $onlyModificators)
    {
        $resolvedLineDamage = false;
        if (!$onlyModificators) {
            if ($lane == 'left') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $leftAbilityModificator, $leftDamageModificator, $leftDiscardOppentEffect, $discardOpponentEffects, $invulnerability, $invulnerability);
            }
            if ($lane == 'center') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $centerAbilityModificator, $centerDamageModificator, $centerDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            if ($lane == 'right') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $rightAbilityModificator, $rightDamageModificator, $rightDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            $currentCardParameter = 'playing_card_' . $lane;
            $backCardParameter = 'playing_card_' . $lane . '_back';
            if ($resolvedLineDamage && $fighterUser1->$currentCardParameter != $fighterUser1->$backCardParameter) {
                $currentActionPointsModificator -= 1;
            }
            return $resolvedLineDamage;
        }

        return true;
    }

    static function AbilityDiscard2CardsOpponentDeck($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$invulnerability, &$currentActionPoints, &$currentActionPoints2, &$currentActionPointsModificator,  $onlyModificators)
    {
        if (!$onlyModificators) {
            if ($lane == 'left') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $leftAbilityModificator, $leftDamageModificator, $leftDiscardOppentEffect, $discardOpponentEffects, $invulnerability, $invulnerability);
            }
            if ($lane == 'center') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $centerAbilityModificator, $centerDamageModificator, $centerDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            if ($lane == 'right') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $rightAbilityModificator, $rightDamageModificator, $rightDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            $currentCardParameter = 'playing_card_' . $lane;
            $backCardParameter = 'playing_card_' . $lane . '_back';
            if ($resolvedLineDamage && $fighterUser1->$currentCardParameter != $fighterUser1->$backCardParameter) {
                FighterBattle::discardFighterUserDeckCards($fighterUser2, 2);
            }
            return $resolvedLineDamage;
        }

        return true;
    }

    static function AbilityDrawTwoCards($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$invulnerability, &$currentActionPoints, &$currentActionPoints2, &$currentActionPointsModificator,  $onlyModificators)
    {
        if (!$onlyModificators) {
            if ($lane == 'left') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $leftAbilityModificator, $leftDamageModificator, $leftDiscardOppentEffect, $discardOpponentEffects, $invulnerability, $invulnerability);
            }
            if ($lane == 'center') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $centerAbilityModificator, $centerDamageModificator, $centerDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            if ($lane == 'right') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $rightAbilityModificator, $rightDamageModificator, $rightDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            $currentCardParameter = 'playing_card_' . $lane;
            $backCardParameter = 'playing_card_' . $lane . '_back';
            if ($resolvedLineDamage && $fighterUser1->$currentCardParameter != $fighterUser1->$backCardParameter) {
                FighterBattle::drawCardsDeck($fighterUser1, 2);
            }
            return $resolvedLineDamage;
        }

        return true;
    }

    static function AbilityDiscardEffectOpponentLane($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$invulnerability, &$currentActionPoints, &$currentActionPoints2, &$currentActionPointsModificator,  $onlyModificators)
    {
        if ($onlyModificators) {
            if ($lane == 'left') {
                if ($fighterUser1->playing_card_left != $fighterUser1->playing_card_left_back) {
                    $leftDiscardOppentEffect = true;
                }
            }
            if ($lane == 'center') {
                if ($fighterUser1->playing_card_center != $fighterUser1->playing_card_center_back) {
                    $centerDiscardOppentEffect = true;
                }
            }
            if ($lane == 'right') {
                if ($fighterUser1->playing_card_right != $fighterUser1->playing_card_right_back) {
                    $rightDiscardOppentEffect = true;
                }
            }
        }

        if (!$onlyModificators) {
            if ($lane == 'left') {
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $leftAbilityModificator, $leftDamageModificator, $leftDiscardOppentEffect, $discardOpponentEffects, $invulnerability, $invulnerability);
            }
            if ($lane == 'center') {
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $centerAbilityModificator, $centerDamageModificator, $centerDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            if ($lane == 'right') {
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $rightAbilityModificator, $rightDamageModificator, $rightDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
        }

        return true;
    }

    static function AbilitySeeNext5Cards($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$invulnerability, &$currentActionPoints, &$currentActionPoints2, &$currentActionPointsModificator,  $onlyModificators)
    {
        if (!$onlyModificators) {
            if ($lane == 'left') {
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $leftAbilityModificator, $leftDamageModificator, $leftDiscardOppentEffect, $discardOpponentEffects, $invulnerability, $invulnerability);
            }
            if ($lane == 'center') {
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $centerAbilityModificator, $centerDamageModificator, $centerDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            if ($lane == 'right') {
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $rightAbilityModificator, $rightDamageModificator, $rightDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
        }

        return true;
    }

    static function AbilitySaveTwo($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$invulnerability, &$currentActionPoints, &$currentActionPoints2, &$currentActionPointsModificator,  $onlyModificators)
    {
        if (!$onlyModificators) {
            if ($lane == 'left') {
                if ($fighterUser1->playing_card_left == $fighterUser1->playing_card_left_back) {
                    $save = 0;
                } else {
                    $save = 2;
                }
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $leftAbilityModificator, $leftDamageModificator, $leftDiscardOppentEffect, $discardOpponentEffects, $invulnerability, $invulnerability, $save);
            }
            if ($lane == 'center') {
                if ($fighterUser1->playing_card_center == $fighterUser1->playing_card_center_back) {
                    $save = 0;
                } else {
                    $save = 2;
                }
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $centerAbilityModificator, $centerDamageModificator, $centerDiscardOppentEffect, $discardOpponentEffects, $invulnerability, $save);
            }
            if ($lane == 'right') {
                if ($fighterUser1->playing_card_right == $fighterUser1->playing_card_right_back) {
                    $save = 0;
                } else {
                    $save = 2;
                }
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $rightAbilityModificator, $rightDamageModificator, $rightDiscardOppentEffect, $discardOpponentEffects, $invulnerability, $save);
            }
        }

        return true;
    }

    static function AbilityMinusTwoToOpponent($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$invulnerability, &$currentActionPoints, &$currentActionPoints2, &$currentActionPointsModificator,  $onlyModificators)
    {
        if (!$onlyModificators) {
            if ($lane == 'left') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $leftAbilityModificator, $leftDamageModificator, $leftDiscardOppentEffect, $discardOpponentEffects, $invulnerability, $invulnerability);
            }
            if ($lane == 'center') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $centerAbilityModificator, $centerDamageModificator, $centerDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            if ($lane == 'right') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $rightAbilityModificator, $rightDamageModificator, $rightDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            $currentCardParameter = 'playing_card_' . $lane;
            $backCardParameter = 'playing_card_' . $lane . '_back';
            if ($resolvedLineDamage && $fighterUser1->$currentCardParameter != $fighterUser1->$backCardParameter) {
                $currentActionPointsModificator -= 2;
            }
            return $resolvedLineDamage;
        }

        return true;
    }

    static function AbilityDiscard3CardsOpponentDeck($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$invulnerability, &$currentActionPoints, &$currentActionPoints2, &$currentActionPointsModificator,  $onlyModificators)
    {
        if (!$onlyModificators) {
            if ($lane == 'left') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $leftAbilityModificator, $leftDamageModificator, $leftDiscardOppentEffect, $discardOpponentEffects, $invulnerability, $invulnerability);
            }
            if ($lane == 'center') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $centerAbilityModificator, $centerDamageModificator, $centerDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            if ($lane == 'right') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $rightAbilityModificator, $rightDamageModificator, $rightDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            $currentCardParameter = 'playing_card_' . $lane;
            $backCardParameter = 'playing_card_' . $lane . '_back';
            if ($resolvedLineDamage && $fighterUser1->$currentCardParameter != $fighterUser1->$backCardParameter) {
                FighterBattle::discardFighterUserDeckCards($fighterUser2, 3);
            }
            return $resolvedLineDamage;
        }

        return true;
    }

    static function AbilityDiscardEffectOpponent($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$invulnerability, &$currentActionPoints, &$currentActionPoints2, &$currentActionPointsModificator,  $onlyModificators)
    {
        if ($onlyModificators) {
            if ($lane == 'left') {
                if ($fighterUser1->playing_card_left != $fighterUser1->playing_card_left_back) {
                    $discardOpponentEffects = true;
                }
            }
            if ($lane == 'center') {
                if ($fighterUser1->playing_card_center != $fighterUser1->playing_card_center_back) {
                    $discardOpponentEffects = true;
                }
            }
            if ($lane == 'right') {
                if ($fighterUser1->playing_card_right != $fighterUser1->playing_card_right_back) {
                    $discardOpponentEffects = true;
                }
            }
        }

        if (!$onlyModificators) {
            if ($lane == 'left') {
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $leftAbilityModificator, $leftDamageModificator, $leftDiscardOppentEffect, $discardOpponentEffects, $invulnerability, $invulnerability);
            }
            if ($lane == 'center') {
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $centerAbilityModificator, $centerDamageModificator, $centerDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            if ($lane == 'right') {
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $rightAbilityModificator, $rightDamageModificator, $rightDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
        }

        return true;
    }

    static function AbilityDiscardOpponentHand($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$invulnerability, &$currentActionPoints, &$currentActionPoints2, &$currentActionPointsModificator,  $onlyModificators)
    {
        if (!$onlyModificators) {
            if ($lane == 'left') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $leftAbilityModificator, $leftDamageModificator, $leftDiscardOppentEffect, $discardOpponentEffects, $invulnerability, $invulnerability);
            }
            if ($lane == 'center') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $centerAbilityModificator, $centerDamageModificator, $centerDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            if ($lane == 'right') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $rightAbilityModificator, $rightDamageModificator, $rightDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            $currentCardParameter = 'playing_card_' . $lane;
            $backCardParameter = 'playing_card_' . $lane . '_back';
            if ($resolvedLineDamage && $fighterUser1->$currentCardParameter != $fighterUser1->$backCardParameter) {
                FighterBattle::returnCardsToDeck($fighterUser2, 7);
            }
            return $resolvedLineDamage;
        }

        return true;
    }

    static function AbilityDiscardOpponentHandOneCardOnw($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$invulnerability, &$currentActionPoints, &$currentActionPoints2, &$currentActionPointsModificator,  $onlyModificators)
    {
        if (!$onlyModificators) {
            if ($lane == 'left') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $leftAbilityModificator, $leftDamageModificator, $leftDiscardOppentEffect, $discardOpponentEffects, $invulnerability, $invulnerability);
            }
            if ($lane == 'center') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $centerAbilityModificator, $centerDamageModificator, $centerDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            if ($lane == 'right') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $rightAbilityModificator, $rightDamageModificator, $rightDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            $currentCardParameter = 'playing_card_' . $lane;
            $backCardParameter = 'playing_card_' . $lane . '_back';
            if ($resolvedLineDamage && $fighterUser1->$currentCardParameter != $fighterUser1->$backCardParameter) {
                FighterBattle::discardFighterUserHandCards($fighterUser2, 1);
            }
            return $resolvedLineDamage;
        }

        return true;
    }

    static function AbilityDrawThreeCards($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$invulnerability, &$currentActionPoints, &$currentActionPoints2, &$currentActionPointsModificator,  $onlyModificators)
    {
        if (!$onlyModificators) {
            if ($lane == 'left') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $leftAbilityModificator, $leftDamageModificator, $leftDiscardOppentEffect, $discardOpponentEffects, $invulnerability, $invulnerability);
            }
            if ($lane == 'center') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $centerAbilityModificator, $centerDamageModificator, $centerDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            if ($lane == 'right') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $rightAbilityModificator, $rightDamageModificator, $rightDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            $currentCardParameter = 'playing_card_' . $lane;
            $backCardParameter = 'playing_card_' . $lane . '_back';
            if ($resolvedLineDamage && $fighterUser1->$currentCardParameter != $fighterUser1->$backCardParameter) {
                FighterBattle::drawCardsDeck($fighterUser1, 3);
            }
            return $resolvedLineDamage;
        }

        return true;
    }

    static function AbilityAdjacentDamagePlusTwo($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$invulnerability, &$currentActionPoints, &$currentActionPoints2, &$currentActionPointsModificator,  $onlyModificators)
    {
        if ($onlyModificators) {
            if ($lane == 'left') {
                $centerDamageModificator += 2;
            }
            if ($lane == 'center') {
                $leftDamageModificator += 2;
                $rightDamageModificator += 2;
            }
            if ($lane == 'right') {
                $centerDamageModificator += 2;
            }
        }

        if (!$onlyModificators) {
            if ($lane == 'left') {
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $leftAbilityModificator, $leftDamageModificator, $leftDiscardOppentEffect, $discardOpponentEffects, $invulnerability, $invulnerability);
            }
            if ($lane == 'center') {
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $centerAbilityModificator, $centerDamageModificator, $centerDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            if ($lane == 'right') {
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $rightAbilityModificator, $rightDamageModificator, $rightDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
        }

        return true;
    }

    static function AbilityAdjacentActionMinusTwo($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$invulnerability, &$currentActionPoints, &$currentActionPoints2, &$currentActionPointsModificator,  $onlyModificators)
    {
        if ($onlyModificators) {
            if ($lane == 'left') {
                $centerAbilityModificator += 2;
            }
            if ($lane == 'center') {
                $leftAbilityModificator += 2;
                $rightAbilityModificator += 2;
            }
            if ($lane == 'right') {
                $centerAbilityModificator += 2;
            }
        }

        if (!$onlyModificators) {
            if ($lane == 'left') {
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $leftAbilityModificator, $leftDamageModificator, $leftDiscardOppentEffect, $discardOpponentEffects, $invulnerability, $invulnerability);
            }
            if ($lane == 'center') {
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $centerAbilityModificator, $centerDamageModificator, $centerDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            if ($lane == 'right') {
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $rightAbilityModificator, $rightDamageModificator, $rightDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
        }

        return true;
    }

    static function AbilityDiscard4CardsOpponentDeck($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$invulnerability, &$currentActionPoints, &$currentActionPoints2, &$currentActionPointsModificator,  $onlyModificators)
    {
        if (!$onlyModificators) {
            if ($lane == 'left') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $leftAbilityModificator, $leftDamageModificator, $leftDiscardOppentEffect, $discardOpponentEffects, $invulnerability, $invulnerability);
            }
            if ($lane == 'center') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $centerAbilityModificator, $centerDamageModificator, $centerDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            if ($lane == 'right') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $rightAbilityModificator, $rightDamageModificator, $rightDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            $currentCardParameter = 'playing_card_' . $lane;
            $backCardParameter = 'playing_card_' . $lane . '_back';
            if ($resolvedLineDamage && $fighterUser1->$currentCardParameter != $fighterUser1->$backCardParameter) {
                FighterBattle::discardFighterUserDeckCards($fighterUser2, 4);
            }
            return $resolvedLineDamage;
        }

        return true;
    }

    static function AbilityDiscardOpponentHandTwoCardOnw($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$invulnerability, &$currentActionPoints, &$currentActionPoints2, &$currentActionPointsModificator,  $onlyModificators)
    {
        if (!$onlyModificators) {
            if ($lane == 'left') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $leftAbilityModificator, $leftDamageModificator, $leftDiscardOppentEffect, $discardOpponentEffects, $invulnerability, $invulnerability);
            }
            if ($lane == 'center') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $centerAbilityModificator, $centerDamageModificator, $centerDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            if ($lane == 'right') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $rightAbilityModificator, $rightDamageModificator, $rightDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            $currentCardParameter = 'playing_card_' . $lane;
            $backCardParameter = 'playing_card_' . $lane . '_back';
            if ($resolvedLineDamage && $fighterUser1->$currentCardParameter != $fighterUser1->$backCardParameter) {
                FighterBattle::discardFighterUserHandCards($fighterUser2, 2);
            }
            return $resolvedLineDamage;
        }

        return true;
    }

    static function AbilityInvulnerable($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$invulnerability, &$currentActionPoints, &$currentActionPoints2, &$currentActionPointsModificator,  $onlyModificators)
    {
        if ($onlyModificators) {
            $currentCardParameter = 'playing_card_' . $lane;
            $backCardParameter = 'playing_card_' . $lane . '_back';
            if ($fighterUser1->$currentCardParameter != $fighterUser1->$backCardParameter) {

            }
        }

        if (!$onlyModificators) {
            if ($lane == 'left') {
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $leftAbilityModificator, $leftDamageModificator, $leftDiscardOppentEffect, $discardOpponentEffects, $invulnerability, $invulnerability);
            }
            if ($lane == 'center') {
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $centerAbilityModificator, $centerDamageModificator, $centerDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            if ($lane == 'right') {
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $rightAbilityModificator, $rightDamageModificator, $rightDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
        }

        return true;
    }

    static function AbilityDiscardDecks($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$invulnerability, &$currentActionPoints, &$currentActionPoints2, &$currentActionPointsModificator,  $onlyModificators)
    {
        if (!$onlyModificators) {
            if ($lane == 'left') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $leftAbilityModificator, $leftDamageModificator, $leftDiscardOppentEffect, $discardOpponentEffects, $invulnerability, $invulnerability);
            }
            if ($lane == 'center') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $centerAbilityModificator, $centerDamageModificator, $centerDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            if ($lane == 'right') {
                $resolvedLineDamage = FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $rightAbilityModificator, $rightDamageModificator, $rightDiscardOppentEffect, $discardOpponentEffects, $invulnerability);
            }
            $currentCardParameter = 'playing_card_' . $lane;
            $backCardParameter = 'playing_card_' . $lane . '_back';
            if ($resolvedLineDamage && $fighterUser1->$currentCardParameter != $fighterUser1->$backCardParameter) {
                FighterBattle::discardFighterUserDeckCards($fighterUser2, 30);
                FighterBattle::discardFighterUserDeckCards($fighterUser1, 30);
            }
            return $resolvedLineDamage;
        }

        return true;
    }


    static function resolveFighterUserBattleTurn(FighterUser $fighterUser1, FighterUser $fighterUser2, FighterPast $fighterPast1, FighterPast $fighterPast2)
    {
        $abilities = [
            [
                "Without" // => "Sin habilidad"
            ],
            [
                "AdjacentDamagePlusOne", // => "Habilidad contigua +1 al daño de ataque",
                "AdjacentActionMinusOne", // => "Habilidad contigua -1 en acción",
                "SaveOne", // => "Cura 1 punto",
                "MinusOneToOpponent", // => "-1 en acción al contrario en el siguiente turno",
                "Discard2CardsOpponentDeck", // => "Descartas 2 cartas del mazo contrario",
                "DrawTwoCards", // => "Robas 2 cartas en el siguiente turno",
                "DiscardEffectOpponentLane", // => "Descartas el efecto contrario del carril",
                "SeeNext5Cards" // => "Puedes mirar las siguientes 5 cartas de tu mazo"
            ],
            [
                "SaveTwo", // => "Cura 2 puntos",
                "MinusTwoToOpponent", // => "-2 en acción al contrario en el siguiente turno",
                "Discard3CardsOpponentDeck", // => "Descartas 3 cartas del mazo contrario",
                "DiscardEffectOpponent", // => "Descartas el efecto contrario de cualquier carril",
                "DiscardOpponentHand", // => "Al final del turno devuelves la mano contraria al mazo",
                "DiscardOpponentHandOneCardOnw" // => "Descartas una carta de la mano contraria"
            ],
            [
                "DrawThreeCards", // => "Robas 3 cartas en el siguiente turno",
                "AdjacentDamagePlusTwo", // => "Habilidad contigua +2 al daño de ataque",
                "AdjacentActionMinusTwo", // => "Habilidad contigua -2 en acción",
                "Discard4CardsOpponentDeck", // => "Descartas 4 cartas del mazo contrario",
                "DiscardOpponentHandTwoCardOnw" // => "Descartas dos cartas de la mano contraria"
            ],
            [
                "Invulnerable", // => "El siguiente turno eres invulnerable",
                "DiscardDecks" // => "Descartas tu mazo y el del rival"
            ]
        ];

        $damage = 0;
        $leftDamageModificator = 0;
        $centerDamageModificator = 0;
        $rightDamageModificator = 0;
        $leftPAModificator = 0;
        $centerPAModificator = 0;
        $rightPAModificator = 0;
        $leftDiscardOppentEffect = false;
        $centerDiscardOppentEffect = false;
        $rightDiscardOppentEffect = false;
        $discardOpponentEffects = false;
        $invulnerability = false;
        $currentActionPointsModificator = $fighterUser1->playing_pa;
        $currentActionPoints = $fighterUser1->playing_pa;
        $errorPa = false;

        $damage2 = 0;
        $leftDamageModificator2 = 0;
        $centerDamageModificator2 = 0;
        $rightDamageModificator2 = 0;
        $leftPAModificator2 = 0;
        $centerPAModificator2 = 0;
        $rightPAModificator2 = 0;
        $leftDiscardOppentEffect2 = false;
        $centerDiscardOppentEffect2 = false;
        $rightDiscardOppentEffect2 = false;
        $discardOpponentEffects2 = false;
        $invulnerability2 = false;
        $currentActionPointsModificator2 = $fighterUser2->playing_pa;
        $currentActionPoints2 = $fighterUser2->playing_pa;
        $errorPa2 = false;

        $leftContentCard = Storage::json('luchador/metadata/' . $fighterUser1->playing_card_left . '.json');
        $leftContentCard2 = Storage::json('luchador/metadata/' . $fighterUser2->playing_card_left . '.json');
        if (empty($leftContentCard)) {
            $abilityIndexArray = [];
        } else {
            $leftContentCard = (object) $leftContentCard;
            $abilityIndexArray = explode('_', $leftContentCard->ability);
        }
        if (empty($leftContentCard2)) {
            $abilityIndexArray2 = [];
        } else {
            $leftContentCard2 = (object) $leftContentCard2;
            $abilityIndexArray2 = explode('_', $leftContentCard2->ability);
        }
        if (count($abilityIndexArray) >= 2) {
            $abilityFunction = 'Ability' . $abilities[$abilityIndexArray[0]][$abilityIndexArray[1]];
            FighterBattle::$abilityFunction($leftContentCard, $fighterUser1, $fighterUser2, 'left', $damage, $leftDamageModificator, $centerDamageModificator, $rightDamageModificator, $leftPAModificator, $centerPAModificator, $rightPAModificator, $leftDiscardOppentEffect, $centerDiscardOppentEffect, $rightDiscardOppentEffect, $invulnerability, $discardOpponentEffects, $currentActionPoints, $currentActionPoints2, $currentActionPointsModificator2,  true);
        }
        if (count($abilityIndexArray2) >= 2) {
            $abilityFunction = 'Ability' . $abilities[$abilityIndexArray2[0]][$abilityIndexArray2[1]];
            FighterBattle::$abilityFunction($leftContentCard2, $fighterUser2, $fighterUser1, 'left', $damage2, $leftDamageModificator2, $centerDamageModificator2, $rightDamageModificator2, $leftPAModificator2, $centerPAModificator2, $rightPAModificator2, $leftDiscardOppentEffect2, $centerDiscardOppentEffect2, $rightDiscardOppentEffect2, $invulnerability2, $discardOpponentEffects2, $currentActionPoints2, $currentActionPoints, $currentActionPointsModificator,  true);
        }

        $centerContentCard = Storage::json('luchador/metadata/' . $fighterUser1->playing_card_center . '.json');
        $centerContentCard2 = Storage::json('luchador/metadata/' . $fighterUser2->playing_card_center . '.json');
        if (empty($centerContentCard)) {
            $abilityIndexArray = [];
        } else {
            $centerContentCard = (object) $centerContentCard;
            $abilityIndexArray = explode('_', $centerContentCard->ability);
        }
        if (empty($centerContentCard2)) {
            $abilityIndexArray2 = [];
        } else {
            $centerContentCard2 = (object) $centerContentCard2;
            $abilityIndexArray2 = explode('_', $centerContentCard2->ability);
        }
        if (count($abilityIndexArray) >= 2) {
            $abilityFunction = 'Ability' . $abilities[$abilityIndexArray[0]][$abilityIndexArray[1]];
            FighterBattle::$abilityFunction($centerContentCard, $fighterUser1, $fighterUser2, 'center', $damage, $leftDamageModificator, $centerDamageModificator, $rightDamageModificator, $leftPAModificator, $centerPAModificator, $rightPAModificator, $leftDiscardOppentEffect, $centerDiscardOppentEffect, $rightDiscardOppentEffect, $invulnerability, $discardOpponentEffects, $currentActionPoints, $currentActionPoints2, $currentActionPointsModificator2,  true);
        }
        if (count($abilityIndexArray2) >= 2) {
            $abilityFunction = 'Ability' . $abilities[$abilityIndexArray2[0]][$abilityIndexArray2[1]];
            FighterBattle::$abilityFunction($centerContentCard2, $fighterUser2, $fighterUser1, 'center', $damage2, $leftDamageModificator2, $centerDamageModificator2, $rightDamageModificator2, $leftPAModificator2, $centerPAModificator2, $rightPAModificator2, $leftDiscardOppentEffect2, $centerDiscardOppentEffect2, $rightDiscardOppentEffect2, $invulnerability2, $discardOpponentEffects2, $currentActionPoints2, $currentActionPoints, $currentActionPointsModificator,  true);
        }

        $rightContentCard = Storage::json('luchador/metadata/' . $fighterUser1->playing_card_right . '.json');
        $rightContentCard2 = Storage::json('luchador/metadata/' . $fighterUser2->playing_card_right . '.json');
        if (empty($rightContentCard)) {
            $abilityIndexArray = [];
        } else {
            $rightContentCard = (object) $rightContentCard;
            $abilityIndexArray = explode('_', $rightContentCard->ability);
        }
        if (empty($rightContentCard2)) {
            $abilityIndexArray2 = [];
        } else {
            $rightContentCard2 = (object) $rightContentCard2;
            $abilityIndexArray2 = explode('_', $rightContentCard2->ability);
        }
        if (count($abilityIndexArray) >= 2) {
            $abilityFunction = 'Ability' . $abilities[$abilityIndexArray[0]][$abilityIndexArray[1]];
            FighterBattle::$abilityFunction($rightContentCard, $fighterUser1, $fighterUser2, 'right', $damage, $leftDamageModificator, $centerDamageModificator, $rightDamageModificator, $leftPAModificator, $centerPAModificator, $rightPAModificator, $leftDiscardOppentEffect, $centerDiscardOppentEffect, $rightDiscardOppentEffect, $invulnerability, $discardOpponentEffects, $currentActionPoints, $currentActionPoints2, $currentActionPointsModificator2,  true);
        }
        if (count($abilityIndexArray2) >= 2) {
            $abilityFunction = 'Ability' . $abilities[$abilityIndexArray2[0]][$abilityIndexArray2[1]];
            FighterBattle::$abilityFunction($rightContentCard2, $fighterUser2, $fighterUser1, 'right', $damage2, $leftDamageModificator2, $centerDamageModificator2, $rightDamageModificator2, $leftPAModificator2, $centerPAModificator2, $rightPAModificator2, $leftDiscardOppentEffect2, $centerDiscardOppentEffect2, $rightDiscardOppentEffect2, $invulnerability2, $discardOpponentEffects2, $currentActionPoints2, $currentActionPoints, $currentActionPointsModificator,  true);
        }

        if (empty($leftContentCard)) {
            $abilityIndexArray = [];
        } else {
            $leftContentCard = (object) $leftContentCard;
            $abilityIndexArray = explode('_', $leftContentCard->ability);
        }
        if (empty($leftContentCard2)) {
            $abilityIndexArray2 = [];
        } else {
            $leftContentCard2 = (object) $leftContentCard2;
            $abilityIndexArray2 = explode('_', $leftContentCard2->ability);
        }
        if (count($abilityIndexArray) >= 2) {
            $abilityFunction = 'Ability' . $abilities[$abilityIndexArray[0]][$abilityIndexArray[1]];
            $resultAbility = FighterBattle::$abilityFunction($leftContentCard, $fighterUser1, $fighterUser2, 'left', $damage, $leftDamageModificator, $centerDamageModificator, $rightDamageModificator, $leftPAModificator, $centerPAModificator, $rightPAModificator, $leftDiscardOppentEffect2, $centerDiscardOppentEffect2, $rightDiscardOppentEffect2, $invulnerability, $discardOpponentEffects2, $currentActionPoints, $currentActionPoints2, $currentActionPointsModificator2,  false);
            if (!$resultAbility) {
                $errorPa = true;
            }
        }
        if (count($abilityIndexArray2) >= 2) {
            $abilityFunction = 'Ability' . $abilities[$abilityIndexArray2[0]][$abilityIndexArray2[1]];
            $resultAbility = FighterBattle::$abilityFunction($leftContentCard2, $fighterUser2, $fighterUser1, 'left', $damage2, $leftDamageModificator2, $centerDamageModificator2, $rightDamageModificator2, $leftPAModificator2, $centerPAModificator2, $rightPAModificator2, $leftDiscardOppentEffect, $centerDiscardOppentEffect, $rightDiscardOppentEffect, $invulnerability2, $discardOpponentEffects, $currentActionPoints2, $currentActionPoints, $currentActionPointsModificator,  false);
            if (!$resultAbility) {
                $errorPa2 = true;
            }
        }

        if (empty($centerContentCard)) {
            $abilityIndexArray = [];
        } else {
            $centerContentCard = (object) $centerContentCard;
            $abilityIndexArray = explode('_', $centerContentCard->ability);
        }
        if (empty($centerContentCard2)) {
            $abilityIndexArray2 = [];
        } else {
            $centerContentCard2 = (object) $centerContentCard2;
            $abilityIndexArray2 = explode('_', $centerContentCard2->ability);
        }
        if (count($abilityIndexArray) >= 2) {
            $abilityFunction = 'Ability' . $abilities[$abilityIndexArray[0]][$abilityIndexArray[1]];
            $resultAbility = FighterBattle::$abilityFunction($centerContentCard, $fighterUser1, $fighterUser2, 'center', $damage, $leftDamageModificator, $centerDamageModificator, $rightDamageModificator, $leftPAModificator, $centerPAModificator, $rightPAModificator, $leftDiscardOppentEffect2, $centerDiscardOppentEffect2, $rightDiscardOppentEffect2, $invulnerability, $discardOpponentEffects2, $currentActionPoints, $currentActionPoints2, $currentActionPointsModificator2,  false);
            if (!$resultAbility) {
                $errorPa = true;
            }
        }
        if (count($abilityIndexArray2) >= 2) {
            $abilityFunction = 'Ability' . $abilities[$abilityIndexArray2[0]][$abilityIndexArray2[1]];
            $resultAbility = FighterBattle::$abilityFunction($centerContentCard2, $fighterUser2, $fighterUser1, 'center', $damage2, $leftDamageModificator2, $centerDamageModificator2, $rightDamageModificator2, $leftPAModificator2, $centerPAModificator2, $rightPAModificator2, $leftDiscardOppentEffect, $centerDiscardOppentEffect, $rightDiscardOppentEffect, $invulnerability2, $discardOpponentEffects, $currentActionPoints2, $currentActionPoints, $currentActionPointsModificator,  false);
            if (!$resultAbility) {
                $errorPa2 = true;
            }
        }

        if (empty($rightContentCard)) {
            $abilityIndexArray = [];
        } else {
            $rightContentCard = (object) $rightContentCard;
            $abilityIndexArray = explode('_', $rightContentCard->ability);
        }
        if (empty($rightContentCard2)) {
            $abilityIndexArray2 = [];
        } else {
            $rightContentCard2 = (object) $rightContentCard2;
            $abilityIndexArray2 = explode('_', $rightContentCard2->ability);
        }
        if (count($abilityIndexArray) >= 2) {
            $abilityFunction = 'Ability' . $abilities[$abilityIndexArray[0]][$abilityIndexArray[1]];
            $resultAbility = FighterBattle::$abilityFunction($rightContentCard, $fighterUser1, $fighterUser2, 'right', $damage, $leftDamageModificator, $centerDamageModificator, $rightDamageModificator, $leftPAModificator, $centerPAModificator, $rightPAModificator, $leftDiscardOppentEffect2, $centerDiscardOppentEffect2, $rightDiscardOppentEffect2, $invulnerability, $discardOpponentEffects2, $currentActionPoints, $currentActionPoints2, $currentActionPointsModificator2,  false);
            if (!$resultAbility) {
                $errorPa = true;
            }
        }
        if (count($abilityIndexArray2) >= 2) {
            $abilityFunction = 'Ability' . $abilities[$abilityIndexArray2[0]][$abilityIndexArray2[1]];
            $resultAbility = FighterBattle::$abilityFunction($rightContentCard2, $fighterUser2, $fighterUser1, 'right', $damage2, $leftDamageModificator2, $centerDamageModificator2, $rightDamageModificator2, $leftPAModificator2, $centerPAModificator2, $rightPAModificator2, $leftDiscardOppentEffect, $centerDiscardOppentEffect, $rightDiscardOppentEffect, $invulnerability2, $discardOpponentEffects, $currentActionPoints2, $currentActionPoints, $currentActionPointsModificator,  false);
            if (!$resultAbility) {
                $errorPa2 = true;
            }
        }

        // El errorPa y errorPa2 ya están aplicados en el damage y los puntos de acción restantes de cada oponente.
        // Por tanto no requiere de ninguna acción adicional.

        $fighterUser1->playing_pa = $currentActionPointsModificator;
        if ($damage2 > $fighterUser1->playing_hp) {
            $fighterUser1->playing_hp = 0;
            $fighterPast1->playing_hp = 0;
        } else {
            $fighterUser1->playing_hp -= $damage2;
            $fighterPast1->playing_hp -= $damage2;
        }
        $fighterPast1->playing_pa = $currentActionPoints;
        $fighterPast1->playing_card_left = $fighterUser1->playing_card_left;
        $fighterPast1->playing_card_center = $fighterUser1->playing_card_center;
        $fighterPast1->playing_card_right = $fighterUser1->playing_card_right;

        $fighterUser2->playing_pa = $currentActionPointsModificator2;
        if ($damage > $fighterUser2->playing_hp) {
            $fighterUser2->playing_hp = 0;
            $fighterPast2->playing_hp = 0;
        } else {
            $fighterUser2->playing_hp -= $damage;
            $fighterPast2->playing_hp -= $damage;
        }
        $fighterPast2->playing_pa = $currentActionPoints2;
        $fighterPast2->playing_card_left = $fighterUser2->playing_card_left;
        $fighterPast2->playing_card_center = $fighterUser2->playing_card_center;
        $fighterPast2->playing_card_right = $fighterUser2->playing_card_right;

        $shiftDate = new Carbon();
        $fighterUser1->playing_shift_resolved += 1;
        $fighterUser1->playing_shift_date = $shiftDate;
        $fighterUser2->playing_shift_resolved += 1;
        $fighterUser2->playing_shift_date = $shiftDate;

        $fighterUser1Save = $fighterUser1->save();
        $fighterPast1Save = $fighterPast1->save();
        $fighterUser2Save = $fighterUser2->save();
        $fighterPast2Save = $fighterPast2->save();

        return $fighterUser1Save && $fighterPast1Save && $fighterUser2Save && $fighterPast2Save;
    }

    static function resolveFighterUsersBattleTurn(FighterUser $fighterUser1)
    {
        $fighterPast1 = FighterPast::
            where('user_id', '=', $fighterUser1->user_id)
            ->orderBy('created_at', 'DESC')
            ->first();
        if (!$fighterPast1) {
            return false;
        }

        $fighterPast2 = FighterPast::
            where('user_id', '!=', $fighterUser1->user_id)
            ->where('game_hash', '!=', $fighterPast1->game_hash)
            ->orderBy('created_at', 'DESC')
            ->first();
        if (!$fighterPast2) {
            return false;
        }

        $fighterUser2 = FighterUser::where('user_id', '=', $fighterPast2->user_id)->first();
        if (!$fighterUser2) {
            return false;
        }

        if ($fighterPast1->playing_shift <= $fighterUser1->playing_shift_resolved && $fighterPast2->playing_shift <= $fighterUser1->playing_shift_resolved) {
            return false;
        }

        $checkedFighterPastsToBattleTurn = FighterBattle::checkFighterPastsToBattleTurn($fighterPast1, $fighterPast2, $fighterUser1, $fighterUser2);
        if (!$checkedFighterPastsToBattleTurn) {
            return false;
        }

        return FighterBattle::resolveFighterUserBattleTurn($fighterUser1, $fighterUser2, $fighterPast1, $fighterPast2);
    }
}
