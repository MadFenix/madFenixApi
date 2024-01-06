<?php


namespace App\Modules\Game\State\Infrastructure\Controller;

use App\Modules\Assistant\GoogleAI\Infrastructure\Service\GoogleAIConversation;
use App\Modules\Assistant\OpenAI\Domain\Assistance;
use App\Modules\Assistant\OpenAI\Domain\Run;
use App\Modules\Assistant\OpenAI\Infrastructure\Service\OpenAIConversation;
use App\Modules\Base\Infrastructure\Controller\ResourceController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Api extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Game\\State';
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getState()    {
        return response()->json("Endoint paused.");
        $response = new \stdClass();
        $response->assistants = [];

        $assistants = Assistance::all();

        $assistantsBusy = [];
        foreach ($assistants as $assistant) {
            if (in_array($assistant->id, $assistantsBusy)) {
                continue;
            }
            $assistantsBusy[] = $assistant->id;

            $newAssistant = new \stdClass();
            $newAssistant->assistant = (object) $assistant;
            $newAssistant->runs = [];

            $lastRun = Run::where('assistance_id', $assistant->id)->orderBy('created_at', 'desc')->take(1)->get();
            if (!$lastRun || empty($lastRun[0])) {
                continue;
            }
            $lastRun = $lastRun[0];

            $assistantRuns = Run::where('thred_id', $lastRun->thred_id)->orderBy('created_at', 'asc')->take(10)->get();
            foreach ($assistantRuns as $assistantRun) {
                $newAssistant->runs[] = (object) $assistantRun;
            }

            $response->assistants[] = $newAssistant;

            $toAssistant = Assistance::find($lastRun->assistance_to_id);
            if (in_array($toAssistant->id, $assistantsBusy)) {
                foreach ($response->assistants as $key => $assistantResponse) {
                    if ($assistantResponse->id == $toAssistant->id) {
                        unset($response->assistants[$key]);
                        break;
                    }
                }
            } else {
                $assistantsBusy[] = $toAssistant->id;
            }
            $lastToRun = Run::where('assistance_id', $toAssistant->id)->where('assistance_to_id', $assistant->id)->orderBy('created_at', 'desc')->take(1)->get();
            if (!$lastToRun || empty($lastToRun[0])) {
                continue;
            }
            $lastToRun = $lastToRun[0];

            $newToAssistant = new \stdClass();
            $newToAssistant->assistant = (object) $toAssistant;
            $newToAssistant->runs = [];

            $toAssistantRuns = Run::where('thred_id', $lastToRun->thred_id)->orderBy('created_at', 'asc')->take(10)->get();
            foreach ($toAssistantRuns as $toAssistantRun) {
                $newToAssistant->runs[] = (object) $toAssistantRun;
            }

            $response->assistants[] = $newToAssistant;
        }

        return response()->json($response);
    }
}
