<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Command;

class MetadataNSUJPGType extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metadata-nsujpg-type';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Metadata NSUJPG type';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $totalNFTs = 2514;
        $nftContentTypes = new \stdClass();

        for ($i = 1; $i <= $totalNFTs; $i++) {
            $data = json_decode(Storage::disk('nsujpg')->get("metadata-nft/" . $i . ".json"));

            $typo_encontrado = 'undefinido';
            $nftId = 'nft_' . $i;
            if ($data) {
                foreach ($data->attributes as $attribute) {
                    if (strtolower($attribute->trait_type) == 'clase') {
                        $typo_encontrado = $attribute->value;
                    }
                }

                $this->info("Type $i $typo_encontrado.");
            } else {
                $this->warn("Error en typo $i");
            }
            $nftContentTypes->$nftId = $typo_encontrado;
        }
        Storage::disk('nsujpg')->put("snap-nft/snap_tipos.json", json_encode($nftContentTypes));

        // Imprimir resultado como JSON

        $this->info("Snapshot typer guardado en snap-nft");

    }
}
