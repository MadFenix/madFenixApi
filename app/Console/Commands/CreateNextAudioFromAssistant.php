<?php

namespace App\Console\Commands;

use App\Modules\Assistant\Infrastructure\Service\Conversation;
use App\Modules\Assistant\OpenAI\Domain\Assistance;
use App\Modules\Assistant\OpenAI\Domain\Run;
use App\Modules\Assistant\OpenAI\Infrastructure\Service\OpenAIConversation;
use Illuminate\Console\Command;

class CreateNextAudioFromAssistant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assistant:create_next_audio {assistant}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create next audio from conversation';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $assistant = Assistance::find($this->argument('assistant'));
        if (!$assistant) {
            throw new \Exception("Assistant not found.");
        }

        $lastRun = Run::where('assistance_id', $assistant->id)->orderBy('created_at', 'desc')->take(1)->get();
        if (!$lastRun || empty($lastRun[0])) {
            throw new \Exception("Last run not found.");
        }
        $lastRun = $lastRun[0];

        $toAssistant = Assistance::find($lastRun->assistance_to_id);
        if (!$assistant) {
            throw new \Exception("To assistant not found.");
        }

        if ($assistant->conversation_blocked || $toAssistant->conversation_blocked) {
            throw new \Exception("Assistant or to assistant conversation blocked.");
        }

        $assistant->conversation_blocked = true;
        $toAssistant->conversation_blocked = true;
        $assistant->save();
        $toAssistant->save();

        $lastToRun = Run::where('assistance_id', $toAssistant->id)->where('assistance_to_id', $assistant->id)->orderBy('created_at', 'desc')->take(1)->get();
        if (!$lastToRun || empty($lastToRun[0])) {
            $assistant->conversation_blocked = false;
            $toAssistant->conversation_blocked = false;
            $assistant->save();
            $toAssistant->save();

            throw new \Exception("Last to run not found.");
        }
        $lastToRun = $lastToRun[0];

        // LastToRun must be last response from conversation
        if ($lastRun->created_at > $lastToRun->created_at) {
            $bkpLastRun = $lastRun;
            $bkpAssistant = $assistant;
            $lastRun = $lastToRun;
            $assistant = $toAssistant;
            $lastToRun = $bkpLastRun;
            $toAssistant = $bkpAssistant;
        }
        $openAiConversation = new OpenAIConversation();
        $openAiConversationTo = new OpenAIConversation();

        $openAiConversation->setThredId($lastRun->thred_id);
        $openAiConversationTo->setThredId($lastToRun->thred_id);

        $finalResponse = false;
        if ($assistant->responsesToDo <= 1 || $toAssistant->responsesToDo <= 1) {
            $finalResponse = true;
        }

        if ($assistant->responsesToDo <= 0 || $toAssistant->responsesToDo <= 0) {
            $assistant->conversation_blocked = false;
            $toAssistant->conversation_blocked = false;
            $assistant->save();
            $toAssistant->save();

            echo 'No quedan respuestas para este asistente.';
            return;
        }

        try {
            Conversation::generateConversation($assistant, $toAssistant, $openAiConversation, $openAiConversationTo, $lastRun, $lastToRun, true, $finalResponse);
        } catch (\Exception $e) {
            $assistant->conversation_blocked = false;
            $toAssistant->conversation_blocked = false;
            $assistant->save();
            $toAssistant->save();

            throw new \Exception($e->getMessage());
        }

        $assistant->responsesToDo -= 1;
        $toAssistant->responsesToDo -= 1;
        $assistant->conversation_blocked = false;
        $toAssistant->conversation_blocked = false;
        $assistant->save();
        $toAssistant->save();
    }
}
