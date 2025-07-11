<?php


namespace App\Modules\Game\Ranking\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use App\Modules\Game\Profile\Domain\Profile;
use App\Modules\Game\Ranking\Domain\Ranking2024s1;
use App\Modules\Game\Ranking\Domain\Tournament;
use App\Modules\Game\Ranking\Domain\TournamentUser;
use App\Modules\Game\Season\Domain\Season;
use App\Modules\Game\Season\Domain\SeasonReward;
use App\Modules\User\Domain\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Rankings season management
 *
 * APIs for managing rankings seasons
 */
class ApiSeason extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Game\\Ranking2024s1';
    }

    /**
     * Display a listing of season rankings.
     *
     * Get a paginated list of all season rankings.
     *
     * @param Request $request
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter rankings by game. Example: "NameOfGame"
     * @bodyParam sorting string Sort rankings by column and direction (column:direction). Example: "created_at:desc"
     * @bodyParam parent_id integer Filter rankings by parent ID. Example: 1
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * Store a newly created season ranking.
     *
     * Create a new season ranking with the provided data.
     *
     * @bodyParam user_id integer required The ID of the user this ranking belongs to. Example: 1
     * @bodyParam game string required The game name (4-255 chars). Example: "NameOfGame"
     * @bodyParam network_group string required The network group (4-255 chars). Example: "Group1"
     * @bodyParam fase integer required The phase or level of the game. Example: 1
     * @bodyParam points integer required The points scored in the game. Example: 1000
     * @return JsonResponse
     */
    public function store()
    {
        return parent::store();
    }

    /**
     * Display the specified season ranking.
     *
     * Get details of a specific season ranking by ID.
     *
     * @param string $account
     * @param int $id
     * @return JsonResponse
     */
    public function show($account, $id)
    {
        return parent::show($account, $id);
    }

    /**
     * Update the specified season ranking.
     *
     * Update an existing season ranking with the provided data.
     *
     * @param string $account
     * @param int $id
     * @bodyParam user_id integer required The ID of the user this ranking belongs to. Example: 1
     * @bodyParam game string required The game name (4-255 chars). Example: "NameOfGame"
     * @bodyParam network_group string required The network group (4-255 chars). Example: "Group1"
     * @bodyParam fase integer required The phase or level of the game. Example: 1
     * @bodyParam points integer required The points scored in the game. Example: 1000
     * @return JsonResponse
     */
    public function update($account, $id)
    {
        return parent::update($account, $id);
    }

    /**
     * Remove the specified season ranking.
     *
     * Delete a season ranking by ID.
     *
     * @param string $account
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy($account, Request $request)
    {
        return parent::destroy($account, $request);
    }

    /**
     * Download season rankings as CSV or JSON.
     *
     * Export the season ranking data in CSV or JSON format.
     *
     * @param Request $request
     * @bodyParam type string The file format to download (csv or json). Example: "csv"
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter rankings by game. Example: "NameOfGame"
     * @bodyParam sorting string Sort rankings by column and direction (column:direction). Example: "created_at:desc"
     * @bodyParam parent_id integer Filter rankings by parent ID. Example: 1
     * @return JsonResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(Request $request)
    {
        return parent::download($request);
    }

    /**
     * List the fields of the season ranking model.
     *
     * Get the structure and field types of the season ranking model.
     *
     * @param string $account
     * @return JsonResponse
     */
    public function fields($account)
    {
        return parent::fields($account);
    }

    /**
     * Upload a CSV file for bulk season ranking processing.
     *
     * Upload a CSV file to create multiple season rankings at once.
     *
     * @param string $account
     * @bodyParam file file required The CSV file to upload (max 1MB). Must be a CSV file.
     * @bodyParam header_mapping array required Array of headers mapping to season ranking fields.
     * @return JsonResponse
     */
    public function upload($account)
    {
        return parent::upload($account);
    }

    /**
     * Get the status of a bulk season ranking upload.
     *
     * Check the progress of a previously submitted bulk upload.
     *
     * @param string $account
     * @return JsonResponse
     */
    public function uploadStatus($account)
    {
        return parent::uploadStatus($account);
    }

    /**
     * Delete a bulk season ranking upload.
     *
     * Remove a pending or processing bulk upload.
     *
     * @param string $account
     * @param int $id
     * @return JsonResponse
     */
    public function deleteUpload($account, $id)
    {
        return parent::deleteUpload($account, $id);
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
            $newIntent->username = $tournamentUser->user->name;
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
            $newIntent->username = $tournamentUser->user->name;
            $bestIntents[] = $newIntent;
        }

        return $bestIntents;
    }

    /**
     * Get user's best time-based ranking for a game.
     *
     * Retrieve the best time for the current user in a specific game.
     *
     * @param Request $request
     * @bodyParam game string required The name of the game to get ranking for. Example: "NameOfGame"
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
     * Get user's best points-based ranking for a game.
     *
     * Retrieve the best points score for the current user in a specific game.
     *
     * @param Request $request
     * @bodyParam game string required The name of the game to get ranking for. Example: "NameOfGame"
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
     * Check if a season game has been started.
     *
     * Determine if a specific game has been initialized or started in the current season.
     *
     * @param Request $request
     * @bodyParam game string required The name of the game to check. Example: "NameOfGame"
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
     * Get season game time-based classification/leaderboard.
     *
     * Retrieve the top 10 players and their times for a specific game in the current season.
     *
     * @param Request $request
     * @bodyParam game string required The name of the game to get classification for. Example: "NameOfGame"
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
     * Get season game points-based classification/leaderboard.
     *
     * Retrieve the top 10 players and their points for a specific game in the current season.
     *
     * @param Request $request
     * @bodyParam game string required The name of the game to get classification for. Example: "NameOfGame"
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
     * Get user's time-based classification/leaderboard.
     *
     * Retrieve the user's personal time-based leaderboard and any tournament leaderboards they're part of.
     *
     * @param Request $request
     * @bodyParam game string required The name of the game to get classification for. Example: "NameOfGame"
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
        $userTournaments = TournamentUser::where('user_id', '=', $user->id)->whereIn('tournament_id', $activeTournamentsIds)->get();
        foreach ($userTournaments as $userTournament) {
            $newClassification = new \stdClass();
            $newClassification->name = $userTournament->tournament->name;
            $newClassification->top10 = $this->getTop10IntentsPerTimeTournament($userTournament->tournament);
            $classifications[] = $newClassification;
        }
        // END Active user tournaments top10

        return $classifications
            ? response()->json($classifications)
            : response()->json('Error al establecer la clasificación', 500);
    }

    /**
     * Get user's points-based classification/leaderboard.
     *
     * Retrieve the user's personal points-based leaderboard and any tournament leaderboards they're part of.
     *
     * @param Request $request
     * @bodyParam game string required The name of the game to get classification for. Example: "NameOfGame"
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
            ->where('start_date', '<', $dateNow->format('Y-m-d H:i:s'))
            ->where('end_date', '>', $dateNow->format('Y-m-d H:i:s'))
            ->get();
        $activeTournamentsIds = [];
        foreach ($activeTournaments as $activeTournament) {
            $activeTournamentsIds[] = $activeTournament->id;
        }
        $userTournaments = TournamentUser::where('user_id', '=', $user->id)->whereIn('tournament_id', $activeTournamentsIds)->get();
        foreach ($userTournaments as $userTournament) {
            $newClassification = new \stdClass();
            $newClassification->name = $userTournament->tournament->name;
            $newClassification->top10 = $this->getTop10IntentsPerPointsTournament($userTournament->tournament);
            $classifications[] = $newClassification;
        }
        // END Active user tournaments top10

        return $classifications
            ? response()->json($classifications)
            : response()->json('Error al establecer la clasificación', 500);
    }

    /**
     * Add a new season ranking entry.
     *
     * Record a new ranking entry for the current user in a specific game for the current season.
     *
     * @param Request $request
     * @bodyParam game string required The name of the game. Example: "NameOfGame"
     * @bodyParam network_group string The network group identifier. Example: "Group1"
     * @bodyParam fase integer The phase or level of the game. Example: 1
     * @bodyParam points integer The points scored in the game. Example: 1000
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

        if ($data['game'] == '2Elevado') {
            $profile = Profile::where('user_id', '=', $user->id)->first();
            if ($profile) {
                $pointsToSeason = 10000;
                if ($ranking->points > 1000000) {
                    $pointsToSeason = 15000;
                }
                if ($ranking->points > 10000000) {
                    $pointsToSeason = 20000;
                }
                if ($ranking->points > 100000000) {
                    $pointsToSeason = 25000;
                }
                if ($ranking->points > 1000000000) {
                    $pointsToSeason = 30000;
                }
                if ($ranking->points > 10000000000) {
                    $pointsToSeason = 35000;
                }
                if ($ranking->points > 50000000000) {
                    $pointsToSeason = 40000;
                }
                if ($ranking->points > 80000000000) {
                    $pointsToSeason = 45000;
                }

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
            }
        }

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
        $userTournaments = TournamentUser::where('user_id', '=', $user->id)->whereIn('tournament_id', $activeTournamentsIds)->get();
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
