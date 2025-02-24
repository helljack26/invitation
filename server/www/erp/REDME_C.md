# Контакты и Постачальникы:

#

# Создайте базу данных для хранения информации о контактах и Постачальниках.

# Включите поля, такие как имя, фамилия, должность, контактная информация (телефон, email), адрес и другие релевантные данные.

# Разработайте связь между контактами и Постачальниками, так как один Постачальник может иметь несколько контактов.

# Лиды:

#

# Добавьте функциональность для создания и управления лидами (потенциальными клиентами).

# Включите поля для описания лидов, их статуса (например, новый, в работе, закрыт), источника и даты создания.

# Проекты:

#

# Создайте функциональность для управления проектами и их этапами.

# Включите поля для названия проекта, описания, статуса проекта, даты начала и завершения.

# Задачи:

#

# Разработайте модуль для создания, назначения и управления задачами.

# Включите поля для названия задачи, описания, даты выполнения, приоритета и ответственного за задачу.

# Напоминания:

#

# Создайте возможность установки напоминаний для задач и событий.

# Обеспечьте удобный календарь для отслеживания дат напоминаний.

# Test Login hash pass user

curl -X POST -H "Content-Type: application/json" -d '{"email": "test@example.com", "password": "yourpassword"}' http://localhost/api/auth/login

# Register

curl -X POST -H "Content-Type: application/json" \
-d '{"username": "testuser", "email": "test@example.com", "password": "yourpassword", "first_name": "Test", "second_name": "User", "last_name": "Example"}' \
http://localhost/api/auth/register

# Добавить контр агента

curl -X POST "http://localhost/api/account/create" \
 -H "Content-Type: application/json" \
 -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0IiwiYXVkIjoiaHR0cDovL2xvY2FsaG9zdCIsImlhdCI6MTcwMjA0MjI3NywiZXhwIjoxNzAyMDQ1ODc3LCJkYXRhIjp7InVzZXJJZCI6IjIifX0.\_pWpWdr7wMKKk18hBZIuisbMdZ3rfmPVQbv0EsFsJrA" \
 -d '{
"name": "Название компании",
"industry": "Отрасль",
"website": "http://www.example.com",
"phone_office": "1234567890",
"billing_address_street": "Улица",
"billing_address_city": "Город",
"billing_address_state": "Регион",
"billing_address_postalcode": "Почтовый индекс",
"billing_address_country": "Страна",
"shipping_address_street": "Улица (доставка)",
"shipping_address_city": "Город (доставка)",
"shipping_address_state": "Регион (доставка)",
"shipping_address_postalcode": "54321",
"shipping_address_country": "Страна (доставка)",
"description": "Описание компании"
}'

# Получит всех Постачальников которые находятся в компании который и ты, Redis есть

curl -X GET http://localhost/api/account/list -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0IiwiYXVkIjoiaHR0cDovL2xvY2FsaG9zdCIsImlhdCI6MTcwMjIxMDc5NCwiZXhwIjoxNzAyMjE0Mzk0LCJkYXRhIjp7InVzZXJJZCI6IjIifX0.yF8_zOGlCyLsylh8xWVQA5kOFuLbT14RolizjsVWX4Q"

# Получить Контр агента по ид

curl -X GET http://localhost/api/account/counterparty?account_id=22 -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0IiwiYXVkIjoiaHR0cDovL2xvY2FsaG9zdCIsImlhdCI6MTcwMjIyMzk4NiwiZXhwIjoxNzAyMjI3NTg2LCJkYXRhIjp7InVzZXJJZCI6IjIifX0.OdcygAkW65K5txpYyY2LUDuWwCLCxVZgDdwJ1RHLHA0"

curl -X DELETE http://localhost/api/account/delete \
-H "Content-Type: application/json" \
-H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0IiwiYXVkIjoiaHR0cDovL2xvY2FsaG9zdCIsImlhdCI6MTcwMjIyNDU1MywiZXhwIjoxNzAyMjI4MTUzLCJkYXRhIjp7InVzZXJJZCI6IjIifX0.SPsEcqynA4pAWhgSHDzN5OIoE7V5HbubUCn3vsvvpbM" \
-d '{"account_id": 23}'
