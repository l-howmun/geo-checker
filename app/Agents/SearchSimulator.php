<?php

namespace App\Agents;

use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

#[Model('google/gemma-4-26b-a4b-it:free')]
class SearchSimulator implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are a search engine simulator. Answer the user query naturally as if you are an AI assistant helping someone research options. Give a helpful, detailed answer mentioning relevant brands, products, or services by name.';
    }
}
