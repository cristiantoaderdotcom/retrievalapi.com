<?php

namespace App\Services\Rag;

use App\Models\Workspace;
use App\Services\Rag\EmbeddingService;
use Illuminate\Support\Facades\Log;

class RagService
{
    protected EmbeddingService $embeddingService;

    public function __construct(EmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;
    }

    /**
     * Filter contexts based on relevance to user's message using embeddings
     * 
     * @param string $userMessage The message from the user
     * @param Workspace $workspace The workspace containing knowledge bases
     * @param \Illuminate\Support\Collection|null $conversationMessages Previous conversation messages for context
     * @return array Array of relevant content, containing both knowledge base and product information
     */
    public function getRelevantContexts(string $userMessage, Workspace $workspace, $conversationMessages = null): array
    {
        $result = [
            'knowledge_bases' => [],
            'products' => []
        ];
        
        // Get all available contexts for this workspace
        $allContexts = $workspace->knowledgeBases;
        $allProducts = $workspace->products;
        
        // If no contexts and no products, return no match indicator
        if ($allContexts->isEmpty() && $allProducts->isEmpty()) {
            return ['no_match' => true];
        }
        
        // Build contextual message including conversation history
        $contextualMessage = $this->buildContextualMessage($userMessage, $conversationMessages);
        
        // Create embedding for contextual message
        $userEmbedding = $this->embeddingService->createEmbedding($contextualMessage);
        if (empty($userEmbedding)) {
            return ['no_match' => true];
        }
        
        // Check for contexts that need embeddings
        $contextsWithoutEmbeddings = $allContexts->filter(function ($context) {
            return empty($context->embedding) || is_null($context->embedding_processed_at);
        });
        
        // If there are contexts without embeddings, create them
        if (!$contextsWithoutEmbeddings->isEmpty()) {
            $this->embeddingService->createEmbeddingsForContexts($contextsWithoutEmbeddings);
            
            // Refresh the contexts to get updated embeddings
            $allContexts = $workspace->knowledgeBases()->get();
        }
        
        // Find similar knowledge base contexts
        if (!$allContexts->isEmpty()) {
            $similarContexts = $this->embeddingService->findSimilarContexts($userEmbedding, $allContexts);
            $result['knowledge_bases'] = array_column($similarContexts, 'id');
        }
        
        // Smart product selection based on user intent
        if (!$allProducts->isEmpty()) {
            $productIntent = $this->analyzeProductIntent($userMessage, $conversationMessages);
            
            Log::info('RAG: Product intent analysis', [
                'user_message' => $userMessage,
                'intent' => $productIntent['type'],
                'specific_product' => $productIntent['specific_product'] ?? null,
                'confidence' => $productIntent['confidence']
            ]);
            
            if ($productIntent['type'] !== 'none') {
                // Check for products that need embeddings
                $productsWithoutEmbeddings = $allProducts->filter(function ($product) {
                    return empty($product->embedding) || is_null($product->embedding_processed_at);
                });
                
                // If there are products without embeddings, create them
                if (!$productsWithoutEmbeddings->isEmpty()) {
                    $this->embeddingService->createEmbeddingsForProducts($productsWithoutEmbeddings);
                    
                    // Refresh the products to get updated embeddings
                    $allProducts = $workspace->products()->get();
                }
                
                $result['products'] = $this->selectProductsBasedOnIntent($productIntent, $userEmbedding, $allProducts);
            }
        }
        
        // If both knowledge bases and products are empty, return no match
        if (empty($result['knowledge_bases']) && empty($result['products'])) {
            return ['no_match' => true];
        }
        
        return $result;
    }

    /**
     * Analyze user intent regarding products
     * 
     * @param string $userMessage The user's message
     * @param \Illuminate\Support\Collection|null $conversationMessages Previous conversation messages
     * @return array Intent analysis result
     */
    private function analyzeProductIntent(string $userMessage, $conversationMessages = null): array
    {
        $message = strtolower($userMessage);
        
        // Build conversation context for better intent detection
        $conversationContext = '';
        if ($conversationMessages && !$conversationMessages->isEmpty()) {
            $recentMessages = $conversationMessages->take(-3); // Last 3 messages for context
            $conversationContext = $recentMessages->pluck('message')->implode(' ');
            $conversationContext = strtolower($conversationContext);
        }
        
        $fullContext = $conversationContext . ' ' . $message;
        
        // Product-related keywords
        $productKeywords = [
            'product', 'item', 'buy', 'purchase', 'price', 'cost', 'sell', 'selling',
            'available', 'stock', 'inventory', 'catalog', 'store', 'shop', 'shopping'
        ];
        
        // Color and style keywords that often indicate product searches
        $productStyleKeywords = [
            'black', 'white', 'grey', 'gray', 'blue', 'red', 'green', 'yellow', 'pink', 'purple',
            'natural', 'light', 'dark', 'medium', 'small', 'large', 'xl', 'xs', 'size',
            'color', 'style', 'design', 'pattern', 'material', 'fabric', 'leather', 'cotton',
            'wool', 'runner', 'shoe', 'sock', 'shirt', 'dress', 'pants', 'jacket'
        ];
        
        // Specific product request indicators
        $specificProductIndicators = [
            'show me', 'tell me about', 'what is', 'describe', 'details about',
            'information about', 'specs for', 'specifications', 'features of',
            'price of', 'cost of', 'where can i find', 'do you have', 'is there',
            'price for', 'how much is', 'how much does', 'what does', 'cost for'
        ];
        
        // Recommendation request indicators
        $recommendationIndicators = [
            'recommend', 'suggest', 'best', 'top', 'popular', 'which', 'what should',
            'help me choose', 'help me find', 'looking for', 'need something',
            'alternatives', 'options', 'similar', 'like', 'compare'
        ];
        
        // General product browsing indicators
        $browsingIndicators = [
            'browse', 'see all', 'show all', 'list', 'what do you have',
            'whats available', 'your products', 'your catalog'
        ];
        
        // Check if message contains product-related terms
        $hasProductKeywords = false;
        foreach ($productKeywords as $keyword) {
            if (str_contains($fullContext, $keyword)) {
                $hasProductKeywords = true;
                Log::info('RAG: Found product keyword', ['keyword' => $keyword, 'context' => $fullContext]);
                break;
            }
        }
        
        // Check for product style/color keywords
        if (!$hasProductKeywords) {
            foreach ($productStyleKeywords as $keyword) {
                if (str_contains($fullContext, $keyword)) {
                    $hasProductKeywords = true;
                    Log::info('RAG: Found product style keyword', ['keyword' => $keyword, 'context' => $fullContext]);
                    break;
                }
            }
        }
        
        // If no product keywords found, check for implicit product requests
        if (!$hasProductKeywords) {
            // Look for price symbols, model numbers, brand names etc.
            if (preg_match('/\$\d+|\d+\.\d+|model|brand|size|color|version/i', $message)) {
                $hasProductKeywords = true;
                Log::info('RAG: Found implicit product pattern', ['message' => $message]);
            }
        }
        
        // Special case: if the message is very short and contains style/color terms, likely a product search
        if (!$hasProductKeywords && strlen(trim($message)) <= 20) {
            foreach ($productStyleKeywords as $keyword) {
                if (str_contains($message, $keyword)) {
                    $hasProductKeywords = true;
                    Log::info('RAG: Found short message style keyword', ['keyword' => $keyword, 'message' => $message]);
                    break;
                }
            }
        }
        
        Log::info('RAG: Product keyword detection result', [
            'message' => $message,
            'fullContext' => $fullContext,
            'hasProductKeywords' => $hasProductKeywords,
            'messageLength' => strlen(trim($message))
        ]);
        
        if (!$hasProductKeywords) {
            return [
                'type' => 'none',
                'confidence' => 0.9
            ];
        }
        
        // Determine specific intent type
        $specificScore = 0;
        $recommendationScore = 0;
        $browsingScore = 0;
        
        // Check for specific product request patterns
        foreach ($specificProductIndicators as $indicator) {
            if (str_contains($fullContext, $indicator)) {
                $specificScore += 1;
            }
        }
        
        // Check for recommendation request patterns
        foreach ($recommendationIndicators as $indicator) {
            if (str_contains($fullContext, $indicator)) {
                $recommendationScore += 1;
            }
        }
        
        // Check for browsing patterns
        foreach ($browsingIndicators as $indicator) {
            if (str_contains($fullContext, $indicator)) {
                $browsingScore += 1;
            }
        }
        
        // Additional scoring based on question patterns
        if (preg_match('/\b(what|which|tell me|show me)\s+.*\b(product|item)\b/i', $message)) {
            $specificScore += 2;
        }
        
        // Check for specific product name patterns (product names with specific details)
        if (preg_match('/\b(price|cost|information|details)\s+(for|of|about)\s+[A-Z][^,]*\s*-\s*[^,]+/i', $message)) {
            $specificScore += 3; // Strong indicator of specific product query
        }
        
        // Check for "I want to know" patterns with specific products
        if (preg_match('/\b(i want to know|want to know|need to know)\s+(the\s+)?(price|cost|details|information)/i', $message)) {
            $specificScore += 2;
        }
        
        // Check for color + material/type combinations (like "Natural Grey", "Black Wool", etc.)
        if (preg_match('/\b(natural|light|dark|medium)\s+(grey|gray|black|white|blue|red|green)\b/i', $message) ||
            preg_match('/\b(grey|gray|black|white|blue|red|green)\s+(wool|cotton|leather|runner|shoe|sock)\b/i', $message)) {
            $recommendationScore += 2; // These are usually browsing queries
        }
        
        // Short queries with just colors or styles are usually browsing
        if (strlen(trim($message)) <= 15 && preg_match('/\b(natural|grey|gray|black|white|blue|red|green|wool|cotton|runner|shoe|sock)\b/i', $message)) {
            $recommendationScore += 2;
        }
        
        if (preg_match('/\b(recommend|suggest|best|top)\b/i', $message)) {
            $recommendationScore += 2;
        }
        
        if (str_contains($message, '?') && $specificScore > 0) {
            $specificScore += 1;
        }
        
        // Determine intent based on scores
        if ($specificScore > $recommendationScore && $specificScore > $browsingScore) {
            return [
                'type' => 'specific',
                'confidence' => min(0.9, 0.6 + ($specificScore * 0.1))
            ];
        } elseif ($recommendationScore > 0 || $browsingScore > 0) {
            return [
                'type' => 'recommendations',
                'confidence' => min(0.9, 0.6 + (max($recommendationScore, $browsingScore) * 0.1))
            ];
        }
        
        // Default to recommendations if we have product keywords but unclear intent
        return [
            'type' => 'recommendations',
            'confidence' => 0.5
        ];
    }

    /**
     * Select products based on detected intent
     * 
     * @param array $intent The detected intent
     * @param array $userEmbedding The user's query embedding
     * @param \Illuminate\Support\Collection $allProducts All available products
     * @return array Array of selected product IDs
     */
    private function selectProductsBasedOnIntent(array $intent, array $userEmbedding, $allProducts): array
    {
        $similarProducts = $this->embeddingService->findSimilarProducts($userEmbedding, $allProducts);
        
        switch ($intent['type']) {
            case 'specific':
                // For specific product requests, return only the most similar product
                // But only if similarity is reasonably high
                if (!empty($similarProducts) && $similarProducts[0]['similarity'] >= 0.6) {
                    return [$similarProducts[0]['id']];
                }
                // If no good match, return empty to avoid confusion
                return [];
                
            case 'recommendations':
                // For recommendations, return top 5 products
                return array_slice(array_column($similarProducts, 'id'), 0, 5);
                
            default:
                return [];
        }
    }

    /**
     * Build a contextual message that includes conversation history
     * 
     * @param string $userMessage The current user message
     * @param \Illuminate\Support\Collection|null $conversationMessages Previous conversation messages
     * @return string The contextual message for embedding
     */
    private function buildContextualMessage(string $userMessage, $conversationMessages = null): string
    {
        if (!$conversationMessages || $conversationMessages->isEmpty()) {
            Log::info('RAG: No conversation messages, using only user message', ['user_message' => $userMessage]);
            return $userMessage;
        }
        
        $contextParts = [];
        
        // Add previous conversation messages for context
        $conversationMessages->each(function ($message) use (&$contextParts) {
            $role = strtolower($message->role->label());
            $contextParts[] = "{$role}: {$message->message}";
        });
        
        // Add the current user message
        $contextParts[] = "user: {$userMessage}";
        
        $contextualMessage = implode("\n", $contextParts);
        
        Log::info('RAG: Built contextual message for embedding', [
            'conversation_messages_count' => $conversationMessages->count(),
            'contextual_message' => $contextualMessage
        ]);
        
        return $contextualMessage;
    }
} 