<?php

namespace App\Agents;

use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Provider;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

#[Provider('openai')]
#[Model('gpt-4o-mini')]
#[Timeout(30)]
class SearchSimulator implements Agent
{
    use Promptable;

    public function instructions(): string
    {
        return 'You are an AI search assistant. When a user asks a question, provide a comprehensive, helpful answer. Mention specific brands, products, companies, and services by name. Include both global and regional/local options when relevant. Be detailed and name as many relevant options as possible.';
    }
}
