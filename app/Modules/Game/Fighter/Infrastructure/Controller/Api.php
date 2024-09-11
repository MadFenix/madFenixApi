<?php

namespace App\Modules\Game\Fighter\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use App\Modules\Blockchain\Block\Domain\NftIdentification;
use App\Modules\Game\Fighter\Domain\FighterFriend;
use App\Modules\Game\Fighter\Domain\FighterUser;
use App\Modules\Twitch\Infrastructure\FighterBattle;
use App\Modules\Twitch\Infrastructure\FighterUtilities;
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
        $newFighterUser->deck_1 = '2003,2164,2197,2284,2334,2517,2747,2893,2954,3053,3091,3163,3204,3258,3326,3389,3469,3541,3813,3916,3950,4060,4132,4380,4524,4604,4766,4988,5261,5308';
        $newFighterUser->deck_2 = '2003,2164,2197,2284,2334,2517,2747,2893,2954,3053,3091,3163,3204,3258,3326,3389,3469,3541,3813,3916,3950,4060,4132,4380,4524,4604,4766,4988,5261,5308';
        $newFighterUser->deck_3 = '2003,2164,2197,2284,2334,2517,2747,2893,2954,3053,3091,3163,3204,3258,3326,3389,3469,3541,3813,3916,3950,4060,4132,4380,4524,4604,4766,4988,5261,5308';

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

        $returnFighterUser = FighterUtilities::getFighterUserTransformer($user, $fighterUser);

        return response()->json($returnFighterUser);
    }

    public function setFighterUserDecks(Request $request)
    {
        $data = $request->validate(['deck_number' => 'required|integer', 'deck' => 'required|string']);

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

        $fighterFriends = FighterFriend::
            where(function ($query) use($user) {
                $query->where('user_id', '=', $user->id)
                    ->orWhere('user_id_friend', '=', $user->id);
            })
            ->where('approved', '=', true)->get();

        $returnFighterFriends = [];
        foreach ($fighterFriends as $fighterFriend) {
            if ($fighterFriend->user_id_friend == $user->id) {
                $userFriend = User::where('id', '=', $fighterFriend->user_id)->first();
            } else {
                $userFriend = User::where('id', '=', $fighterFriend->user_id_friend)->first();
            }
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

        $fighterFriends = FighterFriend::
        where(function ($query) use($user) {
            $query->where('user_id', '=', $user->id)
                ->orWhere('user_id_friend', '=', $user->id);
        })
            ->where('approved', '=', true)->get();

        $fighterFriendsUserIds = [$user->id];
        foreach ($fighterFriends as $fighterFriend) {
            if ($fighterFriend->user_id_friend == $user->id) {
                $fighterFriendsUserIds[] = $fighterFriend->user_id;
            } else {
                $fighterFriendsUserIds[] = $fighterFriend->user_id_friend;
            }
        }

        $users = User::
            where('name', 'like', '%' . $data['name'] . '%')
            ->whereNotIn('id', $fighterFriendsUserIds)
            ->limit(5)
            ->get();

        $returnFighterFriends = [];
        foreach ($users as $user) {
            $fighterUser = FighterUser::where('user_id', '=', $user->id)->first();
            $cups = 0;
            $rank = 'iron';
            if ($fighterUser) {
                $cups = $fighterUser->cups;
                $rank = $fighterUser->rank;
            }

            $newFighterFriend = new \stdClass();

            $newFighterFriend->user_id = $user->id;
            $newFighterFriend->name = $user->name;
            $newFighterFriend->cups = $cups;
            $newFighterFriend->rank = $rank;

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

        $fighterFriends = FighterFriend::where('user_id_friend', '=', $user->id)->where('approved', '=', false)->get();

        $returnFighterFriends = [];
        foreach ($fighterFriends as $fighterFriend) {
            $userFighterFriend = User::where('id', $fighterFriend->user_id)->first();
            $newFighterFriend = new \stdClass();

            $newFighterFriend->user_id = $userFighterFriend->id;
            $newFighterFriend->name = $userFighterFriend->name;

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

        $fighterFriend = FighterFriend::where('user_id', '=', $data['user_id'])->where('user_id_friend', '=', $user->id)->first();

        if (!$fighterFriend) {
            return response()->json('No existe la petición de amistad.', 404);
        }

        $fighterFriend->approved = true;
        $fighterFriendsSaved = $fighterFriend->save();

        $fighterFriendReverse = FighterFriend::where('user_id', '=', $user->id)->where('user_id_friend', '=', $data['user_id'])->first();
        if ($fighterFriendReverse) {
            $fighterFriendReverse->remove();
        }

        return $fighterFriendsSaved
            ? response()->json('Se ha guardado tu petición de amistad.')
            : response()->json('Error al guardar tu petición de amistad.', 500);
    }

    public function cancelFighterFriendRequest(Request $request)
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

        $fighterFriendsRemoved = $fighterFriend->remove();

        return $fighterFriendsRemoved
            ? response()->json('Se ha eliminado tu petición de amistad.')
            : response()->json('Error al eliminar tu petición de amistad.', 500);
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
            $user = User::where('id', $fighterUser->user_id)->first();
            $newFighterUser = new \stdClass();

            $newFighterUser->user_id = $user->id;
            $newFighterUser->name = $user->name;
            $newFighterUser->cups = $fighterUser->cups;
            $newFighterUser->rank = $fighterUser->rank;

            $returnFighterUsers[] = $newFighterUser;
        }

        return response()->json($returnFighterUsers);
    }

    public function findFighterUserBattle()
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

        if (!$fighterUser->ready_to_play) {
            $isFighterUserDeckValid = FighterBattle::checkFighterUserDeck($fighterUser, $user);

            if (!$isFighterUserDeckValid) {
                return response()->json('El mazo no es válido.', 400);
            }

            $fighterUser->ready_to_play = true;
            $fighterUser->playing_with_user = null;
            $fighterUser->ready_to_play_last = new Carbon();
        }


        if (!$fighterUser->playing_with_user && $fighterUser->ready_to_play_last < Carbon::now()->subSeconds(56)) {
            $fighterUser->ready_to_play = false;
            $fighterUser->playing_with_user = null;
        } else if (!$fighterUser->playing_with_user) {
            if ($fighterUser->ready_to_play_last < Carbon::now()->subSeconds(46)) {
                $fighterUserToBattle = FighterBattle::findFighterUserBotToBattle();
            } else {
                $fighterUserToBattle = FighterBattle::findFighterUserToBattle();
            }

            if ($fighterUserToBattle) {
                $fighterPastsSave = FighterBattle::prepareFighterUsersToBattle($fighterUser, $fighterUserToBattle);

                if ($fighterPastsSave) {
                    $fighterUserSave = $fighterUser->save();
                    $fighterUserToBattleSave = $fighterUserToBattle->save();

                    return $fighterUserSave && $fighterUserToBattleSave
                        ? response()->json('Se ha establecido un luchador oponente.')
                        : response()->json('Error al establecer el luchador oponente.', 500);
                }

                return response()->json('Error al establecer la batalla.', 500);
            }
        } else {
            return response()->json('Se ha establecido un luchador oponente.');
        }

        $fighterUserSave = $fighterUser->save();

        return $fighterUserSave
            ? response()->json('Se ha establecido una petición de batalla.')
            : response()->json('Error al establecer la petición de batalla.', 500);
    }

    public function saveFighterUserBattleTurn(Request $request)
    {
        $data = $request->validate(['card_left' => 'integer', 'card_center' => 'integer', 'card_right' => 'integer']);

        /** @var User $user */
        $user = auth()->user();
        if (!$user) {
            return response()->json('Login required.', 403);
        }

        $fighterUser = FighterUser::where('user_id', '=', $user->id)->first();
        if (!$fighterUser) {
            return response()->json('Error al obtener el usuario.', 404);
        }

        $fighterUserTurnSave = FighterBattle::saveFighterUserBattleTurn($fighterUser, $data);

        return $fighterUserTurnSave
            ? response()->json('Se ha guardado el turno.')
            : response()->json('No se ha guardado el turno.', 500);
    }

    public function resolveFighterUsersBattleTurn()
    {
        /** @var User $user */
        $user = auth()->user();
        if (!$user) {
            return response()->json('Login required.', 403);
        }

        $fighterUser = FighterUser::where('user_id', '=', $user->id)->first();
        if (!$fighterUser) {
            return response()->json('Error al obtener el usuario.', 404);
        }

        $fighterUsersBattleTurn = FighterBattle::resolveFighterUsersBattleTurn($fighterUser);

        return $fighterUsersBattleTurn
            ? response()->json('Se ha guardado la resolución del turno.')
            : response()->json('No se ha guardado la resolución del turno.', 500);
    }



    public function getFighterUserBattle()
    {
        /** @var User $user */
        $user = auth()->user();
        if (!$user) {
            return response()->json('Login required.', 403);
        }

        $fighterUser1 = FighterUser::where('user_id', '=', $user->id)->first();
        if (!$fighterUser1 || !$fighterUser1->playing_with_user) {
            return response()->json('Error al obtener el usuario.', 404);
        }

        $fighterUser2 = FighterUser::where('user_id', '=', $fighterUser1->playing_with_user)->first();
        $user2 = User::where('id', '=', $fighterUser1->playing_with_user)->first();
        if (!$fighterUser2 || !$user2) {
            return response()->json('Error al obtener el usuario.', 404);
        }

        $returnFighterUser1 = FighterUtilities::getFighterUserTransformer($user, $fighterUser1);
        $returnFighterUser2 = FighterUtilities::getFighterUserTransformer($user2, $fighterUser2);
        $return = new \stdClass();
        $return->fighter_user1 = $returnFighterUser1;
        $return->fighter_user2 = $returnFighterUser2;

        return response()->json($return);
    }
}
