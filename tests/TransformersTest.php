<?php

use Doppar\AI\Pipeline;
use Doppar\AI\Enum\TaskEnum;
use PHPUnit\Framework\TestCase;

class TransformersTest extends TestCase
{
    public function testSentimentPositive()
    {
        $result = Pipeline::execute(
            task: TaskEnum::SENTIMENT_ANALYSIS,
            data: 'I absolutely love this product! Best purchase ever!'
        );

        $this->assertEquals($result['label'], 'POSITIVE');
    }

    public function testSentimentNegative()
    {
        $result = Pipeline::execute(
            task: TaskEnum::SENTIMENT_ANALYSIS,
            data: 'This is a negative review. The product is terrible!'
        );

        $this->assertEquals($result['label'], 'NEGATIVE');
    }
}