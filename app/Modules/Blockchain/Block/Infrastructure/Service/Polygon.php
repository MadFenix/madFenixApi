<?php

namespace App\Modules\Blockchain\Block\Infrastructure\Service;

use GuzzleHttp\Client;

class Polygon
{
    public function getOwnedTokenIds(string $wallet, string $contract): array
    {
        $client = new Client(['base_uri' => 'https://api.polygonscan.com/api']);
        $apiKey = env('POLYGONSCAN_API_KEY');
        $page = 1;
        $owned = [];

        do {
            $response = $client->get('', [
                'query' => [
                    'module' => 'account',
                    'action' => 'tokennfttx',
                    'contractaddress' => $contract,
                    'address' => $wallet,
                    'page' => $page,
                    'offset' => 100,
                    'sort' => 'asc',
                    'apikey' => $apiKey,
                ],
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            if (!isset($data['result']) || !is_array($data['result'])) {
                break;
            }
            foreach ($data['result'] as $tx) {
                $id = $tx['tokenID'];
                if (strtolower($tx['to']) === strtolower($wallet)) {
                    $owned[$id] = true;
                }
                if (strtolower($tx['from']) === strtolower($wallet)) {
                    unset($owned[$id]);
                }
            }
            $page++;
        } while (count($data['result']) === 100);

        return array_keys($owned);
    }
}
