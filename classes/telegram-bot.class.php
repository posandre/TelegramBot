<?php
class TelegramBot {
    private $token;
    private $apiUrl = 'https://api.telegram.org/bot';

    private $logFilePath = __DIR__ . "/file_log.txt";

    public function __construct($token, $dbConfig = []) {
        $this->token = $token;

        if (!empty($dbConfig)) {
            $this->dbHost = $dbConfig['db-host'];
            $this->dbUser = $dbConfig['db-user'];
            $this->dbPassword = $dbConfig['db-password'];;
            $this->dbName = $dbConfig['db-name'];;

            $this->dbConnection = $this->connectToDatabase();
        }
    }

    public function setWebhook($webhookUrl) {
        if ($this->isWebhookNotSet()) {
            $url = $this->apiUrl . $this->token . '/setWebhook';
            $data = [
                'url' => $webhookUrl,
            ];

            $response = $this->sendRequest($url, $data);
        }

        if (empty($response)) {
            $message = "Failed to set up the webhook.";
        } else {
            $message = "Webhook set up successfully!";
        }

        return $message;
    }

    public function deleteWebhook() {
        $url = $this->apiUrl . $this->token . '/deleteWebhook';

        $response = $this->sendRequest($url);

        if ($response) {
            $message = "Webhook deleted up successfully!";
        } else {
            $message = "Failed to delete the webhook.";
        }

        return $message;
    }

    public function receiveUpdate() {
        return json_decode(file_get_contents('php://input'), true);
    }

    public function sendMessage($chatId, $message) {
        $url = $this->getMetodApiUrl('sendMessage');
        $data = [
            'chat_id' => $chatId,
            'text' => $message,
        ];

        return $this->sendRequest($url, $data);
    }

    public function sendDocument($chatId, $documentPath, $caption) {
        $url = $this->getMetodApiUrl('sendDocument');
        $data = [
            'chat_id' => $chatId,
            "caption" 	=> $caption,
            "document" 	=> new CURLFile($documentPath)
        ];

        return $this->sendRequest($url, $data);
    }

    public function saveDataToLog($logData, $chatId = false) {
        file_put_contents($this->logFilePath, $logData, FILE_APPEND);
        $caption = date("d.m.Y H:i") . " Logging data";

        if (!empty($chatId)) {
            $this->sendDocument($chatId, $this->logFilePath, $caption);
        }

    }

    private function getMetodApiUrl($methodName) {
        return $this->apiUrl . $this->token . '/' . $methodName;
    }

    private function isWebhookNotSet() {
        $url = $this->apiUrl . $this->token . '/getWebhookInfo';
        $responseArray = json_decode(file_get_contents($url), true);

        return empty($responseArray['result']['url']);
    }

    private function sendRequest($url, $data = []) {
        $ch = curl_init($url);
        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}
