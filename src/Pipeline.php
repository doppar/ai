<?php

namespace Doppar\Transformer;

use Doppar\Transformer\Enum\TaskEnum;
use Doppar\Transformer\TaskFactory\TaskFactory;
use function Codewithkyrian\Transformers\Pipelines\pipeline;

class Pipeline
{
    public static function execute(
        TaskEnum $task,
        ?string $data = null,
        ?string $model = null,
        bool $quantized = true,
        array $messages = [],
        bool $addGenerationPrompt = false,
        bool $tokenize = true,
        int $maxNewTokens = 256,
        bool $returnFullText = false,
        ?string $tgtLang = null,
        ?string $question = null,
        ?string $context = null,
        int $topK = 1,
        array $candidateLabels = [],
    ): mixed
    {
        $output = TaskFactory::create($task)->execute([
            'data' => $data,
            'model' => $model,
            'quantized' => $quantized,
            'messages' => $messages,
            'addGenerationPrompt' => $addGenerationPrompt,
            'tokenize' => $tokenize,
            'maxNewTokens' => $maxNewTokens,
            'returnFullText' => $returnFullText,
            'tgtLang' => $tgtLang,
            'question' => $question,
            'context' => $context,
            'topK' => $topK,
            'candidateLabels' => $candidateLabels,
        ]);

        return $output;
    }

    public static function query(
        array|object|string $item,
        string $question,
        ?string $model = null,
        int $topK = 1,
    ): mixed
    {
        if (is_array($item)) {
            $row = $item;
        } else if (is_object($item)) {
            if (method_exists($item, 'toArray')) {
                $row = $item->toArray();
            } else {
                $row = get_object_vars($item);
            }
        } else {
            $row = ['value' => $item];
        }

        $context = "Object:" . "\n" . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n\n";

        $output = self::execute(
            TaskEnum::QUESTION_ANSWERING, 
            model: $model,
            question : $question,
            context : $context,
            topK : $topK
        );

        $text = is_array($output) && isset($output[0]['generated_text']) ? $output[0]['generated_text'] : (string)($output['generated_text'] ?? $output);

        if(str_contains(strtolower($text), 'yes')) {
            return true;
        }
        return false;
    }
}