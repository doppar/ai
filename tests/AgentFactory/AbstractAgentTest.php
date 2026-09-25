<?php

namespace Doppar\AI\Tests\AgentFactory;

use Doppar\AI\Tests\Fixtures\InMemoryTestAgent;
use Doppar\AI\Tests\Fixtures\WeatherTool;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Agent\Memory\StaticMemoryProvider;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Result\ResultInterface;
use Symfony\AI\Platform\Result\TextResult;
use Symfony\AI\Platform\Result\ToolCall;
use Symfony\AI\Platform\Result\ToolCallResult;

class AbstractAgentTest extends TestCase
{
    public function testExecuteWithoutToolsOrMemoryCallsThePlatformDirectly(): void
    {
        $agent = new InMemoryTestAgent('key', 'model');
        $agent->mockResult = 'Plain response';
        $agent->setMessage([['role' => 'user', 'content' => 'Hi']]);

        $this->assertSame('Plain response', $agent->execute([]));
    }

    public function testCompleteModeReturnsTheFullResult(): void
    {
        $agent = new InMemoryTestAgent('key', 'model');
        $agent->mockResult = 'Plain response';
        $agent->setMessage([['role' => 'user', 'content' => 'Hi']]);

        $result = $agent->execute([], complete: true);

        $this->assertSame('Plain response', $result->asText());
    }

    public function testWithMemoryInjectsProviderContentIntoTheSystemMessage(): void
    {
        /** @var MessageBag|null $seenInput */
        $seenInput = null;

        $agent = new InMemoryTestAgent('key', 'model');
        $agent->mockResult = function ($model, $input, $options) use (&$seenInput): string {
            $seenInput = $input;

            return 'ok';
        };
        $agent->withMemory([new StaticMemoryProvider('Customer is a VIP.')]);
        $agent->setMessage([
            ['role' => 'system', 'content' => 'You are a support agent.'],
            ['role' => 'user', 'content' => 'Hi'],
        ]);

        $agent->execute([]);

        $this->assertInstanceOf(MessageBag::class, $seenInput);
        $systemContent = $seenInput->getSystemMessage()?->getContent();
        $this->assertIsString($systemContent);
        $this->assertStringContainsString('Customer is a VIP.', $systemContent);
        $this->assertStringContainsString('You are a support agent.', $systemContent);
    }

    public function testWithToolsExecutesTheRealToolAndFeedsItsResultBackToTheModel(): void
    {
        $tool = new WeatherTool();
        $calls = 0;

        $agent = new InMemoryTestAgent('key', 'model');
        $agent->mockResult = function ($model, $input, $options) use (&$calls): ResultInterface {
            ++$calls;

            if (1 === $calls) {
                $this->assertIsArray($options['tools'] ?? null);
                $this->assertNotEmpty($options['tools']);

                return new ToolCallResult(new ToolCall('call_1', 'get_weather', ['city' => 'Paris']));
            }

            return new TextResult('It is sunny in Paris today.');
        };

        $agent->withTools([$tool]);
        $agent->setMessage([['role' => 'user', 'content' => "What's the weather in Paris?"]]);

        $result = $agent->execute([]);

        $this->assertSame('It is sunny in Paris today.', $result);
        $this->assertSame(['Paris'], $tool->calledWith);
        $this->assertSame(2, $calls, 'the model should be called once to request the tool, once to use its result');
    }

    public function testWithoutToolsOrMemoryToAgentStillReturnsAWorkingSymfonyAgent(): void
    {
        $agent = new InMemoryTestAgent('key', 'model');
        $agent->mockResult = 'from toAgent';

        $result = $agent->toAgent()->call(new MessageBag(\Symfony\AI\Platform\Message\Message::ofUser('hi')));

        $this->assertSame('from toAgent', $result->getContent());
    }

    public function testNamedSetsTheAgentNameUsedByToAgent(): void
    {
        $agent = new InMemoryTestAgent('key', 'model');
        $agent->mockResult = 'ok';
        $agent->named('billing');

        $this->assertSame('billing', $agent->getName());
        $this->assertSame('billing', $agent->toAgent()->getName());
    }

    public function testGetNameDefaultsToTheProviderShortClassName(): void
    {
        $agent = new InMemoryTestAgent('key', 'model');

        $this->assertSame('inmemorytestagent', $agent->getName());
    }

    public function testCreateRejectsAnEmptyModel(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        InMemoryTestAgent::create('key', '');
    }

    public function testStructuredOutputResponseFormatIsForwardedToThePlatformOptions(): void
    {
        /** @var array<string, mixed>|null $seenOptions */
        $seenOptions = null;

        $agent = new InMemoryTestAgent('key', 'model');
        $agent->mockResult = function ($model, $input, $options) use (&$seenOptions): string {
            $seenOptions = $options;

            return '{}';
        };
        $agent->setMessage([['role' => 'user', 'content' => 'Give me JSON']]);
        $agent->asStructured(\stdClass::class);

        $agent->execute([], complete: true);

        $this->assertSame(\stdClass::class, $seenOptions['response_format'] ?? null);
    }

    public function testNamedRejectsAnEmptyName(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('$name must not be empty.');

        (new InMemoryTestAgent('key', 'model'))->named('');
    }

    public function testToAgentCallPreservesTheFormatSetByAsStructured(): void
    {
        /** @var array<string, mixed>|null $seenOptions */
        $seenOptions = null;

        $agent = new InMemoryTestAgent('key', 'model');
        $agent->mockResult = function ($model, $input, $options) use (&$seenOptions): string {
            $seenOptions = $options;

            return '{}';
        };
        $agent->asStructured(\stdClass::class);

        $agent->toAgent()->call(new MessageBag(\Symfony\AI\Platform\Message\Message::ofUser('hi')));

        $this->assertSame(\stdClass::class, $seenOptions['response_format'] ?? null);
    }

    public function testToAgentHonoursAsStructuredCalledAfterToAgent(): void
    {
        /** @var array<string, mixed>|null $seenOptions */
        $seenOptions = null;

        $agent = new InMemoryTestAgent('key', 'model');
        $agent->mockResult = function ($model, $input, $options) use (&$seenOptions): string {
            $seenOptions = $options;

            return '{}';
        };

        $symfonyAgent = $agent->toAgent();
        $agent->asStructured(\stdClass::class);
        $symfonyAgent->call(new MessageBag(\Symfony\AI\Platform\Message\Message::ofUser('hi')));

        $this->assertSame(\stdClass::class, $seenOptions['response_format'] ?? null);
    }

    public function testToAgentCallDoesNotOverrideAnExplicitResponseFormat(): void
    {
        /** @var array<string, mixed>|null $seenOptions */
        $seenOptions = null;

        $agent = new InMemoryTestAgent('key', 'model');
        $agent->mockResult = function ($model, $input, $options) use (&$seenOptions): string {
            $seenOptions = $options;

            return '{}';
        };
        $agent->asStructured(\stdClass::class);

        $agent->toAgent()->call(
            new MessageBag(\Symfony\AI\Platform\Message\Message::ofUser('hi')),
            ['response_format' => \ArrayObject::class]
        );

        $this->assertSame(\ArrayObject::class, $seenOptions['response_format'] ?? null);
    }

    public function testToAgentCallWithoutAsStructuredSendsNoResponseFormat(): void
    {
        /** @var array<string, mixed>|null $seenOptions */
        $seenOptions = null;

        $agent = new InMemoryTestAgent('key', 'model');
        $agent->mockResult = function ($model, $input, $options) use (&$seenOptions): string {
            $seenOptions = $options;

            return 'plain';
        };

        $agent->toAgent()->call(new MessageBag(\Symfony\AI\Platform\Message\Message::ofUser('hi')));

        $this->assertArrayNotHasKey('response_format', $seenOptions ?? []);
    }
}
