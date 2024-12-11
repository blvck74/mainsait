<?php

// Убедитесь, что здесь правильно указаны ваши параметры для подключения
$host = 'localhost';  // Адрес базы данных
$dbname = 'fixit';     // Имя вашей базы данных
$username = 'admin';   // Имя пользователя
$password = 'KoStYA8BesTBER';  // Пароль

try {
    // Создаем подключение к базе данных с использованием PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);

    // Устанавливаем режим обработки ошибок для PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Если соединение успешно, можно удалить последующую строку
    // echo "Подключение к базе данных успешно!";
} catch (PDOException $e) {
    // Если не удалось подключиться, выводим сообщение об ошибке и завершаем выполнение
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>
