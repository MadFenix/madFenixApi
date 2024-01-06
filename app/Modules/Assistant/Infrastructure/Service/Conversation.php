<?php
namespace App\Modules\Assistant\Infrastructure\Service;

use App\Modules\Assistant\GoogleAI\Infrastructure\Service\GoogleAIConversation;
use App\Modules\Assistant\OpenAI\Domain\Run;
use App\Modules\Assistant\OpenAI\Infrastructure\Service\OpenAIConversation;

class Conversation
{
    public static function generateConversation($assistant, $toAssistant, $openAiConversation = new OpenAIConversation(), $openAiConversationTo = new OpenAIConversation(), $newRunAssistant = null, $newRunToAssistant = null, $generateRunToAssistants = false, $finalResponse = false) {
        $googleAiConversation = new GoogleAIConversation();

        $newRunGenerated = false;
        if ($generateRunToAssistants) {
            $newRunGenerated = true;

            $previousRunToResponse = $newRunToAssistant->response;

            $newRunAssistant = new Run();
            $newRunAssistant->assistance_id = $assistant->id;
            $newRunAssistant->assistance_to_id = $toAssistant->id;
            $newRunAssistant->thred_id = $openAiConversation->getThredId();

            $newRunToAssistant = new Run();
            $newRunToAssistant->assistance_id = $toAssistant->id;
            $newRunToAssistant->assistance_to_id = $assistant->id;
            $newRunToAssistant->thred_id = $openAiConversationTo->getThredId();

            // Solo si es una conversación iniciada necesitamos pedir respuesta para la run del primer assitente.
            if ($newRunAssistant->thred_id) {
                $responseDecoded = json_decode($previousRunToResponse);
                $responseToConvert = '';
                if ($finalResponse) {
                    $responseToConvert = 'Despide la conversación en esta última pregunta. Deja el parámetro de pregunta vacío. ';
                }
                foreach ($responseDecoded as $value) {
                    $responseToConvert .= $value . "\n";
                }

                $messageSent = $openAiConversation->createMessage($responseToConvert);
            }
            if ($messageSent) {
                $responseSent = $openAiConversation->runResponse($assistant->open_ai_assistant_id);
            }
            if ($responseSent) {
                $newRunAssistant->run = $openAiConversation->getLastRunId();
                $assistant->last_run_id = $newRunAssistant->run;

                $lastResponseSent = $openAiConversation->getLastResponse();
            }
            if ($lastResponseSent) {
                $newRunAssistant->response = $openAiConversation->getLastRunText();

                $responseDecoded = json_decode($newRunAssistant->response);
                $responseToConvert = '';
                foreach ($responseDecoded as $value) {
                    if (is_string($value)) {
                        $responseToConvert .= $value . "\n";
                    }
                }

                $googleAiConversation->getAudioFromText($responseToConvert, $newRunAssistant->run, $assistant->google_ai_voice_name, $assistant->gender, 'assistances/' . $assistant->id . '/');
            }
            $messageSent = null;
            $responseSent = null;
            $lastResponseSent = null;
        }

        if ($newRunToAssistant->thred_id) {
            $responseToCreateMessage = $toAssistant->current_objective . ' ' . $newRunAssistant->response;
            if ($finalResponse) {
                $responseToCreateMessage .= ' Despide la conversación en esta última pregunta. Deja el parámetro de pregunta vacío. ';
            }
            if ($newRunGenerated) {
                $responseDecoded = json_decode($newRunAssistant->response);
                $responseToCreateMessage = '';
                foreach ($responseDecoded as $value) {
                    if (is_string($value)) {
                        $responseToCreateMessage .= $value . "\n";
                    }
                }
            }

            $messageSent = $openAiConversationTo->createMessage($responseToCreateMessage);
        }
        if ($messageSent) {
            $responseSent = $openAiConversationTo->runResponse($toAssistant->open_ai_assistant_id);
        }
        if ($responseSent) {
            $newRunToAssistant->run = $openAiConversationTo->getLastRunId();
            $toAssistant->last_run_id = $newRunToAssistant->run;

            $lastResponseSent = $openAiConversationTo->getLastResponse();
        }
        if ($lastResponseSent) {
            $newRunToAssistant->response = $openAiConversationTo->getLastRunText();

            $responseDecoded = json_decode($newRunToAssistant->response);
            $responseToConvert = '';
            foreach ($responseDecoded as $value) {
                if (is_string($value)) {
                    $responseToConvert .= $value . "\n";
                }
            }

            $googleAiConversation->getAudioFromText($responseToConvert, $newRunToAssistant->run, $toAssistant->google_ai_voice_name, $toAssistant->gender, 'assistances/' . $toAssistant->id . '/');
        }

        $newRunAssistant->save();
        $newRunToAssistant->save();
    }

    public static function generateContestConversation($assistant, $openAiConversation = new OpenAIConversation())
    {
        $thred_id = $openAiConversation->getThredId();

        if ($thred_id) {
            $message = 'Dame 10 nuevas filas.';

            $messageSent = $openAiConversation->createMessage(
                $message
            );
        } else {
            return false;
        }
        if ($messageSent) {
            $responseSent = $openAiConversation->runResponse(
                $assistant->open_ai_assistant_id
            );
        } else {
            return false;
        }
        if ($responseSent) {
            $last_run_id = $openAiConversation->getLastRunId();
            $assistant->last_run_id = $last_run_id;

            $lastResponseSent = $openAiConversation->getLastResponse();
        } else {
            return false;
        }
        if ($lastResponseSent) {
            $last_run_text = $openAiConversation->getLastRunText();
        } else {
            return false;
        }

        return $last_run_text;
    }
}
