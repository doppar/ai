<?php

namespace Doppar\AI\Tests\Memory;

use Doppar\AI\Memory\StoreMemoryProvider;
use Doppar\AI\Store\CacheStore;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Agent\Input;
use Symfony\AI\Platform\Message\MessageBag;

class StoreMemoryProviderTest extends TestCase
{
    private string $storePath;
    private CacheStore $store;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storePath = sys_get_temp_dir() . '/doppar_ai_memory_test_' . uniqid();
        $this->store = new CacheStore($this->storePath);
    }

    protected function tearDown(): void
    {
        $this->store->clear();
        @rmdir($this->storePath);

        parent::tearDown();
    }

    private function input(): Input
    {
        return new Input('model', new MessageBag());
    }

    public function testLoadReturnsNoMemoryWhenNothingWasRemembered(): void
    {
        $provider = new StoreMemoryProvider($this->store, 'conversation-1');

        $this->assertSame([], $provider->load($this->input()));
    }

    public function testRememberedFactsAreReturnedAsMemory(): void
    {
        $provider = new StoreMemoryProvider($this->store, 'conversation-1');

        $provider->remember('Customer is a VIP.');
        $provider->remember('Prefers email over phone.');

        $memory = $provider->load($this->input());

        $this->assertCount(1, $memory);
        $content = $memory[0]->getContent();
        $this->assertStringContainsString('Customer is a VIP.', $content);
        $this->assertStringContainsString('Prefers email over phone.', $content);
    }

    public function testForgetClearsRememberedFacts(): void
    {
        $provider = new StoreMemoryProvider($this->store, 'conversation-1');
        $provider->remember('Customer is a VIP.');

        $provider->forget();

        $this->assertSame([], $provider->load($this->input()));
    }

    public function testDifferentKeysDoNotShareMemory(): void
    {
        $a = new StoreMemoryProvider($this->store, 'conversation-a');
        $b = new StoreMemoryProvider($this->store, 'conversation-b');

        $a->remember('Fact for A.');

        $this->assertNotEmpty($a->load($this->input()));
        $this->assertSame([], $b->load($this->input()));
    }

    public function testToleratesTheRawRoleContentMessageShapeAlreadyUsedElsewhereInThisPackage(): void
    {
        // Store::store() is also used to persist raw chat history
        // (role/content arrays) elsewhere in this package (see Agent's
        // withStore()/store()) — a StoreMemoryProvider pointed at the same
        // key should still extract usable facts from that shape.
        $this->store->store('conversation-1', [
            ['role' => 'user', 'content' => 'What is my order status?'],
            ['role' => 'assistant', 'content' => 'Your order has shipped.'],
        ]);

        $provider = new StoreMemoryProvider($this->store, 'conversation-1');
        $memory = $provider->load($this->input());

        $this->assertCount(1, $memory);
        $this->assertStringContainsString('Your order has shipped.', $memory[0]->getContent());
    }
}
