<?php

namespace Doppar\AI\TaskFactory\Task;

use Doppar\AI\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class Translation implements TaskInterface
{
    /**
     * The type of task this class represents.
     */
    const TASK = TaskEnum::TRANSLATION;

    /**
     * Execute the translation pipeline.
     *
     * @param mixed $datas Input parameters for the pipeline.
     *                     Expected structure:
     *                     [
     *                         'data' => string,           // Text to translate
     *                         'model' => ?string,          // Optional: model name
     *                         'tgtLang' => string,         // Target language code (e.g., 'fr', 'es')
     *                         'maxNewTokens' => int        // Maximum tokens for generated translation
     *                     ]
     *
     * @return mixed Returns the translated text.
     * @throws \Exception
     */
    public function execute(mixed $datas): mixed
    {
        if ($datas['data'] === null) {
            throw new \Exception('No data provided');
        }

        $translator = pipeline(self::TASK->value, $datas['model']);

        return $translator($datas['data'], tgtLang: $datas['tgtLang'], maxNewTokens: $datas['maxNewTokens']);
    }
}
