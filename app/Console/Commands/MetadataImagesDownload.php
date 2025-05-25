<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Command;

class MetadataImagesDownload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metadata-images-download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Metadata images download';

    protected function fetch_metadata($url) {
        return file_get_contents($url);
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $baseUrl = "https://ipfs.io/ipfs/QmcAcXTXe2ZUJMx39qxapYku3B2bzYkLLcU9sgmaU8ysR4";
        $totalNFTs = 2514;

        for ($i = 1; $i <= $totalNFTs; $i++) {
            $url = $baseUrl . "/" . $i . ".jpg";
            $data = $this->fetch_metadata($url);

            if ($data) {
                Storage::disk('nsujpg')->put("metadata-images/" . $i . ".jpg", $data);
                $this->info("Image $i descargado.");
            } else {
                $this->warn("Error al descargar image $i");
            }

            usleep(300000); // 300ms para evitar sobrecarga (máx ~3 req/seg)
        }

        $this->info("Descarga completa.");

        // Imprimir resultado como JSON

        $this->info("Snapshot guardado en metadata-images");

    }
}
