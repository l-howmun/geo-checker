<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GEO Checker — AI Brand Visibility</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen">
    <div class="max-w-3xl mx-auto px-4 py-12">
        <!-- Header -->
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-white">GEO Checker</h1>
            <p class="text-gray-400 mt-2">Check if your brand appears in AI-generated answers</p>
        </div>

        <!-- Form -->
        <form id="checkForm" class="space-y-6 bg-gray-900 rounded-xl p-6 border border-gray-800">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Brand Name</label>
                <input type="text" id="brand" placeholder="e.g. SiteGiant, Grab, Shopee"
                    class="w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Search Prompts <span class="text-gray-500">(up to 5)</span></label>
                <div id="promptsContainer" class="space-y-2">
                    <input type="text" name="prompts[]" placeholder="e.g. best e-commerce platform in Malaysia"
                        class="prompt-input w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <button type="button" id="addPrompt" class="mt-2 text-sm text-blue-400 hover:text-blue-300">+ Add prompt</button>
            </div>

            <button type="submit" id="submitBtn"
                class="w-full bg-blue-600 hover:bg-blue-500 text-white font-medium py-2.5 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                Check Visibility
            </button>
        </form>

        <!-- Results -->
        <div id="results" class="hidden mt-8 space-y-6">
            <!-- Score Card -->
            <div id="scoreCard" class="bg-gray-900 rounded-xl p-6 border border-gray-800 text-center">
                <div class="text-5xl font-bold" id="scoreValue">—</div>
                <div class="text-gray-400 mt-1">Visibility Score</div>
            </div>

            <!-- Details -->
            <div id="resultDetails" class="space-y-3"></div>
        </div>

        <!-- Error -->
        <div id="error" class="hidden mt-6 bg-red-900/30 border border-red-800 rounded-xl p-4 text-red-300"></div>

        <!-- Footer -->
        <div class="text-center mt-12 text-gray-600 text-sm">
            Built by <a href="https://horizonit.dev" class="text-gray-400 hover:text-white">HorizonIT</a> — Portfolio Demo
        </div>
    </div>

    <script>
        const form = document.getElementById('checkForm');
        const addBtn = document.getElementById('addPrompt');
        const container = document.getElementById('promptsContainer');
        const resultsDiv = document.getElementById('results');
        const scoreValue = document.getElementById('scoreValue');
        const resultDetails = document.getElementById('resultDetails');
        const errorDiv = document.getElementById('error');
        const submitBtn = document.getElementById('submitBtn');

        addBtn.addEventListener('click', () => {
            if (container.children.length >= 5) return;
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'prompts[]';
            input.placeholder = 'e.g. which CRM is popular for Malaysian SMEs';
            input.className = 'prompt-input w-full bg-gray-800 border border-gray-700 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-blue-500 focus:border-transparent';
            container.appendChild(input);
            if (container.children.length >= 5) addBtn.classList.add('hidden');
        });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            errorDiv.classList.add('hidden');
            resultsDiv.classList.add('hidden');

            const brand = document.getElementById('brand').value.trim();
            const prompts = Array.from(document.querySelectorAll('.prompt-input'))
                .map(i => i.value.trim())
                .filter(v => v.length > 0);

            if (!brand || prompts.length === 0) {
                showError('Please enter a brand name and at least one prompt.');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Checking...';

            try {
                const res = await fetch('/check', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ brand, prompts }),
                });

                if (res.status === 429) {
                    showError('Rate limit reached. Try again in an hour (5 checks/hour).');
                    return;
                }

                if (!res.ok) {
                    const err = await res.json();
                    showError(err.message || 'Something went wrong.');
                    return;
                }

                const data = await res.json();
                displayResults(data);
            } catch (err) {
                showError('Network error. Please try again.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Check Visibility';
            }
        });

        function displayResults(data) {
            // Score
            const score = data.score;
            scoreValue.textContent = score + '%';
            scoreValue.className = 'text-5xl font-bold ' +
                (score >= 60 ? 'text-green-400' : score >= 30 ? 'text-yellow-400' : 'text-red-400');

            // Details
            resultDetails.innerHTML = data.results.map(r => `
                <div class="bg-gray-900 rounded-lg p-4 border border-gray-800">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm text-gray-400 truncate max-w-[70%]">${escHtml(r.prompt)}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full ${r.mentioned ? 'bg-green-900 text-green-300' : 'bg-red-900 text-red-300'}">
                            ${r.mentioned ? '✓ Mentioned' : '✗ Not found'}
                        </span>
                    </div>
                    ${r.snippet ? `
                        <div class="text-sm text-gray-300 mt-2 border-l-2 ${sentimentBorder(r.sentiment)} pl-3">
                            ${escHtml(r.snippet)}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">Sentiment: ${r.sentiment}</div>
                    ` : ''}
                </div>
            `).join('');

            resultsDiv.classList.remove('hidden');
        }

        function sentimentBorder(s) {
            if (s === 'positive') return 'border-green-500';
            if (s === 'negative') return 'border-red-500';
            return 'border-gray-600';
        }

        function showError(msg) {
            errorDiv.textContent = msg;
            errorDiv.classList.remove('hidden');
        }

        function escHtml(str) {
            const d = document.createElement('div');
            d.textContent = str;
            return d.innerHTML;
        }
    </script>
</body>
</html>
