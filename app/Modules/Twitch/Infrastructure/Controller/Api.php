<?php


namespace App\Modules\Twitch\Infrastructure\Controller;

use App\Http\Controllers\Controller;
use App\Modules\Game\Profile\Domain\Profile;
use App\Modules\Twitch\Infrastructure\ApiHelix;
use Illuminate\Http\Request;

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
        $profile->twitch_user_id = $twitchUser->data->id;
        $profile->twitch_user_name = $twitchUser->data->login;

        $profileSaved = $profile->save();

        return $profileSaved
            ? redirect(env('SPA_WEBSITE') . '/profile')
            : response()->json('Error al guardar el perfil.', 500);
    }
}
