<?php

namespace Doppar\Transformer\Enum;

enum TaskEnum: string
{
    case SENTIMENT_ANALYSIS = 'sentiment-analysis';
    case TEXT_GENERATION = 'text-generation';
    case TRANSLATION = 'translation';
}