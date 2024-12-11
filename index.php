<?php
// Подключаем конфигурацию и базу данных
include 'config.php';

// Начинаем сессию
session_start();

// Проверка, если пользователь авторизован, перенаправляем на dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FixIt - Инструмент для разработчиков</title>
    <link rel="stylesheet" href="assets/css/style.css"> <!-- Путь к стилям -->
    <link rel="icon" href="public/assets/favicon.ico"> <!-- Иконка сайта -->

    <!-- Подключение стилей для Vanta.js -->
    <link href="public/css/vanta.min.css" rel="stylesheet">

    <!-- Подключение локального Bootstrap -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet"> <!-- Локальный Bootstrap CSS -->
</head>

<body>

    <!-- Добавляем фон для Vanta.js -->
    <div id="vanta-background" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;"></div>

    
    <!-- Основная секция -->
    <main>
        <!-- Панель с описанием проекта -->
        <section class="intro">
            <div class="container text-center">
                <div class="description-panel">
                    <h1 class="text-white">Инструмент для улучшения качества кода с использованием ИИ</h1>
                    <p class="text-white">Наш инструмент помогает разработчикам улучшать качество кода, исправлять ошибки и повышать производительность с помощью искусственного интеллекта.</p>
                    <p class="text-white">После регистрации вы сможете использовать чат с ИИ для анализа вашего кода, загрузки файлов и получения рекомендаций по улучшению качества.</p>
                    <a href="register.php" class="btn btn-primary">Начать использовать</a>
                    <a href="login.php" class="btn btn-secondary">Войти</a>
                </div>
            </div>
        </section>

        <!-- Панель с особенностями -->
        <section class="features">
            <div class="container">
                <h2 class="text-center text-white">Особенности</h2>
                <div class="row">
                    <div class="col-md-4">
                        <div class="feature">
                            <h3 style="text-align:center" class="text-white">Чат с ИИ</h3>
                            <p class="text-white">Общайтесь с искусственным интеллектом для анализа и улучшения вашего кода.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature">
                            <h3 style="text-align:center" class="text-white">История чатов</h3>
                            <p class="text-white">Просматривайте историю ваших чатов с ИИ и сохраняйте полезные ответы.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature">
                            <h3 style="text-align:center" class="text-white">Загрузка файлов</h3>
                            <p class="text-white">Загружайте файлы для анализа и получения рекомендаций по исправлениям.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Подвал -->
    <footer>
        <div class="container text-center text-white py-3">
            <p>&copy; 2024 FixIt. Все права защищены.</p>
        </div>
    </footer>

    <!-- Подключение локальных скриптов для Vanta.js и Three.js -->
    <script src="assets/js/three.r134.min.js"></script> <!-- Локальный путь к Three.js -->
    <script src="assets/js/vanta.net.min.js"></script> <!-- Локальный путь к Vanta.js -->

    <script>
        // Инициализация Vanta.js на фон
        VANTA.NET({
            el: "#vanta-background", // Идентификатор элемента для фона
            mouseControls: true,
            touchControls: true,
            gyroControls: false,
            minHeight: 200.00,
            minWidth: 200.00,
            scale: 1.00,
            scaleMobile: 1.00,
            points: 12.00,
            maxDistance: 28.00
        });
    </script>

    <!-- Подключение локального Bootstrap JS -->
    <script src="assets/js/bootstrap.bundle.min.js"></script> <!-- Локальный путь к Bootstrap JS -->

    <script src="public/js/main.js"></script> <!-- Если есть JavaScript -->
</body>

</html>
