<?php

namespace Doppar\AI\Vector;

use Doppar\AI\Agent;
use Doppar\AI\AgentFactory\Agent\OpenAI;

/**
 * Helper class providing vector utilities for RAG (Retrieval Augmented Generation).
 */
class VectorTools
{
    /**
     * Compute the cosine similarity between two numeric vectors.
     *
     * @param array<int,float> $vecA
     * @param array<int,float> $vecB
     *
     * @return float
     */
    static function cosineSimilarity(array $vecA, array $vecB): float {
        $dot = 0;
        $normA = 0;
        $normB = 0;
        foreach ($vecA as $i => $val) {
            $dot += $val * $vecB[$i];

            $normA += $val * $val;
            $normB += $vecB[$i] * $vecB[$i];
        }
        return $dot / (sqrt($normA) * sqrt($normB) + 1e-10);
    }

    /**
     * Retrieve the most relevant context text for a given question embedding.
     *
     * @param array<int,array{vector:array<int,float>,content:string}> $context
     * @param array<int,float> $questionVector
     *
     * @return string
     */
    static function getContext(array $context, array $questionVector): string
    {
        usort($context, function($a, $b) use ($questionVector) {
            return VectorTools::cosineSimilarity($b['vector'], $questionVector) <=> VectorTools::cosineSimilarity($a['vector'], $questionVector);
        });

        $topChunks = array_slice($context, 0, 3);

        return implode("\n\n", array_column($topChunks, 'content'));
    }

    /**
     * Generate an embedding vector for the given text using the provided Agent.
     *
     * @param Agent $agent
     * @param string $model
     * @param string $content
     *
     * @return array<int,float>
     */
    static function embedding(Agent $agent, string $model, string $content): array
    {
        $agentClass = $agent->getAgentClass();

        match ($agentClass) {
            OpenAI::class => null,
            default => throw new \RuntimeException('Embeddings are only supported for OpenAI agent.'),
        };

        return $agent->embedding($model, $content);
    }
}