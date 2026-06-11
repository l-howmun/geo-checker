<?php

namespace App\Agents;

use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

#[Model('meta-llama/llama-4-maverick:free')]
class SentimentClassifier implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'Classify the sentiment toward the brand mentioned in the text. Reply with exactly one word: positive, neutral, or negative.';
    }
}
