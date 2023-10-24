<?php
class TelegramBotDatabase {
    private $dbHost;
    private $dbUser;
    private $dbPassword;
    private $dbName;
    private $dbConnection;

    public function __construct($dbConfig = []) {
            $this->dbHost = $dbConfig['db-host'];
            $this->dbUser = $dbConfig['db-user'];
            $this->dbPassword = $dbConfig['db-password'];;
            $this->dbName = $dbConfig['db-name'];;

            $this->dbConnection = $this->connectToDatabase();
    }

    public function __destruct() {
        $this->closeDatabaseConnection();
    }

    public function closeDatabaseConnection() {
        if ($this->dbConnection) {
            $this->dbConnection->close();
        }
    }

    public function isAuthorExists($author_id) {
        $sql = "SELECT COUNT(author_id) AS total FROM chat_authors WHERE author_id='" .$author_id. "'";

        if ($result = $this->dbConnection->query($sql)) {
            $obj = $result->fetch_object();

            return $obj->total;
        } else {
            return false;
        }
    }

    public function isAuthorBlocked($author_id) {
        $sql = "SELECT COUNT(author_id) AS total FROM chat_authors WHERE author_id='" .$author_id. "' AND blocked = '1'";

        if ($result = $this->dbConnection->query($sql)) {
            $obj = $result->fetch_object();

            return $obj->total;
        } else {
            return false;
        }
    }

    public function addAuthorToDatabase($authorInfo) {
        $currentDate = date('Y-m-d H:i:s');
        $sql = sprintf(
            "INSERT INTO chat_authors (author_id, author_username, author_name, blocked, date_created, date_updated) VALUES ('%d', '%s', '%s', '%d','%s', '%s')",
            $authorInfo['author_id'],
            $authorInfo['author_username'],
            $authorInfo['author_name'],
            0 ,
            $currentDate,
            $currentDate
        );

        $this->dbConnection->query($sql);
    }

    public function updateAuthorInDatabase($authorInfo) {
        $sql = sprintf(
            "UPDATE chat_authors SET author_username='%s', author_name='%s', date_updated='%s' WHERE author_id='%d'",
            $authorInfo['author_username'],
            $authorInfo['author_name'],
            date('Y-m-d H:i:s'),
            $authorInfo['author_id']

        );

        $this->dbConnection->query($sql);
    }

    public function addUpdateToDatabase($update) {
        $sql = sprintf(
            "INSERT INTO chat_messages (telegram_message_id, chat_id, author_id, text, date) VALUES ('%d', '%d', '%s', '%s', '%s')",
            $update['message']['message_id'],
            $update['message']['chat']['id'],
            $update['message']['from']['id'],
            $update['message']['text'],
            date('Y-m-d H:i:s', $update['message']['date'])

        );

        $this->dbConnection->query($sql);
    }

    private function connectToDatabase() {
        $connection = new mysqli($this->dbHost, $this->dbUser, $this->dbPassword, $this->dbName);
        return $connection;
    }
}