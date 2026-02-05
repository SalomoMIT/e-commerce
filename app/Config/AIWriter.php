<?php

namespace Config;

/**
 * @immutable
 */
class AIWriter
{
    //API URL
    public static $apiUrl = 'https://api.openai.com/v1/chat/completions';

    //AI Models
    public static $models = [
        'gpt-5' => 'GPT-5',
        'gpt-5-mini' => 'GPT-5 Mini',
        'gpt-5-nano' => 'GPT-5 Nano',
        'gpt-4.1' => 'GPT-4.1',
        'gpt-4.1-mini' => 'GPT-4.1 Mini',
        'gpt-4.1-nano' => 'GPT-4.1 Nano',
        'gpt-4o' => 'GPT-4 Omni',
        'gpt-4o-mini' => 'GPT-4o Mini',
        'gpt-3.5-turbo' => 'GPT-3.5 Turbo'
    ];

    //AI Form Defaults
    public static $formDefaults = [
        'model' => 'gpt-5-nano',
        'tone' => 'casual', //academic, casual, critical, formal, humorous, inspirational, persuasive, professional
        'length' => 'medium', //very_short, short, medium, long, very_long
    ];

    //AI Promt
    public static $basePrompt = "Write a {content_type} about the topic: {topic}. It should be {length} in length. Use a {tone} tone. Write it in {language}. Only return the plain text without any introductions, explanations, or formatting.";

    //generate AI promt
    public static function generateAIPrompt($options)
    {
        $contentType = match ($options->contentType) {
            'product' => 'product description',
            'page' => 'page description',
            'blog' => 'blog article',
            default => 'text'
        };

        $prompt = self::$basePrompt;
        if (!empty($prompt)) {
            $prompt = str_replace('{length}', $options->length, $prompt);
            $prompt = str_replace('{content_type}', $contentType, $prompt);
            $prompt = str_replace('{topic}', $options->topic, $prompt);
            $prompt = str_replace('{tone}', $options->tone, $prompt);
            $prompt = str_replace('{language}', $options->langName, $prompt);
        }
        return $prompt;
    }

    //generate text
    public static function generateText($options)
    {
        // Validate and set model
        $model = (!empty(self::$models) && array_key_exists($options->model, self::$models)) ? $options->model : 'gpt-4o-mini';

        // Build the AI prompt
        $prompt = self::generateAIPrompt($options);

        $data = [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 1
        ];

        try {
            // Initialize AI Writer
            $aiWriter = aiWriter();
            if (empty($aiWriter->apiKey)) {
                return [
                    'status' => 'error',
                    'message' => 'API key is missing. Add your API key from the Preferences section.'
                ];
            }

            // Initialize cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, self::$apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $aiWriter->apiKey,
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            // Execute cURL request
            $response = curl_exec($ch);
            log_message('error', 'Stripe API Error while verifying payment: ' . json_encode($response));

            // Check for cURL errors
            if (curl_errno($ch)) {
                $errorMessage = curl_error($ch);
                curl_close($ch);
                return [
                    'status' => 'error',
                    'message' => 'cURL error: ' . $errorMessage
                ];
            }

            // Get HTTP status code
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // Validate HTTP response code
            if ($httpCode !== 200) {
                return [
                    'status' => 'error',
                    'message' => 'Unexpected response code: ' . $httpCode
                ];
            }

            // Decode response
            $responseData = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid JSON response: ' . json_last_error_msg()
                ];
            }

            // Check for API errors in response
            if (isset($responseData['error'])) {
                return [
                    'status' => 'error',
                    'message' => $responseData['error']['message'] ?? 'Unknown error'
                ];
            }

            // Return success response with content
            if (isset($responseData['choices'][0]['message']['content'])) {
                $content = $responseData['choices'][0]['message']['content'];
                if (!empty($content)) {
                    $content = nl2br($content);
                }
                return [
                    'status' => 'success',
                    'content' => $content
                ];
            }

            return [
                'status' => 'error',
                'message' => 'No valid response content found.'
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'An error occurred: ' . $e->getMessage()
            ];
        }
    }
}