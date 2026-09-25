<?php

namespace Doppar\AI\Memory;

use Doppar\AI\Store\StoreInterface;
use Symfony\AI\Agent\Input;
use Symfony\AI\Agent\Memory\Memory;
use Symfony\AI\Agent\Memory\MemoryProviderInterface;

class StoreMemoryProvider implements MemoryProviderInterface
{
    /**
     * @param StoreInterface $store Backing store for remembered facts.
     * @param string $key Store key this provider's memory is kept under.
     */
    public function __construct(
        private readonly StoreInterface $store,
        private readonly string $key,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function load(Input $input): array
    {
        $facts = $this->allFacts();

        if ([] === $facts) {
            return [];
        }

        return [new Memory(implode(\PHP_EOL, array_map(static fn (string $fact): string => '- ' . $fact, $facts)))];
    }

    /**
     * Persist a fact, appended to whatever is already remembered under this key.
     *
     * @param string $fact
     * @return void
     */
    public function remember(string $fact): void
    {
        $facts = $this->allFacts();
        $facts[] = $fact;

        $this->store->store($this->key, $facts);
    }

    /**
     * Clear everything remembered under this key.
     *
     * @return void
     */
    public function forget(): void
    {
        $this->store->delete($this->key);
    }

    /**
     * Normalize whatever is currently stored under this key
     *
     * @return array<int, string>
     */
    private function allFacts(): array
    {
        $data = $this->store->load($this->key);

        if (null === $data || [] === $data || '' === $data) {
            return [];
        }

        if (is_string($data)) {
            return [$data];
        }

        if (!is_array($data)) {
            return [];
        }

        $facts = [];
        foreach ($data as $item) {
            if (is_string($item)) {
                $facts[] = $item;
            } elseif (is_array($item) && isset($item['content']) && is_string($item['content'])) {
                $facts[] = $item['content'];
            }
        }

        return $facts;
    }
}
