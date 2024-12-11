<?php
// Отладка (отключите на боевом сервере)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ChatAI</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/github.min.css"> <!-- Тема для highlight.js -->
    <link rel="stylesheet" href="assets/css/custom.css">
    <link rel="icon" href="assets/favicon.ico">
</head>

<body>
    <header class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">ChatAI</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Переключить навигацию">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="btn btn-danger" href="logout.php">Выход</a>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <div class="container my-4">
        <div class="row g-3">
            <div class="col-lg-8 col-md-7 col-sm-12">
                <div class="chat-left p-3">
                    <div class="chat-box mb-3" id="chatBox">
                        <div class="message user-message mb-2 fade-in">Привет!</div>
                        <div class="message ai-message mb-2 fade-in">Здравствуйте! Как я могу помочь?</div>
                        <div class="chat-typing-indicator hidden" id="typingIndicator">
                            <div class="spinner-border text-secondary me-2" role="status">
                                <span class="visually-hidden">Идет ввод...</span>
                            </div>
                            ИИ думает...
                        </div>
                    </div>
                    <div class="input-area">
                        <textarea id="userMessage" class="form-control mb-2" placeholder="Напишите сообщение..." rows="3"></textarea>
                        <div class="d-flex justify-content-between">
                            <div class="d-flex gap-2">
                                <button id="attachFile" class="btn btn-outline-info" title="Добавить вложение">
                                    <i class="fas fa-paperclip"></i>
                                </button>
                                <button id="searchWeb" class="btn btn-outline-primary" title="Поиск в сети">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <button id="sendMessage" class="btn btn-gradient-green">Отправить</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-5 col-sm-12">
                <div class="chat-history p-3">
                    <div class="d-flex justify-content-between mb-3">
                        <button class="btn btn-outline-primary" id="newDialog" title="Создать новый диалог">
                            <i class="fas fa-comment-dots"></i> Новый диалог
                        </button>
                        <button class="btn btn-outline-secondary" id="toggleHistory" title="Скрыть панель">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div id="historyPanel" class="history-panel">
                        <ul id="chatHistoryList" class="list-group">
                            <?php
                            try {
                                $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
                                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                                $sql = "SELECT chat_id, chat_name, date_created FROM chats WHERE user_id = ? ORDER BY date_created DESC";
                                $stmt = $pdo->prepare($sql);
                                $stmt->execute([$_SESSION['user_id']]);
                                $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

                                foreach ($chats as $chat) {
                                    echo '<li class="list-group-item d-flex justify-content-between align-items-center">
                                            <a href="#" class="chat-link" data-chat-id="' . $chat['chat_id'] . '">' . htmlspecialchars($chat['chat_name'], ENT_QUOTES, 'UTF-8') . '</a>
                                            <button class="btn btn-sm btn-danger delete-chat" data-chat-id="' . $chat['chat_id'] . '" title="Удалить чат">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                          </li>';
                                }

                            } catch (PDOException $e) {
                                echo '<li class="list-group-item text-danger">Ошибка при загрузке чатов</li>';
                            }
                            ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/highlight.min.js"></script>

    <script>
        $(document).ready(function() {
            console.log("Документ готов");

            loadChatHistory();

            $('#toggleHistory').click(function() {
                $('#historyPanel').toggleClass('d-none');
                if ($('#historyPanel').hasClass('d-none')) {
                    $('.chat-history').animate({ 'margin-right': '-100%' }, 300);
                } else {
                    $('.chat-history').animate({ 'margin-right': '0' }, 300);
                }
            });

            $('#newDialog').click(function() {
                console.log("Кнопка 'Новый чат' нажата");
                createNewChat();
            });

            $(document).on('click', '.chat-link', function(e) {
                e.preventDefault();
                let chat_id = $(this).data('chat-id');
                let chat_name = $(this).text();
                $('.chat-link').removeClass('active-chat');
                $(this).addClass('active-chat');
                openChat(chat_id, chat_name);
            });

            $(document).on('click', '.delete-chat', function(e) {
                e.preventDefault();
                let chat_id = $(this).data('chat-id');
                let chatItem = $(this).closest('li');

                if (confirm('Вы уверены, что хотите удалить этот чат?')) {
                    $.ajax({
                        url: 'delete_chat.php',
                        method: 'POST',
                        data: { chat_id: chat_id },
                        success: function(response) {
                            let data;
                            try {
                                data = JSON.parse(response);
                            } catch (e) {
                                console.log("Ошибка парсинга JSON:", e);
                                alert('Ошибка обработки ответа от сервера');
                                return;
                            }
                            if (data.error) {
                                alert(data.error);
                                return;
                            }
                            chatItem.fadeOut(300, function() {
                                $(this).remove();
                            });

                            if ($('.chat-link.active-chat').data('chat-id') == chat_id) {
                                $('#chatBox').empty();
                            }

                            alert('Чат успешно удалён');
                        },
                        error: function() {
                            alert('Ошибка при удалении чата');
                        }
                    });
                }
            });

            $('#sendMessage').click(function() {
                console.log("Кнопка 'Отправить' нажата");
                let message = $('#userMessage').val();
                let current_chat_id = getCurrentChatId();

                if (message.trim() === '') {
                    alert('Сообщение не может быть пустым.');
                    return;
                }

                if (!current_chat_id) {
                    // Если нет активного чата, создаем новый перед отправкой сообщения
                    createNewChat(function(chat_id, chat_name) {
                        sendMessageToChat(chat_id, message);
                    });
                } else {
                    sendMessageToChat(current_chat_id, message);
                }
            });

            $('#userMessage').keypress(function(e) {
                if (e.which == 13 && !e.shiftKey) {
                    e.preventDefault();
                    $('#sendMessage').click();
                }
            });

            function loadChatHistory() {
                $.ajax({
                    url: 'load_chat_history.php',
                    method: 'GET',
                    success: function(response) {
                        $('#chatHistoryList').html(response);
                    },
                    error: function() {
                        alert('Ошибка при загрузке истории чатов');
                    }
                });
            }

            function getCurrentChatId() {
                let activeChat = $('.chat-link.active-chat');
                if (activeChat.length > 0) {
                    return activeChat.data('chat-id');
                }
                return null;
            }

            function htmlspecialchars(str) {
                return $('<div>').text(str).html();
            }

            function openChat(chat_id, chat_name) {
                console.log("Открытие чата ID:", chat_id, "Имя чата:", chat_name);
                $.ajax({
                    url: 'load_chat_messages.php',
                    method: 'GET',
                    data: { chat_id: chat_id },
                    success: function(response) {
                        let data;
                        try {
                            data = JSON.parse(response);
                        } catch (e) {
                            console.log("Ошибка парсинга JSON:", e);
                            alert('Ошибка обработки ответа от сервера');
                            return;
                        }
                        if (data.error) {
                            alert(data.error);
                            return;
                        }
                        $('#chatBox').empty();
                        data.messages.forEach(function(message) {
                            $('#chatBox').append('<div class="message user-message mb-2 fade-in">' + htmlspecialchars(message.user_message) + '</div>');
                            // Обрабатываем AI ответ перед добавлением
                            let aiResponseFormatted = formatAIResponse(message.ai_response);
                            $('#chatBox').append('<div class="message ai-message mb-2 fade-in">' + aiResponseFormatted + '</div>');
                        });
                        $('#chatBox').scrollTop($('#chatBox')[0].scrollHeight);
                        highlightAllCodeBlocks();
                    },
                    error: function() {
                        alert('Ошибка при загрузке сообщений чата');
                    }
                });
            }

            function createNewChat(callback) {
                $.ajax({
                    url: 'create_chat.php',
                    method: 'POST',
                    data: { chat_name: 'Новый диалог' },
                    success: function(response) {
                        let data;
                        try {
                            data = JSON.parse(response);
                        } catch (e) {
                            console.log("Ошибка парсинга JSON:", e);
                            alert('Ошибка обработки ответа от сервера');
                            return;
                        }
                        if (data.error) {
                            alert(data.error);
                            return;
                        }

                        $('.chat-link').removeClass('active-chat');
                        $('#chatHistoryList').prepend('<li class="list-group-item d-flex justify-content-between align-items-center"><a href="#" class="chat-link active-chat" data-chat-id="' + data.chat_id + '">' + data.chat_name + '</a><button class="btn btn-sm btn-danger delete-chat" data-chat-id="' + data.chat_id + '" title="Удалить чат"><i class="fas fa-trash-alt"></i></button></li>');
                        openChat(data.chat_id, data.chat_name);
                        if (typeof callback === 'function') {
                            callback(data.chat_id, data.chat_name);
                        }
                    },
                    error: function() {
                        alert('Ошибка при создании нового диалога');
                    }
                });
            }

            function sendMessageToChat(chat_id, message) {
                // Показываем индикатор печати
                $('#typingIndicator').removeClass('hidden');

                $.ajax({
                    url: 'chat_handler.php',
                    method: 'POST',
                    data: { message: message, chat_id: chat_id },
                    success: function(response) {
                        console.log("Ответ от сервера:", response);
                        $('#typingIndicator').addClass('hidden');
                        let data;
                        try {
                            data = JSON.parse(response);
                        } catch (e) {
                            console.log("Ошибка парсинга JSON:", e);
                            alert('Ошибка обработки ответа от сервера');
                            return;
                        }
                        if (data.error) {
                            console.log("Ошибка от сервера:", data.error);
                            alert(data.error);
                            return;
                        }
                        if (data.response) {
                            $('#userMessage').val('');
                            $('#chatBox').append('<div class="message user-message mb-2 fade-in">' + htmlspecialchars(message) + '</div>');

                            let aiResponseFormatted = formatAIResponse(data.response);
                            $('#chatBox').append('<div class="message ai-message mb-2 fade-in">' + aiResponseFormatted + '</div>');
                            $('#chatBox').scrollTop($('#chatBox')[0].scrollHeight);
                            highlightAllCodeBlocks();
                        } else {
                            console.log("Неизвестный формат ответа:", data);
                            alert('Неизвестный ответ от сервера');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log("AJAX ошибка:", status, error);
                        alert('Ошибка при отправке сообщения');
                        $('#typingIndicator').addClass('hidden');
                    }
                });
            }

            // Функция для форматирования ответа ИИ
            // Заменяем ```...``` на <pre><code>...</code></pre>
            function formatAIResponse(response) {
                // Ищем блоки кода по шаблону ```(язык?)\nкод\n```
                // Упростим задачу:
                // Распарсим тройные обратные кавычки:
                let codeRegex = /```([\s\S]*?)```/g;
                let formatted = response.replace(codeRegex, function(match, p1) {
                    let codeContent = htmlspecialchars(p1.trim());
                    // Добавляем <pre><code> для подсветки
                    // Можно добавить класс для языка, если известен
                    return '<pre><code>' + codeContent + '</code></pre>';
                });
                return formatted;
            }

            function highlightAllCodeBlocks() {
                // Подсветка синтаксиса для всех блоков кода
                $('pre code').each(function(i, block) {
                    hljs.highlightElement(block);
                });
            }

        });
    </script>
</body>

</html>
