<?php
// Начинаем сессию
session_start();

// Уничтожаем все сессионные данные
session_unset();
session_destroy();

// Перенаправляем на страницу входа
header('Location: login.php');
exit;
?>
