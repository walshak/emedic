<?php
/**
 * LlmGatewayService - cURL-based Multi-Provider LLM Gateway for eMedic
 * 
 * Ported from corehealth's Laravel-based LlmGatewayService.
 * Uses native PHP cURL instead of Guzzle/Http facade.
 * Supports: OpenAI, Anthropic (Claude), Google Gemini.
 */
class LlmGatewayService
{
    protected $db;
    protected $config = [];

    public function __construct($db)
    {
        $this->db = $db;
        $this->config = $this->loadConfig();
    }

    /**
     * Send a completion request to the active LLM provider.
     */
    public function complete($systemPrompt, $userMessage, $options = [])
    {
        $provider = isset($options['provider']) ? $options['provider'] : (isset($this->config['active_provider']) ? $this->config['active_provider'] : null);
        $model = isset($options['model']) ? $options['model'] : (isset($this->config['active_model']) ? $this->config['active_model'] : null);

        if (!$model && $provider && isset($this->config['providers'][$provider]['default_model'])) {
            $model = $this->config['providers'][$provider]['default_model'];
        }

        if (!$provider || !$model) {
            throw new Exception('No LLM provider or model configured. Please configure in Admin Control Panel → AI/LLM.');
        }

        $providerConfig = isset($this->config['providers'][$provider]) ? $this->config['providers'][$provider] : null;
        if (!$providerConfig || empty($providerConfig['api_key'])) {
            throw new Exception("LLM provider '{$provider}' is not configured or missing API key.");
        }

        $apiKey = $providerConfig['api_key'];
        $baseUrl = isset($providerConfig['base_url']) ? $providerConfig['base_url'] : '';
        $maxTokens = isset($options['max_tokens']) ? (int)$options['max_tokens'] : 2048;
        $temperature = isset($options['temperature']) ? (float)$options['temperature'] : 0.3;

        $startTime = microtime(true);

        switch ($provider) {
            case 'openai':
                $response = $this->completeOpenAI($apiKey, $baseUrl ?: 'https://api.openai.com/v1', $model, $systemPrompt, $userMessage, $maxTokens, $temperature);
                break;
            case 'anthropic':
                $response = $this->completeAnthropic($apiKey, $baseUrl ?: 'https://api.anthropic.com', $model, $systemPrompt, $userMessage, $maxTokens, $temperature);
                break;
            case 'gemini':
                $response = $this->completeGemini($apiKey, $baseUrl ?: 'https://generativelanguage.googleapis.com', $model, $systemPrompt, $userMessage, $maxTokens, $temperature);
                break;
            default:
                throw new Exception("Unknown LLM provider: {$provider}");
        }

        $elapsed = round((microtime(true) - $startTime) * 1000);

        return [
            'content' => $response,
            'provider' => $provider,
            'model' => $model,
            'elapsed_ms' => $elapsed,
        ];
    }

    /**
     * Check if LLM features are enabled and configured.
     */
    public function isEnabled()
    {
        if (empty($this->config['enabled'])) return false;
        $provider = isset($this->config['active_provider']) ? $this->config['active_provider'] : null;
        if (!$provider) return false;
        $model = isset($this->config['active_model']) ? $this->config['active_model'] : null;
        if (!$model && isset($this->config['providers'][$provider]['default_model'])) {
            $model = $this->config['providers'][$provider]['default_model'];
        }
        return !empty($model);
    }

    /**
     * Get the full LLM config.
     */
    public function getConfig()
    {
        return $this->config;
    }

    // ─── Provider Implementations ────────────────────────────────────

    protected function completeOpenAI($apiKey, $baseUrl, $model, $systemPrompt, $userMessage, $maxTokens, $temperature)
    {
        $url = rtrim($baseUrl, '/') . '/chat/completions';
        $payload = json_encode([
            'model' => $model,
            'max_tokens' => $maxTokens,
            'temperature' => $temperature,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userMessage],
            ],
        ]);

        $headers = [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json',
        ];

        $response = $this->curlPost($url, $payload, $headers);
        $data = json_decode($response, true);

        if (isset($data['error'])) {
            throw new Exception('OpenAI API error: ' . (isset($data['error']['message']) ? $data['error']['message'] : json_encode($data['error'])));
        }

        return isset($data['choices'][0]['message']['content']) ? $data['choices'][0]['message']['content'] : '';
    }

    protected function completeAnthropic($apiKey, $baseUrl, $model, $systemPrompt, $userMessage, $maxTokens, $temperature)
    {
        $url = rtrim($baseUrl, '/') . '/v1/messages';
        $payload = json_encode([
            'model' => $model,
            'max_tokens' => $maxTokens,
            'temperature' => $temperature,
            'system' => $systemPrompt,
            'messages' => [
                ['role' => 'user', 'content' => $userMessage],
            ],
        ]);

        $headers = [
            'x-api-key: ' . $apiKey,
            'anthropic-version: 2023-06-01',
            'Content-Type: application/json',
        ];

        $response = $this->curlPost($url, $payload, $headers);
        $data = json_decode($response, true);

        if (isset($data['error'])) {
            throw new Exception('Anthropic API error: ' . (isset($data['error']['message']) ? $data['error']['message'] : json_encode($data['error'])));
        }

        return isset($data['content'][0]['text']) ? $data['content'][0]['text'] : '';
    }

    protected function completeGemini($apiKey, $baseUrl, $model, $systemPrompt, $userMessage, $maxTokens, $temperature)
    {
        $url = rtrim($baseUrl, '/') . "/v1beta/models/{$model}:generateContent?key={$apiKey}";
        $payload = json_encode([
            'systemInstruction' => [
                'parts' => [['text' => $systemPrompt]],
            ],
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [['text' => $userMessage]],
                ],
            ],
            'generationConfig' => [
                'maxOutputTokens' => $maxTokens,
                'temperature' => $temperature,
            ],
        ]);

        $headers = [
            'Content-Type: application/json',
        ];

        $response = $this->curlPost($url, $payload, $headers);
        $data = json_decode($response, true);

        if (isset($data['error'])) {
            throw new Exception('Gemini API error: ' . (isset($data['error']['message']) ? $data['error']['message'] : json_encode($data['error'])));
        }

        return isset($data['candidates'][0]['content']['parts'][0]['text']) ? $data['candidates'][0]['content']['parts'][0]['text'] : '';
    }

    // ─── Utilities ───────────────────────────────────────────────────

    protected function curlPost($url, $payload, $headers)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new Exception('cURL error: ' . $error);
        }

        if ($httpCode >= 400) {
            $decoded = json_decode($response, true);
            $msg = 'HTTP ' . $httpCode;
            if (isset($decoded['error']['message'])) {
                $msg .= ': ' . $decoded['error']['message'];
            } elseif (isset($decoded['error'])) {
                $msg .= ': ' . (is_string($decoded['error']) ? $decoded['error'] : json_encode($decoded['error']));
            }
            throw new Exception('LLM API error: ' . $msg);
        }

        return $response;
    }

    /**
     * Load LLM config from the hospital_details table.
     */
    protected function loadConfig()
    {
        try {
            $stmt = $this->db->query("SELECT llm_config FROM hospital_details LIMIT 1");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $raw = isset($row['llm_config']) ? $row['llm_config'] : null;

            if (is_string($raw) && !empty($raw)) {
                $decoded = json_decode($raw, true);
                return is_array($decoded) ? $decoded : [];
            }
            return [];
        } catch (Exception $e) {
            return [];
        }
    }
}
