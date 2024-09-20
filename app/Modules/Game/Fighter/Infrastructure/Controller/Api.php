<?php

namespace App\Modules\Game\Fighter\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use App\Modules\Blockchain\Block\Domain\NftIdentification;
use App\Modules\Game\Fighter\Domain\FighterFriend;
use App\Modules\Game\Fighter\Domain\FighterUser;
use App\Modules\Game\Profile\Domain\Profile;
use App\Modules\Game\Season\Domain\Season;
use App\Modules\Game\Season\Domain\SeasonReward;
use App\Modules\Game\Fighter\Infrastructure\FighterBattle;
use App\Modules\Game\Fighter\Infrastructure\FighterUtilities;
use App\Modules\User\Domain\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Api extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Game\\Fighter';
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
            $fighterUser = FighterUtilities::createFighterUser($user->id);
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
            $fighterUser = FighterUtilities::createFighterUser($user->id);
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

    public function findFighterUserBattle(Request $request)
    {
        $data = $request->validate(['bot' => 'required|boolean']);

        /** @var User $user */
        $user = auth()->user();
        if (!$user) {
            return response()->json('Login required.', 403);
        }

        $fighterUser = FighterUser::where('user_id', '=', $user->id)->first();
        if (!$fighterUser) {
            $fighterUser = FighterUtilities::createFighterUser($user->id);
        }

        if (!$fighterUser->ready_to_play) {
            $isFighterUserDeckValid = FighterBattle::checkFighterUserDeck($fighterUser, $user);

            if (!$isFighterUserDeckValid) {
                return response()->json('El mazo no es válido.', 400);
            }

            $fighterUser->ready_to_play = true;
            $fighterUser->playing_with_user = null;
            $fighterUser->ready_to_play_last = new Carbon();
            $fighterUser->playing_bot = false;
            if ($data['bot']) {
                $fighterUser->playing_bot = true;
            }
        }

        if ($fighterUser->playing_with_user && $fighterUser->playing_shift_date < Carbon::now()->subSeconds(100)) {
            $fighterUser->playing_hp = 0;
            $this->getFighterUserBattleResult($user, $fighterUser, false);

            return response()->json('Se ha cancelado la batalla activa.');
        } else if (!$fighterUser->playing_with_user && $fighterUser->ready_to_play_last < Carbon::now()->subSeconds(56)) {
            $fighterUser->ready_to_play = false;
            $fighterUser->playing_with_user = null;

            $fighterUserSave = $fighterUser->save();

            return $fighterUserSave
                ? response()->json('Se ha cancelado la petición de batalla.')
                : response()->json('Error al cancelar la petición de batalla.', 500);
        } else if (!$fighterUser->playing_with_user) {
            try {
                if ($fighterUser->ready_to_play_last < Carbon::now()->subSeconds(46) || $data['bot']) {
                    $fighterUserToBattle = FighterBattle::findFighterUserBotToBattle();
                } else {
                    $fighterUserToBattle = FighterBattle::findFighterUserToBattle($user);
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
            }catch (\Exception $e) {
                return response()->json('Error al buscar un oponente.', 500);
            }

        } else {
            return response()->json('Se ha establecido un luchador oponente.');
        }

        if (empty($data['bot'])){
            $fighterUserSave = $fighterUser->save();
        } else {
            $fighterUserSave = true;
        }

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

        if (!$fighterUser->ready_to_play || empty($fighterUser->playing_with_user)) {
            return response()->json('No hay ningún combate activo.');
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

        if (!$fighterUser->ready_to_play || empty($fighterUser->playing_with_user)) {
            return response()->json('No hay ningún combate activo.');
        }

        $fighterUsersBattleTurn = FighterBattle::resolveFighterUsersBattleTurn($fighterUser);

        return $fighterUsersBattleTurn
            ? response()->json('Se ha guardado la resolución del turno.')
            : response()->json('No se ha guardado la resolución del turno.', 500);
    }

    public function getFighterUserBattleResult($user, FighterUser $fighterUser1, $generateResponse = true) {
        if (!$fighterUser1->ready_to_play || empty($fighterUser1->playing_with_user)) {
            if ($generateResponse) {
                return response()->json('No hay ningún combate activo.');
            } else {
                return true;
            }
        }

        $fighterUser2 = FighterUser::where('user_id', '=', $fighterUser1->playing_with_user)->first();
        $user2 = User::where('id', '=', $fighterUser1->playing_with_user)->first();
        if (!$fighterUser2 || !$user2) {
            if ($generateResponse) {
                return response()->json('Error al obtener el usuario.', 404);
            } else {
                return false;
            }
        }

        $result = 'playing'; // playing, won, defeat, tied
        $cups_won = 0;
        $pointsToSeason = 0;
        if (
            empty($fighterUser1->playing_hp) || (empty($fighterUser1->playing_deck) && empty($fighterUser1->playing_hand)) ||
            empty($fighterUser2->playing_hp) || (empty($fighterUser2->playing_deck) && empty($fighterUser2->playing_hand))
        ) {
            $profile = Profile::where('user_id', '=', $user->id)->first();

            $fighterUser1->ready_to_play = false;
            $fighterUser1->playing_with_user = null;

            $result = 'defeat';
            if (empty($fighterUser2->playing_hp) && !empty($fighterUser1->playing_hp)) {
                $result = 'won';
            } else if (!empty($fighterUser2->playing_hp) && !empty($fighterUser1->playing_hp) && $fighterUser1->playing_hp < $fighterUser2->playing_hp) {
                $result = 'won';
            } else if ($fighterUser2->playing_hp == $fighterUser1->playing_hp) {
                $result = 'tied';
            }
            if ($fighterUser1->playing_bot) {
                $cups_won = 0;
                $pointsToSeason = 3000;
            } else if (in_array($user2->id, FighterUtilities::getUserIdBots())) {
                if ($result == 'won') {
                    $cups_won = 7;
                    $pointsToSeason = 30000;
                }
                if ($result == 'tied') {
                    $cups_won = 3;
                    $pointsToSeason = 12000;
                }
                if ($result == 'defeat') {
                    $cups_won = 1;
                    $pointsToSeason = 6000;
                }
            } else {
                if ($result == 'won') {
                    $cups_won = 11;
                    $pointsToSeason = 45000;
                }
                if ($result == 'tied') {
                    $cups_won = 5;
                    $pointsToSeason = 20000;
                }
                if ($result == 'defeat') {
                    $cups_won = 1;
                    $pointsToSeason = 10000;
                }
            }
            $fighterUser1->cups += $cups_won;

            $profile->season_points += $pointsToSeason;

            $dateNow = Carbon::now();
            $activeSeason = Season::where('start_date', '<', $dateNow->format('Y-m-d H:i:s'))
                ->where('end_date', '>', $dateNow->format('Y-m-d H:i:s'))
                ->first();
            if ($activeSeason) {
                $lastSeasonReward = SeasonReward::where('season_id', '=', $activeSeason->id)
                    ->where('required_points', '<', $profile->season_points)
                    ->orderByDesc('level')
                    ->first();
                if ($lastSeasonReward) {
                    $profile->season_level = $lastSeasonReward->level;
                }
            }

            $profile->save();

            $fighterUsersWithMoreCups = FighterUser::where('cups', '>', $fighterUser1->cups)
                ->where('user_id', '!=' , $fighterUser1->user_id)
                ->orderBy('cups')
                ->limit(10)
                ->get();
            if ($fighterUsersWithMoreCups->count() < 10) {
                $fighterUser1->rank = 'legend';
                $fighterUsersWithMoreCups = FighterUser::where('cups', '>', $fighterUser1->cups)
                    ->orderBy('cups')
                    ->limit(11)
                    ->get();

                if ($fighterUsersWithMoreCups->count() > 10) {
                    if ($fighterUsersWithMoreCups[0]->cups >= 1500) {
                        $fighterUsersWithMoreCups[0]->rank = 'gold';
                    } else {
                        $fighterUsersWithMoreCups[0]->rank = 'iron';
                    }
                    $fighterUsersWithMoreCups[0]->save();
                }
            } else if ($fighterUser1->cups >= 1500) {
                $fighterUser1->rank = 'gold';
            } else {
                $fighterUser1->rank = 'iron';
            }

            $fighterUser1->playing_deck = null;
            $fighterUser1->playing_hand = null;
            $fighterUser1->playing_hp = null;
            $fighterUser1->playing_pa = null;
            $fighterUser1->playing_shift = null;
            $fighterUser1->playing_card_left = null;
            $fighterUser1->playing_card_center = null;
            $fighterUser1->playing_card_right = null;
            $fighterUser1->playing_card_left_back = null;
            $fighterUser1->playing_card_center_back = null;
            $fighterUser1->playing_card_right_back = null;
            $fighterUser1->playing_shift_resolved = null;
            $fighterUser1->playing_bot = null;
            $fighterUser1->playing_shift_date = null;

            $fighterUser1->save();

            if (in_array($user2->id, FighterUtilities::getUserIdBots())) {
                $fighterUser2->ready_to_play = false;
                $fighterUser2->playing_with_user = null;
                $fighterUser2->playing_deck = null;
                $fighterUser2->playing_hand = null;
                $fighterUser2->playing_hp = null;
                $fighterUser2->playing_pa = null;
                $fighterUser2->playing_shift = null;
                $fighterUser2->playing_card_left = null;
                $fighterUser2->playing_card_center = null;
                $fighterUser2->playing_card_right = null;
                $fighterUser2->playing_card_left_back = null;
                $fighterUser2->playing_card_center_back = null;
                $fighterUser2->playing_card_right_back = null;
                $fighterUser2->playing_shift_resolved = null;
                $fighterUser2->playing_bot = null;
                $fighterUser2->playing_shift_date = null;

                $fighterUser2->save();
            }
        }

        $returnFighterUser1 = FighterUtilities::getFighterUserTransformer($user, $fighterUser1);
        $returnFighterUser2 = FighterUtilities::getFighterUserTransformer($user2, $fighterUser2);
        $return = new \stdClass();
        $return->fighter_user1 = $returnFighterUser1;
        $return->fighter_user2 = $returnFighterUser2;
        $return->result = $result;
        if ($result == 'playing') {
            $return->cups_won = false;
            $return->season_points_won = false;
        } else {
            $return->cups_won = $cups_won;
            $return->season_points_won = $pointsToSeason;
        }

        if ($generateResponse) {
            return response()->json($return);
        } else {
            return true;
        }
    }

    public function getFighterUserBattle()
    {
        /** @var User $user */
        $user = auth()->user();
        if (!$user) {
            return response()->json('Login required.', 403);
        }

        $fighterUser1 = FighterUser::where('user_id', '=', $user->id)->first();
        if (!$fighterUser1) {
            return response()->json('Error al obtener el usuario.', 404);
        }

        return $this->getFighterUserBattleResult($user, $fighterUser1);
    }
}
