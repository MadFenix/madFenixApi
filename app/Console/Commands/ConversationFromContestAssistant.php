<?php

namespace App\Console\Commands;

use App\Modules\Assistant\Infrastructure\Service\Conversation;
use App\Modules\Assistant\OpenAI\Domain\Assistance;
use App\Modules\Assistant\OpenAI\Domain\Run;
use App\Modules\Assistant\OpenAI\Domain\WelcomeMessage;
use App\Modules\Assistant\OpenAI\Infrastructure\Service\OpenAIConversation;
use Illuminate\Console\Command;

class ConversationFromContestAssistant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assistant:contest_conversation {assistant} {thred_id} {category} {difficulty}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new conversation from contest Assistant';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $thred_id = $this->argument('thred_id');
        if ($thred_id == 'null') {
            $thred_id = null;
        }
        $category = $this->argument('category');
        $difficulty = $this->argument('difficulty');
        $assistant = Assistance::find($this->argument('assistant'));
        if (!$assistant) {
            throw new \Exception("Assistant not found.");
        }

        if ($assistant->conversation_blocked) {
            throw new \Exception("Assistant or to assistant conversation blocked.");
        }
        $assistant->conversation_blocked = true;
        $assistant->save();

        $openAiConversation = new OpenAIConversation();

        if (!$thred_id && $openAiConversation->createThred()) {
            $thred_id = $openAiConversation->getThredId();
            $this->line('Thred id: ' . $thred_id);
        } else {
            $openAiConversation->setThredId($thred_id);
        }

        try {
            $last_run_text = Conversation::generateContestConversation($assistant, $openAiConversation);

            if (!$last_run_text) {
                throw new \Exception("Text not generated.");
            }

            $csvLines = explode("\n", $last_run_text);
            $firstLine = true;
            $textToAdd = file_get_contents(public_path() . '/contest/' . $category . '/questions_' . $difficulty . '.csv');
            foreach ($csvLines as $csvLine) {
                if ($firstLine) {
                    $firstLine = false;
                    continue;
                }
                $textToAdd .= "\n" . $csvLine;
            }

            file_put_contents(public_path() . '/contest/' . $category . '/questions_' . $difficulty . '.csv', $textToAdd);
        } catch (\Exception $e) {
            $assistant->conversation_blocked = false;
            $assistant->save();

            throw new \Exception($e->getMessage());
        }

        $assistant->responsesToDo = 1;
        $assistant->conversation_blocked = false;
        $assistant->save();
    }
}
