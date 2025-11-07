<?php

namespace Doppar\AI\Enum;

enum TaskEnum: string
{
    case SENTIMENT_ANALYSIS = 'sentiment-analysis';
    case TEXT_GENERATION = 'text-generation';
    case TRANSLATION = 'translation';
    case QUESTION_ANSWERING = 'question-answering';
    case ZERO_SHOT_CLASSIFICATION = 'zero-shot-classification';
    case FILL_MASK = 'fill-mask';
    case SUMMARIZATION = 'summarization';

    case TEXT_CLASSIFICATION = 'text-classification';
    case TOKEN_CLASSIFICATION = 'token-classification';
    case FEATURE_EXTRACTION = 'feature-extraction';
    case EMBEDDING = 'embedding'; // Has Issue

    case IMAGE_CLASSIFICATION = 'image-classification';
    case IMAGE_CAPTION = 'image-to-text';
    case ZERO_SHOT_IMAGE_CLASSIFICATION = 'zero-shot-image-classification';
    case OBJECT_DETECTION = 'object-detection';
}
