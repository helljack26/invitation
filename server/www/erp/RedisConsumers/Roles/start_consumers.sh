#!/bin/bash

# Запускаем каждый консюмер в фоновом режиме
# Вывод перенаправляем в отдельный лог-файл для удобства отладки

php role_create_consumer.php > role_create_consumer.log 2>&1 &
echo "role_create_consumer запущен"

php role_assignment_consumer.php > role_assignment_consumer.log 2>&1 &
echo "role_assignment_consumer запущен"

php role_deletion_consumer.php > role_deletion_consumer.log 2>&1 &
echo "role_deletion_consumer запущен"

php role_update_consumer.php > role_update_consumer.log 2>&1 &
echo "role_update_consumer запущен"

echo "Все консюмеры запущены в фоне."
