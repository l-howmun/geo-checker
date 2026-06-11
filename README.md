# GEO Checker — AI Brand Visibility Tool

A lightweight tool that checks whether a brand appears in AI-generated answers (simulating ChatGPT, Perplexity, Google AI Overviews responses).

## What It Does
1. User enters a brand name + up to 5 search prompts
2. System queries AI engines with those prompts
3. Returns a visibility report: mentioned yes/no, context snippet, sentiment score

## Stack
- **Laravel 13** + Laravel AI SDK (first-party)
- **OpenAI GPT-4o-mini** for AI queries
- **SQLite/PostgreSQL** for result caching
- **Tailwind CSS** (CDN) for frontend
- **Rate limiting** (5 checks/hour per IP) to prevent abuse

## Why This Exists
GEO (Generative Engine Optimization) is a $1B+ market in 2026. Agencies need to prove brand visibility in AI answers. This is a proof-of-concept demonstrating the core value prop.

## Deploy
```bash
# On the droplet (alongside existing Caddy + PHP-FPM)
git clone <repo> /var/www/geo.horizonit.dev
cd /var/www/geo.horizonit.dev
composer install --no-dev
cp .env.example .env
# Edit .env: set OPENAI_API_KEY, DB connection
php artisan key:generate
php artisan migrate
```

Add to Caddyfile:
```
geo.horizonit.dev {
    root * /var/www/geo.horizonit.dev/public
    php_fastcgi unix//run/php/php-fpm.sock
    file_server
}
```

## Rate Limiting
- 5 requests/hour per IP on `/check` endpoint
- Results cached 24h — repeat queries don't consume API credits
- Set OpenAI hard budget cap at $5/month as safety net

## Built With
- Laravel AI SDK Agent pattern (SearchSimulator, SentimentClassifier agents)
- Zero frontend build step (Tailwind CDN + vanilla JS)
- Single-file deployment ready
