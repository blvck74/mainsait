<?php
// Подключаем файл конфигурации для подключения к базе данных
require_once 'config.php';

session_start();  // Начинаем сессию

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Проверка на пустые поля
    if (empty($email) || empty($password)) {
        echo "Пожалуйста, заполните все поля.";
        exit;
    }

    // Ищем пользователя в базе данных
    $stmt = $pdo->prepare("SELECT id, password, nickname FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Если пользователь найден и пароль верный, создаем сессию
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $email;
        $_SESSION['nickname'] = $user['nickname'];  // Добавляем никнейм в сессию
        header("Location: dashboard.php");  // Перенаправление на защищенную страницу
        exit;
    } else {
        echo "Неверный email или пароль.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>

    <!-- Локальное подключение Bootstrap CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="public/styles/style.css" rel="stylesheet"> <!-- Добавим кастомные стили -->
</head>

<body class="bg-light">

    <!-- Навигация с использованием Bootstrap -->
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="index.php">Web Tool</a>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item"><a class="nav-link" href="index.php">Главная</a></li>
                        <li class="nav-item"><a class="nav-link" href="register.php">Регистрация</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Основной контент -->
    <main class="container mt-5">
        <section class="login">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <h1 class="display-4 text-center mb-4">Вход</h1>
                    <form action="login.php" method="POST" class="bg-white p-4 shadow rounded">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="email" class="form-label">Электронная почта</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Пароль</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Войти</button>
                    </form>
                    <p class="text-center mt-3">Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
                </div>
            </div>
        </section>
    </main>

    <!-- Подключение Bootstrap JS -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>

</body>

</html>