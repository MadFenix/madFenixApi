<?php


namespace App\Modules\Game\Coupon\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use App\Modules\Blockchain\Block\Domain\BlockchainHistorical;
use App\Modules\Blockchain\Block\Domain\NftIdentification;
use App\Modules\Blockchain\Block\Infrastructure\Service\UserDragonCustodio;
use App\Modules\Game\Coupon\Domain\Coupon;
use App\Modules\Game\Coupon\Domain\CouponGold;
use App\Modules\Game\Coupon\Domain\CouponGoldUser;
use App\Modules\Game\Coupon\Domain\CouponItem;
use App\Modules\Game\Coupon\Domain\CouponItemUser;
use App\Modules\Game\Coupon\Domain\CouponUser;
use App\Modules\Game\Profile\Domain\Profile;
use App\Modules\User\Domain\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Api extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Game\\Coupon';
    }

    protected function usePlumasCouponFunctionality($couponString, $user)
    {
        $profile = Profile::where('user_id', '=', $user->id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }
        $dateNow = Carbon::now();
        $coupon = Coupon::where('coupon', '=', $couponString)
            ->where('start_date', '<', $dateNow->format('Y-m-d H:i:s'))
            ->where('end_date', '>', $dateNow->format('Y-m-d H:i:s'))
            ->first();
        if (!$coupon) {
            return response()->json('Cupón no encontrado.', 404);
        }
        if ($coupon->uses >= $coupon->max_uses) {
            return response()->json('Cupón gastado.', 403);
        }
        $couponUser = CouponUser::where('user_id', '=', $user->id)
            ->where('coupon_id', '=', $coupon->id)
            ->first();
        if ($couponUser) {
            return response()->json('Ya has usado este cupon con tu usuario.', 403);
        }

        $returnProductsOrdered = new \stdClass();
        $returnProductsOrdered->video_purchase = '';
        $returnProductsOrdered->oro = 0;
        $returnProductsOrdered->plumas = 0;
        $returnProductsOrdered->nfts = [];

        $couponUser = new CouponUser();
        $couponUser->user_id = $user->id;
        $couponUser->coupon_id = $coupon->id;
        $couponUser->save();

        $coupon->uses++;
        $coupon->save();

        $plumasToAdd = ceil($coupon->plumas * UserDragonCustodio::tokenMultiplier($profile));

        $returnProductsOrdered->plumas += $plumasToAdd;

        $profile->plumas += $plumasToAdd;
        $profileSaved = $profile->save();

        $newBlockchainHistorical = new BlockchainHistorical();
        $newBlockchainHistorical->user_id = $user->id;
        $newBlockchainHistorical->plumas = $plumasToAdd;
        $newBlockchainHistorical->memo = "Coupon";
        $blockchainHistoricalSaved = $newBlockchainHistorical->save();

        return $profileSaved && $blockchainHistoricalSaved
            ? response()->json($returnProductsOrdered)
            : response()->json('Error al guardar el perfil.', 500);
    }

    public function usePlumasCoupon(Request $request)
    {
        $data = $request->validate(['coupon' => 'required|string']);
        /** @var User $user */
        $user = auth()->user();

        return $this->usePlumasCouponFunctionality($data['coupon'], $user);
    }

    protected function useOroCouponFunctionality($couponString, $user)
    {
        $profile = Profile::where('user_id', '=', $user->id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }
        $dateNow = Carbon::now();
        $coupon = CouponGold::where('coupon', '=', $couponString)
            ->where('start_date', '<', $dateNow->format('Y-m-d H:i:s'))
            ->where('end_date', '>', $dateNow->format('Y-m-d H:i:s'))
            ->first();
        if (!$coupon) {
            return response()->json('Cupón no encontrado.', 404);
        }
        if ($coupon->uses >= $coupon->max_uses) {
            return response()->json('Cupón gastado.', 403);
        }
        $couponUser = CouponGoldUser::where('user_id', '=', $user->id)
            ->where('coupon_id', '=', $coupon->id)
            ->first();
        if ($couponUser) {
            return response()->json('Ya has usado este cupon con tu usuario.', 403);
        }

        $returnProductsOrdered = new \stdClass();
        $returnProductsOrdered->video_purchase = '';
        $returnProductsOrdered->oro = 0;
        $returnProductsOrdered->plumas = 0;
        $returnProductsOrdered->nfts = [];

        $couponUser = new CouponGoldUser();
        $couponUser->user_id = $user->id;
        $couponUser->coupon_id = $coupon->id;
        $couponUser->save();

        $coupon->uses++;
        $coupon->save();

        $oroToAdd = ceil($coupon->oro * UserDragonCustodio::tokenMultiplier($profile));

        $returnProductsOrdered->oro += $oroToAdd;

        $profile->oro += $oroToAdd;
        $profileSaved = $profile->save();

        $newBlockchainHistorical = new BlockchainHistorical();
        $newBlockchainHistorical->user_id = $user->id;
        $newBlockchainHistorical->piezas_de_oro_ft = $oroToAdd;
        $newBlockchainHistorical->memo = "Coupon";
        $blockchainHistoricalSaved = $newBlockchainHistorical->save();

        return $profileSaved && $blockchainHistoricalSaved
            ? response()->json($returnProductsOrdered)
            : response()->json('Error al guardar el perfil.', 500);
    }

    public function useOroCoupon(Request $request)
    {
        $data = $request->validate(['coupon' => 'required|string']);
        /** @var User $user */
        $user = auth()->user();

        return $this->useOroCouponFunctionality($data['coupon'], $user);
    }

    protected function useItemCouponFunctionality($couponString, $user)
    {
        $profile = Profile::where('user_id', '=', $user->id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }
        $dateNow = Carbon::now();
        $coupon = CouponItem::where('coupon', '=', $couponString)
            ->where('start_date', '<', $dateNow->format('Y-m-d H:i:s'))
            ->where('end_date', '>', $dateNow->format('Y-m-d H:i:s'))
            ->first();
        if (!$coupon) {
            return response()->json('Cupón no encontrado.', 404);
        }
        if ($coupon->uses >= $coupon->max_uses) {
            return response()->json('Cupón gastado.', 403);
        }
        $couponUser = CouponItemUser::where('user_id', '=', $user->id)
            ->where('coupon_id', '=', $coupon->id)
            ->first();
        if ($couponUser) {
            return response()->json('Ya has usado este cupon con tu usuario.', 403);
        }

        $returnProductsOrdered = new \stdClass();
        $returnProductsOrdered->video_purchase = '';
        $returnProductsOrdered->oro = 0;
        $returnProductsOrdered->plumas = 0;
        $returnProductsOrdered->nfts = [];

        DB::beginTransaction();
        $nftIdentificationToAssociate = NftIdentification::where('nft_id', '=', $coupon->nft_id)
            ->whereNull('user_id')
            ->where('madfenix_ownership', '=', '1');
        if (!empty($coupon->rarity)) {
            $rarities = explode(',', $coupon->rarity);
            $nftIdentificationToAssociate = $nftIdentificationToAssociate
                ->where(function ($query) use($rarities) {
                    $query->where('rarity', '=', $rarities[0]);
                    foreach ($rarities as $key => $rarity) {
                        if (empty($key)) {
                            continue;
                        }
                        $query->orWhere('rarity', '=', $rarities[0]);
                    }
                });
        }
        if (!empty($coupon->tags)) {
            $tags = explode(',', $coupon->tags);
            $nftIdentificationToAssociate = $nftIdentificationToAssociate
                ->where(function ($query) use($tags) {
                    $query->where('tag_1', '=', $tags[0]);
                    $query->orWhere('tag_2', '=', $tags[0]);
                    $query->orWhere('tag_3', '=', $tags[0]);
                    foreach ($tags as $key => $tag) {
                        if (empty($key)) {
                            continue;
                        }
                        $query->orWhere('tag_1', '=', $tag);
                        $query->orWhere('tag_2', '=', $tag);
                        $query->orWhere('tag_3', '=', $tag);
                    }
                });
        }
        if (!empty($coupon->nft_serial_greater_equal)) {
            $nftIdentificationToAssociate = $nftIdentificationToAssociate
                ->where('nft_identification', '>=', $coupon->nft_serial_greater_equal);
        }
        if (!empty($coupon->nft_serial_less_equal)) {
            $nftIdentificationToAssociate = $nftIdentificationToAssociate
                ->where('nft_identification', '<=', $coupon->nft_serial_less_equal);
        }
        $nftIdentificationToAssociate = $nftIdentificationToAssociate
            ->lockForUpdate()
            ->first();
        if (!$nftIdentificationToAssociate) {
            DB::rollBack();
            return response()->json('No se ha encontrado el activo digital del cupón.', 404);
        }
        $nftIdentificationToAssociate->madfenix_ownership = false;
        $nftIdentificationToAssociate->user_id = $profile->user_id;
        $nftIdentificationToAssociate->save();
        DB::commit();

        $returnProductsOrdered->nfts[] = (object) $nftIdentificationToAssociate->toArray();

        $couponUser = new CouponItemUser();
        $couponUser->user_id = $user->id;
        $couponUser->coupon_id = $coupon->id;
        $couponUser->save();

        $coupon->uses++;
        $coupon->save();

        $oroToAdd = ceil($coupon->oro * UserDragonCustodio::tokenMultiplier($profile));

        $profile->oro += $oroToAdd;
        $profileSaved = $profile->save();

        $newBlockchainHistorical = new BlockchainHistorical();
        $newBlockchainHistorical->user_id = $user->id;
        $newBlockchainHistorical->nft_identification_id = $nftIdentificationToAssociate->id;
        $newBlockchainHistorical->memo = "Coupon";
        $blockchainHistoricalSaved = $newBlockchainHistorical->save();

        return $profileSaved && $blockchainHistoricalSaved
            ? response()->json($returnProductsOrdered)
            : response()->json('Error al guardar el perfil.', 500);
    }

    public function useItemCoupon(Request $request)
    {
        $data = $request->validate(['coupon' => 'required|string']);
        /** @var User $user */
        $user = auth()->user();

        return $this->useItemCouponFunctionality($data['coupon'], $user);
    }

    public function useCoupon(Request $request)
    {
        $data = $request->validate(['coupon' => 'required|string']);
        /** @var User $user */
        $user = auth()->user();

        $couponType = substr($data['coupon'], 0, 2);
        $couponString = substr($data['coupon'], 2);

        if ($couponType == 'p-') {
            return $this->usePlumasCouponFunctionality($couponString, $user);
        }
        if ($couponType == 'o-') {
            return $this->useOroCouponFunctionality($couponString, $user);
        }
        if ($couponType == 'i-') {
            return $this->useItemCouponFunctionality($couponString, $user);
        }

        return response()->json('Tipo de cupón no disponible.', 403);
    }
}
