<?php

namespace Doppar\Transformer;

use Doppar\Transformer\Enum\TaskEnum;
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
        if ($task === TaskEnum::TEXT_GENERATION) {
            if(empty($model)) {
                $model = 'Xenova/TinyLlama-1.1B-Chat-v1.0';
            }
            $generator = pipeline($task->value, modelName: $model);
            if (!empty($messages)) {

                $input = $generator->tokenizer->applyChatTemplate($messages, addGenerationPrompt: true, tokenize: false);

                $output = $generator($input, maxNewTokens: $maxNewTokens, returnFullText: $returnFullText);

                return $output;
            }

            throw new \Exception('No messages provided');
        }else if ($task === TaskEnum::TRANSLATION) {
            $translator = pipeline('translation', $model);

            $output = $translator($data, tgtLang: $tgtLang, maxNewTokens: $maxNewTokens);
        }
        else if ($task === TaskEnum::QUESTION_ANSWERING) {
            $questionAnswerer = pipeline('question-answering', $model);

            $output = $questionAnswerer($question, $context, topK: $topK);
        }
        else if ($task === TaskEnum::ZERO_SHOT_CLASSIFICATION) {
            if (empty($model)) {
                $model = 'Xenova/distilbert-base-uncased-mnli';
            }
            if (empty($candidateLabels)) {
                throw new \Exception('No candidate labels provided');
            }
            $classifier = pipeline('zero-shot-classification', $model);
            $output = $classifier($data, $candidateLabels);
            return $output;
        }
        else if ($task === TaskEnum::FILL_MASK) {
            if (empty($model)) {
                $model = 'Xenova/bert-base-uncased';
            }
            $unmasker = pipeline('fill-mask', $model);
            $output = $unmasker($data, topK: $topK);
            return $output;
        }
        else if ($task === TaskEnum::TEXT_CLASSIFICATION) {
            if (empty($model)) {
                $model = 'Xenova/distilbert-base-uncased-finetuned-sst-2-english';
            }
            if (empty($data)) {
                throw new \Exception('No data provided for text classification');
            }
            $classifier = pipeline('text-classification', $model);
            $output = $classifier($data);
            return $output;
        }
        else if ($task === TaskEnum::SUMMARIZATION) {
            if (empty($model)) {
                $model = 'Xenova/distilbart-cnn-6-6';
            }
            if (empty($data)) {
                throw new \Exception('No data provided for summarization');
            }
            $summarizer = pipeline('summarization', $model);
            $output = $summarizer($data, maxNewTokens: $maxNewTokens);
            return $output;
        }
        else {
            if($data === null) {
                throw new \Exception('No data provided');
            }
            $classifier = pipeline($task->value, quantized: $quantized, modelName: $model);
            
            $output = $classifier($data);
        }

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

        $schema = array_keys($row);

        $context = "Object:" . "\n" . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n\n";

        $output = self::execute(
            TaskEnum::QUESTION_ANSWERING, 
            model: $model,
            question : $question,
            context : $context,
            topK : $topK
        );

        dd($output);

        $text = is_array($output) && isset($output[0]['generated_text']) ? $output[0]['generated_text'] : (string)($output['generated_text'] ?? $output);

        if(str_contains(strtolower($text), 'yes')) {
            return true;
        }
        return false;
    }
}