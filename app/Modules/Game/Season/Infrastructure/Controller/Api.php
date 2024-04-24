<?php


namespace App\Modules\Game\Season\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use App\Modules\Blockchain\Block\Domain\BlockchainHistorical;
use App\Modules\Blockchain\Block\Domain\HederaQueue;
use App\Modules\Blockchain\Block\Domain\Nft;
use App\Modules\Blockchain\Block\Domain\NftIdentification;
use App\Modules\Game\Profile\Domain\Profile;
use App\Modules\Game\Ranking\Domain\Tournament;
use App\Modules\Game\Season\Domain\Season;
use App\Modules\Game\Season\Domain\SeasonReward;
use App\Modules\Game\Season\Domain\SeasonRewardRedeemed;
use App\Modules\User\Domain\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Api extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Game\\Season';
    }

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

        $seasonRewards = SeasonReward::where('season_id', '=', $activeSeason->id)
            ->orderByAsc('level')
            ->get();
        if ($seasonRewards->count() <= 0) {
            return response()->json('No se ha encontrado las recompensas de la season.', 404);
        }

        $seasonDetails->seasonRewards = [];
        $seasonRewardRedeemeds = [];
        foreach ($seasonRewards as $seasonReward) {
            $newSeasonReward = (object) $seasonReward->toArray();
            if ($seasonReward->nft_id > 0) {
                $nft = Nft::find($seasonReward->nft_id);
                if ($nft) {
                    $newSeasonReward->nft = (object) $nft->toArray();
                }
            }
            $seasonDetails->seasonRewards[] = $newSeasonReward;
            $seasonRewardRedeemed = SeasonRewardRedeemed::where('season_reward_id', '=', $seasonReward->id)
                ->where('user_id', '=', $user->id)
                ->first();
            if ($seasonRewardRedeemed) {
                $newSeasonRewardRedeemed = (object) $seasonRewardRedeemed->toArray();
                $newSeasonRewardRedeemed->level = $seasonReward->level;
                $seasonRewardRedeemeds[] = $seasonRewardRedeemed;
            }
        }
        $seasonDetails->seasonRewardRedeemeds = $seasonRewardRedeemeds;

        return response()->json($seasonDetails);
    }

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

        if (!($profile->season_premium > 0) && !($data['level'] == 1 || $data['level'] == 5 || $data['level'] == 10 || $data['level'] == 15 || $data['level'] == 20)) {
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
            $profile->oro += $seasonReward->oro;
            $profileSaved = $profile->save();

            $newBlockchainHistorical = new BlockchainHistorical();
            $newBlockchainHistorical->user_id = $user->id;
            $newBlockchainHistorical->piezas_de_oro_ft = $seasonReward->oro;
            $newBlockchainHistorical->memo = "Season " . $activeSeason->id . ", reward lvl " . $seasonReward->level;
            $blockchainHistoricalSaved = $newBlockchainHistorical->save();

            if (!($profileSaved && $blockchainHistoricalSaved)) {
                return response()->json('Error al canjear la recompensa.', 500);
            }
        }

        if ($seasonReward->plumas > 0) {
            $profile->plumas += $seasonReward->plumas;
            $profileSaved = $profile->save();

            $newBlockchainHistorical = new BlockchainHistorical();
            $newBlockchainHistorical->user_id = $user->id;
            $newBlockchainHistorical->plumas = $seasonReward->plumas;
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

                if (!($profileSaved && $blockchainHistoricalSaved)) {
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
