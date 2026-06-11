<?php

namespace App\Http\Controllers;

use App\Models\VisibilityCheck;
use App\Agents\SearchSimulator;
use App\Agents\SentimentClassifier;
use Illuminate\Http\Request;

class GeoCheckerController extends Controller
{
    public function index()
    {
        return view('checker');
    }

    public function check(Request $request)
    {
        $validated = $request->validate([
            'brand' => 'required|string|max:100',
            'prompts' => 'required|array|min:1|max:5',
            'prompts.*' => 'required|string|max:200',
        ]);

        $brand = $validated['brand'];
        $results = [];

        foreach ($validated['prompts'] as $prompt) {
            // Check cache first (24h)
            $cached = VisibilityCheck::where('brand', $brand)
                ->where('prompt', $prompt)
                ->where('engine', 'openai')
                ->where('created_at', '>=', now()->subHours(24))
                ->first();

            if ($cached) {
                $results[] = $cached->toArray();
                continue;
            }

            // Simulate AI search engine response
            $response = SearchSimulator::make()->prompt($prompt, model: 'gpt-4o-mini');
            $answer = $response->text;

            // Check if brand is mentioned
            $mentioned = stripos($answer, $brand) !== false;

            // Extract snippet
            $snippet = null;
            if ($mentioned) {
                $pos = stripos($answer, $brand);
                $start = max(0, $pos - 80);
                $end = min(strlen($answer), $pos + strlen($brand) + 80);
                $snippet = ($start > 0 ? '...' : '') . substr($answer, $start, $end - $start) . ($end < strlen($answer) ? '...' : '');
            }

            // Classify sentiment
            $sentiment = 'neutral';
            if ($mentioned && $snippet) {
                $sentimentResponse = SentimentClassifier::make()->prompt(
                    "Brand: {$brand}\nText: {$snippet}",
                    model: 'gpt-4o-mini'
                );
                $sentiment = strtolower(trim($sentimentResponse->text));
                if (!in_array($sentiment, ['positive', 'neutral', 'negative'])) {
                    $sentiment = 'neutral';
                }
            }

            // Cache result
            $check = VisibilityCheck::create([
                'brand' => $brand,
                'prompt' => $prompt,
                'engine' => 'openai',
                'mentioned' => $mentioned,
                'snippet' => $snippet,
                'sentiment' => $sentiment,
                'ip_address' => $request->ip(),
            ]);

            $results[] = $check->toArray();
        }

        $totalPrompts = count($results);
        $mentionedCount = collect($results)->where('mentioned', true)->count();
        $score = $totalPrompts > 0 ? round(($mentionedCount / $totalPrompts) * 100) : 0;

        return response()->json([
            'brand' => $brand,
            'score' => $score,
            'results' => $results,
        ]);
    }
}
