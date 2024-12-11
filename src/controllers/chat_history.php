<?php

// chat_history.php
$stmt = $pdo->prepare("SELECT * FROM chat_history WHERE user_id = ?");
$stmt->execute([$user_id]); // Замените $user_id на идентификатор текущего пользователя

$chats = $stmt->fetchAll();

foreach ($chats as $chat) {
    echo "<p>Вы: " . htmlspecialchars($chat['message']) . "</p>";
    echo "<p>ИИ: " . htmlspecialchars($chat['response']) . "</p>";
}
