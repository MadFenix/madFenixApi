<?php

namespace App\Console\Commands;

use App\Modules\Blockchain\Block\Infrastructure\Service\Polygon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Command;

class MetadataNSUJPGTypeResume extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metadata-nsujpg-type-resume';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Metadata NSUJPG type resume';

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
            if ($data) {
                foreach ($data->attributes as $attribute) {
                    if (strtolower($attribute->trait_type) == 'clase') {
                        $typo_encontrado = Polygon::limpiarString($attribute->value);
                    }
                }

                $this->info("Type $i $typo_encontrado.");
            } else {
                $this->warn("Error en typo $i");
            }
            if (empty($nftContentTypes->$typo_encontrado)) {
                $nftContentTypes->$typo_encontrado = 1;
            } else {
                $nftContentTypes->$typo_encontrado += 1;
            }
        }
        Storage::disk('nsujpg')->put("snap-nft/snap_tipos_resume.json", json_encode($nftContentTypes));

        // Imprimir resultado como JSON

        $this->info("Snapshot type resume guardado en snap-nft");

    }
}
