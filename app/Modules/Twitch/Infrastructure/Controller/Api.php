<?php


namespace App\Modules\Twitch\Infrastructure\Controller;

use App\Http\Controllers\Controller;
use App\Modules\Game\Profile\Domain\Profile;
use App\Modules\Twitch\Infrastructure\ApiHelix;
use App\Modules\User\Domain\User;
use Illuminate\Http\Request;

/**
 * @group Twitch management
 *
 * APIs for managing teitch conections
 */
class Api extends Controller
{
    public function connectTwitchAccount(Request $request) {
        $data = $request->validate(['code' => 'required|string', 'scope' => 'required|string', 'state' => 'required|string']);

        $userId = substr($data['state'], 4);
        $profile = Profile::where('user_id', '=', $userId)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }

        $apiHelix = new ApiHelix();
        $oAuthTokenUser = $apiHelix->createOAuthTokenUser($data['code']);

        $profile->twitch_api_user_token = $oAuthTokenUser->access_token;
        $profile->twitch_api_user_refresh_token = $oAuthTokenUser->refresh_token;
        $profile->twitch_scope = json_encode($oAuthTokenUser->scope);

        $apiHelix->setTwitchAccessToken($profile->twitch_api_user_token);

        $twitchUser = $apiHelix->getUserFromToken();
        $profile->twitch_user_id = $twitchUser->data[0]->id;
        $profile->twitch_user_name = $twitchUser->data[0]->login;

        $profileSaved = $profile->save();

        return $profileSaved
            ? redirect(env('SPA_WEBSITE'))
            : response()->json('Error al guardar el perfil.', 500);
    }

    public function disconnectTwitch()
    {
        /** @var User $user */
        $user = auth()->user();

        $profile = Profile::where('user_id', '=', $user->id)->first();
        if (!$profile) {
            return response()->json('Perfil del usuario no encontrado.', 404);
        }

        $profile->twitch_api_user_token = null;
        $profile->twitch_api_user_refresh_token = null;
        $profile->twitch_scope = null;
        $profile->twitch_user_id = null;
        $profile->twitch_user_name = null;

        $profileSaved = $profile->save();

        return $profileSaved
            ? response()->json('Twitch desconectado del usuario.')
            : response()->json('Error al guardar el perfil.', 500);
    }
}
