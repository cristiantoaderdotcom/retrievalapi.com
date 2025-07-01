<?php

namespace App\Jobs;

use App\Enums\ResourceStatus;
use GuzzleHttp\Client;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use OpenAI;
use App\Models\Workspace;		

class ProcessChunk implements ShouldQueue {
	use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

	public $timeout = 180;

	public $tries = 3;

	public function backoff(): array {
		return [60, 120, 180];
	}

	private $resource;

	private string $text;

	private $language;

	private array $trainingSettings;

	private Workspace $workspace;

	/**
	 * Create a new job instance.
	 */
	public function __construct($resource, $text) {
		$this->resource = $resource;
		$this->text = $text;

		$this->workspace = Workspace::query()
			->with('user', 'settings')
			->where('id', $this->resource->workspace_id)
			->first();

		$this->trainingSettings = $this->workspace->setting('training', []);

		$this->language = $this->resource->workspace->language;
	}

	/**
	 * Execute the job.
	 * @throws ConnectionException
	 */
	public function handle(): void {
		// $openai = OpenAI::factory()
		// 	->withApiKey(config('services.openai.key'))
		//     ->withOrganization(config('services.openai.organization'))
		// 	->withHttpClient(new Client(['timeout' => 300, 'connect_timeout' => 300]))
		//     ->make();

		$client = OpenAI::factory()
			->withApiKey(config('services.groq.api_key'))
			->withBaseUri('api.groq.com/openai/v1')
			->make();

		$response = $client->chat()->create([
			'model' => 'meta-llama/llama-4-scout-17b-16e-instruct',
			'temperature' => (float) data_get($this->trainingSettings, 'temperature'),
			'messages' => [
				['role' => 'system', 'content' => $this->getSystemPrompt()],
				['role' => 'user', 'content' => $this->getUserPrompt($this->text)],
			],
			'response_format' => ['type' => 'json_object'],
		]);

		if (!isset($response->choices[0]->message->content)) {
			throw new ConnectionException('OpenAI response does not contain content');
		}

		$content = $response->choices[0]->message->content;
		$content = json_decode($content, true);

		// Get existing questions to check for duplicates
		$existingQuestions = $this->resource->contexts()->pluck('question')->toArray();
		$existingQuestionsLower = array_map(function($question) {
			return strtolower(trim($question));
		}, $existingQuestions);

		$createdQuestionsInThisRun = [];
		$skippedCount = 0;

		foreach ($content['questions-answers'] as $conversation) {
			$question = $conversation['question'];
			$questionLower = strtolower(trim($question));
			
			// Skip exact duplicates
			if (in_array($questionLower, $existingQuestionsLower)) {
				Log::info('Skipping exact duplicate question: ' . $question);
				$skippedCount++;
				continue;
			}
			
			// Check for similarity with existing questions
			$isDuplicate = false;
			
			// Check for high similarity based on word overlap
			foreach ($existingQuestionsLower as $index => $existingQuestionLower) {
				// Skip short questions for similarity check (less than 5 words)
				$wordCount = count(explode(' ', $questionLower));
				if ($wordCount < 5) {
					continue;
				}
				
				// Calculate word overlap
				$similarityScore = $this->calculateSimilarity($questionLower, $existingQuestionLower);
				
				// If similarity is above threshold, consider it a duplicate
				if ($similarityScore > 0.7) {
					Log::info("Skipping similar question (score: $similarityScore): '$question' similar to '{$existingQuestions[$index]}'");
					$isDuplicate = true;
					$skippedCount++;
					break;
				}
			}
			
			if ($isDuplicate) {
				continue;
			}
			
			// Check for similarity with questions created in this run
			foreach ($createdQuestionsInThisRun as $createdQuestion) {
				$similarityScore = $this->calculateSimilarity($questionLower, strtolower(trim($createdQuestion)));
				
				if ($similarityScore > 0.7) {
					Log::info("Skipping similar question (score: $similarityScore): '$question' similar to a question from same batch");
					$isDuplicate = true;
					$skippedCount++;
					break;
				}
			}
			
			if ($isDuplicate) {
				continue;
			}
			
			// Not a duplicate, add it to our tracking arrays
			$existingQuestionsLower[] = $questionLower;
			$createdQuestionsInThisRun[] = $question;
			
			$conversation['knowledge_base_resource_id'] = $this->resource->id;
			$conversation['workspace_id'] = $this->resource->workspace_id;
			
			try {
				$this->resource->contexts()->create($conversation);
			} catch (\Illuminate\Database\QueryException $e) {
				// Check if this is a duplicate entry error
				if (str_contains($e->getMessage(), '1062 Duplicate entry') && 
					str_contains($e->getMessage(), 'knowledge_bases_question_workspace_id_unique')) {
					Log::info("Skipping duplicate question (database constraint): '{$question}'");
					$skippedCount++;
					continue;
				}
				// If it's not a duplicate constraint issue, rethrow the exception
				throw $e;
			}
		}
		
		if ($skippedCount > 0) {
			Log::info("Total skipped duplicate/similar questions: $skippedCount");
		}

		$this->resource->update([
			'status' => ResourceStatus::PROCESSED,
			'process_completed_at' => now(),
			'total_tokens' => $response->usage->totalTokens ?? 0,
		]);
	}

	private function getSystemPrompt(): string {
		$system = [];

		$trainingInstructions = data_get($this->trainingSettings, 'instructions');
		$trainingRules = data_get($this->trainingSettings, 'rules');
		$trainingLinks = data_get($this->trainingSettings, 'links');
		$trainingLength = data_get($this->trainingSettings, 'length');

		// Add main purpose
		$system[] = 'You are an expert at creating high-quality training data for chatbots. Your task is to analyze the provided content and generate relevant question-answer pairs that reflect realistic user-assistant interactions.';

		// Guidelines section
		$system[] = PHP_EOL;
		$system[] = '### Guidelines:';
		$system[] = '- Carefully analyze the provided content to determine the optimal number of Q&A pairs needed (neither too few nor too many).';
		$system[] = '- Create diverse questions that represent different user intents, knowledge levels, and inquiry styles.';
		$system[] = '- Ensure questions are natural, conversational, and represent what real users would likely ask.';
		$system[] = '- Craft answers that are accurate, helpful, and based strictly on the information present in the provided content.';
		$system[] = '- Do not introduce information, assumptions, or speculations not supported by the content.';
		$system[] = '- Include both straightforward factual questions and more nuanced conceptual questions where appropriate.';
		$system[] = '- Limit repetitive or overlapping questions that cover the same information.';

		// Analysis Process section
		$system[] = PHP_EOL;
		$system[] = '### Analysis Process:';
		$system[] = '1. First, identify the key topics, facts, and concepts in the provided content.';
		$system[] = '2. Determine which topics merit their own Q&A pairs based on importance and complexity.';
		$system[] = '3. Critically assess how many Q&A pairs are needed to adequately cover the material (typically between 3-12 pairs depending on content length and complexity).';
		$system[] = '4. Eliminate redundant questions.';
		$system[] = '5. DO NOT HALLUCINATE.';
		$system[] = '6. DO NOT INCLUDE ANY INFORMATION THAT IS NOT PRESENT IN THE CONTENT.';
		$system[] = '7. DO NOT GENERATE DUPLICATE QUESTIONS - each question must be unique and not semantically similar to existing questions.';

		// Additional Instructions section
		$system[] = PHP_EOL;
		$system[] = '### Additional Instructions:';
		if (!empty($trainingInstructions)) {
			$system[] = '- ' . $trainingInstructions;
		}

		// Additional Rules section
		if (!empty($trainingRules)) {
			$system[] = PHP_EOL;
			$system[] = '### Additional Rules:';
			$system[] = '- ' . $trainingRules;
		}

		// Website Links Handling section
		$system[] = PHP_EOL;
		$system[] = '### Website Links Handling:';
		$system[] = '- ' . ($trainingLinks ? 'Include links in the data that it uses to answer the questions, include only the links that are relevant to the question.' : 'Do not include links in the data.');

		// Response Format section
		$system[] = PHP_EOL;
		$system[] = '### Response Format:';
		$system[] = '- Respond in ' . $this->language->name . '.';
		if (!empty($trainingLength)) {
			$system[] = '- Response data length: ' . $trainingLength;
		}
		$system[] = '- Ensure answers are complete but concise, focusing on the most relevant information.';

		// Existing questions section
		$existingQuestions = $this->resource->contexts()->pluck('question')->filter()->implode(PHP_EOL);
		if (!empty($existingQuestions)) {
			$system[] = PHP_EOL;
			$system[] = '### List of existing questions:';
			$system[] = '```';
			$system[] = $existingQuestions;
			$system[] = '```';
			$system[] = '- IMPORTANT: DO NOT create any questions that are identical or semantically similar to the above existing questions.';
			$system[] = '- Each new question must cover different aspects of the content not already addressed in existing questions.';
		}

		// JSON structure expectations
		$system[] = '- The response should be a JSON object with the following structure:';
		$system[] = '```json';
		$system[] = '{"questions-answers":[{"question":"What are the types of products offered by Tablofy?","answer":"Tablofy sells canvas paintings and fine-art prints printed on premium paper, available in collections such as *black & gold*, *fashion*, *motivational*, *abstract*, or *nature*. Additionally, they can be personalized or embroidered."},{"question":"What are the most popular collections of paintings?","answer":"The most popular collections include *black & gold*, *fashion*, *motivational*, *abstract*, *flowers*, *animals*, *urban art*, or *typography*."},{"question":"What are the conditions for delivery?","answer":"Delivery is free for orders over 200 RON."}]}';
		$system[] = '```';

		return collect($system)->implode(PHP_EOL);
	}

	private function getUserPrompt($content): string {

		return collect([
			'Please analyze the following content and generate multiple training conversations:',

			'```',
			$content,
			'```',
		])->implode(PHP_EOL);
	}

	private function getJsonSchema(): array {
		return [
			'type' => 'json_schema',
			'json_schema' => [
				'name' => 'conversations',
				'strict' => true,
				'schema' => [
					'type' => 'object',
					'properties' => [
						'conversations' => [
							'type' => 'array',
							'description' => 'Generate a comprehensive set of question-answer pairs based on this content. Each conversation should consist of a user question and assistant response. Ensure the conversations cover different aspects and depths of the content, ranging from basic inquiries to more complex discussions. The number of pairs should be appropriate to thoroughly cover the material without redundancy.',
							'items' => [
								'type' => 'object',
								'properties' => [
									'question' => [
										'type' => 'string',
										'description' => 'The user question'
									],
									'answer' => [
										'type' => 'string',
										'description' => 'The assistant response'
									]
								],
								'required' => ['question', 'answer'],
								'additionalProperties' => false
							]
						]
					],
					'required' => ['conversations'],
					'additionalProperties' => false
				]
			]
		];
	}

	public function failed($exception): void {
		Log::info("==========================");
		Log::info($exception->getMessage());
		Log::info($exception->getTraceAsString());
		Log::info("==========================");

		$this->resource->update([
			'status' => ResourceStatus::FAILED,
			'error_message' => $exception->getTraceAsString(),
			'process_completed_at' => now(),
		]);
	}

	// Add a new method for similarity calculation
	private function calculateSimilarity(string $text1, string $text2): float {
		// Convert texts to word arrays and filter out common stop words
		$stopWords = ['a', 'an', 'the', 'and', 'or', 'but', 'is', 'are', 'in', 'on', 'at', 'to', 'for', 'with', 'by', 'about', 'like', 'as', 'of', 'do', 'does', 'what', 'when', 'where', 'who', 'how', 'why', 'can', 'could', 'would', 'should', 'will'];
		
		$words1 = array_filter(explode(' ', $text1), function($word) use ($stopWords) {
			return !in_array($word, $stopWords) && strlen($word) > 2;
		});
		
		$words2 = array_filter(explode(' ', $text2), function($word) use ($stopWords) {
			return !in_array($word, $stopWords) && strlen($word) > 2;
		});
		
		// If either array is empty after filtering, return 0
		if (empty($words1) || empty($words2)) {
			return 0;
		}
		
		// Count common words
		$common = array_intersect($words1, $words2);
		$commonCount = count($common);
		
		// Calculate Jaccard similarity
		$totalUniqueWords = count(array_unique(array_merge($words1, $words2)));
		if ($totalUniqueWords === 0) {
			return 0;
		}
		
		return $commonCount / $totalUniqueWords;
	}
}