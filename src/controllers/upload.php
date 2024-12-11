<?php

// upload.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $upload_dir = 'uploads/';

    // Перемещаем загруженный файл в папку
    $target_file = $upload_dir . basename($file['name']);
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        echo "Файл загружен успешно!";
    } else {
        echo "Ошибка при загрузке файла.";
    }
}
