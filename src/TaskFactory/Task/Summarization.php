<?php

namespace Doppar\AI\TaskFactory\Task;

use Doppar\AI\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class Summarization implements TaskInterface
{
    /**
     * The type of task this class represents.
     */
    const TASK = TaskEnum::SUMMARIZATION;

    /**
     * Execute the summarization pipeline.
     *
     * @param mixed $datas Input parameters for the pipeline.
     *                     Expected structure:
     *                     [
     *                         'data' => string,           // Text to summarize
     *                         'model' => ?string,          // Optional: custom model name
     *                         'maxNewTokens' => int        // Maximum tokens for the generated summary
     *                     ]
     *
     * @return mixed Returns the generated summary text.
     */
    public function execute(mixed $datas): mixed
    {
        if (empty($datas['model'])) {
            $datas['model'] = 'Xenova/distilbart-cnn-6-6';
        }

        $summarizer = pipeline(self::TASK->value, $datas['model']);

        return $summarizer($datas['data'], maxNewTokens: $datas['maxNewTokens']);
    }
}
