<?php

namespace App\Console\Commands;

use App\Modules\Assistant\GoogleAI\Infrastructure\Service\GoogleAIConversation;
use App\Modules\Assistant\OpenAI\Domain\Assistance;
use App\Modules\Assistant\OpenAI\Domain\Run;
use Illuminate\Console\Command;

class GenerateAudiosFromGoogleAI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'google_ai:generate_audio';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create audio from Google AI';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $googleAI = new GoogleAIConversation();

        $googleAI->getAudioFromText('Gracias por su pedido. Si necesita algo más, no dude en llamarme.', '5', 'es-ES-Neural2-B', 'Male');
    }
}
