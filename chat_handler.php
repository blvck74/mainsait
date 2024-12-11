<?php
// Включаем отображение ошибок (только для отладки)
// Убедитесь, что эти настройки отключены на боевом сервере
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Подключаем конфигурацию и подключаемся к базе данных
include 'config.php';

// Начинаем сессию
session_start();

// Проверка аутентификации пользователя
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Пользователь не авторизован']);
    exit;
}

// Обработка POST-запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверяем наличие необходимых параметров
    if (isset($_POST['message'], $_POST['chat_id']) && !empty(trim($_POST['message']))) {
        $user_message = trim($_POST['message']);
        $chat_id = intval($_POST['chat_id']);
        $user_id = $_SESSION['user_id'];

        try {
            // Проверяем, принадлежит ли чат текущему пользователю
            $sql = "SELECT chat_id FROM chats WHERE chat_id = ? AND user_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$chat_id, $user_id]);
            $chat = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$chat) {
                echo json_encode(['error' => 'Чат не найден или вы не имеете к нему доступа']);
                exit;
            }

            // Вставляем сообщение пользователя в таблицу `messages`
            $sql_insert = "INSERT INTO messages (chat_id, user_message, ai_response) VALUES (?, ?, ?)";
            $stmt_insert = $pdo->prepare($sql_insert);
            $stmt_insert->execute([$chat_id, $user_message, '']);
            $message_id = $pdo->lastInsertId();

            // Настройки AI API
            $ai_api_key = 'sk-gfEzUwwBc2ybxPeg56Ac07Ac371c495eB7E52f09883e32F3';  // Замените на ваш действительный API-ключ
            $ai_url = 'https://neuroapi.host/v1/chat/completions';  // Замените на правильный endpoint AI API

            // Формируем данные для отправки к AI API
            $data = [
                'model' => 'gpt-3.5-turbo',  // Замените на нужную модель
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $user_message
                    ]
                ]
            ];

            // Заголовки для запроса к AI API
            $headers = [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $ai_api_key
            ];

            // Инициализация cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $ai_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            // Выполняем запрос
            $response = curl_exec($ch);

            // Проверяем на ошибки cURL
            if (curl_errno($ch)) {
                $ai_response = 'Ошибка подключения к ИИ: ' . curl_error($ch);
                // Обновляем сообщение с ошибкой
                $sql_update = "UPDATE messages SET ai_response = ? WHERE message_id = ?";
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->execute([$ai_response, $message_id]);

                echo json_encode(['error' => $ai_response]);
                curl_close($ch);
                exit;
            }

            // Закрываем cURL-сессию
            curl_close($ch);

            // Декодируем ответ от AI API
            $response_data = json_decode($response, true);

            // Логируем ответ для отладки (убедитесь, что PHP имеет права на запись в этот файл)
            file_put_contents('ai_api_log.txt', date('Y-m-d H:i:s') . " - Response: " . print_r($response_data, true) . PHP_EOL, FILE_APPEND);

            // Обрабатываем ответ от AI
            if (isset($response_data['choices'][0]['message']['content'])) {
                $ai_response = $response_data['choices'][0]['message']['content'];
            } elseif (isset($response_data['error'])) {
                if (is_array($response_data['error'])) {
                    if (isset($response_data['error']['message'])) {
                        $ai_response = 'Ошибка от ИИ: ' . $response_data['error']['message'];
                    } else {
                        $ai_response = 'Ошибка от ИИ: ' . implode(' ', $response_data['error']);
                    }
                } else {
                    $ai_response = 'Ошибка от ИИ: ' . $response_data['error'];
                }
            } else {
                $ai_response = "Ошибка: Ответ от ИИ не получен. Ответ API: " . print_r($response_data, true);
            }

            // Если ai_response является массивом, объединяем его в строку
            if (is_array($ai_response)) {
                $ai_response = implode(' ', $ai_response);
            }

            // Обновляем AI ответ в таблице `messages`
            $sql_update = "UPDATE messages SET ai_response = ? WHERE message_id = ?";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([$ai_response, $message_id]);

            // Возвращаем ответ ИИ
            echo json_encode(['response' => trim($ai_response)]);
            exit;

        } catch (PDOException $e) {
            echo json_encode(['error' => 'Ошибка при работе с базой данных: ' . $e->getMessage()]);
            exit;
        }
    } else {
        // Если не POST-запрос
        echo json_encode(['error' => 'Неверный тип запроса']);
        exit;
    }
}
?>
