<?php

namespace Doppar\AI\TaskFactory\Task;

use Doppar\AI\Enum\TaskEnum;
use function Codewithkyrian\Transformers\Pipelines\pipeline;

class SpeechRecognition implements TaskInterface
{
    // Associate this task with the AUTOMATIC_SPEECH_RECOGNITION enum
    const TASK = TaskEnum::AUTOMATIC_SPEECH_RECOGNITION;

    /**
     * Execute speech recognition on a provided audio file.
     *
     * @param mixed $datas Array containing:
     *   - 'audioPath': string, path to the audio file to transcribe (required)
     *   - 'model': string, optional model name (defaults to 'Xenova/whisper-tiny')
     * @return mixed Returns the transcription result from the pipeline
     * @throws \Exception If the audio file is missing or invalid
     *
     * Example $datas:
     * [
     *   'audioPath' => '/public/audio/example.wav',
     *   'model' => 'Xenova/whisper-small'
     * ]
     */
    public function execute(mixed $datas): mixed
    {
        if (empty($datas['audioPath']) || !file_exists($datas['audioPath'])) {
            throw new \Exception('Audio file not found or path is invalid');
        }

        if (empty($datas['model'])) {
            $datas['model'] = 'Xenova/whisper-tiny';
        }

        $transcriber = pipeline(self::TASK->value, modelName: $datas['model']);

        return $transcriber($datas['audioPath']);
    }
}
