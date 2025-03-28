<?php

namespace Model;

use PDO;
use Exception;

class TelegramNotifierModel
{
    protected PDO $conn;
    protected string $token;
    protected string $chatId;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;

        $config = $this->loadConfig();

        if (!$config) {
            throw new Exception("Telegram configuration not found.");
        }

        $this->token = $config['token'];
        $this->chatId = $config['chat_id'];
    }

    /**
     * Load Telegram bot credentials from the database.
     */
    protected function loadConfig(): ?array
    {
        $query = "SELECT token, chat_id FROM telegram_config LIMIT 1";
        $stmt = $this->conn->query($query);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    /**
     * Generate a notification message for Telegram.
     */
    public function generateTelegramNotification(array $guest, array $updateData): string
    {
        $statusTranslations = [
            'pending'  => 'Очікує',
            'accepted' => 'Прийнято',
            'declined' => 'Відхилено'
        ];

        $guestName = htmlspecialchars($guest['first_name'] ?? 'Гість');
        $statusGuest = $statusTranslations[$updateData['rsvp_status']] ?? 'не вказано';

        // Getting user location and phone model from $_SERVER variables
        $userIP = $_SERVER['REMOTE_ADDR'] ?? 'не вказано';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'не вказано';


        $message = "🎉 <b>Оновлення інформації про гостя:</b>\n\n"
            . "👤 <b>{$guestName}</b>\n"
            . "📌 <b>Статус відповіді:</b> {$statusGuest}\n"
            . "🍷 <b>Алкогольні вподобання:</b> " . ($updateData['alcohol_preferences'] ?? 'не вказано') . "\n";

        if (!empty($updateData['wine_type'])) {
            $message .= "🍇 <b>Тип вина:</b> {$updateData['wine_type']}\n";
        }

        if (!empty($updateData['custom_alcohol'])) {
            $message .= "🥃 <b>Свій варіант алкоголю:</b> {$updateData['custom_alcohol']}\n";
        }

        // Перевірка та додавання інформації про додаткового гостя, якщо він є
        if (!empty($guest['first_name_plus_1'])) {
            $plusOneName = htmlspecialchars($guest['first_name_plus_1']);
            $statusPlusOne = $statusTranslations[$updateData['rsvp_status_plus_one']] ?? 'не вказано';

            $message .= "\n👥 <b>Додатковий гість:</b> {$plusOneName}\n"
                . "📌 <b>Статус відповіді:</b> {$statusPlusOne}\n"
                . "🍷 <b>Алкогольні вподобання:</b> " . ($updateData['alcohol_preferences_plus_one'] ?? 'не вказано') . "\n";

            if (!empty($updateData['wine_type_plus_one'])) {
                $message .= "🍇 <b>Тип вина:</b> {$updateData['wine_type_plus_one']}\n";
            }

            if (!empty($updateData['custom_alcohol_plus_one'])) {
                $message .= "🥃 <b>Свій варіант алкоголю:</b> {$updateData['custom_alcohol_plus_one']}\n";
            }
        }

        // Додаємо інформацію про користувача
        $message .= "🌐 <b>Провів часу на сторінці:</b> {$updateData['time_spent_formatted']}\n"
            . "📱 <b>Пристрій користувача:</b> {$userAgent}\n";

        // Посилання на сторінку адміністратора
        $message .= "\n🔗 <a href=\"http://127.0.0.1:3000/admin\">Перейти до адмін панелі</a>";

        return $message;
    }



    /**
     * Send a message via Telegram bot.
     */ public function sendMessage(array $guest, array $updateData, string $parseMode = 'HTML'): bool
    {
        if (empty($this->token) || empty($this->chatId)) {
            throw new Exception('Telegram bot token or chat ID is not set.');
        }
        if (empty($guest) || empty($updateData)) {
            throw new Exception('Guest data or update data cannot be empty.');
        }
        if (!is_array($guest) || !is_array($updateData)) {
            throw new Exception('Guest data and update data must be arrays.');
        }
        $message = $this->generateTelegramNotification($guest, $updateData);

        if (empty($message)) {
            throw new Exception('Message cannot be empty.');
        }
        $url = "https://api.telegram.org/bot{$this->token}/sendMessage";

        $payload = [
            'chat_id' => $this->chatId,
            'text' => $message,
            'parse_mode' => $parseMode,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $errorMsg = curl_error($ch);
            curl_close($ch);
            throw new Exception('Telegram API Error: ' . $errorMsg);
        }

        curl_close($ch);

        $responseData = json_decode($response, true);

        if (!$responseData || !isset($responseData['ok']) || $responseData['ok'] !== true) {
            throw new Exception('Telegram API Error: Invalid response - ' . $response);
        }

        return true;
    }
}
