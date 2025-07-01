<?php

namespace App\Jobs\KnowledgeBase;

use App\Models\KnowledgeBaseUrlResource;
use App\Models\Workspace;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use OpenAI;

class PrioritizeUrlsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 180;
    public $tries = 3;
    
    private Collection $urls;
    private Workspace $workspace;

    /**
     * Create a new job instance.
     */
    public function __construct(Collection $urls, Workspace $workspace)
    {
        $this->urls = $urls;
        $this->workspace = $workspace;
    }

    /**
     * Get backoff times for retries.
     */
    public function backoff(): array
    {
        return [60, 120, 180];
    }

    /**
     * Execute the job.
     * @throws ConnectionException
     */
    public function handle(): void
    {
        if ($this->urls->isEmpty()) {
            return;
        }

        // Process URLs in chunks of 100
        $urlChunks = $this->urls->chunk(100);
        
        foreach ($urlChunks as $chunk) {
            $this->processUrlChunk($chunk);
            
            // Add a small delay between API calls to avoid rate limiting
            if ($urlChunks->count() > 1) {
                sleep(2);
            }
        }
    }
    
    /**
     * Process a chunk of URLs (max 100)
     * 
     * @param Collection $urlsChunk
     * @throws ConnectionException
     */
    private function processUrlChunk(Collection $urlsChunk): void
    {
        try {
            $client = OpenAI::factory()
                ->withApiKey(config('services.groq.api_key'))
                ->withBaseUri('api.groq.com/openai/v1')
                ->make();

            $response = $client->chat()->create([
                'model' => 'meta-llama/llama-4-scout-17b-16e-instruct',
                'temperature' => 0.2,
                'messages' => [
                    ['role' => 'system', 'content' => $this->getSystemPrompt()],
                    ['role' => 'user', 'content' => $this->getUserPrompt($urlsChunk)],
                ],
                'response_format' => ['type' => 'json_object'],
            ]);

            if (!isset($response->choices[0]->message->content)) {
                throw new ConnectionException('GroqAI response does not contain content');
            }

            $content = json_decode($response->choices[0]->message->content, true);
            
            // Update priority scores in the database
            if (isset($content['url_priorities']) && is_array($content['url_priorities'])) {
                foreach ($content['url_priorities'] as $urlData) {
                    if (isset($urlData['url']) && isset($urlData['priority_score'])) {
                        $score = (int) $urlData['priority_score'];
                        
                        // Ensure score is within 1-100 range
                        $score = max(1, min(100, $score));
                        
                        KnowledgeBaseUrlResource::query()
                            ->where('url', $urlData['url'])
                            ->where('workspace_id', $this->workspace->id)
                            ->update(['priority_score' => $score]);
                        
                        Log::info("Updated priority score for URL: {$urlData['url']} to {$score} for workspace {$this->workspace->id}");
                    }
                }
            }
        } catch (\Exception $exception) {
            Log::error("Error prioritizing URLs chunk: " . $exception->getMessage());
            Log::error($exception->getTraceAsString());
            
            // Re-throw to trigger retry mechanism
            throw $exception;
        }
    }

    /**
     * System prompt for the AI model.
     */
    private function getSystemPrompt(): string
    {
        return <<<EOT
You are an expert at analyzing website URLs and determining their importance for AI training in a support chatbot context.

### Task:
Analyze the provided URLs and assign a priority score from 1 to 100 to each URL based on its potential value for training a support chatbot.

### Scoring Criteria:
- 90-100: Critical for support (pricing, terms, privacy policies, refund policy, shipping policy ,FAQs, features, benefits)
- 70-89: Very useful (help docs, troubleshooting guides, product specs, categories, use cases )
- 40-69: Moderately useful (general information about the company, team, processes)
- 20-39: Somewhat useful (industry news, general blog posts related to the domain)
- 1-19: Minimally useful (about pages, contact pages, careers,)

### URL Analysis Guidelines:
- Examine URL structure and path components to infer content type
- Higher priority for pages likely containing factual, instructional, or support content
- Lower priority for marketing, general info, or transient pages
- Consider URL depth - deeper paths often contain more specific information
- Pages with query parameters are often less valuable unless they appear to be specific product pages

### Response Format:
Respond with a JSON object containing a "url_priorities" array with each URL and its priority score.
Example:
```json
{
  "url_priorities": [
    {"url": "https://example.com/help/faq", "priority_score": 95},
    {"url": "https://example.com/help/refund-policy", "priority_score": 96},
    {"url": "https://example.com/help/shipping", "priority_score": 95},
    {"url": "https://example.com/help/categories", "priority_score": 85},    
    {"url": "https://example.com/product/product-name", "priority_score": 25}
  ]
}
```

Your analysis should be thorough but efficient. For each URL, make your best assessment based on URL structure alone, as you don't have access to actual page content.
EOT;
    }

    /**
     * User prompt containing the URLs to analyze.
     */
    private function getUserPrompt(Collection $urls): string
    {
        $urlsList = $urls->implode("\n");
        
        return <<<EOT
Please analyze the following URLs and assign a priority score from 1 to 100 for each based on their potential importance for training a support AI chatbot.

URLs to analyze:
{$urlsList}

Return a JSON object with the URL and priority score for each link, following the format in the instructions. Ensure every URL has a priority score between 1 and 100.
EOT;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("PrioritizeUrlsJob failed: " . $exception->getMessage());
        Log::error($exception->getTraceAsString());
    }
} 