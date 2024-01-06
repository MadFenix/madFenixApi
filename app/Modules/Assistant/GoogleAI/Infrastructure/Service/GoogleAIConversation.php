<?php
namespace App\Modules\Assistant\GoogleAI\Infrastructure\Service;

use Google\Cloud\Speech\V1\SpeechClient;
use Google\Cloud\Speech\V1\RecognitionAudio;
use Google\Cloud\Speech\V1\RecognitionConfig;
use Google\Cloud\Speech\V1\RecognitionConfig\AudioEncoding;

use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\SsmlVoiceGender;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;

class GoogleAIConversation
{
    public function createAudioFile($audioMessage) {
        $uniqueId = uniqid();
        if (strpos($audioMessage, 'audio/webm')) {
            $filetype = 'webm';
            $audioMessage = str_replace('data:audio/webm;codecs=opus;base64,', '', $audioMessage);
        } else {
            $filetype = 'mp3';
        }
        $fileName = public_path() . '/audiosInput/' . $uniqueId . '.' . $filetype;
        file_put_contents($fileName, base64_decode($audioMessage));

        return $fileName;
    }

    public function getMessageFromAudio($audioMessage) {
        // change these variables if necessary
        $languageCode = 'es-ES';

        if (strpos($audioMessage, 'audio/webm')) {
            $encoding = AudioEncoding::WEBM_OPUS;
            $sampleRateHertz = 48000;

            // set config
            $config = (new RecognitionConfig())
                ->setEncoding($encoding)
                ->setSampleRateHertz($sampleRateHertz)
                ->setLanguageCode($languageCode);
        } else {
            $encoding = AudioEncoding::LINEAR16;

            // set config
            $config = (new RecognitionConfig())
                ->setEncoding($encoding)
                ->setLanguageCode($languageCode);
        }

        // get contents of a file into a string
        $content = file_get_contents($this->createAudioFile($audioMessage));

        // set string as audio content
        $audio = (new RecognitionAudio())
            ->setContent($content);

        // create the speech client
        $client = new SpeechClient();

        $transcript = '';
        try {
            $response = $client->recognize($config, $audio);
            foreach ($response->getResults() as $result) {
                $alternatives = $result->getAlternatives();
                $mostLikely = $alternatives[0];
                $transcript = $mostLikely->getTranscript();
                $confidence = $mostLikely->getConfidence();
            }
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        } finally {
            $client->close();
        }

        return $transcript;
    }

    public function getAudioFromText($text, $lastRunId, $name = null, $gender = null, $subdir = '') {
        // create client object
        $client = new TextToSpeechClient();

        $input_text = (new SynthesisInput())
            ->setText($text);

        // note: the voice can also be specified by name
        // names of voices can be retrieved with $client->listVoices()
        $voice = (new VoiceSelectionParams())
            ->setLanguageCode('es-ES');
        if (!$gender || $gender == 'Male') {
            $voice->setSsmlGender(SsmlVoiceGender::MALE);
        } else {
            $voice->setSsmlGender(SsmlVoiceGender::FEMALE);
        }
        if ($name) {
            $voice->setName($name);
        }

        $audioConfig = (new AudioConfig())
            ->setAudioEncoding(AudioEncoding::MP3);

        $response = $client->synthesizeSpeech($input_text, $voice, $audioConfig);
        $audioContent = $response->getAudioContent();

        file_put_contents(public_path() . '/audios/' . $subdir . $lastRunId . '.mp3', $audioContent);

        $client->close();
    }
}
