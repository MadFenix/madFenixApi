<?php
namespace App\Modules\Steam\Infrastructure;


use App\Modules\Game\Profile\Domain\Profile;
use App\Modules\Game\Season\Domain\Season;
use App\Modules\Game\Season\Domain\SeasonReward;
use Carbon\Carbon;

class ApiHelix
{
    protected $steamApiUrl = 'http://api.steampowered.com/';

    protected $steamApiKey;

    function __construct() {
        $this->steamApiKey = env('STEAM_API_KEY');
    }

    protected function getDefaultCurlHeaders() {
        return [
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
        curl_setopt($curl, CURLOPT_URL, $this->steamApiUrl . $endpoint);
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

    public function getUserStatsForGame($login) {
        if (!$this->steamAccessToken) {
            return false;
        }

        $curl = curl_init();
        $endpoint = 'ISteamUserStats/GetUserStatsForGame/v0002/?appid=440' .
            '&key=' . $this->steamApiKey .
            '&steamid=76561197972495328';
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
}
