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
 * @group Season management
 *
 * APIs for managing seasons
 */
class Api extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Game\\Season';
    }

    /**
     * Display a listing of seasons.
     *
     * Get a paginated list of all seasons.
     *
     * @param Request $request
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter seasons by name. Example: "Summer Season"
     * @bodyParam sorting string Sort seasons by column and direction (column:direction). Example: "created_at:desc"
     * @bodyParam parent_id integer Filter seasons by parent ID. Example: 1
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * Store a newly created season.
     *
     * Create a new season with the provided data.
     *
     * @bodyParam name string required The name of the season. Example: "Summer Season 2023"
     * @bodyParam max_level integer required The maximum level achievable in this season. Example: 100
     * @bodyParam max_points integer required The maximum points achievable in this season. Example: 10000
     * @bodyParam start_date datetime required The start date and time of the season. Example: "2023-06-01 00:00:00"
     * @bodyParam end_date datetime required The end date and time of the season. Example: "2023-08-31 23:59:59"
     * @return JsonResponse
     */
    public function store()
    {
        return parent::store();
    }

    /**
     * Display the specified season.
     *
     * Get details of a specific season by ID.
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
     * Update the specified season.
     *
     * Update an existing season with the provided data.
     *
     * @param string $account
     * @param int $id
     * @bodyParam name string required The name of the season. Example: "Updated Summer Season 2023"
     * @bodyParam max_level integer required The maximum level achievable in this season. Example: 100
     * @bodyParam max_points integer required The maximum points achievable in this season. Example: 10000
     * @bodyParam start_date datetime required The start date and time of the season. Example: "2023-06-01 00:00:00"
     * @bodyParam end_date datetime required The end date and time of the season. Example: "2023-08-31 23:59:59"
     * @return JsonResponse
     */
    public function update($account, $id)
    {
        return parent::update($account, $id);
    }

    /**
     * Remove the specified season.
     *
     * Delete a season by ID.
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
     * Download seasons as CSV or JSON.
     *
     * Export the season data in CSV or JSON format.
     *
     * @param Request $request
     * @bodyParam type string The file format to download (csv or json). Example: "csv"
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter seasons by name. Example: "Summer Season"
     * @bodyParam sorting string Sort seasons by column and direction (column:direction). Example: "created_at:desc"
     * @bodyParam parent_id integer Filter seasons by parent ID. Example: 1
     * @return JsonResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(Request $request)
    {
        return parent::download($request);
    }

    /**
     * List the fields of the season model.
     *
     * Get the structure and field types of the season model.
     *
     * @param string $account
     * @return JsonResponse
     */
    public function fields($account)
    {
        return parent::fields($account);
    }

    /**
     * Upload a CSV file for bulk season processing.
     *
     * Upload a CSV file to create multiple seasons at once.
     *
     * @param string $account
     * @bodyParam file file required The CSV file to upload (max 1MB). Must be a CSV file.
     * @bodyParam header_mapping array required Array of headers mapping to season fields.
     * @return JsonResponse
     */
    public function upload($account)
    {
        return parent::upload($account);
    }

    /**
     * Get the status of a bulk season upload.
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
     * Delete a bulk season upload.
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

    /**
     * Get details of the current active season.
     *
     * Retrieve detailed information about the currently active season, including rewards and user progress.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function seasonDetails(Request $request)
    {
        $user = auth()->user();

        $seasonDetails = new \stdClass();

        $profile = Profile::where('user_id', '=', $user->id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }

        $dateNow = Carbon::now();
        $activeSeason = Season::where('start_date', '<', $dateNow->format('Y-m-d H:i:s'))
            ->where('end_date', '>', $dateNow->format('Y-m-d H:i:s'))
            ->first();
        if (!$activeSeason) {
            return response()->json('No se ha encontrado la season.', 404);
        }
        $seasonDetails = (object) $activeSeason->toArray();
        $seasonDetails->user_season_level = $profile->season_level;
        $seasonDetails->user_season_points = $profile->season_points;
        $seasonDetails->user_season_premium = UserSeasonPremium::isUserSeasonPremium($profile);

        $seasonRewards = SeasonReward::where('season_id', '=', $activeSeason->id)
            ->orderBy('level')
            ->get();
        if ($seasonRewards->count() <= 0) {
            return response()->json('No se ha encontrado las recompensas de la season.', 404);
        }

        $seasonDetails->seasonRewards = [];
        foreach ($seasonRewards as $seasonReward) {
            $newSeasonReward = (object) $seasonReward->toArray();
            $newSeasonReward->nft = null;
            if ($seasonReward->nft_id > 0) {
                $nft = Nft::find($seasonReward->nft_id);
                if ($nft) {
                    $newSeasonReward->nft = (object) $nft->toArray();
                }
            }
            $newSeasonReward->redeemed = null;
            $seasonRewardRedeemed = SeasonRewardRedeemed::where('season_reward_id', '=', $seasonReward->id)
                ->where('user_id', '=', $user->id)
                ->first();
            if ($seasonRewardRedeemed) {
                $newSeasonRewardRedeemed = (object) $seasonRewardRedeemed->toArray();
                $newSeasonRewardRedeemed->level = $seasonReward->level;
                $newSeasonReward->redeemed = $seasonRewardRedeemed;
            }
            $seasonDetails->seasonRewards[] = $newSeasonReward;
        }

        return response()->json($seasonDetails);
    }

    /**
     * Redeem a season level reward.
     *
     * Claim the reward for a specific level in the current season.
     *
     * @param Request $request
     * @bodyParam level integer required The level to redeem the reward for. Example: 5
     * @return JsonResponse
     */
    public function redeemSeasonLvl(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'level' => 'required',
        ]);
        $data['level'] = (int) $data['level'];

        $profile = Profile::where('user_id', '=', $user->id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }

        if (!(UserSeasonPremium::isUserSeasonPremium($profile) == true) && !($data['level'] == 1 || $data['level'] == 5 || $data['level'] == 10 || $data['level'] == 15 || $data['level'] == 20)) {
            return response()->json('No puedes canjear este nivel sin el pase de temporada premium.', 400);
        }

        $dateNow = Carbon::now();
        $activeSeason = Season::where('start_date', '<', $dateNow->format('Y-m-d H:i:s'))
            ->where('end_date', '>', $dateNow->format('Y-m-d H:i:s'))
            ->first();
        if (!$activeSeason) {
            return response()->json('No se ha encontrado la season.', 404);
        }

        $seasonReward = SeasonReward::where('season_id', '=', $activeSeason->id)
            ->where('level', '=', $data['level'])
            ->first();
        if (!$seasonReward) {
            return response()->json('No se ha encontrado la recompensa de la season.', 404);
        }

        if ($seasonReward->required_points > $profile->season_points) {
            return response()->json('No tienes puntos suficientes para esta recompensa de la season.', 400);
        }

        $seasonRewardRedeemed = SeasonRewardRedeemed::where('season_reward_id', '=', $seasonReward->id)
            ->where('user_id', '=', $user->id)
            ->first();
        if ($seasonRewardRedeemed) {
            return response()->json('Ya has canjeado el premio de este nivel.', 400);
        }

        $seasonRewardRedeemed = new SeasonRewardRedeemed();

        if ($seasonReward->oro > 0) {
            $oroToAdd = ceil($seasonReward->oro * UserDragonCustodio::tokenMultiplier($profile));
            $profile->oro += $oroToAdd;
            $profileSaved = $profile->save();

            $newBlockchainHistorical = new BlockchainHistorical();
            $newBlockchainHistorical->user_id = $user->id;
            $newBlockchainHistorical->piezas_de_oro_ft = $oroToAdd;
            $newBlockchainHistorical->memo = "Season " . $activeSeason->id . ", reward lvl " . $seasonReward->level;
            $blockchainHistoricalSaved = $newBlockchainHistorical->save();

            if (!($profileSaved && $blockchainHistoricalSaved)) {
                return response()->json('Error al canjear la recompensa.', 500);
            }
        }

        if ($seasonReward->plumas > 0) {
            $plumasToAdd = ceil($seasonReward->plumas * UserDragonCustodio::tokenMultiplier($profile));
            $profile->plumas += $plumasToAdd;
            $profileSaved = $profile->save();

            $newBlockchainHistorical = new BlockchainHistorical();
            $newBlockchainHistorical->user_id = $user->id;
            $newBlockchainHistorical->plumas = $plumasToAdd;
            $newBlockchainHistorical->memo = "Season " . $activeSeason->id . ", reward lvl " . $seasonReward->level;
            $blockchainHistoricalSaved = $newBlockchainHistorical->save();

            if (!($profileSaved && $blockchainHistoricalSaved)) {
                return response()->json('Error al canjear la recompensa.', 500);
            }
        }

        if ($seasonReward->nft_id > 0) {
            $nftSeasonRewardRedeemeds = SeasonRewardRedeemed::where('season_reward_id', '=', $seasonReward->id)
                ->get();

            if ($nftSeasonRewardRedeemeds->count() < $seasonReward->max_nft_rewards) {
                $nftIdentificationToAssociate = NftIdentification::where('nft_id', '=', $seasonReward->nft_id)
                    ->whereNull('user_id')
                    ->where('madfenix_ownership', '=', '1')
                    ->first();
                if (!$nftIdentificationToAssociate) {
                    return response()->json('No se ha encontrado el activo digital de la recompensa.', 404);
                }
                $nftIdentificationToAssociate->madfenix_ownership = false;
                $nftIdentificationToAssociate->user_id = $user->id;
                $nftIdentificationToAssociateSaved = $nftIdentificationToAssociate->save();

                $newBlockchainHistorical = new BlockchainHistorical();
                $newBlockchainHistorical->user_id = $user->id;
                $newBlockchainHistorical->nft_identification_id = $nftIdentificationToAssociate->id;
                $newBlockchainHistorical->memo = "Season " . $activeSeason->id . ", reward lvl " . $seasonReward->level;
                $blockchainHistoricalSaved = $newBlockchainHistorical->save();

                if (!($blockchainHistoricalSaved)) {
                    return response()->json('Error al canjear la recompensa.', 500);
                }
            }
        }

        $seasonRewardRedeemed->season_reward_id = $seasonReward->id;
        $seasonRewardRedeemed->user_id = $user->id;
        if (!empty($newBlockchainHistorical)) {
            $seasonRewardRedeemed->blockchain_historical_id = $newBlockchainHistorical->id;
        }
        $seasonRewardRedeemedSaved = $seasonRewardRedeemed->save();

        return $seasonRewardRedeemedSaved
            ? response()->json('Se ha canjeado el nivel.')
            : response()->json('Error al canjear el nivel.', 500);
    }
}
