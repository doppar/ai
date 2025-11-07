<?php

namespace Doppar\Transformer\TaskFactory\Task;

use Doppar\Transformer\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class QuestionAnswer implements TaskInterface
{
    /**
     * The type of task this class represents.
     */
    const TASK = TaskEnum::QUESTION_ANSWERING;

    /**
     * Execute the question-answering pipeline.
     *
     * @param mixed $datas Input parameters for the pipeline.
     *                     Expected structure:
     *                     [
     *                         'question' => string,       // The question to answer
     *                         'context' => string,        // The context text containing the answer
     *                         'model' => ?string,          // Optional: model name
     *                         'topK' => int               // Number of top answers to return
     *                     ]
     *
     * @return mixed Returns the top answers with scores from the model.
     * @throws \Exception
     */
    public function execute(mixed $datas): mixed
    {
        if ($datas['question'] === null || $datas['context'] === null) {
            throw new \Exception('No question or context provided');
        }

        $questionAnswerer = pipeline(self::TASK->value, $datas['model']);

        return $questionAnswerer($datas['question'], $datas['context'], topK: $datas['topK']);
    }
}
