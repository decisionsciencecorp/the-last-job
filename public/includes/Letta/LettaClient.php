<?php
declare(strict_types=1);

namespace LastJob\Letta;

/**
 * Minimal Letta HTTP client (API-only — no DB access).
 */
final class LettaClient
{
    public function __construct(
        private LettaConfig $config,
    ) {
    }

    /** @return array<string,mixed> */
    public function health(): array
    {
        return $this->request('GET', '/v1/health/');
    }

    /**
     * Ask the NPC agent for intent + dialogue. Returns parsed JSON when the
     * model complies; otherwise wraps raw assistant text.
     *
     * @param array<string,mixed> $beatContext Player-safe beat facts only.
     * @return array{intent:string,dialogue:string,raw:string}
     */
    public function npcIntent(array $beatContext): array
    {
        $payload = [
            'messages' => [[
                'role' => 'user',
                'content' => json_encode($beatContext, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            ]],
            'streaming' => false,
        ];

        $response = $this->request('POST', '/v1/agents/' . rawurlencode($this->config->agentId) . '/messages', $payload);

        if (!empty($response['error'])) {
            $msg = is_array($response['error'])
                ? (string) ($response['error']['message'] ?? json_encode($response['error']))
                : (string) $response['error'];
            throw new \RuntimeException('Letta error: ' . $msg);
        }

        $text = $this->extractAssistantText($response);
        $parsed = $this->parseIntentJson($text);

        return [
            'intent' => (string) ($parsed['intent'] ?? 'react'),
            'dialogue' => (string) ($parsed['dialogue'] ?? $text),
            'raw' => $text,
        ];
    }

    /** @param array<string,mixed> $response */
    private function extractAssistantText(array $response): string
    {
        $chunks = [];
        foreach ($response['messages'] ?? [] as $msg) {
            if (!is_array($msg)) {
                continue;
            }
            if (($msg['message_type'] ?? '') === 'assistant_message' && isset($msg['content'])) {
                $chunks[] = (string) $msg['content'];
            }
        }
        if ($chunks !== []) {
            return trim(end($chunks));
        }

        return trim(json_encode($response, JSON_UNESCAPED_SLASHES) ?: '');
    }

    /** @return array<string,mixed> */
    private function parseIntentJson(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            return [];
        }
        if ($text[0] === '{') {
            $decoded = json_decode($text, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        if (preg_match('/\{[\s\S]*\}/', $text, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return ['dialogue' => $text];
    }

    /**
     * @param array<string,mixed>|null $jsonBody
     * @return array<string,mixed>
     */
    private function request(string $method, string $path, ?array $jsonBody = null): array
    {
        $url = $this->config->baseUrl . $path;
        $headers = [
            'Authorization: Bearer ' . $this->config->apiKey,
            'Accept: application/json',
        ];

        $opts = [
            'method' => $method,
            'header' => implode("\r\n", $headers) . "\r\n",
            'timeout' => $this->config->timeoutSeconds,
            'ignore_errors' => true,
        ];

        if ($jsonBody !== null) {
            $opts['header'] .= "Content-Type: application/json\r\n";
            $opts['content'] = json_encode($jsonBody, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        }

        $ctx = stream_context_create(['http' => $opts]);
        $raw = @file_get_contents($url, false, $ctx);
        if ($raw === false) {
            $err = error_get_last()['message'] ?? 'unknown HTTP error';
            throw new \RuntimeException("Letta HTTP failed ({$method} {$path}): {$err}");
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            throw new \RuntimeException("Letta returned non-JSON from {$path}");
        }

        return $decoded;
    }
}
