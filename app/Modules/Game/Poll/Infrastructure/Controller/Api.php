<?php


namespace App\Modules\Game\Poll\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use App\Modules\Blockchain\Block\Domain\NftIdentification;
use App\Modules\Game\Poll\Domain\PollAnswer;
use App\Modules\Game\Poll\Infrastructure\Service\PollUtilities;
use App\Modules\Game\Profile\Domain\Profile;
use App\Modules\Game\Poll\Domain\Poll;
use App\Modules\Game\Poll\Domain\PollReward;
use App\Modules\Game\Poll\Domain\PollRewardRedeemed;
use App\Modules\Game\Poll\Infrastructure\Service\UserPollPremium;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * @group Poll management
 *
 * APIs for managing polls
 */
class Api extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Game\\Poll';
    }

    /**
     * Display a listing of polls.
     *
     * Get a paginated list of all polls.
     *
     * @param Request $request
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter polls by name. Example: "Community Survey"
     * @bodyParam sorting string Sort polls by column and direction (column:direction). Example: "created_at:desc"
     * @bodyParam parent_id integer Filter polls by parent ID. Example: 1
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * Store a newly created poll.
     *
     * Create a new poll with the provided data.
     *
     * @bodyParam name string required The name of the poll. Example: "Community Feedback Survey"
     * @bodyParam short_description string The short description of the poll. Example: "A quick survey about our latest features"
     * @bodyParam description string The detailed description of the poll. Example: "This survey aims to collect feedback about our latest platform features and improvements."
     * @bodyParam portrait_image string The portrait image URL of the poll. Example: "https://example.com/portrait.jpg"
     * @bodyParam featured_image string The featured image URL of the poll. Example: "https://example.com/featured.jpg"
     * @bodyParam answers string The possible answers for the poll in JSON format. Example: "['Yes', 'No', 'Maybe']"
     * @bodyParam start_date datetime required The start date and time of the poll. Example: "2023-01-01 00:00:00"
     * @bodyParam end_date datetime required The end date and time of the poll. Example: "2023-01-31 23:59:59"
     * @return \Illuminate\Http\JsonResponse
     */
    public function store()
    {
        return parent::store();
    }

    /**
     * Display the specified poll.
     *
     * Get details of a specific poll by ID.
     *
     * @param string $account
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($account, $id)
    {
        return parent::show($account, $id);
    }

    /**
     * Update the specified poll.
     *
     * Update an existing poll with the provided data.
     *
     * @param string $account
     * @param int $id
     * @bodyParam name string required The name of the poll. Example: "Updated Community Feedback Survey"
     * @bodyParam short_description string The short description of the poll. Example: "A quick survey about our latest features"
     * @bodyParam description string The detailed description of the poll. Example: "This survey aims to collect feedback about our latest platform features and improvements."
     * @bodyParam portrait_image string The portrait image URL of the poll. Example: "https://example.com/portrait.jpg"
     * @bodyParam featured_image string The featured image URL of the poll. Example: "https://example.com/featured.jpg"
     * @bodyParam answers string The possible answers for the poll in JSON format. Example: "['Yes', 'No', 'Maybe']"
     * @bodyParam start_date datetime required The start date and time of the poll. Example: "2023-01-01 00:00:00"
     * @bodyParam end_date datetime required The end date and time of the poll. Example: "2023-01-31 23:59:59"
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($account, $id)
    {
        return parent::update($account, $id);
    }

    /**
     * Remove the specified poll.
     *
     * Delete a poll by ID.
     *
     * @param string $account
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($account, Request $request)
    {
        return parent::destroy($account, $request);
    }

    /**
     * Download polls as CSV or JSON.
     *
     * Export the poll data in CSV or JSON format.
     *
     * @param Request $request
     * @bodyParam type string The file format to download (csv or json). Example: "csv"
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter polls by name. Example: "Community Survey"
     * @bodyParam sorting string Sort polls by column and direction (column:direction). Example: "created_at:desc"
     * @bodyParam parent_id integer Filter polls by parent ID. Example: 1
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(Request $request)
    {
        return parent::download($request);
    }

    /**
     * List the fields of the poll model.
     *
     * Get the structure and field types of the poll model.
     *
     * @param string $account
     * @return \Illuminate\Http\JsonResponse
     */
    public function fields($account)
    {
        return parent::fields($account);
    }

    /**
     * Upload a CSV file for bulk poll processing.
     *
     * Upload a CSV file to create multiple polls at once.
     *
     * @param string $account
     * @bodyParam file file required The CSV file to upload (max 1MB). Must be a CSV file.
     * @bodyParam header_mapping array required Array of headers mapping to poll fields.
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload($account)
    {
        return parent::upload($account);
    }

    /**
     * Get the status of a bulk poll upload.
     *
     * Check the progress of a previously submitted bulk upload.
     *
     * @param string $account
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadStatus($account)
    {
        return parent::uploadStatus($account);
    }

    /**
     * Delete a bulk poll upload.
     *
     * Remove a pending or processing bulk upload.
     *
     * @param string $account
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteUpload($account, $id)
    {
        return parent::deleteUpload($account, $id);
    }

    /**
     * Get details of a specific poll.
     *
     * Retrieve detailed information about a poll including its status for the current user.
     *
     * @param Request $request
     * @bodyParam poll_id integer required The ID of the poll to get details for. Example: 1
     * @bodyParam active boolean required Whether to only include active polls. Example: true
     * @return \Illuminate\Http\JsonResponse
     */
    public function pollDetails(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'poll_id' => 'required',
            'active' => 'required'
        ]);

        $active = true;
        if (empty($data['active'])) {
            $active = false;
        }
        try {
            $pollDetails = PollUtilities::pollDetails($data['poll_id'], $active, $user);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }

        return response()->json($pollDetails);
    }

    /**
     * Get details of polls from the last 30 days.
     *
     * Retrieve information about all polls that were active in the last 30 days.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function pollsDetailsLast30Days()
    {
        $user = auth()->user();

        $dateNow = Carbon::now();
        $date = Carbon::now();
        $date->subDays(30);
        $polls = Poll::where('start_date', '<', $dateNow->format('Y-m-d H:i:s'))
            ->where('end_date', '>', $date->format('Y-m-d H:i:s'))
            ->orderBy('start_date', 'DESC')
            ->get();
        $pollsReturn = [];
        foreach ($polls as $poll) {
            try {
                $pollsReturn[] = PollUtilities::pollDetails($poll->id, false, $user, $poll);
            } catch (\Exception $e) {
                return response()->json($e->getMessage(), 500);
            }
        }

        return response()->json($pollsReturn);
    }

    /**
     * Submit an answer to a poll.
     *
     * Record a user's response to a specific poll.
     *
     * @param Request $request
     * @bodyParam poll_id integer required The ID of the poll to answer. Example: 1
     * @bodyParam answer string required The user's answer to the poll. Example: "Yes"
     * @return \Illuminate\Http\JsonResponse
     */
    public function answerPoll(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'poll_id' => 'required',
            'answer' => 'required',
        ]);
        $answer = $data['answer'];
        $poll_id = $data['poll_id'];

        $profile = Profile::where('user_id', '=', $user->id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }
        $userAnswer = PollAnswer::where('poll_id', '=', $poll_id)
            ->where('user_id', '=', $user->id)
            ->first();
        if ($userAnswer) {
            return response()->json('Ya se ha registrado una respuesta con tu usuario en esta encuesta.', 403);
        }

        $dateNow = Carbon::now();
        $activePoll = Poll::where('start_date', '<', $dateNow->format('Y-m-d H:i:s'))
            ->where('end_date', '>', $dateNow->format('Y-m-d H:i:s'))
            ->where('id', '=', $poll_id)
            ->first();
        if (!$activePoll) {
            return response()->json('No se ha encontrado la poll.', 404);
        }

        $cronistas = NftIdentification::where('nft_id', '=', 34)
            ->where(function ($query) use($user) {
                $query->where('user_id', '=', $user->id)
                    ->orWhere('user_id_hedera', '=', $user->id);
            })
            ->count();
        $plumas = $profile->plumas + $profile->plumas_hedera;

        $pollAnswer = new PollAnswer();
        $pollAnswer->poll_id = $activePoll->id;
        $pollAnswer->user_id = $user->id;
        $pollAnswer->plumas = $plumas;
        $pollAnswer->cronistas = $cronistas;
        $pollAnswer->answer = $answer;
        $pollAnswerSaved = $pollAnswer->save();

        return $pollAnswerSaved
            ? response()->json('Se ha registrado la respuesta.')
            : response()->json('Error al registrar la respuesta.', 500);
    }
}
