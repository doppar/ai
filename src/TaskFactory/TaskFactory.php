<?php

namespace Doppar\Transformer\TaskFactory;

use InvalidArgumentException;
use Doppar\Transformer\TaskFactory\Task\ZeroShotClassification;
use Doppar\Transformer\TaskFactory\Task\Translation;
use Doppar\Transformer\TaskFactory\Task\TextGeneration;
use Doppar\Transformer\TaskFactory\Task\TextClassification;
use Doppar\Transformer\TaskFactory\Task\TaskInterface;
use Doppar\Transformer\TaskFactory\Task\Summarization;
use Doppar\Transformer\TaskFactory\Task\SentimentAnalysis;
use Doppar\Transformer\TaskFactory\Task\QuestionAnswer;
use Doppar\Transformer\TaskFactory\Task\FillMask;
use Doppar\Transformer\TaskFactory\Task\FeatureExtraction;
use Doppar\Transformer\Enum\TaskEnum;
use Doppar\Transformer\TaskFactory\Task\Embedding;
use Doppar\Transformer\TaskFactory\Task\ImageClassification;
use Doppar\Transformer\TaskFactory\Task\TokenClassification;

class TaskFactory
{
    public static function create(TaskEnum $task): TaskInterface
    {
        return match ($task) {
            SentimentAnalysis::TASK => new SentimentAnalysis(),
            TextGeneration::TASK => new TextGeneration(),
            Translation::TASK => new Translation(),
            ZeroShotClassification::TASK => new ZeroShotClassification(),
            QuestionAnswer::TASK => new QuestionAnswer(),
            FillMask::TASK => new FillMask(),
            TextClassification::TASK => new TextClassification(),
            Summarization::TASK =>  new Summarization(),
            TokenClassification::TASK => new TokenClassification(),
            FeatureExtraction::TASK => new FeatureExtraction(),
            Embedding::TASK => new Embedding(),
            ImageClassification::TASK => new ImageClassification(),
            default => throw new InvalidArgumentException("Invalid task"),
        };
    }
}
