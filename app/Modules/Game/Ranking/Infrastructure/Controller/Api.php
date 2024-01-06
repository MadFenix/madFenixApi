<?php


namespace App\Modules\Game\Ranking\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use App\Modules\Game\Ranking\Domain\Ranking;
use App\Modules\User\Domain\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Api extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Game\\Ranking';
    }

    protected function getBestIntentOfUser($game, $user)
    {
        $fasesDelJuego = 1000;
        if ($game == 'BookersVillage') {
            $fasesDelJuego = 4;
        }

        $rankings = Ranking::where('game', '=', $game)->where('user_id', '=', $user->id)->where('fase', '=', 1)->orderBy('created_at', 'asc')->get();
        $intents = [];
        $currentIntentStart = null;
        $currentIntentEnd = null;
        foreach ($rankings as $ranking) {
            if ($game == 'BookersVillage') {
                $currentIntentStart = $ranking;
                $currentIntentEnd = Ranking::where('game', '=', $game)->where('user_id', '=', $user->id)->where('fase', '=', $fasesDelJuego)->where('created_at', '>', $ranking->created_at)->orderBy('created_at', 'asc')->first();

                if ($currentIntentStart && $currentIntentEnd) {
                    $intents[] = ($currentIntentEnd->created_at->timestamp - $currentIntentStart->created_at->timestamp);
                }
            }
        }

        $bestIntent = 0;
        foreach ($intents as $intent) {
            if ($intent < $bestIntent || $bestIntent === 0) {
                $bestIntent = $intent;
            }
        }

        return $bestIntent;
    }

    protected function get10BestIntent($game)
    {
        $fasesDelJuego = 1000;
        if ($game == 'BookersVillage') {
            $fasesDelJuego = 4;
        }

        $rankings = Ranking::where('game', '=', $game)->where('fase', '=', 1)->orderBy('created_at', 'asc')->get();
        $intents = [];
        $currentIntentStart = null;
        $currentIntentEnd = null;
        foreach ($rankings as $ranking) {
            if ($game == 'BookersVillage') {
                $currentIntentStart = $ranking;
                $currentIntentEnd = Ranking::where('game', '=', $game)->where('user_id', '=', $ranking->user_id)->where('fase', '=', $fasesDelJuego)->where('created_at', '>', $ranking->created_at)->orderBy('created_at', 'asc')->first();

                if ($currentIntentStart && $currentIntentEnd) {
                    $newIntent = new \stdClass();
                    $newIntent->record = ($currentIntentEnd->created_at->timestamp - $currentIntentStart->created_at->timestamp);
                    $newIntent->user = $ranking->user_id;
                    $newIntent->username = User::where('id', '=', $ranking->user_id)->first()->name;
                    $intents[] = $newIntent;
                }
            }
        }

        usort($intents, function($a, $b) {
            if ($a->record == $b->record) {
                return 0;
            }
            return ($a->record < $b->record) ? -1 : 1;
        });
        $bestIntents = [];
        $usersInBestIntents = [];
        foreach ($intents as $intent) {
            if (isset($intent) && !in_array($intent->user, $usersInBestIntents)) {
                $usersInBestIntents[] = $intent->user;
                $intent->record = number_format($intent->record, 2, ',', '.');
                $bestIntents[] = $intent;

                if (count($bestIntents) >= 10) {
                    break;
                }
            }
        }

        return $bestIntents;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getRanking(Request $request)
    {
        $data = $request->validate(['game' => 'required|string']);
        /** @var User $user */
        $user = auth()->user();

        $bestIntent = $this->getBestIntentOfUser($data['game'], $user);

        return $bestIntent
            ? response()->json($bestIntent)
            : response()->json('Error al establecer el mejor ranking', 500);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getGameStarted(Request $request)    {
        $data = $request->validate(['game' => 'required|string']);

        $gameStarted = 'No iniciado.';
        if ($data['game'] == 'BookersVillage') {
            $gameStarted = 'Iniciado.';
        }

        return response()->json($gameStarted);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getClassification(Request $request)    {
        $data = $request->validate(['game' => 'required|string']);

        $bestIntents = $this->get10BestIntent($data['game']);

        return $bestIntents
            ? response()->json($bestIntents)
            : response()->json('Error al establecer la clasificación', 500);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function addRanking(Request $request)
    {
        $data = $request->validate(['game' => 'required|string', 'network_group' => 'required|string', 'fase' => 'required|integer']);
        /** @var User $user */
        $user = auth()->user();

        $ranking = new Ranking();
        $ranking->user_id = $user->id;
        $ranking->game = $data['game'];
        $ranking->network_group = $data['network_group'];
        $ranking->fase = $data['fase'];
        $rankingSaved = $ranking->save();

        $friendsPrepared = Ranking::where('game', '=', $data['game'])->where('network_group', '=', $data['network_group'])->where('fase', '=', $data['fase'])->get();
        $friendsPreparedArray = [];
        foreach ($friendsPrepared as $friendPrepared) {
            if (!in_array($friendPrepared->user_id, $friendsPreparedArray)) {
                $friendsPreparedArray[] = $friendPrepared->user_id;
            }
        }

        $usersPrepared = new \stdClass();
        $usersPrepared->friendsPrepared = count($friendsPreparedArray);

        return $rankingSaved
            ? response()->json($usersPrepared)
            : response()->json('Error al guardar el ranking', 500);
    }
}
