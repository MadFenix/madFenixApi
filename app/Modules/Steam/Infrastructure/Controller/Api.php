<?php


namespace App\Modules\Steam\Infrastructure\Controller;

use App\Http\Controllers\Controller;
use App\Modules\Game\Profile\Domain\Profile;
use App\Modules\User\Domain\User;
use Illuminate\Http\Request;

/**
 * @group Steam management
 *
 * APIs for managing steam conections
 */
class Api extends Controller
{
    public function connectSteamAccount(Request $request) {
        $data = $request->all();
        var_dump($data);
        return response()->json('Test.');

        $userId = substr($data['state'], 4);
        $profile = Profile::where('user_id', '=', $userId)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }

        $profile->steam_user_id = $data['user_id'];
        $profile->steam_user_name = $data['user_name'];
        $profileSaved = $profile->save();
        $profileSaved = true;

        return $profileSaved
            ? redirect(env('SPA_WEBSITE'))
            : response()->json('Error al guardar el perfil.', 500);
    }

    public function disconnectSteam()
    {
        /** @var User $user */
        $user = auth()->user();

        $profile = Profile::where('user_id', '=', $user->id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }

        $profile->steam_user_id = null;
        $profile->steam_user_name = null;

        $profileSaved = $profile->save();

        return $profileSaved
            ? response()->json('Steam desconectado del usuario.')
            : response()->json('Error al guardar el perfil.', 500);
    }
}
