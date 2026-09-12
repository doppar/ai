<?php

namespace Doppar\AI\TaskFactory\Task;

use Doppar\AI\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class Embedding implements TaskInterface
{
    /**
     * The type of task this class represents.
     */
    const TASK = TaskEnum::EMBEDDING;

    /**
     * Execute the embedding pipeline.
     *
     * @param mixed $datas  Input data and parameters.
     *                      Expected structure:
     *                      [
     *                          'data' => string|array,     // The text(s) to embed
     *                          'model' => ?string,          // Optional: custom model name
     *                          'pooling' => ?string,         // Optional: 'mean' (default) or 'none'
     *                          'normalize' => ?bool,         // Optional: defaults to true
     *                      ]
     *
     * @return mixed The pipeline result — an array of floats when pooled (the default),
     *                or a Tensor when 'pooling' => 'none' is explicitly requested.
     */
    public function execute(mixed $datas): mixed
    {
        if (empty($datas['model'])) {
            $datas['model'] = 'Xenova/all-MiniLM-L6-v2';
        }

        // The underlying transformers pipeline has no "embedding" task of its
        // own — embeddings are produced by the "feature-extraction" task.
        // TaskEnum::EMBEDDING stays the public-facing task name; only the
        // pipeline() call underneath needs the library's real task string.
        $embedder = pipeline(TaskEnum::FEATURE_EXTRACTION->value, $datas['model']);

        // Without pooling, feature-extraction returns one vector per token,
        // which isn't comparable between texts of different lengths. Mean
        // pooling collapses that into a single fixed-size sentence vector.
        return $embedder(
            $datas['data'],
            pooling: $datas['pooling'] ?? 'mean',
            normalize: $datas['normalize'] ?? true,
        );
    }
}
