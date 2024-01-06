<?php


namespace App\Modules\Assistant\Thred\Infrastructure\Controller;

use App\Modules\Assistant\GoogleAI\Infrastructure\Service\GoogleAIConversation;
use App\Modules\Assistant\OpenAI\Domain\Assistance;
use App\Modules\Assistant\OpenAI\Infrastructure\Service\OpenAIConversation;
use App\Modules\Base\Infrastructure\Controller\ResourceController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Api extends ResourceController
{
    protected function getModelName(): string
    {
        return 'Assistant\\Thred';
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getResponseFromAI(Request $request)    {
        return response()->json("Endoint paused.");
        $clipInput = $request->get('clipInput');
        $thredId = $request->get('thredId');

        $openAiConversation = new OpenAIConversation();
        $output = $openAiConversation->getResponseFromMessage($thredId, $clipInput);

        return $output->clipOutput
            ? response()->json($output)
            : response()->json('No se ha conseguido procesar el audio', 500);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAudioResponseFromAI(Request $request)    {
        return response()->json("Endoint paused.");
        $clipInput = $request->get('clipInput');
        $thredId = $request->get('thredId');

        $openAiConversation = new OpenAIConversation();
        $output = $openAiConversation->getResponseFromAudioMessage($thredId, $clipInput);

        return $output->clipOutput
            ? response()->json($output)
            : response()->json('No se ha conseguido procesar el audio', 500);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAudioResponseFromMixAI(Request $request)    {
        return response()->json("Endoint paused.");
        $clipInput = $request->get('clipInput');
        $thredId = $request->get('thredId');

        $googleAiConversation = new GoogleAIConversation();
        $openAiConversation = new OpenAIConversation();

        $message = $googleAiConversation->getMessageFromAudio($clipInput);

        $output = new \stdClass();
        $output->clipInput = $message;
        $output->clipOutput = '';
        $output->lastRunId = '';
        $output->thredId = $thredId;
        $messageSent = false;
        $responseSent = false;
        $lastResponseSent = false;

        if ($thredId) {
            $openAiConversation->setThredId($thredId);
        } else {
            if ($openAiConversation->createThred()) {
                $thredId = $openAiConversation->getThredId();
                $output->thredId = $thredId;
            }
        }
        if ($thredId) {
            $messageSent = $openAiConversation->createMessage($message);
        }
        if ($messageSent) {
            $responseSent = $openAiConversation->runResponse();
        }
        if ($responseSent) {
            $output->lastRunId = $openAiConversation->getLastRunId();

            $lastResponseSent = $openAiConversation->getLastResponse();
        }
        if ($lastResponseSent) {
            $output->clipOutput = $openAiConversation->getLastRunText();

            $googleAiConversation->getAudioFromText($output->clipOutput, $output->lastRunId);
        }

        return $output->clipOutput
            ? response()->json($output)
            : response()->json('No se ha conseguido procesar el audio', 500);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getClientCommandFromMixAI(Request $request)    {
        return response()->json("Endoint paused.");
        $clipInput = $request->get('clipInput');
        $thredId = $request->get('thredId');

        $assistant = Assistance::find(3);

        $googleAiConversation = new GoogleAIConversation();
        $openAiConversation = new OpenAIConversation();

        $message = $googleAiConversation->getMessageFromAudio($clipInput);

        $output = new \stdClass();
        $output->clipInput = $message;
        $output->clipOutput = '';
        $output->lastRunId = '';
        $output->productos = null;
        $output->productosAcciones = [];
        $output->thredId = $thredId;
        $messageSent = false;
        $responseSent = false;
        $lastResponseSent = false;

        if (strpos(strtolower($message), "terminar") !== false || strpos(strtolower($message), "finalizar") !== false || strpos(strtolower($message), "es todo") !== false) {
            $output->productosAcciones = Cart::finalizeCommand($thredId); // TODO
            $output->clipOutput = 'Pedido terminado';

            return $output->productosAcciones
                ? response()->json($output)
                : response()->json('No se ha conseguido finalizar el pedido', 500);
        }

        if ($thredId) {
            $openAiConversation->setThredId($thredId);
        } else {
            if ($openAiConversation->createThred()) {
                $thredId = $openAiConversation->getThredId();
                $output->thredId = $thredId;
            }
        }
        if ($thredId) {
            $messageSent = $openAiConversation->createMessage($message);
        }
        if ($messageSent) {
            $responseSent = $openAiConversation->runResponse($assistant->open_ai_assistant_id);
        }
        if ($responseSent) {
            $output->lastRunId = $openAiConversation->getLastRunId();

            $lastResponseSent = $openAiConversation->getLastResponse();
        }
        $responseDecoded = new \stdClass();
        if ($lastResponseSent) {
            $assistantResponse = $openAiConversation->getLastRunText();
            $responseDecoded = json_decode($assistantResponse);
        }
        if (isset($responseDecoded->respuesta) && $responseDecoded->respuesta) {
            $output->clipOutput = $responseDecoded->respuesta;

            $googleAiConversation->getAudioFromText($output->clipOutput, $output->lastRunId, $assistant->google_ai_voice_name, $assistant->gender, 'assistances/' . $assistant->id . '/');
        }
        if (isset($responseDecoded->productos) && is_array($responseDecoded->productos) && count($responseDecoded->productos) > 0) {
            $output->productos = $responseDecoded->productos;
            Cart::executeOrders($responseDecoded->productos, $thredId, $output->productosAcciones);
        }

        return $output->clipOutput
            ? response()->json($output)
            : response()->json('No se ha conseguido procesar el audio', 500);
    }
}
