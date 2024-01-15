<?php


namespace App\Modules\Game\Ranking\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use App\Modules\Game\Ranking\Domain\Ranking2024s1;
use App\Modules\User\Domain\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiSeason extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Game\\Ranking2024s1';
    }

    protected function getBestIntentOfUserPerTime($game, $user)
    {
        $fasesDelJuego = 1000;
        if ($game == 'NameOfGame') {
            $fasesDelJuego = 4;
        }

        $rankings = Ranking2024s1::where('game', '=', $game)->where('user_id', '=', $user->id)->where('fase', '=', 1)->orderBy('created_at', 'asc')->get();
        $intents = [];
        $currentIntentStart = null;
        $currentIntentEnd = null;
        foreach ($rankings as $ranking) {
            if ($game == 'NameOfGame') {
                $currentIntentStart = $ranking;
                $currentIntentEnd = Ranking2024s1::where('game', '=', $game)->where('user_id', '=', $user->id)->where('fase', '=', $fasesDelJuego)->where('created_at', '>', $ranking->created_at)->orderBy('created_at', 'asc')->first();

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

    protected function getBestIntentOfUserPerPoints($game, $user)
    {
        $bestRanking = Ranking2024s1::where('game', '=', $game)->where('user_id', '=', $user->id)->where('fase', '=', 1)->orderBy('points', 'desc')->first();

        $bestIntent = 0;
        if ($bestRanking) {
            $bestIntent = $bestRanking->points;
        }

        return $bestIntent;
    }

    protected function get10BestIntentPerTime($game, $user = null)
    {
        $fasesDelJuego = 1000;
        if ($game == 'NameOfGame') {
            $fasesDelJuego = 4;
        }

        $rankings = Ranking2024s1::where('game', '=', $game)->where('fase', '=', 1);
        if ($user !== null) {
            $rankings->where('user_id', '=', $user->id);
        }
        $rankings = $rankings->orderBy('created_at', 'asc')->get();
        $intents = [];
        $currentIntentStart = null;
        $currentIntentEnd = null;
        foreach ($rankings as $ranking) {
            if ($game == 'BookersVillage') {
                $currentIntentStart = $ranking;
                $currentIntentEnd = Ranking2024s1::where('game', '=', $game)->where('user_id', '=', $ranking->user_id)->where('fase', '=', $fasesDelJuego)->where('created_at', '>', $ranking->created_at)->orderBy('created_at', 'asc')->first();

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

    protected function get10BestIntentPerPoints($game, $user = null)
    {
        $rankings = Ranking2024s1::where('game', '=', $game)->where('fase', '=', 1);
        if ($user !== null) {
            $rankings->where('user_id', '=', $user->id);
        }
        $rankings = $rankings->orderBy('points', 'desc')->limit(10)->get();

        $bestIntents = [];
        foreach ($rankings as $ranking) {
            $newIntent = new \stdClass();
            $newIntent->points =$rankings->points;
            $newIntent->user = $ranking->user_id;
            $newIntent->username = User::where('id', '=', $ranking->user_id)->first()->name;
        }

        return $bestIntents;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getRankingPerTime(Request $request)
    {
        $data = $request->validate(['game' => 'required|string']);
        /** @var User $user */
        $user = auth()->user();

        $bestIntent = $this->getBestIntentOfUserPerTime($data['game'], $user);

        return $bestIntent
            ? response()->json($bestIntent)
            : response()->json('Error al establecer el mejor ranking', 500);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getRankingPerPoints(Request $request)
    {
        $data = $request->validate(['game' => 'required|string']);
        /** @var User $user */
        $user = auth()->user();

        $bestIntent = $this->getBestIntentOfUserPerPoints($data['game'], $user);

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
        if ($data['game'] == 'NameOfGame') {
            $gameStarted = 'Iniciado.';
        }
        if ($data['game'] == '2Elevado') {
            $gameStarted = 'Iniciado.';
        }

        return response()->json($gameStarted);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getClassificationPerTime(Request $request)    {
        $data = $request->validate(['game' => 'required|string']);

        $bestIntents = $this->get10BestIntentPerTime($data['game']);

        return $bestIntents
            ? response()->json($bestIntents)
            : response()->json('Error al establecer la clasificación', 500);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getClassificationPerPoints(Request $request)    {
        $data = $request->validate(['game' => 'required|string']);

        $bestIntents = $this->get10BestIntentPerPoints($data['game']);

        return $bestIntents
            ? response()->json($bestIntents)
            : response()->json('Error al establecer la clasificación', 500);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserClassificationPerTime(Request $request)    {
        $data = $request->validate(['game' => 'required|string']);
        /** @var User $user */
        $user = auth()->user();

        $bestIntents = $this->get10BestIntentPerTime($data['game'], $user);

        return $bestIntents
            ? response()->json($bestIntents)
            : response()->json('Error al establecer la clasificación', 500);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserClassificationPerPoints(Request $request)    {
        $data = $request->validate(['game' => 'required|string']);
        /** @var User $user */
        $user = auth()->user();

        $bestIntents = $this->get10BestIntentPerPoints($data['game'], $user);

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
        $data = $request->validate(['game' => 'required|string', 'network_group' => 'string', 'fase' => 'integer', 'points' => 'integer']);
        /** @var User $user */
        $user = auth()->user();

        $ranking = new Ranking2024s1();
        $ranking->user_id = $user->id;
        $ranking->game = $data['game'];
        $ranking->network_group = (empty($data['network_group']))? '' : $data['network_group'];
        $ranking->fase = (empty($data['fase']))? 1 : $data['fase'];
        $ranking->points = (empty($data['points']))? 0 : $data['points'];
        $rankingSaved = $ranking->save();

        return $rankingSaved
            ? response()->json($ranking)
            : response()->json('Error al guardar el ranking', 500);
    }
}
