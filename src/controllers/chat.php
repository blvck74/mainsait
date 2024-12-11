<?php

// chat.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = $_POST['message'];
    $api_key = 'YOUR_OPENAI_API_KEY';
    $url = 'https://api.openai.com/v1/completions';

    $data = [
        'model' => 'gpt-3.5-turbo',
        'messages' => [['role' => 'user', 'content' => $message]],
        'max_tokens' => 100
    ];

    $options = [
        'http' => [
            'header' => "Content-Type: application/json\r\n" .
                       "Authorization: Bearer $api_key\r\n",
            'method' => 'POST',
            'content' => json_encode($data)
        ]
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    $response_data = json_decode($response, true);

    echo $response_data['choices'][0]['message']['content'];
}
