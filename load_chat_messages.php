<?php
// Включаем отображение ошибок (только для отладки)
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

// Проверяем наличие параметра chat_id
if (!isset($_GET['chat_id'])) {
    echo json_encode(['error' => 'Не указан ID чата']);
    exit;
}

$chat_id = intval($_GET['chat_id']);
$user_id = $_SESSION['user_id'];

try {
    // Проверяем, принадлежит ли чат пользователю
    $sql = "SELECT chat_id FROM chats WHERE chat_id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$chat_id, $user_id]);
    $chat = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$chat) {
        echo json_encode(['error' => 'Чат не найден или вы не имеете к нему доступа']);
        exit;
    }

    // Получаем сообщения для чата
    $sql_messages = "SELECT user_message, ai_response, date_sent FROM messages WHERE chat_id = ? ORDER BY date_sent ASC";
    $stmt_messages = $pdo->prepare($sql_messages);
    $stmt_messages->execute([$chat_id]);
    $messages = $stmt_messages->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['messages' => $messages]);
    exit;

} catch (PDOException $e) {
    error_log("Ошибка при загрузке сообщений чата: " . $e->getMessage());
    echo json_encode(['error' => 'Ошибка при загрузке сообщений чата']);
    exit;
}
?>
