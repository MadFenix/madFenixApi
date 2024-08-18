<?php

namespace App\Modules\Game\Fighter\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use App\Modules\Game\Fighter\Domain\FighterFriend;
use App\Modules\Game\Fighter\Domain\FighterUser;
use App\Modules\User\Domain\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Api extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Game\\Fighter';
    }

    public function createFighterUser($user)
    {
        $newFighterUser = new FighterUser();
        $newFighterUser->user_id = $user->id;
        $newFighterUser->avatar_image = 'default';
        $newFighterUser->avatar_frame = 'default';
        $newFighterUser->action_frame = 'default';
        $newFighterUser->card_frame = 'default';
        $newFighterUser->game_arena = 'default';
        $newFighterUser->cups = 0;
        $newFighterUser->rank = 'iron';
        $newFighterUser->decks_available = 3;
        $newFighterUser->deck_current = 1;
        $newFighterUser->deck_1 = '';

        $newFighterUser->save();

        return $newFighterUser;
    }

    public function getFighterUser()
    {
        /** @var User $user */
        $user = auth()->user();
        if (!$user) {
            return response()->json('Login required.', 403);
        }

        $fighterUser = FighterUser::where('user_id', '=', $user->id)->first();
        if (!$fighterUser) {
            $fighterUser = $this->createFighterUser($user);
        }

        $returnFighterUser = new \stdClass();
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

        return response()->json($returnFighterUser);
    }

    public function setFighterUserDecks(Request $request)
    {
        $data = $request->validate(['deck' => 'required|integer', 'deck_number' => 'required|string']);

        /** @var User $user */
        $user = auth()->user();
        if (!$user) {
            return response()->json('Login required.', 403);
        }

        $fighterUser = FighterUser::where('user_id', '=', $user->id)->first();
        if (!$fighterUser) {
            $fighterUser = $this->createFighterUser($user);
        }

        if ($data['deck_number'] > $fighterUser->decks_available) {
            return response()->json('No tienes disponible este slot de baraja.', 400);
        }

        $deckNumber = 'deck_' . $data['deck_number'];

        $fighterUser->$deckNumber = $data['deck'];
        $fighterUser->deck_current = $data['deck_number'];
        $fighterUserSaved = $fighterUser->save();

        return $fighterUserSaved
            ? response()->json('Se ha establecido tu nueva baraja.')
            : response()->json('Error al guardar la baraja.', 500);
    }

    public function getFighterFriends()
    {
        /** @var User $user */
        $user = auth()->user();
        if (!$user) {
            return response()->json('Login required.', 403);
        }

        $fighterFriends = FighterFriend::where('user_id', '=', $user->id)->where('approved', '=', true)->get();

        $returnFighterFriends = [];
        foreach ($fighterFriends as $fighterFriend) {
            $userFriend = $fighterFriend->userFriend();
            $fighterUser = FighterUser::where('user_id', '=', $userFriend->id)->first();

            $newFighterFriend = new \stdClass();
            $newFighterFriend->user_id_friend = $userFriend->id;
            $newFighterFriend->name = $userFriend->name;
            $newFighterFriend->cups = $fighterUser->cups;
            $newFighterFriend->rank = $fighterUser->rank;

            $returnFighterFriends[] = $newFighterFriend;
        }

        return response()->json($returnFighterFriends);
    }

    public function findFighterFriend(Request $request)
    {
        $data = $request->validate(['name' => 'required|string']);

        /** @var User $user */
        $user = auth()->user();
        if (!$user) {
            return response()->json('Login required.', 403);
        }
        if (strlen($data['name']) < 3) {
            return response()->json('Debes saber almenos 3 carácteres del usuario.', 404);
        }

        $users = User::where('name', 'like', '%' . $data['name'] . '%')->limit(5);

        $returnFighterFriends = [];
        foreach ($users as $user) {
            $newFighterFriend = new \stdClass();

            $newFighterFriend->user_id = $user->id;
            $newFighterFriend->name = $user->name;

            $returnFighterFriends[] = $newFighterFriend;
        }

        return response()->json($returnFighterFriends);
    }

    public function requestFighterFriend(Request $request)
    {
        $data = $request->validate(['user_id' => 'required|integer']);

        /** @var User $user */
        $user = auth()->user();
        if (!$user) {
            return response()->json('Login required.', 403);
        }

        $fighterFriends = FighterFriend::where('user_id', '=', $user->id)->where('user_id_friend', '=', $data['user_id'])->first();

        if (!$fighterFriends) {
            $fighterFriends = new FighterFriend();
            $fighterFriends->user_id = $user->id;
            $fighterFriends->user_id_friend = $data['user_id'];
            $fighterFriends->approved = false;

            $fighterFriendsSaved = $fighterFriends->save();

            return $fighterFriendsSaved
                ? response()->json('Se ha guardado tu petición de amistad.')
                : response()->json('Error al guardar tu petición de amistad.', 500);
        }

        return response()->json('Ya has mandado la petición de amistad');
    }

    public function getFighterFriendRequests()
    {
        /** @var User $user */
        $user = auth()->user();
        if (!$user) {
            return response()->json('Login required.', 403);
        }

        $fighterFriends = FighterFriend::where('user_id', '=', $user->id)->where('approved', '=', false)->get();

        $returnFighterFriends = [];
        foreach ($fighterFriends as $fighterFriend) {
            $user = User::where('id', $fighterFriend->user_id_friend)->first();
            $newFighterFriend = new \stdClass();

            $newFighterFriend->user_id = $user->id;
            $newFighterFriend->name = $user->name;

            $returnFighterFriends[] = $newFighterFriend;
        }

        return response()->json($returnFighterFriends);
    }

    public function approveFighterFriendRequest(Request $request)
    {
        $data = $request->validate(['user_id' => 'required|integer']);

        /** @var User $user */
        $user = auth()->user();
        if (!$user) {
            return response()->json('Login required.', 403);
        }

        $fighterFriend = FighterFriend::where('user_id', '=', $user->id)->where('user_id_friend', '=', $data['user_id'])->first();

        if (!$fighterFriend) {
            return response()->json('No existe la petición de amistad.', 404);
        }

        $fighterFriend->approved = true;
        $fighterFriendsSaved = $fighterFriend->save();

        return $fighterFriendsSaved
            ? response()->json('Se ha guardado tu petición de amistad.')
            : response()->json('Error al guardar tu petición de amistad.', 500);
    }

    public function getRanking()
    {
        /** @var User $user */
        $user = auth()->user();
        if (!$user) {
            return response()->json('Login required.', 403);
        }

        $fighterUsers = FighterUser::orderBy('cups', 'desc')->limit(100)->get();

        $returnFighterUsers = [];
        foreach ($fighterUsers as $fighterUser) {
            $user = User::where('id', $fighterUsers->user_id)->first();
            $newFighterUser = new \stdClass();

            $newFighterUser->user_id = $user->id;
            $newFighterUser->name = $user->name;
            $newFighterUser->cups = $fighterUser->cups;
            $newFighterUser->rank = $fighterUser->rank;

            $returnFighterUsers[] = $newFighterUser;
        }

        return response()->json($returnFighterUsers);
    }

    // Common Cards
    /*
     * [2003,2164,2197,2284,2334,2517,2747,2893,2954,3053,3091,3163,3204,
     * 3258,3326,3389,3469,3541,3813,3916,3950,4060,4132,4380,4524,2604,
     * 4766,4988,5261,5308,5580,5612,5676,5746,5789,5828,5939,6171,6235,
     * 6259,6381,6445,6735,6858,7122]
     */
}
