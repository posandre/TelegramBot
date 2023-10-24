<?php

class TelegramBotHelper
{
    public static function addToLogFile($message, $logFilePath)
    {
        $text = "<---------\t" . date("d.m.Y H:i") . "\t--------->";
        $text .= "\n" . $message . "\n\n";
        file_put_contents($logFilePath, $text, FILE_APPEND);
    }
}