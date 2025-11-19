<?php

namespace Doppar\AI\Enum;

enum TaskEnum: string
{
    // Analyze text to determine emotional tone (positive, negative, neutral)
    case SENTIMENT_ANALYSIS = 'sentiment-analysis';

    // Generate new text based on a prompt or context
    case TEXT_GENERATION = 'text-generation';

    // Translate text from one language to another
    case TRANSLATION = 'translation';

    // Provide an answer to a given question based on available context
    case QUESTION_ANSWERING = 'question-answering';

    // Classify text into categories without prior examples (zero-shot learning)
    case ZERO_SHOT_CLASSIFICATION = 'zero-shot-classification';

    // Predict masked or missing words in a sentence
    case FILL_MASK = 'fill-mask';

    // Produce a shorter version of text while preserving meaning
    case SUMMARIZATION = 'summarization';

    // Categorize text into predefined labels
    case TEXT_CLASSIFICATION = 'text-classification';

    // Perform token-level classification (e.g., NER, POS tagging)
    case TOKEN_CLASSIFICATION = 'token-classification';

    // Extract numerical feature vectors from text for deeper analysis
    case FEATURE_EXTRACTION = 'feature-extraction';

    // Generate embeddings representing semantic meaning of text
    case EMBEDDING = 'embedding'; // Has Issue

    // Classify images into predefined categories
    case IMAGE_CLASSIFICATION = 'image-classification';

    // Generate captions or textual descriptions from images
    case IMAGE_CAPTION = 'image-to-text';

    // Classify images into categories without prior examples (zero-shot learning)
    case ZERO_SHOT_IMAGE_CLASSIFICATION = 'zero-shot-image-classification';

    // Detect and localize objects within an image
    case OBJECT_DETECTION = 'object-detection';
}
