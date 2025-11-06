<?php

namespace Doppar\Transformer\TaskFactory;

use InvalidArgumentException;
use Doppar\Transformer\Enum\TaskEnum;
use Doppar\Transformer\TaskFactory\Task\FillMask;
use Doppar\Transformer\TaskFactory\Task\Translation;
use Doppar\Transformer\TaskFactory\Task\TaskInterface;
use Doppar\Transformer\TaskFactory\Task\QuestionAnswer;
use Doppar\Transformer\TaskFactory\Task\TextGeneration;
use Doppar\Transformer\TaskFactory\Task\SentimentAnalysis;
use Doppar\Transformer\TaskFactory\Task\ZeroShotClassification;

class TaskFactory
{
    public static function create(TaskEnum $task): TaskInterface {
        return match($task) {
            SentimentAnalysis::TASK => new SentimentAnalysis(),
            TextGeneration::TASK => new TextGeneration(),
            Translation::TASK => new Translation(),
            ZeroShotClassification::TASK => new ZeroShotClassification(),
            QuestionAnswer::TASK => new QuestionAnswer(),
            FillMask::TASK => new FillMask(),
            default => throw new InvalidArgumentException("Unknown task"),
        };
    }
}
