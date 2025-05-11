<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Command;

class GetNFTCollectionImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get-nft-colllection-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'GetNFTCollectionImages';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tokenId = '0.0.2179656'; // Tu token ID
        $apiKey = ''; // Tu API SECRET de DragonGlass
        $from = 0;


        $pagina = 1;
        do {
            $this->info('Iniciamos la página ' . $pagina . ' de NFTs.');

            $url = "https://api.dragonglass.me/hedera/api/nfts?tokenId={$tokenId}&size=50&from=" . $from;

            $headers = [
                "x-api-key: $apiKey",
                "Accept: application/json"
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            if(curl_errno($ch)){
                $this->error('Error: ' . curl_error($ch));
                break;
            } else {
                $data = json_decode($response, true);
            }

            curl_close($ch);

            if (empty($data['data']) || count($data['data']) == 0) {
                $this->error("No se pudieron obtener más NFTs.");
                break;
            }
            $nfts = $data['data'];

            foreach ($nfts as $nft) {
                if (!isset($nft['imageUrl']) && !isset($nft['serialNumber'])) {
                    continue;
                }

                $serial = $nft['serialNumber'];

                if (str_starts_with($nft['imageUrl'], 'ipfs://')) {
                    $imageUrl = str_replace("ipfs://", "https://ipfs.io/ipfs/", $nft['imageUrl']);
                } elseif (str_starts_with($nft['imageUrl'], 'ar://')) {
                    $imageUrl = str_replace("ar://", "https://arweave.net/", $nft['imageUrl']);
                } elseif (str_starts_with($nft['imageUrl'], 'https://ipfs.io/ipfs/')) {
                    $imageUrl = $nft['imageUrl'];
                } else {
                    $this->warn("No se pudo descargar imagen para serial $serial.");
                    continue;
                }

                try {
                    $imageContent = $this->getWithTimeout($imageUrl, 15);
                    if (!$imageContent) {
                        $this->warn("No se pudo descargar imagen del serial $serial.");
                        continue;
                    }

                    $finfo = finfo_open();
                    $mime = finfo_buffer($finfo, $imageContent, FILEINFO_MIME_TYPE);
                    $extension = $this->extFromMime($mime) ?? 'jpg';
                    finfo_close($finfo);

                    $filename = "$serial.$extension";
                    Storage::disk('public')->put("nft-images/$filename", $imageContent);

                    $this->info("Imagen $filename guardada.");
                } catch (\Exception $e) {
                    $this->warn("Error al descargar serial $serial: " . $e->getMessage());
                }
            }

            $from += 50;
            $pagina++;

        } while ($from < 10000);

        $this->info("Descarga completada.");
        return 0;
    }

    private function getJson(string $url): ?array
    {
        $context = stream_context_create([
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ],
            "http" => [
                "timeout" => 10,
            ]
        ]);

        $response = @file_get_contents($url, false, $context);
        return $response ? json_decode($response, true) : null;
    }

    private function getWithTimeout(string $url, int $timeout = 15): ?string
    {
        $context = stream_context_create([
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ],
            "http" => [
                "timeout" => $timeout,
            ]
        ]);

        return @file_get_contents($url, false, $context) ?: null;
    }

    private function extFromMime($mime)
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
            default      => null,
        };
    }
}
