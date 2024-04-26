<?php
namespace App\Modules\Twitch\Infrastructure;


class ApiHelix
{
    protected $twitchApiUrl = 'https://api.twitch.tv/helix/';

    protected $twitchApiKey;

    protected $twitchApiSecret;

    protected $twitchAccessToken;

    protected $twitchUserId;

    function __construct() {
        $this->twitchApiKey = env('TWITCH_API_KEY');
        $this->twitchApiSecret = env('TWITCH_API_SECRET');
        $this->twitchAccessToken = env('TWITCH_API_USER_TOKEN');
        $this->twitchUserId = env('TWITCH_API_USER_ID');
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

            throw new \Exception('Error to Twtich Api call.');
        }
        return $response;
    }

    protected function setDefaultCurlOpt($curl, $endpoint, $headers, $data, $method = 'POST') {
        var_dump($this->twitchApiUrl . $endpoint);
        curl_setopt($curl, CURLOPT_URL, $this->twitchApiUrl . $endpoint);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if ($method == 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
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
            if (!$response) {
                return false;
            }

            return $response;
        }

        throw new \Exception('Error al crear el mensaje');
    }
}
