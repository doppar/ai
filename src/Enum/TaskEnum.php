<?php

namespace Doppar\Transformer\Enum;

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
}
