<?php


namespace App\Modules\Game\Season\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use App\Modules\Blockchain\Block\Domain\BlockchainHistorical;
use App\Modules\Blockchain\Block\Domain\HederaQueue;
use App\Modules\Blockchain\Block\Domain\Nft;
use App\Modules\Blockchain\Block\Domain\NftIdentification;
use App\Modules\Blockchain\Block\Infrastructure\Service\UserDragonCustodio;
use App\Modules\Game\Profile\Domain\Profile;
use App\Modules\Game\Ranking\Domain\Tournament;
use App\Modules\Game\Season\Domain\Season;
use App\Modules\Game\Season\Domain\SeasonReward;
use App\Modules\Game\Season\Domain\SeasonRewardRedeemed;
use App\Modules\Game\Season\Infrastructure\Service\UserSeasonPremium;
use App\Modules\User\Domain\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Season Rewards management
 *
 * APIs for managing season rewards
 */
class ApiReward extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Game\\SeasonReward';
    }

    protected function getParentIdentificator(): string
    {
        return 'season_id';
    }

    protected function getModelClass(): string
    {
        $modelName = $this->getModelName();
        $lastModelName = explode('\\', $modelName);
        $lastModelName = array_pop($lastModelName);

        return '\\App\\Modules\\Game\\Season\\Domain\\' . $lastModelName;
    }

    protected function getTransformerClass(): string
    {
        $modelName = $this->getModelName();
        $lastModelName = explode('\\', $modelName);
        $lastModelName = array_pop($lastModelName);

        return '\\App\\Modules\\Game\\Season\\Transformers\\' . $lastModelName;
    }

    /**
     * Display a listing of season rewards.
     *
     * Get a paginated list of all season rewards.
     *
     * @param Request $request
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter rewards by level. Example: "5"
     * @bodyParam sorting string Sort rewards by column and direction (column:direction). Example: "level:asc"
     * @bodyParam parent_id integer Filter rewards by season ID. Example: 1
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * Store a newly created season reward.
     *
     * Create a new season reward with the provided data.
     *
     * @bodyParam level integer required The level at which this reward is unlocked. Example: 5
     * @bodyParam required_points integer required The points required to reach this level. Example: 500
     * @bodyParam oro integer The amount of oro (gold) awarded. Example: 50
     * @bodyParam plumas integer The amount of plumas (feathers) awarded. Example: 100
     * @bodyParam nft_id integer The ID of the NFT awarded. Example: 1
     * @bodyParam max_nft_rewards integer The maximum number of NFT rewards available. Example: 100
     * @bodyParam custom_reward string Any custom reward description. Example: "Special avatar frame"
     * @bodyParam season_id integer required The ID of the season this reward belongs to. Example: 1
     * @return JsonResponse
     */
    public function store()
    {
        return parent::store();
    }

    /**
     * Display the specified season reward.
     *
     * Get details of a specific season reward by ID.
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
     * Update the specified season reward.
     *
     * Update an existing season reward with the provided data.
     *
     * @param string $account
     * @param int $id
     * @bodyParam level integer required The level at which this reward is unlocked. Example: 5
     * @bodyParam required_points integer required The points required to reach this level. Example: 500
     * @bodyParam oro integer The amount of oro (gold) awarded. Example: 50
     * @bodyParam plumas integer The amount of plumas (feathers) awarded. Example: 100
     * @bodyParam nft_id integer The ID of the NFT awarded. Example: 1
     * @bodyParam max_nft_rewards integer The maximum number of NFT rewards available. Example: 100
     * @bodyParam custom_reward string Any custom reward description. Example: "Special avatar frame"
     * @bodyParam season_id integer required The ID of the season this reward belongs to. Example: 1
     * @return JsonResponse
     */
    public function update($account, $id)
    {
        return parent::update($account, $id);
    }

    /**
     * Remove the specified season reward.
     *
     * Delete a season reward by ID.
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
     * Download season rewards as CSV or JSON.
     *
     * Export the season reward data in CSV or JSON format.
     *
     * @param Request $request
     * @bodyParam type string The file format to download (csv or json). Example: "csv"
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter rewards by level. Example: "5"
     * @bodyParam sorting string Sort rewards by column and direction (column:direction). Example: "level:asc"
     * @bodyParam parent_id integer Filter rewards by season ID. Example: 1
     * @return JsonResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(Request $request)
    {
        return parent::download($request);
    }

    /**
     * List the fields of the season reward model.
     *
     * Get the structure and field types of the season reward model.
     *
     * @param string $account
     * @return JsonResponse
     */
    public function fields($account)
    {
        return parent::fields($account);
    }

    /**
     * Upload a CSV file for bulk season reward processing.
     *
     * Upload a CSV file to create multiple season rewards at once.
     *
     * @param string $account
     * @bodyParam file file required The CSV file to upload (max 1MB). Must be a CSV file.
     * @bodyParam header_mapping array required Array of headers mapping to season reward fields.
     * @return JsonResponse
     */
    public function upload($account)
    {
        return parent::upload($account);
    }

    /**
     * Get the status of a bulk season reward upload.
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
     * Delete a bulk season reward upload.
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
}
