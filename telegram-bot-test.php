<?php

/* Load Config */
require_once(__DIR__ . '/telegram-bot.config.php');

/* Load classes */
require_once(__DIR__ . '/classes/telegram-bot-helper.class.php');
require_once(__DIR__ . '/classes/telegram-bot-database.class.php');
require_once(__DIR__ . '/classes/telegram-bot.class.php');


try {
    $telegramBotDatabase = new TelegramBotDatabase(DATABASE_CONFIG);

    $telegramBot = new TelegramBot(TELEGRAM_BOT_TOKEN);

    /* Register web hook */
    if (!empty($_GET['hook-action'])) {
        if ($_GET['hook-action'] == 'register') {
            echo $telegramBot->setWebhook(WEBHOOK_URL);
        } elseif ($_GET['hook-action'] == 'delete') {
            echo $telegramBot->deleteWebhook();
        }
    }

    /* Process incoming updates */
    $update = $telegramBot->receiveUpdate();

    if ($update) {
        $chatId = $update['message']['chat']['id'];
        $authorId = $update['message']['from']['id'];
        $authorName = $update['message']['from']['first_name'] . ' ' . $update['message']['from']['last_name'];
        $message = $update['message']['text'];

        $authorInfo = [
            'author_id' => $authorId,
            'author_username' => $update['message']['from']['username'],
            'author_name' => $authorName
        ];

        if ($telegramBotDatabase->isAuthorExists($authorId)) {
            $telegramBotDatabase->updateAuthorInDatabase($authorInfo);
        } else {
            $telegramBotDatabase->addAuthorToDatabase($authorInfo);
        }

        if ($telegramBotDatabase->isAuthorBlocked($authorId)) {
            $message = "Hello.\nYour user {$authorName} was blocked!!!\nPlease, contact with chat administrator.";
        } else {
            $telegramBotDatabase->addUpdateToDatabase($update);
            $message = "Hello.\nHow are you {$authorName}?";
        }

        $telegramBot->sendMessage(
            $chatId,
            $message
        );
    }

} catch (Exception $e) {
    $errorMessage = $e->getMessage();
    TelegramBotHelper::addToLogFile($errorMessage, LOG_FILE_PATH);
}