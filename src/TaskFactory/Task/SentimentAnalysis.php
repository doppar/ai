<?php

namespace Doppar\Transformer\TaskFactory\Task;

use Doppar\Transformer\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class SentimentAnalysis implements TaskInterface
{
    /**
     * The type of task this class represents.
     */
    const TASK = TaskEnum::SENTIMENT_ANALYSIS;

    /**
     * Execute the sentiment analysis pipeline.
     *
     * @param mixed $datas Input parameters for the pipeline.
     *                     Expected structure:
     *                     [
     *                         'data' => string,          // The text to analyze
     *                         'model' => ?string,         // Optional: custom model name
     *                         'quantized' => bool         // Optional: whether to use quantized model
     *                     ]
     *
     * @return mixed Returns the sentiment classification results.
     * @throws \Exception
     */
    public function execute(mixed $datas): mixed
    {
        if ($datas['data'] === null) {
            throw new \Exception('No data provided');
        }

        $classifier = pipeline(self::TASK->value, quantized: $datas['quantized'], modelName: $datas['model']);

        return $classifier($datas['data']);
    }
}
