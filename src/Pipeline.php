<?php

namespace Doppar\AI;

use Doppar\AI\Enum\TaskEnum;
use Doppar\AI\TaskFactory\TaskFactory;

class Pipeline
{
    /**
     * Execute a specified transformer task.
     *
     * @param TaskEnum $task The task to execute (e.g., SENTIMENT_ANALYSIS, TEXT_GENERATION, IMAGE_CLASSIFICATION, etc.)
     * @param string|null $data The primary input data (text) for tasks that require it.
     * @param string|null $model Optional model name to override the default task model.
     * @param bool $quantized Whether to use a quantized model (if supported).
     * @param array $messages Chat messages for conversational/text-generation tasks.
     * @param bool $addGenerationPrompt Whether to automatically add a generation prompt (for chat/text-generation tasks).
     * @param bool $tokenize Whether to tokenize input (for chat/text-generation tasks).
     * @param int $maxNewTokens Maximum number of tokens to generate (for text generation / summarization / translation tasks).
     * @param bool $returnFullText Whether to return the full generated text (for text generation tasks).
     * @param string|null $tgtLang Target language code (for translation tasks).
     * @param string|null $question Question string (for question-answering tasks).
     * @param string|null $context Context string (for question-answering tasks).
     * @param int $topK Number of top results to return (for classification / QA / generation tasks).
     * @param array $candidateLabels Candidate labels (for zero-shot classification tasks).
     * @param string|null $imageUrl URL or path to an image (for vision tasks like classification or object detection).
     * @param float $threshold Confidence threshold (for object detection / image tasks).
     * @return mixed Returns the output of the executed task.
     */
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
        ?string $imageUrl = null,
        float $threshold = 0.5,
    ): mixed {
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
            'imageUrl' => $imageUrl,
            'threshold' => $threshold
        ]);

        return $output;
    }

    /**
     * Query a structured item (array, object, or string) with a question using a QA pipeline.
     *
     * @param array|object|string $item The data object or string to query.
     * @param string $question The question to ask about the item.
     * @param string|null $model Optional model name for QA.
     * @param int $topK Number of top answers to consider.
     * @return bool
     */
    public static function query(array|object|string $item, string $question, ?string $model = null, int $topK = 1): mixed
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
            question: $question,
            context: $context,
            topK: $topK
        );

        $text = is_array($output) && isset($output[0]['generated_text']) ? $output[0]['generated_text'] : (string)($output['generated_text'] ?? $output);

        if (str_contains(strtolower($text), 'yes')) {
            return true;
        }
        return false;
    }
}
