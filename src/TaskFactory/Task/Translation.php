<?php

namespace Doppar\Transformer\TaskFactory\Task;

use Doppar\Transformer\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class Translation implements TaskInterface
{
    const TASK = TaskEnum::TRANSLATION;
    
    public function execute(mixed $datas): mixed
    {
        if($datas['data'] === null) {
            throw new \Exception('No data provided');
        }
        $translator = pipeline(self::TASK->value, $datas['model']);
        
        return $translator($datas['data'], tgtLang: $datas['tgtLang'], maxNewTokens: $datas['maxNewTokens']);
    }
}
