<?php

namespace Doppar\AI\TaskFactory\Task;

use Doppar\AI\Enum\TaskEnum;

use function Codewithkyrian\Transformers\Pipelines\pipeline;

class ImageCaption implements TaskInterface
{
    /**
     * The type of task this class represents.
     */
    const TASK = TaskEnum::IMAGE_CAPTION;

    /**
     * Execute the image-to-text (caption generation) pipeline.
     *
     * @param mixed $datas Input parameters for the pipeline.
     *                     Expected structure:
     *                     [
     *                         'imageUrl' => string,        // Path or URL to the image
     *                         'model' => ?string,           // Optional: model name
     *                         'maxNewTokens' => int         // Maximum tokens for generated caption
     *                     ]
     *
     * @return mixed Returns the generated image caption text or array of results.
     */
    public function execute(mixed $datas): mixed
    {
        if (empty($datas['model'])) {
            $datas['model'] = 'Xenova/vit-gpt2-image-captioning';
        }

        $classifier = pipeline(self::TASK->value, $datas['model']);

        return $classifier($datas['imageUrl'], maxNewTokens: $datas['maxNewTokens']);
    }
}
