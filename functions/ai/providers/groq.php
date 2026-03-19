<?php
/**
 * providers/groq.php — Adaptador para la API de Groq (free tier)
 * Compatible PHP 5.6+
 */

function callGroq($systemPrompt, $userMessage)
{
    $payload = array(
        'model'       => GROQ_MODEL,
        'messages'    => array(
            array('role' => 'system', 'content' => $systemPrompt),
            array('role' => 'user',   'content' => $userMessage),
        ),
        'max_tokens'  => 400,
        'temperature' => 0.4,
    );

    $ch = curl_init(GROQ_API_URL);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => array(
            'Authorization: Bearer ' . GROQ_API_KEY,
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
            'message' => 'No se pudo conectar con el servicio de IA. Intenta de nuevo.',
            'tokens'  => 0,
            'modelo'  => GROQ_MODEL,
        );
    }

    $data = json_decode($result, true);

    if (!isset($data['choices'][0]['message']['content'])) {
        $api_error  = isset($data['error']['message']) ? $data['error']['message'] : 'Respuesta inesperada del servicio de IA.';
        $error_code = isset($data['error']['code'])    ? $data['error']['code']    : '';
        $is_rate_limit = strpos($error_code, 'rate_limit') !== false
            || strpos($api_error, 'Rate limit')     !== false
            || strpos($api_error, 'Request too large') !== false;
        return array(
            'error'      => true,
            'rate_limit' => $is_rate_limit,
            'message'    => $api_error,
            'tokens'     => 0,
            'modelo'     => GROQ_MODEL,
        );
    }

    return array(
        'error'   => false,
        'message' => $data['choices'][0]['message']['content'],
        'tokens'  => (int)(isset($data['usage']['total_tokens']) ? $data['usage']['total_tokens'] : 0),
        'modelo'  => GROQ_MODEL,
    );
}
