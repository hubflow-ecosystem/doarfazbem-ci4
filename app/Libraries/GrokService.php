<?php

namespace App\Libraries;

/**
 * GrokService — Serviço central de geração de texto por IA
 *
 * Cadeia de fallback (todos gratuitos exceto OpenAI):
 * 1º Groq API (groq.com) — llama-3.3-70b-versatile [PRIMÁRIO - GRÁTIS]
 * 2º Pollinations claude (gen.pollinations.ai) [FALLBACK GRÁTIS]
 * 3º Pollinations mistral (gen.pollinations.ai) [FALLBACK GRÁTIS]
 * 4º OpenAI API (api.openai.com) — gpt-4o-mini [ÚLTIMO RECURSO - PAGO]
 *
 * Configuração no .env:
 *   grok.api_key = gsk_...
 *   grok.model = llama-3.3-70b-versatile
 *   pollinations.api_keys = key1,key2,key3
 *   openai.api_key = sk-... (opcional, pago)
 */
class GrokService
{
  private string $apiKey;
  private string $model;
  private string $endpoint = 'https://api.groq.com/openai/v1/chat/completions';
  private int $timeout = 30;
  private array $pollinationsKeys = [];
  private int $pollinationsKeyIndex = 0;

  const MODEL_PRIMARY = 'llama-3.3-70b-versatile';
  const MODEL_FAST = 'llama-3.1-8b-instant';

  public function __construct()
  {
    $this->apiKey = env('grok.api_key', '');
    $this->model = env('grok.model', self::MODEL_PRIMARY);

    $keys = env('pollinations.api_keys', '');
    if ($keys) {
      $this->pollinationsKeys = array_map('trim', explode(',', $keys));
    }
  }

  /**
   * Gerar texto via IA
   *
   * @param string $prompt    Instrução para a IA
   * @param string $system    Contexto do sistema (opcional)
   * @param int    $maxTokens Máximo de tokens na resposta
   * @param float  $temp      Temperatura (0.0 = determinístico, 1.0 = criativo)
   * @param bool   $json      Se true, solicita resposta em JSON
   * @return string|null      Texto gerado ou null em caso de falha total
   */
  public function generate(
    string $prompt,
    string $system = '',
    int $maxTokens = 500,
    float $temp = 0.7,
    bool $json = false
  ): ?string {
    // Tentar Groq API primeiro
    $result = $this->callGroq($prompt, $system, $maxTokens, $temp, $json);
    if ($result !== null) {
      return $result;
    }

    // Fallback: Pollinations claude
    log_message('warning', 'GrokService: Groq API falhou, tentando Pollinations claude');
    $result = $this->callPollinations($prompt, $system, $maxTokens, 'claude');
    if ($result !== null) {
      return $result;
    }

    // Fallback: Pollinations mistral
    log_message('warning', 'GrokService: Pollinations claude falhou, tentando Pollinations mistral');
    $result = $this->callPollinations($prompt, $system, $maxTokens, 'mistral');
    if ($result !== null) {
      return $result;
    }

    // Último recurso: OpenAI (pago)
    log_message('warning', 'GrokService: Pollinations falhou, tentando OpenAI (pago)');
    return $this->callOpenAI($prompt, $system, $maxTokens, $temp, $json);
  }

  /**
   * Gerar JSON estruturado via IA
   */
  public function generateJson(string $prompt, string $system = '', int $maxTokens = 800): ?array
  {
    $fullSystem = $system . "\n\nResposta OBRIGATORIAMENTE em JSON válido, sem texto adicional, sem markdown fences.";
    $text = $this->generate($prompt, $fullSystem, $maxTokens, 0.3, true);

    if ($text === null) {
      return null;
    }

    // Limpar markdown fences se existirem
    $text = preg_replace('/^```(?:json)?\s*/i', '', trim($text));
    $text = preg_replace('/\s*```$/i', '', $text);
    $text = trim($text);

    $decoded = json_decode($text, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
      log_message('error', 'GrokService: JSON inválido recebido: ' . substr($text, 0, 300));
      return null;
    }

    return $decoded;
  }

  /**
   * Chamar Groq API (primário)
   */
  private function callGroq(
    string $prompt,
    string $system,
    int $maxTokens,
    float $temp,
    bool $json
  ): ?string {
    if (empty($this->apiKey)) {
      log_message('warning', 'GrokService: grok.api_key não configurado');
      return null;
    }

    $messages = [];
    if ($system) {
      $messages[] = ['role' => 'system', 'content' => $system];
    }
    $messages[] = ['role' => 'user', 'content' => $prompt];

    $payload = [
      'model' => $this->model,
      'messages' => $messages,
      'max_tokens' => $maxTokens,
      'temperature' => $temp,
    ];

    if ($json) {
      $payload['response_format'] = ['type' => 'json_object'];
    }

    $response = $this->httpPost($this->endpoint, $payload, [
      'Authorization: Bearer ' . $this->apiKey,
      'Content-Type: application/json',
    ]);

    if (!$response) {
      return null;
    }

    $data = json_decode($response, true);
    $content = $data['choices'][0]['message']['content'] ?? null;

    if (empty($content)) {
      log_message('error', 'GrokService: Groq retornou conteúdo vazio. Response: ' . substr($response, 0, 500));
      return null;
    }

    return trim($content);
  }

  /**
   * Chamar Pollinations API (fallback)
   */
  private function callPollinations(
    string $prompt,
    string $system,
    int $maxTokens,
    string $model = 'mistral'
  ): ?string {
    $keysCount = count($this->pollinationsKeys);
    if ($keysCount === 0) {
      return null;
    }

    for ($attempt = 0; $attempt < min($keysCount, 3); $attempt++) {
      $keyIdx = ($this->pollinationsKeyIndex + $attempt) % $keysCount;
      $key = $this->pollinationsKeys[$keyIdx];

      $messages = [];
      if ($system) {
        $messages[] = ['role' => 'system', 'content' => $system];
      }
      $messages[] = ['role' => 'user', 'content' => $prompt];

      $payload = [
        'model' => $model,
        'messages' => $messages,
        'max_tokens' => $maxTokens,
      ];

      $response = $this->httpPost(
        'https://gen.pollinations.ai/v1/chat/completions',
        $payload,
        [
          'Authorization: Bearer ' . $key,
          'Content-Type: application/json',
        ]
      );

      if (!$response) {
        continue;
      }

      $data = json_decode($response, true);
      $content = $data['choices'][0]['message']['content'] ?? null;

      if (!empty($content)) {
        $this->pollinationsKeyIndex = ($keyIdx + 1) % $keysCount;
        return trim($content);
      }
    }

    return null;
  }

  /**
   * Chamar OpenAI API (último recurso — pago)
   */
  private function callOpenAI(
    string $prompt,
    string $system,
    int $maxTokens,
    float $temp,
    bool $json
  ): ?string {
    $openaiKey = env('openai.api_key', '');
    if (empty($openaiKey)) {
      return null;
    }

    $messages = [];
    if ($system) {
      $messages[] = ['role' => 'system', 'content' => $system];
    }
    $messages[] = ['role' => 'user', 'content' => $prompt];

    $payload = [
      'model' => 'gpt-4o-mini',
      'messages' => $messages,
      'max_tokens' => $maxTokens,
      'temperature' => $temp,
    ];

    if ($json) {
      $payload['response_format'] = ['type' => 'json_object'];
    }

    $response = $this->httpPost(
      'https://api.openai.com/v1/chat/completions',
      $payload,
      [
        'Authorization: Bearer ' . $openaiKey,
        'Content-Type: application/json',
      ]
    );

    if (!$response) {
      return null;
    }

    $data = json_decode($response, true);
    return trim($data['choices'][0]['message']['content'] ?? '') ?: null;
  }

  /**
   * HTTP POST com cURL
   */
  private function httpPost(string $url, array $payload, array $headers): ?string
  {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
      CURLOPT_POST => true,
      CURLOPT_POSTFIELDS => json_encode($payload),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT => $this->timeout,
      CURLOPT_HTTPHEADER => $headers,
      CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
      log_message('error', "GrokService: cURL error para {$url}: {$error}");
      return null;
    }

    if ($httpCode < 200 || $httpCode >= 300) {
      log_message('error', "GrokService: HTTP {$httpCode} para {$url}: " . substr($response, 0, 300));
      return null;
    }

    return $response;
  }

  /**
   * Testar conectividade com Groq API
   */
  public function testConnection(): array
  {
    $result = $this->callGroq(
      'Responda apenas: "OK"',
      'Você é um assistente de teste.',
      10,
      0.0,
      false
    );

    return [
      'success' => $result !== null,
      'response' => $result,
      'api_key_configured' => !empty($this->apiKey),
      'model' => $this->model,
    ];
  }
}
