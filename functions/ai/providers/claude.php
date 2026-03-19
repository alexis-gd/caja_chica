<?php
/**
 * providers/claude.php — Adaptador para la API de Anthropic (Claude)
 * Compatible PHP 5.6+
 * Para activar: definir CLAUDE_API_KEY, CLAUDE_MODEL, CLAUDE_API_URL en config.php
 *               y cambiar AI_PROVIDER a 'claude'
 */

function callClaude($systemPrompt, $userMessage)
{
    $payload = array(
        'model'      => CLAUDE_MODEL,
        'max_tokens' => 1024,
        'system'     => $systemPrompt,
        'messages'   => array(
            array('role' => 'user', 'content' => $userMessage),
        ),
    );

    $ch = curl_init(CLAUDE_API_URL);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => array(
            'x-api-key: '        . CLAUDE_API_KEY,
            'anthropic-version: 2023-06-01',
            'Content-Type: application/json',
        ),
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => true,
    ));

    $result  = curl_exec($ch);
    $errCode = curl_errno($ch);
    curl_close($ch);

    if ($errCode || $result === false) {
        return array(
            'error'   => true,
            'message' => 'No se pudo conectar con Claude. Intenta de nuevo.',
            'tokens'  => 0,
            'modelo'  => CLAUDE_MODEL,
        );
    }

    $data = json_decode($result, true);

    if (!isset($data['content'][0]['text'])) {
        $api_error  = isset($data['error']['message']) ? $data['error']['message'] : 'Respuesta inesperada de Claude.';
        $error_type = isset($data['error']['type'])    ? $data['error']['type']    : '';
        $is_rate_limit = $error_type === 'rate_limit_error'
            || strpos($api_error, 'rate limit')  !== false
            || strpos($api_error, 'overloaded')  !== false
            || strpos($api_error, 'quota')       !== false;
        return array(
            'error'      => true,
            'rate_limit' => $is_rate_limit,
            'message'    => $api_error,
            'tokens'     => 0,
            'modelo'     => CLAUDE_MODEL,
        );
    }

    $tokens = isset($data['usage']['input_tokens']) && isset($data['usage']['output_tokens'])
        ? (int)$data['usage']['input_tokens'] + (int)$data['usage']['output_tokens']
        : 0;

    return array(
        'error'   => false,
        'message' => $data['content'][0]['text'],
        'tokens'  => $tokens,
        'modelo'  => CLAUDE_MODEL,
    );
}
