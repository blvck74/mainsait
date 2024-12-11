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
    echo '<li class="list-group-item">Пользователь не авторизован</li>';
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Получаем список чатов пользователя
    $sql = "SELECT chat_id, chat_name FROM chats WHERE user_id = ? ORDER BY date_created DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($chats) {
        foreach ($chats as $chat) {
            echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
            echo '<a href="#" class="chat-link" data-chat-id="' . htmlspecialchars($chat['chat_id'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($chat['chat_name'], ENT_QUOTES, 'UTF-8') . '</a>';
            echo '<button class="btn btn-sm btn-danger delete-chat" data-chat-id="' . htmlspecialchars($chat['chat_id'], ENT_QUOTES, 'UTF-8') . '" title="Удалить чат"><i class="fas fa-trash-alt"></i></button>';
            echo '</li>';
        }
    } else {
        echo '<li class="list-group-item">Нет доступных чатов</li>';
    }

} catch (PDOException $e) {
    error_log("Ошибка при загрузке истории чатов: " . $e->getMessage());
    echo '<li class="list-group-item">Ошибка при загрузке чатов</li>';
}
?>
