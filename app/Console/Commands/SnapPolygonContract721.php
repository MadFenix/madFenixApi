<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Command;

class SnapPolygonContract721 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snap-polygon-contract-721';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Snapshot polygon contract 721';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Configuración
        $contractAddress = '0x6172974acedb93a0121b2a7b68b8acea0918be8c'; // reemplaza con la dirección del contrato
        $apiKey = 'KGVSTK2EN4T7N2M5RMW12PYUDGUG4N2TC6'; // gratuita desde polygonscan.com
        $startBlock = 25000000; // cambia esto al bloque de deploy real
        $endBlock = 'latest';
        $pageSize = 1000;
        $delayMs = 250; // 250ms = 4 peticiones por segundo

        $snapshot = [];

        // Obtener todos los logs del evento Transfer (keccak256 hash fijo)
        $topic0 = '0xddf252ad1be2c89b69c2b068fc378daa952ba7f163c4a11628f55a4df523b3ef';

        $page = 1;
        $hasMore = true;

        $this->info("Obteniendo eventos Transfer desde el contrato $contractAddress");

        while ($hasMore) {
            $url = "https://api.polygonscan.com/api?" . http_build_query([
                    'module' => 'logs',
                    'action' => 'getLogs',
                    'fromBlock' => $startBlock,
                    'toBlock' => $endBlock,
                    'address' => $contractAddress,
                    'topic0' => $topic0,
                    'apikey' => $apiKey,
                    'page' => $page,
                    'offset' => $pageSize
                ]);

            $json = file_get_contents($url);
            $data = json_decode($json, true);

            if ($data['status'] !== '1' || !isset($data['result'])) {
                $this->info("Sin más resultados o error: {$data['message']}");
                break;
            }

            $logs = $data['result'];
            if (count($logs) === 0) {
                $hasMore = false;
                break;
            }

            foreach ($logs as $log) {
                $topics = $log['topics'];
                if (count($topics) < 3) continue;

                $from = '0x' . substr($topics[1], 26);
                $to   = '0x' . substr($topics[2], 26);

                $tokenId = hexdec($topics[3]); // Indexed => aparece en topics[3]

                if (strtolower($to) === '0x0000000000000000000000000000000000000000') {
                    unset($snapshot[$tokenId]); // token quemado
                } else {
                    $snapshot[$tokenId] = $to;
                }
            }

            $this->info("Página $page procesada, total tokens: " . count($snapshot));
            $page++;
            usleep($delayMs * 1000);
        }

        // Imprimir resultado como JSON
        Storage::disk('nsujpg')->put("snap-nft/snapshot.json", json_encode($snapshot, JSON_PRETTY_PRINT));
        $this->info("Snapshot guardado en snapshot.json");

    }
}
