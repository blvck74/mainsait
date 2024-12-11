<?php
// Включаем отображение ошибок (только для отладки)
// Убедитесь, что эти настройки отключены на боевом сервере
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Подключаем конфигурацию и базу данных
include 'config.php';  // Убедитесь, что файл `config.php` содержит правильные настройки подключения

// Начинаем сессию
session_start();

// Проверка, если пользователь авторизован, перенаправляем на dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Функция для генерации CSRF-токена
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

// Функция для проверки CSRF-токена
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Генерируем CSRF-токен
generateCsrfToken();

// Обработка формы регистрации
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Проверка CSRF-токена
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $error_message = "Неверный CSRF-токен.";
    } else {
        // Получаем и очищаем данные из формы
        $nickname = trim($_POST['nickname']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);

        // Инициализируем массив для ошибок
        $errors = [];

        // Валидация никнейма
        if (empty($nickname)) {
            $errors[] = "Имя пользователя обязательно.";
        } elseif (strlen($nickname) < 3 || strlen($nickname) > 20) {
            $errors[] = "Имя пользователя должно содержать от 3 до 20 символов.";
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $nickname)) {
            $errors[] = "Имя пользователя может содержать только буквы, цифры и символы подчеркивания.";
        }

        // Валидация электронной почты
        if (empty($email)) {
            $errors[] = "Электронная почта обязательна.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Некорректный формат электронной почты.";
        }

        // Валидация пароля
        if (empty($password)) {
            $errors[] = "Пароль обязателен.";
        } elseif (strlen($password) < 6) {
            $errors[] = "Пароль должен содержать не менее 6 символов.";
        }

        // Проверка совпадения паролей
        if ($password !== $confirm_password) {
            $errors[] = "Пароли не совпадают!";
        }

        // Если нет ошибок, продолжаем регистрацию
        if (empty($errors)) {
            try {
                // Проверка уникальности никнейма
                $stmt = $pdo->prepare("SELECT id FROM users WHERE nickname = ?");
                $stmt->execute([$nickname]);
                if ($stmt->fetch()) {
                    $errors[] = "Имя пользователя уже занято.";
                }

                // Проверка уникальности электронной почты
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $errors[] = "Электронная почта уже зарегистрирована.";
                }

                // Если после проверок нет ошибок, вставляем пользователя в базу
                if (empty($errors)) {
                    // Хешируем пароль перед сохранением
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Вставка данных в базу
                    $stmt = $pdo->prepare("INSERT INTO users (nickname, email, password) VALUES (?, ?, ?)");
                    $stmt->execute([$nickname, $email, $hashed_password]);

                    // Получаем ID нового пользователя
                    $user_id = $pdo->lastInsertId();

                    // Устанавливаем сессионные переменные
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['nickname'] = $nickname;

                    // Перенаправляем на страницу dashboard
                    header('Location: dashboard.php');
                    exit;
                }
            } catch (PDOException $e) {
                // Логирование ошибки на сервере (не показываем пользователю)
                error_log("Ошибка при регистрации пользователя: " . $e->getMessage());

                $errors[] = "Произошла ошибка при регистрации. Пожалуйста, попробуйте позже.";
            }
        }

        // Если есть ошибки, сохраняем их для отображения
        if (!empty($errors)) {
            $error_message = implode("<br>", $errors);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - ChatAI</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="public/assets/favicon.ico">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div id="vanta-background" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;"></div>


    <!-- Форма регистрации -->
    <main>
        <section class="form-section">
            <div class="container">
                <h1 class="text-center text-white">Регистрация</h1>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger text-center"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <form style="text-align:center" action="register.php" method="POST">
                    <!-- Добавляем CSRF-токен -->
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                    <div style="text-align:center" class="form-group">
                        <label for="nickname" class="text-white">Имя пользователя</label>
                        <input type="text" class="form-control" id="nickname" name="nickname" value="<?php echo isset($nickname) ? htmlspecialchars($nickname) : ''; ?>" required>
                    </div>
                    <div style="text-align:center" class="form-group">
                        <label for="email" class="text-white">Электронная почта</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
                    </div>
                    <div style="text-align:center" class="form-group">
                        <label for="password" class="text-white">Пароль</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div style="text-align:center" class="form-group">
                        <label for="confirm_password" class="text-white">Подтвердите пароль</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-register">Зарегистрироваться</button>
                </form>

                <p class="text-white text-center mt-3">Уже есть аккаунт? <a href="login.php" class="text-white">Войти</a></p>
            </div>
        </section>
    </main>

    <!-- Подвал -->
    <footer>
        <div class="container text-center text-white py-3">
            <p>&copy; 2024 ChatAI. Все права защищены.</p>
        </div>
    </footer>

    <script src="assets/js/three.r134.min.js"></script>
    <script src="assets/js/vanta.net.min.js"></script>
    <script>
        VANTA.NET({
            el: "#vanta-background",
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
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>
