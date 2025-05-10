<?php

namespace Model;

use PDO;
use Exception;

class TelegramNotifierModel
{
    protected PDO $conn;
    protected string $token;
    protected array $chatIds;

    public function __construct(PDO $conn)
    {
        $this->conn = $conn;

        // Load Telegram bot token from telegram_config table.
        $this->token = $this->loadToken();

        // Load chat IDs from telegram_chat_ids table.
        $this->chatIds = $this->loadChatIds();

        if (empty($this->token) || empty($this->chatIds)) {
            throw new Exception("Telegram token or chat IDs not found.");
        }
    }

    /**
     * Load the Telegram bot token from the telegram_config table.
     */
    protected function loadToken(): string
    {
        $query = "SELECT token FROM telegram_config LIMIT 1";
        $stmt = $this->conn->query($query);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result || !isset($result['token'])) {
            throw new Exception("Telegram token not found in telegram_config table.");
        }

        return $result['token'];
    }

    /**
     * Load all chat IDs from the telegram_chat_ids table.
     */
    protected function loadChatIds(): array
    {
        $query = "SELECT chat_id FROM telegram_chat_ids";
        $stmt = $this->conn->query($query);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$rows) {
            throw new Exception("No chat IDs found in telegram_chat_ids table.");
        }

        // Extract the chat_id values into an array
        return array_column($rows, 'chat_id');
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

        // Basic guest information
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

        // If a plus-one exists, add plus-one information
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

        // --- Added section for detailed statistics ---
        $query = "SELECT 
                    COUNT(*) as guest_count,
                    SUM(CASE WHEN rsvp_status = 'accepted' THEN 1 ELSE 0 END) as accepted_count,
                    SUM(CASE WHEN rsvp_status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                    SUM(CASE WHEN rsvp_status = 'declined' THEN 1 ELSE 0 END) as declined_count,
                    SUM(CASE WHEN (first_name_plus_1 IS NOT NULL AND first_name_plus_1 != '') THEN 1 ELSE 0 END) as plus_one_count,
                    SUM(CASE WHEN rsvp_status_plus_one = 'accepted' THEN 1 ELSE 0 END) as accepted_plus_one_count,
                    SUM(CASE WHEN rsvp_status_plus_one = 'pending' THEN 1 ELSE 0 END) as pending_plus_one_count,
                    SUM(CASE WHEN rsvp_status_plus_one = 'declined' THEN 1 ELSE 0 END) as declined_plus_one_count
                  FROM guests";
        $stmt = $this->conn->query($query);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        // Calculate totals:
        $totalGuests = $data['guest_count'] + $data['plus_one_count'];
        $totalAccepted = $data['accepted_count'] + $data['accepted_plus_one_count'];
        $totalPending = $data['pending_count'] + $data['pending_plus_one_count'];
        $totalDeclined = $data['declined_count'] + $data['declined_plus_one_count'];

        $message .= "\n📊 <b>Статистика:</b>\n"
            . "✅ Прийнято: {$totalAccepted}\n"
            . "⌛ Очікують: {$totalPending}\n"
            . "❌ Відхилено: {$totalDeclined}\n"
            . "👥 Загалом: {$totalGuests}\n";
        // --- End of added section ---

        // Append link to admin panel
        $message .= "\n🔗 <a href=\"http://127.0.0.1:3000/admin\">Перейти до адмін панелі</a>";

        return $message;
    }

    /**
     * Send a message via Telegram bot to all chat IDs.
     */
    public function sendMessage(array $guest, array $updateData, string $parseMode = 'HTML'): bool
    {
        if (empty($this->token) || empty($this->chatIds)) {
            throw new Exception('Telegram bot token or chat IDs are not set.');
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

        // Loop over all chat IDs and send the notification
        foreach ($this->chatIds as $chatId) {
            $url = "https://api.telegram.org/bot{$this->token}/sendMessage";

            $payload = [
                'chat_id'    => $chatId,
                'text'       => $message,
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
        }

        return true;
    }
}
