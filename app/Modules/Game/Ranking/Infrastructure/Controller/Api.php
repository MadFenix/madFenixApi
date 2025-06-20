<?php


namespace App\Modules\Game\Ranking\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use App\Modules\Game\Ranking\Domain\Ranking;
use App\Modules\User\Domain\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Ranking management
 *
 * APIs for managing rankings
 */
class Api extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Game\\Ranking';
    }

    /**
     * Display a listing of rankings.
     *
     * Get a paginated list of all rankings.
     *
     * @param Request $request
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter rankings by game. Example: "BookersVillage"
     * @bodyParam sorting string Sort rankings by column and direction (column:direction). Example: "created_at:desc"
     * @bodyParam parent_id integer Filter rankings by parent ID. Example: 1
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * Store a newly created ranking.
     *
     * Create a new ranking with the provided data.
     *
     * @bodyParam user_id integer required The ID of the user this ranking belongs to. Example: 1
     * @bodyParam game string required The game name (4-255 chars). Example: "BookersVillage"
     * @bodyParam network_group string required The network group (4-255 chars). Example: "Group1"
     * @bodyParam fase integer required The phase or level of the game. Example: 1
     * @return JsonResponse
     */
    public function store()
    {
        return parent::store();
    }

    /**
     * Display the specified ranking.
     *
     * Get details of a specific ranking by ID.
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
     * Update the specified ranking.
     *
     * Update an existing ranking with the provided data.
     *
     * @param string $account
     * @param int $id
     * @bodyParam user_id integer required The ID of the user this ranking belongs to. Example: 1
     * @bodyParam game string required The game name (4-255 chars). Example: "BookersVillage"
     * @bodyParam network_group string required The network group (4-255 chars). Example: "Group1"
     * @bodyParam fase integer required The phase or level of the game. Example: 1
     * @return JsonResponse
     */
    public function update($account, $id)
    {
        return parent::update($account, $id);
    }

    /**
     * Remove the specified ranking.
     *
     * Delete a ranking by ID.
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
     * Download rankings as CSV or JSON.
     *
     * Export the ranking data in CSV or JSON format.
     *
     * @param Request $request
     * @bodyParam type string The file format to download (csv or json). Example: "csv"
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter rankings by game. Example: "BookersVillage"
     * @bodyParam sorting string Sort rankings by column and direction (column:direction). Example: "created_at:desc"
     * @bodyParam parent_id integer Filter rankings by parent ID. Example: 1
     * @return JsonResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(Request $request)
    {
        return parent::download($request);
    }

    /**
     * List the fields of the ranking model.
     *
     * Get the structure and field types of the ranking model.
     *
     * @param string $account
     * @return JsonResponse
     */
    public function fields($account)
    {
        return parent::fields($account);
    }

    /**
     * Upload a CSV file for bulk ranking processing.
     *
     * Upload a CSV file to create multiple rankings at once.
     *
     * @param string $account
     * @bodyParam file file required The CSV file to upload (max 1MB). Must be a CSV file.
     * @bodyParam header_mapping array required Array of headers mapping to ranking fields.
     * @return JsonResponse
     */
    public function upload($account)
    {
        return parent::upload($account);
    }

    /**
     * Get the status of a bulk ranking upload.
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
     * Delete a bulk ranking upload.
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
     * Get user's best ranking for a game.
     *
     * Retrieve the best time/score for the current user in a specific game.
     *
     * @param Request $request
     * @bodyParam game string required The name of the game to get ranking for. Example: "BookersVillage"
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
     * Check if a game has been started.
     *
     * Determine if a specific game has been initialized or started.
     *
     * @param Request $request
     * @bodyParam game string required The name of the game to check. Example: "BookersVillage"
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
     * Get game classification/leaderboard.
     *
     * Retrieve the top 10 players and their scores for a specific game.
     *
     * @param Request $request
     * @bodyParam game string required The name of the game to get classification for. Example: "BookersVillage"
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
     * Add a new ranking entry.
     *
     * Record a new ranking entry for the current user in a specific game.
     *
     * @param Request $request
     * @bodyParam game string required The name of the game. Example: "BookersVillage"
     * @bodyParam network_group string required The network group identifier. Example: "Group1"
     * @bodyParam fase integer required The phase or level of the game. Example: 1
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
