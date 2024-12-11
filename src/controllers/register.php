<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <!-- Локальное подключение Bootstrap CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet"> <!-- Добавим кастомные стили -->
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
                        <li class="nav-item"><a class="nav-link" href="login.php">Войти</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Основной контент -->
    <main class="container mt-5">
        <section class="register">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <h1 class="display-4 text-center mb-4">Регистрация</h1>
                    <form action="register.php" method="POST" class="bg-white p-4 shadow rounded">
                        <div class="mb-3">
                            <label for="email" class="form-label">Электронная почта</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Пароль</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="nickname" class="form-label">Никнейм</label>
                            <input type="text" class="form-control" id="nickname" name="nickname" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
                    </form>
                    <p class="text-center mt-3">Уже есть аккаунт? <a href="login.php">Войти</a></p>
                </div>
            </div>
        </section>
    </main>

    <!-- Подключение Bootstrap JS -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>

</body>

</html>