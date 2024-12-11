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

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Неверный тип запроса']);
    exit;
}

// Проверка наличия chat_id
if (!isset($_POST['chat_id']) || empty($_POST['chat_id'])) {
    echo json_encode(['error' => 'Не указан ID чата']);
    exit;
}

$chat_id = intval($_POST['chat_id']);
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

    // Удаляем чат (все связанные сообщения удаляются благодаря ON DELETE CASCADE)
    $sql_delete = "DELETE FROM chats WHERE chat_id = ?";
    $stmt_delete = $pdo->prepare($sql_delete);
    $stmt_delete->execute([$chat_id]);

    echo json_encode(['success' => 'Чат успешно удалён']);
    exit;

} catch (PDOException $e) {
    error_log("Ошибка при удалении чата: " . $e->getMessage());
    echo json_encode(['error' => 'Ошибка при удалении чата']);
    exit;
}
?>
