<?php
namespace App\Modules\Game\ThePhoenixDiary\Infrastructure;

use App\Modules\Game\ThePhoenixDiary\Domain\TpdCard;
use App\Modules\Game\ThePhoenixDiary\Domain\TpdCharacter;
use App\Modules\Game\ThePhoenixDiary\Domain\TpdEnemy;
use App\Modules\Game\ThePhoenixDiary\Domain\TpdEvent;
use App\Modules\Game\ThePhoenixDiary\Domain\TpdObject;
use App\Modules\User\Domain\User;

class ThePhoenixDiaryUtilities
{
    static function getCharacters(User $user)
    {
        $characters = TpdCharacter::where('active', '=', 1)->get();

        $charactersReturn = new \stdClass();
        $charactersReturn->characters = [];
        foreach ($characters as $character) {
            $charactersReturn->characters[] = (object) $character->toArray();
        }

        return $charactersReturn;
    }
    static function createNewGame(User $user, TpdCharacter $character)
    {
        $cardsAvailable = TpdCard::where('character_id', '=', $character->id)
            ->where('active', '=', 1)
            ->orderBy('id', 'ASC')
            ->get();
        $objectsAvailable = TpdObject::where('active', '=', 1)
            ->orderBy('id', 'ASC')
            ->get();
        $enemiesAvailable = TpdEnemy::where('active', '=', 1)
            ->orderBy('id', 'ASC')
            ->get();
        $eventsAvailable = TpdEvent::where('active', '=', 1)
            ->orderBy('id', 'ASC')
            ->get();
        $newGame = new \stdClass();
        $newGame->cardsAvailable = new \stdClass();
        $newGame->cardsAvailableIds = [];
        $newGame->cardsDeck = [];
        $newGame->aelios = 11;
        $counterInitialCardsDeck = 0;
        foreach ($cardsAvailable as $cardAvailable) {
            if ($counterInitialCardsDeck < 11) {
                $counterInitialCardsDeck++;
                $newCardDeck = new \stdClass();
                $newCardDeck->card_id = $cardAvailable->id;
                $newGame->cardsDeck[] = $newCardDeck;
            }
            $card_identification = 'card_' . $cardAvailable->id;
            $newGame->cardsAvailable->$card_identification = (object) $cardAvailable->toArray();
            $newGame->cardsAvailableIds[] = $cardAvailable->id;
        }
        $newGame->objectsAvailable = new \stdClass();
        $newGame->objectsAvailableIds = [];
        foreach ($objectsAvailable as $objectAvailable) {
            if ($objectAvailable->character_id && $objectAvailable->character_id != $character->id) {
                continue;
            }
            $object_identification = 'object_' . $objectAvailable->id;
            $newGame->objectsAvailable->$object_identification = (object) $objectAvailable->toArray();
            $newGame->objectsAvailableIds[] = $objectAvailable->id;
        }
        $newGame->enemiesAvailable = new \stdClass();
        $newGame->enemiesAvailableIds = [];
        foreach ($enemiesAvailable as $enemyAvailable) {
            $enemy_identification = 'enemy_' . $enemyAvailable->id;
            $newGame->enemysAvailable->$enemy_identification = (object) $enemyAvailable->toArray();
            $newGame->enemysAvailableIds[] = $enemyAvailable->id;
        }
        $newGame->eventsAvailable = new \stdClass();
        $newGame->eventsAvailableIds = [];
        foreach ($eventsAvailable as $eventAvailable) {
            if ($eventAvailable->character_id && $eventAvailable->character_id != $character->id) {
                continue;
            }
            $event_identification = 'event_' . $eventAvailable->id;
            $newGame->eventsAvailable->$event_identification = (object) $eventAvailable->toArray();
            $newGame->eventsAvailableIds[] = $eventAvailable->id;
        }

        return $newGame;
    }
}
