<?php


namespace App\Modules\Game\Coupon\Infrastructure\Controller;

use App\Modules\Base\Infrastructure\Controller\ResourceController;
use App\Modules\Game\Profile\Domain\Profile;
use App\Modules\Game\Profile\Transformers\Profile as ProfileTransformer;
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
        $data = $request->validate(['coupon' => 'required|integer']);
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

        $coupon->uses++;
        $coupon->save();

        $profile->plumas += $coupon->plumas;
        $profileSaved = $profile->save();

        return $profileSaved
            ? response()->json('Se han sumado las plumas al usuario.')
            : response()->json('Error al guardar el perfil.', 500);
    }

}
