<?php declare(strict_types=1);

namespace Codematic\Inci\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class OpenAIService
{
    private const API_URL = 'https://api.openai.com/v1/chat/completions';

    public function __construct(
        private readonly string $apiKey,
        private readonly string $model,
        private readonly LoggerInterface $logger,
        private readonly SystemConfigService $systemConfigService,
        private readonly Client $httpClient = new Client()
    ) {
    }


    public function generateDescription(string $ingredientName, ?string $salesChannelId = null): ?string
    {
        return $this->generateFromTemplate('description', $ingredientName, $salesChannelId);
    }

    public function generateMainFunctions(string $ingredientName, ?string $salesChannelId = null): ?string
    {
        return $this->generateFromTemplate('main_functions', $ingredientName, $salesChannelId);
    }

    public function generateSafetyInformation(string $ingredientName, ?string $salesChannelId = null): ?string
    {
        return $this->generateFromTemplate('safety', $ingredientName, $salesChannelId);
    }

    public function generateRating(string $ingredientName): ?int
    {
        $result = $this->generateFromTemplate('rating', $ingredientName);
        
        if ($result === null) {
            return null;
        }

        // Extract rating number from response
        if (preg_match('/(\d+)/', trim($result), $matches)) {
            $rating = (int) $matches[1];
            return ($rating >= 1 && $rating <= 3) ? $rating : null;
        }

        return null;
    }


    public function generateGeneralFields(string $ingredientName, ?string $salesChannelId = null): ?array
    {
        // Get prompt from config first, fallback to file
        $prompt = $this->systemConfigService->get('CodematicInci.config.generalFieldsPrompt', $salesChannelId);
        
        if (!$prompt) {
            $promptPath = __DIR__ . '/../../prompts/general_fields.txt';
            if (file_exists($promptPath)) {
                $prompt = file_get_contents($promptPath);
            }
        }
        
        if (!$prompt) {
            $this->logger->warning('General fields prompt not found in config or file');
            return null;
        }

        $prompt = str_replace('{INGREDIENT_NAME}', $ingredientName, $prompt);

        $result = $this->makeApiCall($prompt, $this->model);
        
        if ($result === null) {
            return null;
        }

        // Parse JSON response
        $decoded = json_decode($result, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        $this->logger->warning('Failed to parse general fields JSON response', ['response' => $result]);
        return null;
    }

    private function generateFromTemplate(string $templateName, string $ingredientName, ?string $salesChannelId = null): ?string
    {
        // Map template names to config keys
        $configKeyMap = [
            'description' => 'CodematicInci.config.descriptionPrompt',
            'safety' => 'CodematicInci.config.safetyPrompt',
            'main_functions' => 'CodematicInci.config.mainFunctionsPrompt',
            'rating' => 'CodematicInci.config.ratingPrompt',
            'general_fields' => 'CodematicInci.config.generalFieldsPrompt'
        ];

        // Try to get prompt from config first, fallback to file
        $prompt = null;
        if (isset($configKeyMap[$templateName]) && strpos($configKeyMap[$templateName], 'CodematicInci.config.') === 0) {
            $prompt = $this->systemConfigService->get($configKeyMap[$templateName], $salesChannelId);
        }
        
        if (!$prompt) {
            $promptPath = __DIR__ . '/../../prompts/' . $templateName . '.txt';
            if (file_exists($promptPath)) {
                $prompt = file_get_contents($promptPath);
            }
        }
        
        if (!$prompt) {
            $this->logger->warning('Prompt template not found in config or file', ['template' => $templateName]);
            return null;
        }

        $prompt = str_replace('{INGREDIENT_NAME}', $ingredientName, $prompt);

        return $this->makeApiCall($prompt, $this->model);
    }

    private function makeApiCall(string $prompt, string $model, int $maxRetries = 3): ?string
    {
        $payload = [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ]
        ];

        // Use appropriate parameters based on model
        if (str_contains($model, 'gpt-5') || str_contains($model, 'o1') || str_contains($model, 'o3')) {
            $payload['max_completion_tokens'] = 2000;
            // These models don't support custom temperature, use default
        } else {
            $payload['max_tokens'] = 2000;
            $payload['temperature'] = 0.7;
        }

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                $response = $this->httpClient->post(self::API_URL, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $payload,
                    'timeout' => 60,
                ]);

                $body = $response->getBody()->getContents();
                $data = json_decode($body, true);

                if (isset($data['choices'][0]['message']['content'])) {
                    return trim($data['choices'][0]['message']['content']);
                }

                $this->logger->error('Invalid OpenAI response format', ['response' => $body]);
                
            } catch (GuzzleException $e) {
                $this->logger->error('OpenAI API call failed', [
                    'attempt' => $attempt,
                    'model' => $model,
                    'error' => $e->getMessage()
                ]);

                if ($attempt === $maxRetries) {
                    return null;
                }

                // Wait before retry (exponential backoff)
                sleep($attempt * 2);
            }
        }

        return null;
    }
}