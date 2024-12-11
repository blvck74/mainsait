<?php
// Подключаем конфигурацию и базу данных
include 'config.php';

// Начинаем сессию
session_start();

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    // Если пользователь не авторизован, перенаправляем на страницу входа
    header('Location: login.php');
    exit;
}

// Получаем информацию о пользователе из базы данных
$stmt = $pdo->prepare("SELECT email, nickname FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Если пользователя не найдено (что маловероятно), перенаправляем на страницу входа
if (!$user) {
    header('Location: login.php');
    exit;
}

// Здесь будет основной контент страницы
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Дашборд</title>

    <!-- Локальное подключение Bootstrap CSS -->
    <link href="C:/xampp/htdocs/new_project/assets/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <!-- Навигация с использованием Bootstrap -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="index.php">Web Tool</a>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item"><a class="nav-link" href="dashboard.php">Дашборд</a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Выйти</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Основной контент -->
    <main class="container mt-5">
        <h1 class="display-4">Добро пожаловать, <?php echo htmlspecialchars($user['nickname']); ?>!</h1>
        <p>Здесь будет ваш дашборд для работы с ИИ.</p>
        <!-- Здесь может быть функционал для чатов с ИИ и прочее -->
    </main>

    <!-- Подключение Bootstrap JS -->
    <script src="C:/xampp/htdocs/new_project/assets/jsbootstrap.bundle.min.js"></script>

</body>

</html>
