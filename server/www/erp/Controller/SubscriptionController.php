<?php
namespace Controller;

use Model\SubscriptionModel;
use Model\UserModel;// Визначаємо клас SubscriptionController, який розширює функціональність базового контролера

class SubscriptionController extends BaseController
{
    private $subscriptionModel;
    private $userModel;

    public function __construct(SubscriptionModel $subscriptionModel, UserModel $userModel)
    {
        $this->subscriptionModel = $subscriptionModel;
        $this->userModel = $userModel;
    }

    // Метод для перевірки активності підписки користувача
    public function checkSubscriptionActive($userId)
    {
        // Отримуємо деталі підписки для користувача
        $subscription = $this->subscriptionModel->getSubscriptionByUserId($userId);

        // Перевіряємо, чи існує підписка та чи активна вона
        return $subscription && $this->isSubscriptionActive($subscription);
    }

    // Допоміжний метод для визначення кількості днів, що залишилися у підписки
    private function getDaysLeft($subscription)
    {
        $today = new DateTime(); // Сьогоднішня дата
        $expirationDate = new DateTime($subscription['created_at']); // Дата початку підписки
        $expirationDate->modify("+{$subscription['valid_for_days']} days"); // Обчислюємо дату закінчення підписки

        // Якщо дата закінчення ще не настала, обчислюємо залишкові дні
        if ($expirationDate >= $today) {
            $interval = $today->diff($expirationDate); // Отримуємо інтервал між сьогоднішньою датою та датою закінчення
            return $interval->days; // Повертаємо кількість залишкових днів
        } else {
            return 0; // Підписка закінчилася
        }
    }

    // Метод для обробки запиту на отримання даних про підписку

    public function leads($userId)
    {
        $subscription = $this->subscriptionModel->getSubscriptionByUserId($userId);

        if ($subscription && $this->isSubscriptionActive($subscription)) {
            $daysLeft = $this->getDaysLeft($subscription);
            http_response_code(200); // Успешный ответ
            echo json_encode([
                "message" => "Деталі підписки. Днів залишилося: {$daysLeft}"
            ]);
        } else {
            http_response_code(403); // Доступ запрещен
            echo json_encode([
                "error" => "Доступ заборонено. Потрібна активна підписка."
            ]);
        }
    }

    public function availableSubscriptions($userId)
    {
        $subscriptions = $this->subscriptionModel->getAvailableSubscriptions();
        http_response_code(200); // Успешный ответ
        echo json_encode($subscriptions);
    }

    public function subscribe($userId)
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $subscriptionTypeId = $data['subscription_type_id'] ?? null;

        if (!$subscriptionTypeId) {
            http_response_code(400); // Неверный запрос
            echo json_encode(["error" => "Необхідно вказати тип підписки."]);
            return;
        }

        if ($this->subscriptionModel->subscribeUserToSubscription($userId, $subscriptionTypeId)) {
            http_response_code(201); // Создано
            echo json_encode(["message" => "Підписку оформлено успішно."]);
        } else {
            http_response_code(500); // Внутренняя ошибка сервера
            echo json_encode(["error" => "Під час оформлення підписки виникла помилка."]);
        }
    }

    public function viewAllSubscriptions($userId)
    {
        $limit = $_GET['limit'] ?? 10;
        $page = $_GET['page'] ?? 1;

        if ($this->userModel->hasPermission($userId, 'admin_viev_edit_content')) {
            $subscriptions = $this->subscriptionModel->getAllSubscriptions($limit, $page);
            http_response_code(200); // Успешный ответ
            echo json_encode($subscriptions);
        } else {
            http_response_code(403); // Доступ запрещен
            echo json_encode(["error" => "У вас нет разрешения на просмотр всех подписок."]);
        }
    }
}
?>