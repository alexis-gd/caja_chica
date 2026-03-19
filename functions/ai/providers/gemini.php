<?php
/**
 * providers/gemini.php — Adaptador para la API de Gemini (Google AI Studio)
 * Free tier: 1,000,000 tokens/min con gemini-1.5-flash
 * Compatible PHP 5.6+
 */

function callGemini($systemPrompt, $userMessage)
{
    $url = GEMINI_API_URL . '?key=' . GEMINI_API_KEY;

    $payload = array(
        'system_instruction' => array(
            'parts' => array(array('text' => $systemPrompt)),
        ),
        'contents' => array(
            array(
                'role'  => 'user',
                'parts' => array(array('text' => $userMessage)),
            ),
        ),
        'generationConfig' => array(
            'maxOutputTokens' => 2048,
            'temperature'     => 0.4,
            'thinkingConfig'  => array('thinkingBudget' => 0),
        ),
    );

    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => array('Content-Type: application/json'),
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => true,
    ));

    $result  = curl_exec($ch);
    $errCode = curl_errno($ch);
    curl_close($ch);

    if ($errCode || $result === false) {
        return array(
            'error'   => true,
            'message' => 'No se pudo conectar con Gemini. Intenta de nuevo.',
            'tokens'  => 0,
            'modelo'  => GEMINI_MODEL,
        );
    }

    $data = json_decode($result, true);

    if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        $api_error  = isset($data['error']['message']) ? $data['error']['message'] : 'Respuesta inesperada de Gemini.';
        $error_code = isset($data['error']['code'])    ? (string)$data['error']['code'] : '';
        $is_rate_limit = $error_code === '429'
            || strpos($api_error, 'quota')          !== false
            || strpos($api_error, 'RESOURCE_EXHAUSTED') !== false
            || strpos($api_error, 'rate limit')     !== false;
        return array(
            'error'      => true,
            'rate_limit' => $is_rate_limit,
            'message'    => $api_error,
            'tokens'     => 0,
            'modelo'     => GEMINI_MODEL,
        );
    }

    $tokens = isset($data['usageMetadata']['totalTokenCount'])
        ? (int)$data['usageMetadata']['totalTokenCount']
        : 0;

    return array(
        'error'   => false,
        'message' => $data['candidates'][0]['content']['parts'][0]['text'],
        'tokens'  => $tokens,
        'modelo'  => GEMINI_MODEL,
    );
}
