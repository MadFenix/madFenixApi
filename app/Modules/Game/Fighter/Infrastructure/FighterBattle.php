<?php
namespace App\Modules\Twitch\Infrastructure;

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

    static function findFighterUserToBattle()
    {
        $fighterUserToBattle = FighterUser::
            where('ready_to_play', '=', true)
            ->whereNull('playing_with_user')
            ->whereDate('ready_to_play_last', '>', Carbon::now()->subSeconds(45))
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

    static function prepareFighterUserToBattle(FighterUser $fighterUser1, FighterUser $fighterUser2, $gameHash)
    {
        $deckNumber = 'deck_' . $fighterUser1->deck_current;

        $fighterUser1->playing_with_user = $fighterUser2->user_id;
        $fighterUser1->playing_deck = $fighterUser1->$deckNumber;
        $fighterUser1->playing_hand = '';
        $fighterUser1->playing_shift = 1;
        $fighterUser1->playing_hp = 37;
        $fighterUser1->playing_pa = 1;
        $fighterUser1->playing_card_left = '0';
        $fighterUser1->playing_card_center = '0';
        $fighterUser1->playing_card_right = '0';

        return FighterBattle::saveNewFighterPast($fighterUser1, $gameHash);
    }

    static function prepareFighterUsersToBattle(FighterUser $fighterUser1, FighterUser $fighterUser2)
    {
        $battleTime = time();
        $gameHash = hash('sha256', $fighterUser1->user_id . '_' . $fighterUser2->user_id . '_' . $battleTime);
        $fighterPast1Save = FighterBattle::prepareFighterUserToBattle($fighterUser1, $fighterUser2, $gameHash);
        $fighterPast2Save = FighterBattle::prepareFighterUserToBattle($fighterUser2, $fighterUser1, $gameHash);

        return $fighterPast1Save && $fighterPast2Save;
    }

    static function checkPlayedCards(FighterUser $fighterUser, $dataPlayedCards): bool
    {
        $currentHand = $fighterUser->playing_hand;
        $currentHandArray = explode(',', $currentHand);

        if (!empty($dataPlayedCards['card_left']) && !in_array($dataPlayedCards['card_left'], $currentHandArray)) {
            return false;
        }

        if (!empty($dataPlayedCards['card_center']) && !in_array($dataPlayedCards['card_center'], $currentHandArray)) {
            return false;
        }

        if (!empty($dataPlayedCards['card_right']) && !in_array($dataPlayedCards['card_right'], $currentHandArray)) {
            return false;
        }

        return true;
    }

    static function drawCardsDeck(FighterUser $fighterUser, $quantityCards): void
    {
        $currentHand = $fighterUser->playing_hand;
        $currentHandArray = explode(',', $currentHand);
        if (7 - count($currentHandArray) > $quantityCards) {
            $quantityCards = 7 - count($currentHandArray);
        }

        $playingDeckArray = explode(',', $fighterUser->playing_deck);
        for ($i = 0; $i < $quantityCards; $i++) {
            $currentHand .= array_shift($playingDeckArray) . ',';
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
    }

    static function saveFighterUserBattleTurn(FighterUser $fighterUser, $dataPlayedCards)
    {
        $fighterPast = FighterPast::
            where('user_id', '=', $fighterUser->user_id)
            ->whereDate('created_at', '<', Carbon::now()->subSeconds(8))
            ->orderBy('created_at', 'DESC')
            ->first();

        if (!$fighterPast || $fighterPast->playing_shift != $fighterUser->playing_shift || $fighterPast->playing_with_user != $fighterUser->playing_with_user) {
            return false;
        }

        if (!FighterBattle::checkPlayedCards($fighterUser, $dataPlayedCards)) {
            return false;
        }

        if ($fighterUser->playing_shift == 1) {
            FighterBattle::drawCardsDeck($fighterUser, 7);
        } else if ($fighterUser->playing_deck) {
            if ($fighterUser->playing_shift >= 2 && $fighterUser->playing_shift <= 5) {
                FighterBattle::drawCardsDeck($fighterUser, 2);
            } else if ($fighterUser->playing_shift >= 6) {
                FighterBattle::drawCardsDeck($fighterUser, 3);
            }
        }

        if ($fighterUser->playing_shift == 2) {
            $fighterUser->playing_pa = 2;
        }

        if ($fighterUser->playing_shift >= 3) {
            $fighterUser->playing_pa = 3;
        }

        $fighterUser->playing_shift += 1;
        if (!empty($dataPlayedCards['card_left'])) {
            $fighterUser->playing_card_left_back = $fighterUser->playing_card_left;
            $fighterUser->playing_card_left = $dataPlayedCards['card_left'];
        }
        if (!empty($dataPlayedCards['card_center'])) {
            $fighterUser->playing_card_center_back = $fighterUser->playing_card_center;
            $fighterUser->playing_card_center = $dataPlayedCards['card_center'];
        }
        if (!empty($dataPlayedCards['card_right'])) {
            $fighterUser->playing_card_right_back = $fighterUser->playing_card_right;
            $fighterUser->playing_card_right = $dataPlayedCards['card_right'];
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

    static function resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, &$currentActionPoints, &$damgePoints, $abilityModificator, $damageModificator, $lineDiscardEffect, $discardEffect)
    {
        if ($lineDiscardEffect || $discardEffect) {
            $abilityModificator = 0;
            $damageModificator = 0;
        }
        if (!$onlyModificators) {
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
            $damgePoints += (int) $contentCard->damage + $damageModificator;
        }

        return true;
    }

    static function AbilityWithout($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$currentActionPoints, &$currentActionPointsModificator,  $onlyModificators)
    {
        return false;
    }

    static function AbilityAdjacentDamagePlusOne($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$currentActionPoints, &$currentActionPointsModificator,  $onlyModificators)
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
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $leftAbilityModificator, $leftDamageModificator, $leftDiscardOppentEffect, $discardOpponentEffects);
            }
            if ($lane == 'center') {
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $centerAbilityModificator, $centerDamageModificator, $centerDiscardOppentEffect, $discardOpponentEffects);
            }
            if ($lane == 'right') {
                return FighterBattle::resolveLineDamage($fighterUser1, $lane, $contentCard, $onlyModificators, $currentActionPoints, $damgePoints, $rightAbilityModificator, $rightDamageModificator, $rightDiscardOppentEffect, $discardOpponentEffects);
            }
        }

        return true;
    }

    static function AbilityAdjacentActionMinusOne($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$currentActionPoints, &$currentActionPointsModificator,  $onlyModificators)
    {

    }

    static function AbilitySaveOne($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$currentActionPoints, &$currentActionPointsModificator,  $onlyModificators)
    {

    }

    static function AbilityMinusOneToOpponent($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$currentActionPoints, &$currentActionPointsModificator,  $onlyModificators)
    {

    }

    static function AbilityDiscard2CardsOpponentDeck($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$currentActionPoints, &$currentActionPointsModificator,  $onlyModificators)
    {

    }

    static function AbilityDrawTwoCards($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$currentActionPoints, &$currentActionPointsModificator,  $onlyModificators)
    {

    }

    static function AbilityDiscardEffectOpponentLane($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$currentActionPoints, &$currentActionPointsModificator,  $onlyModificators)
    {

    }

    static function AbilitySeeNext5Cards($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$currentActionPoints, &$currentActionPointsModificator,  $onlyModificators)
    {

    }

    static function AbilitySaveTwo($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$currentActionPoints, &$currentActionPointsModificator,  $onlyModificators)
    {

    }

    static function AbilityMinusTwoToOpponent($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$currentActionPoints, &$currentActionPointsModificator,  $onlyModificators)
    {

    }

    static function AbilityDiscard3CardsOpponentDeck($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$currentActionPoints, &$currentActionPointsModificator,  $onlyModificators)
    {

    }

    static function AbilityDiscardEffectOpponent($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$currentActionPoints, &$currentActionPointsModificator,  $onlyModificators)
    {

    }

    static function AbilityDiscardOpponentHand($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$currentActionPoints, &$currentActionPointsModificator,  $onlyModificators)
    {

    }

    static function AbilityDiscardOpponentHandOneCardOnw($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$currentActionPoints, &$currentActionPointsModificator,  $onlyModificators)
    {

    }

    static function AbilityDrawThreeCards($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$currentActionPoints, &$currentActionPointsModificator,  $onlyModificators)
    {

    }

    static function AbilityAdjacentDamagePlusTwo($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$currentActionPoints, &$currentActionPointsModificator,  $onlyModificators)
    {

    }

    static function AbilityAdjacentActionMinusTwo($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$currentActionPoints, &$currentActionPointsModificator,  $onlyModificators)
    {

    }

    static function AbilityDiscard4CardsOpponentDeck($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$currentActionPoints, &$currentActionPointsModificator,  $onlyModificators)
    {

    }

    static function AbilityDiscardOpponentHandCardTwo($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$currentActionPoints, &$currentActionPointsModificator,  $onlyModificators)
    {

    }

    static function AbilityInvulnerable($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$currentActionPoints, &$currentActionPointsModificator,  $onlyModificators)
    {

    }

    static function AbilityDiscardDecks($contentCard, FighterUser $fighterUser1, FighterUser $fighterUser2, $lane, &$damgePoints, &$leftDamageModificator, &$centerDamageModificator, &$rightDamageModificator, &$leftAbilityModificator, &$centerAbilityModificator, &$rightAbilityModificator, &$leftDiscardOppentEffect, &$centerDiscardOppentEffect, &$rightDiscardOppentEffect, &$discardOpponentEffects, &$currentActionPoints, &$currentActionPointsModificator,  $onlyModificators)
    {

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
                "DiscardOpponentHandCardTwo" // => "Descartas dos cartas de la mano contraria"
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
        $currentActionPointsModificator2 = $fighterUser2->playing_pa;
        $currentActionPoints2 = $fighterUser2->playing_pa;
        $errorPa2 = false;

        $leftContentCard = Storage::json('luchador/metadata/' . $fighterUser1->playing_card_left . '.json');
        $leftContentCard2 = Storage::json('luchador/metadata/' . $fighterUser2->playing_card_left . '.json');
        $abilityIndexArray = explode('_', $leftContentCard->ability);
        $abilityIndexArray2 = explode('_', $leftContentCard2->ability);
        if (count($abilityIndexArray) >= 2) {
            $abilityFunction = 'Ability' . $abilities[$abilityIndexArray[0]][$abilityIndexArray[1]];
            FighterBattle::$abilityFunction($leftContentCard, $fighterUser1, $fighterUser2, 'left', $damage, $leftDamageModificator, $centerDamageModificator, $rightDamageModificator, $leftPAModificator, $centerPAModificator, $rightPAModificator, $leftDiscardOppentEffect, $centerDiscardOppentEffect, $rightDiscardOppentEffect, $discardOpponentEffects, $currentActionPoints, $currentActionPointsModificator,  true);
        }
        if (count($abilityIndexArray2) >= 2) {
            $abilityFunction = 'Ability' . $abilities[$abilityIndexArray2[0]][$abilityIndexArray2[1]];
            FighterBattle::$abilityFunction($leftContentCard2, $fighterUser2, $fighterUser1, 'left', $damage2, $leftDamageModificator2, $centerDamageModificator2, $rightDamageModificator2, $leftPAModificator2, $centerPAModificator2, $rightPAModificator2, $leftDiscardOppentEffect2, $centerDiscardOppentEffect2, $rightDiscardOppentEffect2, $discardOpponentEffects2, $currentActionPoints2, $currentActionPointsModificator2,  true);
        }

        $centerContentCard = Storage::json('luchador/metadata/' . $fighterUser1->playing_card_center . '.json');
        $centerContentCard2 = Storage::json('luchador/metadata/' . $fighterUser2->playing_card_center . '.json');
        $abilityIndexArray = explode('_', $centerContentCard->ability);
        $abilityIndexArray2 = explode('_', $centerContentCard2->ability);
        if (count($abilityIndexArray) >= 2) {
            $abilityFunction = 'Ability' . $abilities[$abilityIndexArray[0]][$abilityIndexArray[1]];
            FighterBattle::$abilityFunction($centerContentCard, $fighterUser1, $fighterUser2, 'center', $damage, $leftDamageModificator, $centerDamageModificator, $rightDamageModificator, $leftPAModificator, $centerPAModificator, $rightPAModificator, $leftDiscardOppentEffect, $centerDiscardOppentEffect, $rightDiscardOppentEffect, $discardOpponentEffects, $currentActionPoints, $currentActionPointsModificator,  true);
        }
        if (count($abilityIndexArray2) >= 2) {
            $abilityFunction = 'Ability' . $abilities[$abilityIndexArray2[0]][$abilityIndexArray2[1]];
            FighterBattle::$abilityFunction($centerContentCard2, $fighterUser2, $fighterUser1, 'center', $damage2, $leftDamageModificator2, $centerDamageModificator2, $rightDamageModificator2, $leftPAModificator2, $centerPAModificator2, $rightPAModificator2, $leftDiscardOppentEffect2, $centerDiscardOppentEffect2, $rightDiscardOppentEffect2, $discardOpponentEffects2, $currentActionPoints2, $currentActionPointsModificator2,  true);
        }

        $rightContentCard = Storage::json('luchador/metadata/' . $fighterUser1->playing_card_right . '.json');
        $rightContentCard2 = Storage::json('luchador/metadata/' . $fighterUser2->playing_card_right . '.json');
        $abilityIndexArray = explode('_', $rightContentCard->ability);
        $abilityIndexArray2 = explode('_', $rightContentCard2->ability);
        if (count($abilityIndexArray) >= 2) {
            $abilityFunction = 'Ability' . $abilities[$abilityIndexArray[0]][$abilityIndexArray[1]];
            FighterBattle::$abilityFunction($rightContentCard, $fighterUser1, $fighterUser2, 'right', $damage, $leftDamageModificator, $centerDamageModificator, $rightDamageModificator, $leftPAModificator, $centerPAModificator, $rightPAModificator, $leftDiscardOppentEffect, $centerDiscardOppentEffect, $rightDiscardOppentEffect, $discardOpponentEffects, $currentActionPoints, $currentActionPointsModificator,  true);
        }
        if (count($abilityIndexArray2) >= 2) {
            $abilityFunction = 'Ability' . $abilities[$abilityIndexArray2[0]][$abilityIndexArray2[1]];
            FighterBattle::$abilityFunction($rightContentCard2, $fighterUser2, $fighterUser1, 'right', $damage2, $leftDamageModificator2, $centerDamageModificator2, $rightDamageModificator2, $leftPAModificator2, $centerPAModificator2, $rightPAModificator2, $leftDiscardOppentEffect2, $centerDiscardOppentEffect2, $rightDiscardOppentEffect2, $discardOpponentEffects2, $currentActionPoints2, $currentActionPointsModificator2,  true);
        }

        $abilityIndexArray = explode('_', $leftContentCard->ability);
        $abilityIndexArray2 = explode('_', $leftContentCard2->ability);
        if (count($abilityIndexArray) >= 2) {
            $abilityFunction = 'Ability' . $abilities[$abilityIndexArray[0]][$abilityIndexArray[1]];
            $resultAbility = FighterBattle::$abilityFunction($leftContentCard, $fighterUser1, $fighterUser2, 'left', $damage, $leftDamageModificator, $centerDamageModificator, $rightDamageModificator, $leftPAModificator, $centerPAModificator, $rightPAModificator, $leftDiscardOppentEffect2, $centerDiscardOppentEffect2, $rightDiscardOppentEffect2, $discardOpponentEffects2, $currentActionPoints, $currentActionPointsModificator,  false);
            if (!$resultAbility) {
                $errorPa = true;
            }
        }
        if (count($abilityIndexArray2) >= 2) {
            $abilityFunction = 'Ability' . $abilities[$abilityIndexArray2[0]][$abilityIndexArray2[1]];
            $resultAbility = FighterBattle::$abilityFunction($leftContentCard2, $fighterUser2, $fighterUser1, 'left', $damage2, $leftDamageModificator2, $centerDamageModificator2, $rightDamageModificator2, $leftPAModificator2, $centerPAModificator2, $rightPAModificator2, $leftDiscardOppentEffect, $centerDiscardOppentEffect, $rightDiscardOppentEffect, $discardOpponentEffects, $currentActionPoints2, $currentActionPointsModificator2,  false);
            if (!$resultAbility) {
                $errorPa2 = true;
            }
        }

        $abilityIndexArray = explode('_', $centerContentCard->ability);
        $abilityIndexArray2 = explode('_', $centerContentCard2->ability);
        if (count($abilityIndexArray) >= 2) {
            $abilityFunction = 'Ability' . $abilities[$abilityIndexArray[0]][$abilityIndexArray[1]];
            $resultAbility = FighterBattle::$abilityFunction($centerContentCard, $fighterUser1, $fighterUser2, 'center', $damage, $leftDamageModificator, $centerDamageModificator, $rightDamageModificator, $leftPAModificator, $centerPAModificator, $rightPAModificator, $leftDiscardOppentEffect2, $centerDiscardOppentEffect2, $rightDiscardOppentEffect2, $discardOpponentEffects2, $currentActionPoints, $currentActionPointsModificator,  false);
            if (!$resultAbility) {
                $errorPa = true;
            }
        }
        if (count($abilityIndexArray2) >= 2) {
            $abilityFunction = 'Ability' . $abilities[$abilityIndexArray2[0]][$abilityIndexArray2[1]];
            $resultAbility = FighterBattle::$abilityFunction($centerContentCard2, $fighterUser2, $fighterUser1, 'center', $damage2, $leftDamageModificator2, $centerDamageModificator2, $rightDamageModificator2, $leftPAModificator2, $centerPAModificator2, $rightPAModificator2, $leftDiscardOppentEffect, $centerDiscardOppentEffect, $rightDiscardOppentEffect, $discardOpponentEffects, $currentActionPoints2, $currentActionPointsModificator2,  false);
            if (!$resultAbility) {
                $errorPa2 = true;
            }
        }

        $abilityIndexArray = explode('_', $rightContentCard->ability);
        $abilityIndexArray2 = explode('_', $rightContentCard2->ability);
        if (count($abilityIndexArray) >= 2) {
            $abilityFunction = 'Ability' . $abilities[$abilityIndexArray[0]][$abilityIndexArray[1]];
            $resultAbility = FighterBattle::$abilityFunction($rightContentCard, $fighterUser1, $fighterUser2, 'right', $damage, $leftDamageModificator, $centerDamageModificator, $rightDamageModificator, $leftPAModificator, $centerPAModificator, $rightPAModificator, $leftDiscardOppentEffect2, $centerDiscardOppentEffect2, $rightDiscardOppentEffect2, $discardOpponentEffects2, $currentActionPoints, $currentActionPointsModificator,  false);
            if (!$resultAbility) {
                $errorPa = true;
            }
        }
        if (count($abilityIndexArray2) >= 2) {
            $abilityFunction = 'Ability' . $abilities[$abilityIndexArray2[0]][$abilityIndexArray2[1]];
            $resultAbility = FighterBattle::$abilityFunction($rightContentCard2, $fighterUser2, $fighterUser1, 'right', $damage2, $leftDamageModificator2, $centerDamageModificator2, $rightDamageModificator2, $leftPAModificator2, $centerPAModificator2, $rightPAModificator2, $leftDiscardOppentEffect, $centerDiscardOppentEffect, $rightDiscardOppentEffect, $discardOpponentEffects, $currentActionPoints2, $currentActionPointsModificator2,  false);
            if (!$resultAbility) {
                $errorPa2 = true;
            }
        }

        // El errorPa y errorPa2 ya están aplicados en el damage y los puntos de acción restantes de cada oponente.
        // Por tanto no requiere de ninguna acción adicional.

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

        $checkedFighterPastsToBattleTurn = FighterBattle::checkFighterPastsToBattleTurn($fighterPast1, $fighterPast2, $fighterUser1, $fighterUser2);
        if (!$checkedFighterPastsToBattleTurn) {
            return false;
        }

        FighterBattle::resolveFighterUserBattleTurn($fighterUser1, $fighterUser2, $fighterPast1, $fighterPast2);
    }
}
