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
    // Проверяем наличие имени чата
    if (isset($_POST['chat_name']) && !empty(trim($_POST['chat_name']))) {
        $chat_name = trim($_POST['chat_name']);
        $user_id = $_SESSION['user_id'];

        try {
            // Вставляем новый чат в таблицу `chats`
            $sql = "INSERT INTO chats (user_id, chat_name) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id, $chat_name]);

            // Получаем ID вставленного чата
            $chat_id = $pdo->lastInsertId();

            // Возвращаем данные о новом чате
            echo json_encode([
                'chat_id' => $chat_id,
                'chat_name' => htmlspecialchars($chat_name, ENT_QUOTES, 'UTF-8')
            ]);
            exit;

        } catch (PDOException $e) {
            // Логирование ошибки на сервере
            error_log("Ошибка при создании чата: " . $e->getMessage());
            echo json_encode(['error' => 'Произошла ошибка при создании чата. Попробуйте позже.']);
            exit;
        }
    } else {
        echo json_encode(['error' => 'Имя чата не предоставлено']);
        exit;
    }
} else {
    echo json_encode(['error' => 'Неверный тип запроса']);
    exit;
}
?>
