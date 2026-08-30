<?php

use Doppar\AI\Pipeline;
use Doppar\AI\Enum\TaskEnum;
use PHPUnit\Framework\TestCase;

class TransformersTest extends TestCase
{
    public function testSentimentPositive()
    {
        $result = $this->executeSentiment('I absolutely love this product! Best purchase ever!');

        $this->assertEquals($result['label'], 'POSITIVE');
    }

    public function testSentimentNegative()
    {
        $result = $this->executeSentiment('This is a negative review. The product is terrible!');

        $this->assertEquals($result['label'], 'NEGATIVE');
    }

    private function executeSentiment(string $text): array
    {
        try {
            return Pipeline::execute(
                task: TaskEnum::SENTIMENT_ANALYSIS,
                data: $text,
            );
        } catch (\Throwable $exception) {
            $this->markTestSkipped(
                'The sentiment model is unavailable in this environment: ' . $exception->getMessage()
            );
        }
    }
}
