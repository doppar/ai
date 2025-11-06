<?php

namespace Doppar\Transformer\TaskFactory\Task;

use Doppar\Transformer\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class Embedding implements TaskInterface
{
    const TASK = TaskEnum::EMBEDDING;

    public function execute(mixed $datas): mixed
    {
        if (empty($datas['model'])) {
            $datas['model'] = 'Xenova/all-MiniLM-L6-v2';
        }
        $embedder = pipeline(self::TASK->value, $datas['model']);

        return $embedder($datas['data']);
    }
}
