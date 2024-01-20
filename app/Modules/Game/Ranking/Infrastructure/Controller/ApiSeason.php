<?php


namespace App\Modules\Game\Ranking\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use App\Modules\Game\Ranking\Domain\Ranking2024s1;
use App\Modules\Game\Ranking\Domain\Tournament;
use App\Modules\Game\Ranking\Domain\TournamentUser;
use App\Modules\User\Domain\User;
use Carbon\Carbon;
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

    protected function getTop10IntentsPerTimeTournament(Tournament $tournament)
    {
        $tournamentUsers = TournamentUser::where('tournament_id', '=', $tournament->id)->orderBy('max_time', 'asc')->limit(10)->get();

        $bestIntents = [];
        foreach ($tournamentUsers as $tournamentUser) {
            $newIntent = new \stdClass();
            $newIntent->record = $tournamentUser->max_time;
            $newIntent->user = $tournamentUser->user_id;
            $newIntent->username = $tournamentUser->user()->name;
            $bestIntents[] = $newIntent;
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
            $newIntent->points =$ranking->points;
            $newIntent->user = $ranking->user_id;
            $newIntent->username = User::where('id', '=', $ranking->user_id)->first()->name;
            $bestIntents[] = $newIntent;
        }

        return $bestIntents;
    }

    protected function getTop10IntentsPerPointsTournament(Tournament $tournament)
    {
        $tournamentUsers = TournamentUser::where('tournament_id', '=', $tournament->id)->orderBy('max_points', 'desc')->limit(10)->get();

        $bestIntents = [];
        foreach ($tournamentUsers as $tournamentUser) {
            $newIntent = new \stdClass();
            $newIntent->points = $tournamentUser->max_points;
            $newIntent->user = $tournamentUser->user_id;
            $newIntent->username = $tournamentUser->user()->name;
            $bestIntents[] = $newIntent;
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


        $classifications = [];
        $newClassification = new \stdClass();
        $newClassification->name = 'Personal';
        $newClassification->top10 = $this->get10BestIntentPerTime($data['game'], $user);
        $classifications[] = $newClassification;

        // Active user tournaments top10
        $dateNow = Carbon::now();
        $activeTournaments = Tournament::where('game', '=', $data['game'])
            ->where('start_date', '<', $dateNow)
            ->where('end_date', '>', $dateNow)
            ->get();
        $activeTournamentsIds = [];
        foreach ($activeTournaments as $activeTournament) {
            $activeTournamentsIds[] = $activeTournament->id;
        }
        $userTournaments = TournamentUser::where('user_id', '=', $user->id)->whereIn('tournament_id', $activeTournamentsIds);
        foreach ($userTournaments as $userTournament) {
            $newClassification = new \stdClass();
            $newClassification->name = $userTournament->tournament()->name;
            $newClassification->top10 = $this->getTop10IntentsPerTimeTournament($userTournament->tournament());
            $classifications[] = $newClassification;
        }
        // END Active user tournaments top10

        return $classifications
            ? response()->json($classifications)
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

        $classifications = [];
        $newClassification = new \stdClass();
        $newClassification->name = 'Personal';
        $newClassification->top10 = $this->get10BestIntentPerPoints($data['game'], $user);
        $classifications[] = $newClassification;

        // Active user tournaments top10
        $dateNow = Carbon::now();
        $activeTournaments = Tournament::where('game', '=', $data['game'])
            ->where('start_date', '<', $dateNow)
            ->where('end_date', '>', $dateNow)
            ->get();
        var_dump($activeTournaments);
        $activeTournamentsIds = [];
        foreach ($activeTournaments as $activeTournament) {
            $activeTournamentsIds[] = $activeTournament->id;
        }
        $userTournaments = TournamentUser::where('user_id', '=', $user->id)->whereIn('tournament_id', $activeTournamentsIds);
        foreach ($userTournaments as $userTournament) {
            $newClassification = new \stdClass();
            $newClassification->name = $userTournament->tournament()->name;
            $newClassification->top10 = $this->getTop10IntentsPerPointsTournament($userTournament->tournament());
            $classifications[] = $newClassification;
        }
        // END Active user tournaments top10

        return $classifications
            ? response()->json($classifications)
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

        // Active user tournaments maxpoints update
        $dateNow = Carbon::now();
        $activeTournaments = Tournament::where('game', '=', $data['game'])
            ->where('start_date', '<', $dateNow->format('Y-m-d H:i:s'))
            ->where('end_date', '>', $dateNow->format('Y-m-d H:i:s'))
            ->get();
        $activeTournamentsIds = [];
        foreach ($activeTournaments as $activeTournament) {
            $activeTournamentsIds[] = $activeTournament->id;
        }
        $userTournaments = TournamentUser::where('user_id', '=', $user->id)->whereIn('tournament_id', $activeTournamentsIds);
        foreach ($userTournaments as $userTournament) {
            if ($userTournament->max_points < $data['points']) {
                $userTournament->max_points = $data['points'];
                $userTournament->save();
            }
        }
        // END Active user tournaments maxpoints update

        return $rankingSaved
            ? response()->json($ranking)
            : response()->json('Error al guardar el ranking', 500);
    }
}
