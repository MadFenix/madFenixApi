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

/**
 * @group Free Coin Coupon management
 *
 * APIs for managing free coin coupons
 */
class Api extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Game\\Coupon';
    }

    protected function getNameParameter(): string
    {
        return 'coupon';
    }

    /**
     * Display a listing of free coin coupons.
     *
     * Get a paginated list of all free coin coupons.
     *
     * @param Request $request
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter coupons by code. Example: "WELCOME"
     * @bodyParam sorting string Sort coupons by column and direction (column:direction). Example: "created_at:desc"
     * @bodyParam parent_id integer Filter coupons by parent ID. Example: 1
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        return parent::index($request);
    }

    /**
     * Store a newly created free coin coupon.
     *
     * Create a new free coin coupon with the provided data.
     *
     * @bodyParam coupon string required The coupon code (4-150 chars). Example: "WELCOME2023"
     * @bodyParam plumas integer The number of plumas (feathers) to award. Example: 100
     * @bodyParam uses integer The current number of uses. Example: 0
     * @bodyParam max_uses integer The maximum number of uses allowed. Example: 1000
     * @bodyParam start_date datetime required The start date and time of the coupon validity. Example: "2023-01-01 00:00:00"
     * @bodyParam end_date datetime required The end date and time of the coupon validity. Example: "2023-12-31 23:59:59"
     * @return \Illuminate\Http\JsonResponse
     */
    public function store()
    {
        return parent::store();
    }

    /**
     * Display the specified free coin coupon.
     *
     * Get details of a specific free coin coupon by ID.
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
     * Update the specified free coin coupon.
     *
     * Update an existing free coin coupon with the provided data.
     *
     * @param string $account
     * @param int $id
     * @bodyParam coupon string required The coupon code (4-150 chars). Example: "WELCOME2023"
     * @bodyParam plumas integer The number of plumas (feathers) to award. Example: 100
     * @bodyParam uses integer The current number of uses. Example: 0
     * @bodyParam max_uses integer The maximum number of uses allowed. Example: 1000
     * @bodyParam start_date datetime required The start date and time of the coupon validity. Example: "2023-01-01 00:00:00"
     * @bodyParam end_date datetime required The end date and time of the coupon validity. Example: "2023-12-31 23:59:59"
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($account, $id)
    {
        return parent::update($account, $id);
    }

    /**
     * Remove the specified free coin coupon.
     *
     * Delete a free coin coupon by ID.
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
     * Download free coin coupons as CSV or JSON.
     *
     * Export the free coin coupon data in CSV or JSON format.
     *
     * @param Request $request
     * @bodyParam type string The file format to download (csv or json). Example: "csv"
     * @bodyParam page integer The page number for pagination. Example: 0
     * @bodyParam limit integer The number of items per page (1-100). Example: 10
     * @bodyParam filter string Filter coupons by code. Example: "WELCOME"
     * @bodyParam sorting string Sort coupons by column and direction (column:direction). Example: "created_at:desc"
     * @bodyParam parent_id integer Filter coupons by parent ID. Example: 1
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(Request $request)
    {
        return parent::download($request);
    }

    /**
     * List the fields of the free coin coupon model.
     *
     * Get the structure and field types of the free coin coupon model.
     *
     * @param string $account
     * @return \Illuminate\Http\JsonResponse
     */
    public function fields($account)
    {
        return parent::fields($account);
    }

    /**
     * Upload a CSV file for bulk free coin coupon processing.
     *
     * Upload a CSV file to create multiple free coin coupons at once.
     *
     * @param string $account
     * @bodyParam file file required The CSV file to upload (max 1MB). Must be a CSV file.
     * @bodyParam header_mapping array required Array of headers mapping to free coin coupon fields.
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload($account)
    {
        return parent::upload($account);
    }

    /**
     * Get the status of a bulk free coin coupon upload.
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
     * Delete a bulk free coin coupon upload.
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

    /**
     * Use a plumas (feathers) coupon.
     *
     * Redeem a coupon code to receive plumas (feathers).
     *
     * @param Request $request
     * @bodyParam coupon string required The coupon code to redeem. Example: "WELCOME2023"
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Use an oro (gold) coupon.
     *
     * Redeem a coupon code to receive oro (gold).
     *
     * @param Request $request
     * @bodyParam coupon string required The coupon code to redeem. Example: "GOLD2023"
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Use an item coupon.
     *
     * Redeem a coupon code to receive an item.
     *
     * @param Request $request
     * @bodyParam coupon string required The coupon code to redeem. Example: "ITEM2023"
     * @return \Illuminate\Http\JsonResponse
     */
    public function useItemCoupon(Request $request)
    {
        $data = $request->validate(['coupon' => 'required|string']);
        /** @var User $user */
        $user = auth()->user();

        return $this->useItemCouponFunctionality($data['coupon'], $user);
    }

    /**
     * Use any type of coupon.
     *
     * Redeem a coupon code with a prefix to determine the type (p- for plumas, o- for oro, i- for item).
     *
     * @param Request $request
     * @bodyParam coupon string required The coupon code to redeem with prefix (p-, o-, i-). Example: "p-WELCOME2023"
     * @return \Illuminate\Http\JsonResponse
     */
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
