<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Command;

class MetadataPolygonContract721 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metadata-polygon-contract-721';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Metadata polygon contract 721';

    protected function fetch_metadata($url) {
        return file_get_contents($url);
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $baseUrl = "https://ipfs.io/ipfs/QmS2RWXstiLqQBvkvJxpc3BtLa9dwddfjUte8Ky23xTYAH";
        $allContent = '[';
        $totalNFTs = 2514;

        for ($i = 1; $i <= $totalNFTs; $i++) {
            $url = $baseUrl . "/" . $i . ".json";
            $data = $this->fetch_metadata($url);

            if ($data) {
                $allContent .= $data;
                if ($i < $totalNFTs) {
                    $allContent .= ',';
                }
                Storage::disk('nsujpg')->put("metadata-nft/" . $i . ".json", $data);
                $this->info("Token $i descargado.");
            } else {
                $this->warn("Error al descargar token $i");
            }

            usleep(300000); // 300ms para evitar sobrecarga (máx ~3 req/seg)
        }
        $allContent .= ']';
        Storage::disk('nsujpg')->put("metadata-nft/content.json", $allContent);

        $this->info("Descarga completa.");

        // Imprimir resultado como JSON

        $this->info("Snapshot guardado en content.json");

    }
}
