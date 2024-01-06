<?php
namespace App\Modules\Assistant\OpenAI\Infrastructure\Service;


class OpenAIConversation
{
    protected $openAIApiUrl = 'https://api.openai.com/v1/';

    protected $openAIApiKey;

    protected $assistantIdTest1;

    protected $thredId;

    protected $lastRunId;

    protected $lastRunText;

    protected $intentsToGetRunCompleted;

    function __construct() {
        $this->openAIApiKey = env('OPEN_AI_API_KEY');
        $this->setAssistantId(env('OPEN_AI_ASSISTANT_ID_TEST_1'));
        $this->intentsToGetRunCompleted = 0;
    }

    public function setThredId($thredId) {
        $this->thredId = $thredId;
    }

    public function getThredId() {
        return $this->thredId;
    }

    public function setAssistantId($assistantId) {
        $this->assistantIdTest1 = $assistantId;
    }

    public function getAssistantId() {
        return $this->assistantIdTest1;
    }

    public function setLastRunId($lastRunId) {
        $this->lastRunId = $lastRunId;
    }

    public function getLastRunId() {
        return $this->lastRunId;
    }

    public function setLastRunText($lastRunText) {
        $this->lastRunText = $lastRunText;
    }

    public function getLastRunText() {
        return str_replace("\n```\n", '', str_replace("```csv\n", '',  str_replace("```json\n", '', $this->lastRunText)));
    }

    protected function getDefaultCurlHeaders() {
        return [
            'Authorization: Bearer ' . $this->openAIApiKey,
            'Content-Type: application/json',
            'OpenAI-Beta: assistants=v1',
        ];
    }

    protected function getAudioTranscriptionCurlHeaders() {
        return [
            'Authorization: Bearer ' . $this->openAIApiKey,
            // 'Content-Type: multipart/form-data', se genera automáticamente con el post
        ];
    }

    protected function decodeResponse($response) {
        $response = json_decode($response);
        if (isset($response->error)) {
            var_dump($response);

            throw new \Exception('Error to Open AI call.');
        }
        return $response;
    }

    protected function setDefaultCurlOpt($curl, $endpoint, $headers, $data, $method = 'POST') {
        curl_setopt($curl, CURLOPT_URL, $this->openAIApiUrl . $endpoint);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if ($method == 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }

    protected function setAudioTranscriptionCurlOpt($curl, $endpoint, $headers, $data, $method = 'POST') {
        curl_setopt($curl, CURLOPT_URL, $this->openAIApiUrl . $endpoint);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        if ($method == 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
    }

    public function createThred() {
        $curl = curl_init();
        $endpoint = 'threads';
        $headers = $this->getDefaultCurlHeaders();
        $data = [];
        $this->setDefaultCurlOpt($curl, $endpoint, $headers, $data);

        $response = curl_exec($curl);
        curl_close($curl);

        if ($response) {
            $response = $this->decodeResponse($response);
            if (!$response) {
                return false;
            }

            $this->setThredId($response->id);

            return true;
        }

        throw new \Exception('Error al crear un thred');
    }

    public function createMessage($message) {
        if (!$thredId = $this->getThredId()) {
            return false;
        }

        $curl = curl_init();
        $endpoint = 'threads/' . $thredId . '/messages';
        $headers = $this->getDefaultCurlHeaders();

        $data = new \stdClass();
        $data->role = 'user';
        $data->content = $message;

        $this->setDefaultCurlOpt($curl, $endpoint, $headers, $data);

        $response = curl_exec($curl);
        curl_close($curl);

        if ($response) {
            return true;
        }

        throw new \Exception('Error al crear el mensaje');
    }

    public function runResponse($assistantId = null) {
        if (!$thredId = $this->getThredId()) {
            return false;
        }
        if ($assistantId === null && !$assistantId = $this->getAssistantId()) {
            return false;
        }

        $curl = curl_init();
        $endpoint = 'threads/' . $thredId . '/runs';
        $headers = $this->getDefaultCurlHeaders();

        $data = new \stdClass();
        $data->assistant_id = $assistantId;

        $this->setDefaultCurlOpt($curl, $endpoint, $headers, $data);

        $response = curl_exec($curl);
        curl_close($curl);

        if ($response) {
            $response = $this->decodeResponse($response);
            if (!$response) {
                return false;
            }

            $this->setLastRunId($response->id);

            return true;
        }

        throw new \Exception('Error al ejecutar una respuesta');
    }

    protected function isStatusRunCompleted() {
        if (!$thredId = $this->getThredId()) {
            return false;
        }
        if (!$lastRunId = $this->getLastRunId()) {
            return false;
        }

        $curl = curl_init();
        $endpoint = 'threads/' . $thredId . '/runs/' . $lastRunId;
        $headers = $this->getDefaultCurlHeaders();

        $data = null;

        $this->setDefaultCurlOpt($curl, $endpoint, $headers, $data, 'GET');

        $response = curl_exec($curl);
        curl_close($curl);

        if ($response) {
            $response = $this->decodeResponse($response);
            if (!$response) {
                return false;
            }

            if ($response->status == 'completed') {
                return true;
            } else {
                if ($this->intentsToGetRunCompleted < 30 && $response->status != 'failed') {
                    $this->intentsToGetRunCompleted++;
                    sleep(6);
                    echo 'New intent to get last run status completed: ' . $response->status . "\n";
                    return $this->isStatusRunCompleted();
                } else {
                    echo 'Failed to get status completed: ' . $response->status . "\n";
                    return false;
                }
            }
        }

        return false;
    }

    public function getLastResponse() {
        if (!$thredId = $this->getThredId()) {
            return false;
        }

        $this->intentsToGetRunCompleted = 0;
        if (!$this->isStatusRunCompleted()) {
            return false;
        }

        $curl = curl_init();
        $endpoint = 'threads/' . $thredId . '/messages';
        $headers = $this->getDefaultCurlHeaders();

        $data = null;

        $this->setDefaultCurlOpt($curl, $endpoint, $headers, $data, 'GET');

        $response = curl_exec($curl);
        curl_close($curl);

        if ($response) {
            $response = $this->decodeResponse($response);
            if (!$response) {
                return false;
            }

            $lastResponse = '';
            for ($i = 0; $i < 5; $i++) {
                $lastDataMessage = array_shift($response->data);

                if ($lastDataMessage->role == 'assistant') {
                    for ($z = 0; $z < count($lastDataMessage->content); $z++) {
                        $lastResponse .= $lastDataMessage->content[$z]->text->value . "\n";
                    }
                } else {
                    break;
                }
            }

            $this->setLastRunText($lastResponse);

            return true;
        }

        throw new \Exception('Error al obtener último mensaje');
    }

    public function getAudioFromText($message) {
        $curl = curl_init();
        $endpoint = 'audio/speech';
        $headers = $this->getDefaultCurlHeaders();

        $data = new \stdClass();
        $data->model = 'tts-1';
        $data->input = $message;
        $data->voice = 'alloy';

        $this->setDefaultCurlOpt($curl, $endpoint, $headers, $data);

        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($status == 200) {
            file_put_contents(public_path() . '/audios/' . $this->getLastRunId() . '.mp3', $response);

            return true;
        }

        throw new \Exception('Error al speech');
    }

    public function getMessageFromAudio($audioMessage) {
        $uniqueId = uniqid();
        if (strpos($audioMessage, 'audio/webm')) {
            $filetype = 'webm';
            $audioMessage = str_replace('data:audio/webm;codecs=opus;base64,', '', $audioMessage);
        } else {
            $filetype = 'mp3';
        }
        $fileName = public_path() . '/audiosInput/' . $uniqueId . '.' . $filetype;
        file_put_contents($fileName, base64_decode($audioMessage));

        $curl = curl_init();
        $endpoint = 'audio/transcriptions';
        $headers = $this->getAudioTranscriptionCurlHeaders();

        $data = [];
        if (function_exists('curl_file_create')) {
            $cFile = curl_file_create($fileName);
        } else { //
            $cFile = '@' . realpath($fileName);
        }

        $data['file'] = $cFile;
        $data['model'] = 'whisper-1';
        $data['response_format'] = 'text';

        $this->setAudioTranscriptionCurlOpt($curl, $endpoint, $headers, $data);

        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($status == 200) {
            return $response;
        }

        throw new \Exception('Error al transcribir');
    }

    public function getResponseFromAudioMessage($thredId, $audioMessage) {
        $message = $this->getMessageFromAudio($audioMessage);

        return $this->getResponseFromMessage($thredId, $message);
    }

    public function getResponseFromMessage($thredId, $message) {
        $output = new \stdClass();
        $output->clipInput = $message;
        $output->clipOutput = '';
        $output->lastRunId = '';
        $output->thredId = $thredId;
        $messageSent = false;
        $responseSent = false;
        $lastResponseSent = false;

        if ($thredId) {
            $this->setThredId($thredId);
        } else {
            if ($this->createThred()) {
                $thredId = $this->getThredId();
                $output->thredId = $thredId;
            }
        }
        if ($thredId) {
            $messageSent = $this->createMessage($message);
        }
        if ($messageSent) {
            $responseSent = $this->runResponse();
        }
        if ($responseSent) {
            $output->lastRunId = $this->getLastRunId();

            $lastResponseSent = $this->getLastResponse();
        }
        if ($lastResponseSent) {
            $output->clipOutput = $this->getLastRunText();

            $this->getAudioFromText($output->clipOutput);
        }

        return $output;
    }
}
