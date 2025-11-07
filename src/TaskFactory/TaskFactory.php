<?php

namespace Doppar\Transformer\TaskFactory;

use Doppar\Transformer\Enum\TaskEnum;
use Doppar\Transformer\TaskFactory\Task\TaskInterface;
use Doppar\Transformer\TaskFactory\Task\{
    ZeroShotClassification,
    Translation,
    TokenClassification,
    TextGeneration,
    TextClassification,
    Summarization,
    SentimentAnalysis,
    QuestionAnswer,
    ImageToText,
    ImageClassification,
    FillMask,
    FeatureExtraction,
    Embedding,
    ObjectDetection,
    ZeroShotImageClassification
};

class TaskFactory
{
    /**
     * Map of TaskEnum values to their corresponding Task class names
     */
    private const TASK_MAP = [
        SentimentAnalysis::TASK->value => SentimentAnalysis::class,
        TextGeneration::TASK->value => TextGeneration::class,
        Translation::TASK->value => Translation::class,
        ZeroShotClassification::TASK->value => ZeroShotClassification::class,
        QuestionAnswer::TASK->value => QuestionAnswer::class,
        FillMask::TASK->value => FillMask::class,
        TextClassification::TASK->value => TextClassification::class,
        Summarization::TASK->value => Summarization::class,
        TokenClassification::TASK->value => TokenClassification::class,
        FeatureExtraction::TASK->value => FeatureExtraction::class,
        Embedding::TASK->value => Embedding::class,
        ImageClassification::TASK->value => ImageClassification::class,
        ImageToText::TASK->value => ImageToText::class,
        ZeroShotImageClassification::TASK->value => ZeroShotImageClassification::class,
        ObjectDetection::TASK->value => ObjectDetection::class,
    ];

    /**
     * Create a task instance based on the given TaskEnum
     *
     * @param TaskEnum $task
     * @return TaskInterface
     * @throws \InvalidArgumentException
     */
    public static function create(TaskEnum $task): TaskInterface
    {
        $taskKey = $task->value;

        if (!isset(self::TASK_MAP[$taskKey])) {
            throw new \InvalidArgumentException("Invalid task: {$taskKey}");
        }

        $taskClass = self::TASK_MAP[$taskKey];

        return app()->make($taskClass);
    }
}
