<?php


namespace App\Modules\Game\Coupon\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use App\Modules\Blockchain\Block\Domain\BlockchainHistorical;
use App\Modules\Game\Coupon\Domain\Coupon;
use App\Modules\Game\Coupon\Domain\CouponUser;
use App\Modules\Game\Profile\Domain\Profile;
use App\Modules\User\Domain\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Api extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Game\\Coupon';
    }

    public function usePlumasCoupon(Request $request)
    {
        $data = $request->validate(['coupon' => 'required|string']);
        /** @var User $user */
        $user = auth()->user();

        $profile = Profile::where('user_id', '=', $user->id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }
        $dateNow = Carbon::now();
        $coupon = Coupon::where('coupon', '=', $data['coupon'])
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

        $couponUser = new CouponUser();
        $couponUser->user_id = $user->id;
        $couponUser->coupon_id = $coupon->id;
        $couponUser->save();

        $coupon->uses++;
        $coupon->save();

        $profile->plumas += $coupon->plumas;
        $profileSaved = $profile->save();

        $newBlockchainHistorical = new BlockchainHistorical();
        $newBlockchainHistorical->user_id = $user->id;
        $newBlockchainHistorical->plumas = $coupon->plumas;
        $newBlockchainHistorical->memo = "Coupon";
        $blockchainHistoricalSaved = $newBlockchainHistorical->save();

        return $profileSaved && $blockchainHistoricalSaved
            ? response()->json('Se han sumado las plumas al usuario.')
            : response()->json('Error al guardar el perfil.', 500);
    }

}
