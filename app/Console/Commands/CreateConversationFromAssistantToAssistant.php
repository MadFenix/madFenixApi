<?php

namespace App\Console\Commands;

use App\Modules\Assistant\Infrastructure\Service\Conversation;
use App\Modules\Assistant\OpenAI\Domain\Assistance;
use App\Modules\Assistant\OpenAI\Domain\Run;
use App\Modules\Assistant\OpenAI\Domain\WelcomeMessage;
use App\Modules\Assistant\OpenAI\Infrastructure\Service\OpenAIConversation;
use Illuminate\Console\Command;

class CreateConversationFromAssistantToAssistant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assistant:create_new_conversation {assistant} {toAssistant}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new conversation from Assistant to Assistant';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $assistant = Assistance::find($this->argument('assistant'));
        if (!$assistant) {
            throw new \Exception("Assistant not found.");
        }

        $toAssistant = Assistance::find($this->argument('toAssistant'));
        if (!$toAssistant) {
            throw new \Exception("To assistant not found.");
        }

        if ($assistant->conversation_blocked || $toAssistant->conversation_blocked) {
            throw new \Exception("Assistant or to assistant conversation blocked.");
        }
        $assistant->conversation_blocked = true;
        $toAssistant->conversation_blocked = true;
        $assistant->save();
        $toAssistant->save();

        $welcomeMessageMessageId = rand(1, 5);
        $welcomeMessage = WelcomeMessage::where('message_id', $welcomeMessageMessageId)->take(1)->get();
        if (!$welcomeMessage || empty($welcomeMessage[0])) {
            $assistant->conversation_blocked = false;
            $toAssistant->conversation_blocked = false;
            $assistant->save();
            $toAssistant->save();

            throw new \Exception("Welcome message not found.");
        }
        $welcomeMessage = $welcomeMessage[0];

        $openAiConversation = new OpenAIConversation();
        $openAiConversationTo = new OpenAIConversation();

        $newRunAssistant = new Run();
        $newRunAssistant->assistance_id = $assistant->id;
        $newRunAssistant->assistance_to_id = $toAssistant->id;
        $newRunAssistant->response = $welcomeMessage->response;
        $newRunAssistant->run = 'welcome/' . $welcomeMessageMessageId;
        $assistant->last_run_id = $newRunAssistant->run;

        $newRunToAssistant = new Run();
        $newRunToAssistant->assistance_id = $toAssistant->id;
        $newRunToAssistant->assistance_to_id = $assistant->id;

        if ($openAiConversation->createThred()) {
            $newRunAssistant->thred_id = $openAiConversation->getThredId();
            $newRunAssistant->save();
        } else {
            $assistant->conversation_blocked = false;
            $toAssistant->conversation_blocked = false;
            $assistant->save();
            $toAssistant->save();

            throw new \Exception('Error al crear el thred del asistente');
        }

        if ($openAiConversationTo->createThred()) {
            $newRunToAssistant->thred_id = $openAiConversationTo->getThredId();
        } else {
            $assistant->conversation_blocked = false;
            $toAssistant->conversation_blocked = false;
            $assistant->save();
            $toAssistant->save();

            throw new \Exception('Error al crear el thred del asistente');
        }

        try {
            Conversation::generateConversation($assistant, $toAssistant, $openAiConversation, $openAiConversationTo, $newRunAssistant, $newRunToAssistant);
        } catch (\Exception $e) {
            $assistant->conversation_blocked = false;
            $toAssistant->conversation_blocked = false;
            $assistant->save();
            $toAssistant->save();

            throw new \Exception($e->getMessage());
        }

        $assistant->responsesToDo = 3;
        $toAssistant->responsesToDo = 3;
        $assistant->conversation_blocked = false;
        $toAssistant->conversation_blocked = false;
        $assistant->save();
        $toAssistant->save();
    }
}
