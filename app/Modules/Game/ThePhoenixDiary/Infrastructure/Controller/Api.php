<?php

namespace App\Modules\Game\ThePhoenixDiary\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use App\Modules\Game\ThePhoenixDiary\Domain\TpdCharacter;
use App\Modules\Game\ThePhoenixDiary\Infrastructure\ThePhoenixDiaryUtilities;
use App\Modules\User\Domain\User;

class Api extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Game\\ThePhoenixDiary';
    }

    public function getCharacters()
    {
        /** @var User $user */
        $user = auth()->user();
        if (!$user) {
            return response()->json('Login required.', 403);
        }

        $returnThePhoenixDiaryGame = ThePhoenixDiaryUtilities::getCharacters($user);

        return response()->json($returnThePhoenixDiaryGame);
    }

    public function createNewGame(Request $request)
    {
        /** @var User $user */
        $user = auth()->user();
        if (!$user) {
            return response()->json('Login required.', 403);
        }

        $data = $request->validate([
            'character_id' => 'required'
        ]);
        $character = TpdCharacter::find($data['character_id']);
        if (!$character) {
            return response()->json('Character not found.', 404);
        }

        $returnThePhoenixDiaryGame = ThePhoenixDiaryUtilities::createNewGame($user, $character);

        return response()->json($returnThePhoenixDiaryGame);
    }
}
