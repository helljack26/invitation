<?php
namespace Model;

use PDO;

class SubscriptionModel extends Database
{
    public function getSubscriptionPrice($subscriptionTypeId)
    {
        $query = "SELECT price FROM SubscriptionTypes WHERE id = :subscriptionTypeId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':subscriptionTypeId', $subscriptionTypeId);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function getSubscriptionValidDays($subscriptionTypeId)
    {
        $query = "SELECT valid_for_days FROM SubscriptionTypes WHERE id = :subscriptionTypeId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':subscriptionTypeId', $subscriptionTypeId);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // Метод для получения информации о подписке по ID пользователя
    public function getSubscriptionByUserId($userId)
    {
        // Текущая дата
        $currentDate = date('Y-m-d H:i:s');

        // Запрос к базе данных для получения активной подписки компании, к которой принадлежит пользователь
        $query = "SELECT cs.*, st.valid_for_days 
                    FROM CompanySubscriptions cs
                    INNER JOIN SubscriptionTypes st ON cs.subscription_type_id = st.id
                    INNER JOIN UserCompanies uc ON cs.company_id = uc.company_id
                    WHERE uc.user_id = :userId
                    AND cs.start_date <= :currentDate
                    AND DATE_ADD(cs.start_date, INTERVAL st.valid_for_days DAY) >= :currentDate
                    AND cs.is_active = 1  
                    ORDER BY cs.start_date DESC LIMIT 1"; // Лимит 1 гарантирует, что вернется только одна подписка
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userId', $userId);
        $stmt->bindParam(':currentDate', $currentDate);
        $stmt->execute();

        // Получение результата запроса
        $subscription = $stmt->fetch(PDO::FETCH_ASSOC);

        // Возвращаем информацию о подписке или null, если подписка не найдена
        return $subscription ? $subscription : null;
    }

    public function getAvailableSubscriptions()
    {
        $query = "SELECT * FROM SubscriptionTypes";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function subscribeUserToSubscription($userId, $subscriptionTypeId)
    {
        // Get the company ID for the user
        $companyQuery = "SELECT company_id FROM UserCompanies WHERE user_id = :userId LIMIT 1";
        $companyStmt = $this->conn->prepare($companyQuery);
        $companyStmt->bindParam(':userId', $userId);
        $companyStmt->execute();
        $company = $companyStmt->fetch(PDO::FETCH_ASSOC);

        // Get subscription type details
        $subscriptionTypeQuery = "SELECT * FROM SubscriptionTypes WHERE id = :subscriptionTypeId LIMIT 1";
        $subscriptionTypeStmt = $this->conn->prepare($subscriptionTypeQuery);
        $subscriptionTypeStmt->bindParam(':subscriptionTypeId', $subscriptionTypeId);
        $subscriptionTypeStmt->execute();
        $subscriptionType = $subscriptionTypeStmt->fetch(PDO::FETCH_ASSOC);

        // Add new subscription
        $subscriptionQuery = "INSERT INTO CompanySubscriptions (company_id, subscription_type_id, start_date, is_active) VALUES (:companyId, :subscriptionTypeId, NOW(), 1)";
        $subscriptionStmt = $this->conn->prepare($subscriptionQuery);
        $subscriptionStmt->bindParam(':companyId', $company['company_id']);
        $subscriptionStmt->bindParam(':subscriptionTypeId', $subscriptionTypeId);
        $subscriptionStmt->execute();

        // Return the ID of the new subscription
        return $this->conn->lastInsertId();
    }

    public function canUpgradeTo($currentTypeId, $newTypeId)
    {
        // Проверяем, что новый тип подписки существует
        $subscriptionTypeQuery = "SELECT price FROM SubscriptionTypes WHERE id = :subscriptionTypeId";
        $subscriptionTypeStmt = $this->conn->prepare($subscriptionTypeQuery);

        // Получаем цену текущего типа подписки
        $subscriptionTypeStmt->bindParam(':subscriptionTypeId', $currentTypeId);
        $subscriptionTypeStmt->execute();
        $currentPrice = $subscriptionTypeStmt->fetchColumn();

        // Получаем цену нового типа подписки
        $subscriptionTypeStmt->bindParam(':subscriptionTypeId', $newTypeId);
        $subscriptionTypeStmt->execute();
        $newPrice = $subscriptionTypeStmt->fetchColumn();

        // Если новый тип подписки не найден, возвращаем false
        if ($newPrice === false) {
            return false;
        }

        // Рассчитываем разницу в цене
        $priceDifference = $newPrice - $currentPrice;

        // Пользователь может перейти на новый тип подписки, если разница в цене положительна
        return $priceDifference > 0;
    }

    public function getDaysLeft($subscription)
    {
        $today = new DateTime(); // Сегодняшняя дата
        $expirationDate = new DateTime($subscription['start_date']);
        $expirationDate->modify("+{$subscription['valid_for_days']} days"); // Дата окончания подписки

        if ($expirationDate > $today) {
            $interval = $today->diff($expirationDate);
            return $interval->days;
        } else {
            return 0;
        }
    }
    public function updateUserSubscription($userId, $newTypeId)
    {
        // Начало транзакции
        $this->conn->beginTransaction();

        try {
            // Проверяем, активна ли текущая подписка пользователя
            $currentSubscription = $this->getSubscriptionByUserId($userId);
            if (!$currentSubscription) {
                // Нет активной подписки для обновления
                $this->conn->rollBack();
                return false;
            }

            // Расчет оставшегося времени на текущем тарифе
            $remainingDays = $this->getDaysLeft($currentSubscription);
            $currentSubscriptionPrice = $this->getSubscriptionPrice($currentSubscription['subscription_type_id']);
            $newSubscriptionPrice = $this->getSubscriptionPrice($newTypeId);

            // Проратирование платежа
            $dailyRate = $currentSubscriptionPrice / $currentSubscription['valid_for_days'];
            $credit = $dailyRate * $remainingDays;
            $newDailyRate = $newSubscriptionPrice / $this->getSubscriptionValidDays($newTypeId);
            $chargeForRemainingDays = $newDailyRate * $remainingDays;

            // Итоговая сумма к оплате за переход на новую подписку
            $amountToPay = $chargeForRemainingDays - $credit;

            // Тут логика для создания платежа через LiqPay
            // Возможно, вам потребуется использовать API LiqPay для создания платежа
            // ...
            /*
            $liqPay = new LiqPay($public_key, $private_key);
            $response = $liqPay->api("request", array(
                'action'         => 'pay',
                'version'        => '3',
                'amount'         => $amountToPay,
                'currency'       => 'UAH',
                'description'    => 'Subscription change',
                'order_id'       => 'order_id_' . $userId,
                'product_url'    => 'URL на ваш продукт или услугу',
                // другие параметры, если необходимо
            ));
            
            if ($response->result !== 'success') {
                // Обработка ошибки платежа
                $this->conn->rollBack();
                throw new Exception("Ошибка при выполнении платежа через LiqPay");
            }
            */

            // После успешного платежа, обновляем информацию о подписке
            $updateQuery = "UPDATE CompanySubscriptions SET subscription_type_id = :newTypeId, start_date = NOW(), is_active = 1 WHERE id = :subscriptionId";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bindParam(':newTypeId', $newTypeId);
            $updateStmt->bindParam(':subscriptionId', $currentSubscription['id']);
            $updateStmt->execute();

            // Подтверждение транзакции
            $this->conn->commit();

            return true;
        } catch (Exception $e) {
            // Откат транзакции в случае ошибки
            $this->conn->rollBack();
            // Залогировать ошибку
            error_log($e->getMessage());
            // Возвращаем false или выбрасываем исключение, которое можно будет обработать на более высоком уровне
            return false;
        }
    }
    public function getAllSubscriptions($limit = 10, $page = 1)
    {
        $offset = ($page - 1) * $limit;
        $query = "SELECT * FROM CompanySubscriptions LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}