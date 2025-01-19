// In your Blade view
<div id="tweets-container" class="space-y-4">
    <div id="loading" class="hidden">
        <p class="text-gray-600">Loading tweets...</p>
    </div>
    <div id="error" class="hidden">
        <p class="text-red-500"></p>
    </div>
</div>

<script>
    let isRetrying = false;

    async function fetchTweets() {
        const loading = document.getElementById('loading');
        const error = document.getElementById('error');
        const container = document.getElementById('tweets-container');

        loading.classList.remove('hidden');
        error.classList.add('hidden');

        try {
            const response = await fetch('/api/tweets');
            const data = await response.json();

            if (response.status === 429) {
                // Rate limit hit
                const retryAfter = data.retry_after || 900;
                error.querySelector('p').textContent =
                    `Rate limit exceeded. Retrying in ${Math.ceil(retryAfter/60)} minutes...`;
                error.classList.remove('hidden');

                // Schedule retry
                if (!isRetrying) {
                    isRetrying = true;
                    setTimeout(() => {
                        isRetrying = false;
                        fetchTweets();
                    }, retryAfter * 1000);
                }
                return;
            }

            if (!response.ok) {
                throw new Error(data.message || 'Failed to fetch tweets');
            }

            // Clear and display tweets
            container.innerHTML = '';
            data.data.forEach(tweet => {
                const tweetElement = `
                <div class="bg-white rounded-lg shadow p-4 hover:bg-gray-50 transition">
                    <p class="text-gray-600 text-sm mb-2">
                        ${new Date(tweet.created_at).toLocaleDateString()}
                    </p>
                    <p class="text-gray-900">
                        ${tweet.text}
                    </p>
                </div>
            `;
                container.insertAdjacentHTML('beforeend', tweetElement);
            });
        } catch (error) {
            error.querySelector('p').textContent = error.message;
            error.classList.remove('hidden');
        } finally {
            loading.classList.add('hidden');
        }
    }

    // Initial fetch
    document.addEventListener('DOMContentLoaded', fetchTweets);

    // Refresh every 15 minutes (Twitter's typical rate limit window)
    setInterval(fetchTweets, 15 * 60 * 1000);
</script>
