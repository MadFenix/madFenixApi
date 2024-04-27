<?php
namespace App\Modules\Twitch\Infrastructure;


use App\Modules\Game\Profile\Domain\Profile;
use App\Modules\Game\Season\Domain\Season;
use App\Modules\Game\Season\Domain\SeasonReward;
use App\Modules\Twitch\Domain\TwitchReward;
use App\Modules\Twitch\Domain\TwitchRewardRedemption;
use Carbon\Carbon;

class ApiHelix
{
    protected $twitchApiUrl = 'https://api.twitch.tv/helix/';

    protected $twitchApiKey;

    protected $twitchApiSecret;

    protected $twitchAccessToken;

    protected $twitchUserId;

    protected $attemptToRefreshToken;

    function __construct() {
        $defaultProfile = Profile::find(env('TWITCH_DEFAULT_PROFILE'));
        $this->twitchApiKey = env('TWITCH_API_KEY');
        $this->twitchApiSecret = env('TWITCH_API_SECRET');
        $this->setProfile($defaultProfile);
        $this->attemptToRefreshToken = false;
    }

    function setProfile(Profile $profile) {
        $this->twitchAccessToken = $profile->twitch_api_user_token;
        $this->twitchUserId = $profile->twitch_user_id;
    }

    function setTwitchAccessToken($newAccessToken) {
        $this->twitchAccessToken = $newAccessToken;
    }

    function setTwitchUserId($newUserId) {
        $this->twitchUserId = $newUserId;
    }

    protected function getDefaultCurlHeaders() {
        return [
            'Authorization: Bearer ' . $this->twitchAccessToken,
            'Client-Id: ' . $this->twitchApiKey,
        ];
    }

    protected function decodeResponse($response) {
        $response = json_decode($response);
        if (isset($response->error)) {
            var_dump($response);

            if ($response->error == 'Unauthorized' && !$this->attemptToRefreshToken) {
                $this->attemptToRefreshToken = true;
                $refreshedToken = $this->refreshOAuthTokenUser();

                if ($refreshedToken) {
                    return 'token refreshed';
                }
            }

            throw new \Exception('Error to Twtich Api call.');
        }
        return $response;
    }

    protected function setDefaultCurlOpt($curl, $endpoint, $headers, $data, $method = 'POST') {
        curl_setopt($curl, CURLOPT_URL, $this->twitchApiUrl . $endpoint);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if ($method == 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }
        if ($method == 'PATCH') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }

    protected function setOAuthCurlOpt($curl, $data, $method = 'POST') {
        curl_setopt($curl, CURLOPT_URL, 'https://id.twitch.tv/oauth2/token');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if ($method == 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
    }

    public function createOAuthTokenApp() {
        $curl = curl_init();

        $data = 'client_id=' . $this->twitchApiKey;
        $data .= '&client_secret=' . $this->twitchApiSecret;
        $data .= '&grant_type=client_credentials';
        $this->setOAuthCurlOpt($curl, $data);

        $response = curl_exec($curl);
        curl_close($curl);

        if ($response) {
            $response = $this->decodeResponse($response);
            if (!$response) {
                return false;
            }

            $this->twitchAccessToken = $response->access_token;

            return true;
        }

        throw new \Exception('Error al obtener el access token');
    }

    public function createOAuthTokenUser($code) {
        $curl = curl_init();

        $data = 'client_id=' . $this->twitchApiKey;
        $data .= '&client_secret=' . $this->twitchApiSecret;
        $data .= '&code=' . $code;
        $data .= '&grant_type=authorization_code';
        $data .= '&redirect_uri=http://localhost:8000';
        $this->setOAuthCurlOpt($curl, $data);

        $response = curl_exec($curl);
        curl_close($curl);

        if ($response) {
            $response = $this->decodeResponse($response);
            if (!$response) {
                return false;
            }

            return $response;
        }

        throw new \Exception('Error al obtener el access token');
    }

    public function refreshOAuthTokenUser() {
        $profile = Profile::where('twitch_api_user_token', '=', $this->twitchAccessToken)->first();
        if (!$profile) {
            return false;
        }

        $curl = curl_init();

        $data = 'client_id=' . $this->twitchApiKey;
        $data .= '&client_secret=' . $this->twitchApiSecret;
        $data .= '&grant_type=refresh_token';
        $data .= '&refresh_token=' . $profile->twitch_api_user_refresh_token;
        $this->setOAuthCurlOpt($curl, $data);

        $response = curl_exec($curl);
        curl_close($curl);

        if ($response) {
            $response = $this->decodeResponse($response);
            if (!$response) {
                return false;
            }

            $profile->twitch_api_user_token = $response->access_token;
            $profile->twitch_api_user_refresh_token = $response->refresh_token;
            $profile->save();

            return $response;
        }

        throw new \Exception('Error al obtener el nuevo access token');
    }

    public function getUser($login) {
        if (!$this->twitchAccessToken) {
            return false;
        }

        $curl = curl_init();
        $endpoint = 'users?login=' . $login;
        $headers = $this->getDefaultCurlHeaders();

        $data = new \stdClass();

        $this->setDefaultCurlOpt($curl, $endpoint, $headers, $data, 'GET');

        $response = curl_exec($curl);
        curl_close($curl);

        if ($response) {
            $response = $this->decodeResponse($response);
            if ($response == 'token refreshed') {
                $response = $this->getUser($login);
            }

            if (!$response) {
                return false;
            }

            return $response;
        }

        throw new \Exception('Error al crear el mensaje');
    }

    public function getUserFollowers() {
        if (!$this->twitchAccessToken) {
            return false;
        }

        $curl = curl_init();
        $endpoint = 'channels/followers?broadcaster_id=' . $this->twitchUserId;
        $headers = $this->getDefaultCurlHeaders();

        $data = new \stdClass();

        $this->setDefaultCurlOpt($curl, $endpoint, $headers, $data, 'GET');

        $response = curl_exec($curl);
        curl_close($curl);

        if ($response) {
            $response = $this->decodeResponse($response);
            if ($response == 'token refreshed') {
                $response = $this->getUserFollowers();
            }

            if (!$response) {
                return false;
            }

            return $response;
        }

        throw new \Exception('Error al crear el mensaje');
    }

    public function getUserFromToken() {
        if (!$this->twitchAccessToken) {
            return false;
        }

        $curl = curl_init();
        $endpoint = 'users';
        $headers = $this->getDefaultCurlHeaders();

        $data = new \stdClass();

        $this->setDefaultCurlOpt($curl, $endpoint, $headers, $data, 'GET');

        $response = curl_exec($curl);
        curl_close($curl);

        if ($response) {
            $response = $this->decodeResponse($response);
            if ($response == 'token refreshed') {
                $response = $this->getUserFromToken();
            }

            if (!$response) {
                return false;
            }

            return $response;
        }

        throw new \Exception('Error al crear el mensaje');
    }

    public function createChannelReward() {
        if (!$this->twitchAccessToken) {
            return false;
        }

        $curl = curl_init();
        $endpoint = 'channel_points/custom_rewards?broadcaster_id=' . $this->twitchUserId .
            '&title=' . urlencode('Puntos de temporada Mad Fénix') .
            '&cost=500' .
            '&background_color=#23202A';
        $headers = $this->getDefaultCurlHeaders();

        $data = new \stdClass();

        $this->setDefaultCurlOpt($curl, $endpoint, $headers, $data);

        $response = curl_exec($curl);
        curl_close($curl);

        if ($response) {
            $response = $this->decodeResponse($response);
            if ($response == 'token refreshed') {
                $response = $this->createChannelReward();
            }

            if (!$response) {
                return false;
            }

            return $response;
        }

        throw new \Exception('Error al crear el reward');
    }

    public function redeemCustomChannelRewardRedemption($twitchApiRewardRedemption, TwitchReward $twitchReward) {
        if (!$this->twitchAccessToken) {
            return false;
        }

        $profile = Profile::where('twitch_user_id', '=', $twitchApiRewardRedemption->user_id)->first();
        if (!$profile) {
            return false;
        }

        $twitchApiRewardRedemptionId = $twitchApiRewardRedemption->id;

        $twitchRewardRedemption = TwitchRewardRedemption::where('twitch_api_reward_redemption_id', '=', $twitchApiRewardRedemptionId)->first();
        if ($twitchRewardRedemption) {
            return false;
        }

        $curl = curl_init();
        $endpoint = 'channel_points/custom_rewards/redemptions?broadcaster_id=' . $this->twitchUserId .
            '&id=' . $twitchApiRewardRedemptionId .
            '&reward_id=' . $twitchApiRewardRedemption->reward->id;
        $headers = $this->getDefaultCurlHeaders();
        $headers = array_merge($headers, ['Content-Type: application/json']);

        $data = new \stdClass();
        $data->status = 'FULFILLED';

        $this->setDefaultCurlOpt($curl, $endpoint, $headers, $data, 'PATCH');

        $response = curl_exec($curl);
        curl_close($curl);

        if ($response) {
            $response = $this->decodeResponse($response);
            if ($response == 'token refreshed') {
                $response = $this->createChannelReward();
            }

            if (!$response) {
                return false;
            }

            $newTwitchRewardRedemption = new TwitchRewardRedemption();
            $newTwitchRewardRedemption->twitch_reward_id = $twitchReward->id;
            $newTwitchRewardRedemption->user_id = $profile->user_id;
            $newTwitchRewardRedemption->twitch_api_reward_redemption_id = $twitchApiRewardRedemptionId;
            $newTwitchRewardRedemption->save();

            $profile->season_points += 15000;

            $dateNow = Carbon::now();
            $activeSeason = Season::where('start_date', '<', $dateNow->format('Y-m-d H:i:s'))
                ->where('end_date', '>', $dateNow->format('Y-m-d H:i:s'))
                ->first();
            if ($activeSeason) {
                $lastSeasonReward = SeasonReward::where('season_id', '=', $activeSeason->id)
                    ->where('required_points', '<', $profile->season_points)
                    ->orderByDesc('level')
                    ->first();
                if ($lastSeasonReward) {
                    $profile->season_level = $lastSeasonReward->level;
                }
            }

            $profile->save();

            return $response;
        }

        throw new \Exception('Error al crear el reward');
    }

    public function getCustomChannelRewardRedemptions() {
        if (!$this->twitchAccessToken) {
            return false;
        }

        $twitchRewards = TwitchReward::all();

        foreach ($twitchRewards as $twitchReward) {
            $profile = Profile::where('user_id', '=', $twitchReward->user_id)->first();
            if (!$profile) {
                throw new \Exception('Profile not found');
            }
            $this->setProfile($profile);

            $curl = curl_init();
            $endpoint = 'channel_points/custom_rewards/redemptions?broadcaster_id=' . $this->twitchUserId .
                '&reward_id=' . $twitchReward->twitch_api_reward_id .
                '&status=UNFULFILLED';
            $headers = $this->getDefaultCurlHeaders();

            $data = new \stdClass();

            $this->setDefaultCurlOpt($curl, $endpoint, $headers, $data, 'GET');

            $response = curl_exec($curl);
            curl_close($curl);

            if ($response) {
                $response = $this->decodeResponse($response);
                if ($response == 'token refreshed') {
                    $response = $this->createChannelReward();
                }

                foreach ($response->data as $twitchApiRewardRedemption) {
                    $this->redeemCustomChannelRewardRedemption($twitchApiRewardRedemption, $twitchReward);
                }
            }
        }
    }
}
