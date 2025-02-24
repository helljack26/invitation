# Тестовый запрос на создания ролей

curl -X POST \
-H "Content-Type: application/json" \
-H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vIiwiYXVkIjoiaHR0cDovLyIsImlhdCI6MTczNDE4NjU3NywiZXhwIjoxNzM0MTkwMTc3LCJkYXRhIjp7InVzZXJJZCI6IjIifX0.4C8pfZagj3JAQiRilcs2H1BiFfPye6ZwTf7vxMpBa4Y" \
-d '{"count": 100}' \
http://localhost/api/role/test-bulk-create-roles

# Получить пользвателей и лимит для вывода защиненный роут

curl -H "Authorization: Bearer $token" http://localhost/api/user/list?limit=20

# Test Login hash pass user

curl -X POST -H "Content-Type: application/json" -d '{"email": "test@example.com", "password": "yourpassword"}' http://localhost/api/auth/login

curl -X POST -H "Content-Type: application/json" -d '{"email": "newemail@example.com", "password": "yourpassword"}' http://localhost/api/auth/login

curl -c cookies.txt -X POST -H "Content-Type: application/json" -d '{"email": "test@example.com", "password": "yourpassword"}' http://localhost/api/auth/login

# Register

curl -X POST -H "Content-Type: application/json" \
-d '{"username": "testuser", "email": "test@example.com", "password": "yourpassword", "first_name": "Test", "second_name": "User", "last_name": "Example"}' \
http://localhost/api/auth/register

# Создание компании

curl -X POST http://localhost/api/company/create \
-H "Content-Type: application/x-www-form-urlencoded" \
-H "Authorization: Bearer $token" \
-d "company_name=ExampleCompanyName" \
-d "address=ExampleAddress"

# Получение информации о компании

curl -X GET "http://localhost/api/company/info" \
-H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vIiwiYXVkIjoiaHR0cDovLyIsImlhdCI6MTczMTIzNjUzMiwiZXhwIjoxNzMxMjQwMTMyLCJkYXRhIjp7InVzZXJJZCI6IjIifX0.HIB45GwjWSkTCCSocmVgNNG0JufCP_9LPCS4puTuMP0"

# Список сотрудников

curl -X GET "http://localhost/api/company/employees" \
-H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vIiwiYXVkIjoiaHR0cDovLyIsImlhdCI6MTczNDUxMjA4OCwiZXhwIjoxNzM0NTE1Njg4LCJkYXRhIjp7InVzZXJJZCI6IjMifX0.vJkTyUrsSZXqQ39pcu68m84eAFzxwkKAMLt9Lydb8Ag"

# список подписок

curl -H "Authorization: Bearer $token" http://localhost/api/subscription/available

# Получить все подписки компаний

curl -H "Authorization: Bearer $token" http://localhost/api/subscription/vievall?limit=10&page=1

# Запрос для создания выбраной подписки

curl -X POST \
 http://localhost/api/subscription/subscribe \
 -H 'Authorization: Bearer $token' \
 -H 'Content-Type: application/json' \
 -d '{"subscription_type_id": "1"}'

# Смена подписки

curl -X POST \
 http://localhost/api/subscription/update \
 -H 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0IiwiYXVkIjoiaHR0cDovL2xvY2FsaG9zdCIsImlhdCI6MTY5OTM0MzEwNywiZXhwIjoxNjk5MzQ2NzA3LCJkYXRhIjp7InVzZXJJZCI6IjIifX0.vUGdMItyKuq0DiG9OwDzE4G63JpyspAanQJ9LonfuDo' \
 -H 'Content-Type: application/json' \
 -d '{"new_subscription_type_id": "2"}' # назначить подписку с ид 2, в коде придусмотрина лоигка для лик пей она закоментирована

# ----------Роли и пермишины----

# Получить роль пользвателя по ид

curl -X POST "http://localhost/api/role/getUserRole" \
-H "Content-Type: application/json" \
-H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vIiwiYXVkIjoiaHR0cDovLyIsImlhdCI6MTczNDUxNDQwMywiZXhwIjoxNzM0NTE4MDAzLCJkYXRhIjp7InVzZXJJZCI6IjMifX0.kYEQEQEwaX0tkaeE6H-JdRCU7jguZp6zVE6Od_Vt80o" \
-d '{
"userId": 3
}'

# Создать роль для компании: +

curl -X POST "http://localhost/api/role/create" \
-H "Content-Type: application/json" \
-H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vIiwiYXVkIjoiaHR0cDovLyIsImlhdCI6MTczNDM3MDA2NCwiZXhwIjoxNzM0MzczNjY0LCJkYXRhIjp7InVzZXJJZCI6IjIifX0.ckLWl_LONTQ1Cga2QXUnahfQHD5zk34TnXaBaoGsTh8" \
-d '{
"role_name": "Manager123213",
"description": "Role for managing content and users",
"permissions": ["SEE CONTENT", "edit_content", "manage_users"]
}'

# Список всех ролей своей компании +

curl -X GET http://localhost/api/role/list \
-H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vIiwiYXVkIjoiaHR0cDovLyIsImlhdCI6MTczNDg5ODQ5NywiZXhwIjoxNzM0OTAyMDk3LCJkYXRhIjp7InVzZXJJZCI6IjMifX0.3tYyJjcxsfsrUYvceXmOGm1-pGMFRrrZw4J2eweOHSw"

# Список всех доступных пермишинов: +

curl -X GET http://localhost/api/role/getAllPermissions \
-H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vIiwiYXVkIjoiaHR0cDovLyIsImlhdCI6MTczMzIyMjUwNywiZXhwIjoxNzMzMjI2MTA3LCJkYXRhIjp7InVzZXJJZCI6IjIifX0.wEy2WLrj24mvWZh-x7_EYXHjXWqXGSAA3R3oF8swPQE"

# Назначить роль пользователю: +

curl -X POST http://localhost/api/role/assign \
-H "Content-Type: application/json" \
-H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vIiwiYXVkIjoiaHR0cDovLyIsImlhdCI6MTczNDM0MDAzMywiZXhwIjoxNzM0MzQzNjMzLCJkYXRhIjp7InVzZXJJZCI6IjIifX0.qYEQ8mEswkvgEe973uKxCj1ZCxfAySBKUOXhOgZ2iuo" \
-d '{"target_user_id": "3", "role_id": "6"}'

# Назначить пермишин роли:

curl -X POST http://localhost/api/role/assignPermissions \
-H "Content-Type: application/json" \
-H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vIiwiYXVkIjoiaHR0cDovLyIsImlhdCI6MTczNDUxMjUxMCwiZXhwIjoxNzM0NTE2MTEwLCJkYXRhIjp7InVzZXJJZCI6IjIifX0.WuKPYGTvLDd6acZ7NYowfhDSIbBm9gBjk4OAPL9O-30" \
-d '{
"role_id": 2,
"permissions": ["SEE CONTENT", "EDYT CONTENT"]
}'

# Удалить роль

curl -X POST "http://localhost/api/role/delete" \
-H "Content-Type: application/json" \
-H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vIiwiYXVkIjoiaHR0cDovLyIsImlhdCI6MTczMTc2NjEzOSwiZXhwIjoxNzMxNzY5NzM5LCJkYXRhIjp7InVzZXJJZCI6IjIifX0.6l8uFAF1_Lq6GESRwdOOQj0JRC-oHi-I1Yzj3QgR2_Q" \
-d '{"role_id": 3}'

# Детали роли

curl -X POST "http://localhost/api/role/details" \
-H "Content-Type: application/json" \
-H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vIiwiYXVkIjoiaHR0cDovLyIsImlhdCI6MTczNDUxMzU1OSwiZXhwIjoxNzM0NTE3MTU5LCJkYXRhIjp7InVzZXJJZCI6IjIifX0.Mzc84uBoLWP9-l410OOeFkdsquEr8gaRJ58gyyEEEak" \
-d '{"role_id": 2}'

# Поулчить инфо о текщем пользватели ФИО (Поменял на метод на GET)

curl -X POST http://localhost/api/user/current -H "Authorization: Bearer $token"

# Обновить или занести фоп 2 доп параметр при обновлении "FOP3_Taxes_id": 8,

# если не нету FOP2_Taxes_id значит занести

curl -X POST http://localhost/api/tax/insertF2 \
 -H "Content-Type: application/json" \
 -H "Authorization: Bearer $token" \
 -d '{
"FOP2_Taxes_id" : 25,
"FOP3_Taxes_id" : null,
"Month": 1,
"Quarter": 1,
"Year": 2024,
"Default_ZP": 3000.00,
"eGRPOUId": 1231231231,
"EN_Tax_Rate": 18.5,
"ESV_Rate": 20.0
}'

# Тут поменялось

# Обновить или занести фоп 3 доп параметр при обновлении "FOP3_Taxes_id": 8,

# если не нету FOP3_Taxes_id значит занести

curl -X POST http://localhost/api/tax/insertF3 \
 -H "Content-Type: application/json" \
 -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vIiwiYXVkIjoiaHR0cDovLyIsImlhdCI6MTcwNjY5MzQ2OSwiZXhwIjoxNzA2Njk3MDY5LCJkYXRhIjp7InVzZXJJZCI6IjIifX0.wGis2M3rdig2eeygYv9MchySdiWP8T5wL0azwzr3szc" \
 -d '{
"Month": 5,
"Quarter": 2,
"Year": 2024,
"Default_ZP": 8000.00,
"eGRPOUId": 1231231231,
"EN_Tax_Rate": 18.5,
"ESV_Rate": 20.0
}'

# Тут поменялось

# Получить общую сумму налога предприятия по ИД

curl -X GET "http://localhost/api/tax/calculate?eGRPOUId=1231231231" -H "Authorization: Bearer $token"

# Получить список собственых предпиятий (Уже изменил на ЕДРПО)

curl -X GET "http://localhost/api/enterprise/all" -H "Authorization: Bearer $token"

# Проучиь разчет по налогам включая даты

curl -X GET "http://localhost/api/tax/calculate?enterpriseId=1&startDate=2023-01-01&endDate=2024-01-31" -H "Authorization: Bearer $token"

# Получение списка интеграций:

curl -X GET "http://localhost/api/integration/list" -H "Authorization: Bearer $token"

# Добавление новой интеграции:

curl -X POST "http://localhost/api/integration/add" \
 -H "Content-Type: application/json" \
 -H "Authorization: Bearer $token" \
 -d '{"integrationType": "Torgsoft", "settings": {"host": "example.com", "port": "1234"}}'

# Обновление интеграции:

curl -X PUT "http://localhost/api/integration/update" -H "Content-Type: application/json" -H "Authorization: Bearer YOUR_TOKEN" -d '{"integrationId": "1", "settings": {"host": "newexample.com", "port": "4321"}}'

# Удаление интеграции:

curl -X DELETE "http://localhost/api/integration/delete" -H "Content-Type: application/json" -H "Authorization: Bearer YOUR_TOKEN" -d '{"integrationId": "1"}'

# Синхронизация с апи торгхоста по клику

curl -X POST "http://localhost/api/torgsoft/syncEnterprises" -H "Authorization: Bearer $token"

# Получение складских документов

curl -X POST "http://localhost/api/torgsoft/syncInvoices" -H "Authorization: Bearer $token"

# Получение финансовых документов

## curl -X POST "http://localhost/api/torgsoft/syncFinancialDocuments" -H "Authorization: Bearer $token"

# Получить квед по ЕДРПО КОМПАНИИ

curl -X POST -H "Authorization: Bearer $token" -H "Content-Type: application/json" -d '{
"eGRPOUId": "1231231231"
}' http://localhost/api/kved/get

# Создать КВЕД

curl -X POST -H "Authorization: Bearer $token" -H "Content-Type: application/json" -d '{
"number": "123453",
"name": "Sample KVED2",
"main": 1,
"eGRPOUId": "9999999"
}' http://localhost/api/kved/add

# Удалить кведы масив ИД + ЕДРПО

curl -X DELETE \
 -H "Authorization: Bearer $token" \
 -H "Content-Type: application/json" \
 -d '{"eGRPOUId": "1231231231", "kved_id":["33", "34"]}' \
 http://localhost/api/kved/delete

---

# Стандратные Групы счетов

# Получить все групы стандартных счетво

# Получить групы счетов

curl -X GET "http://localhost/api/DefoultAccountGroup/getAllStandardAccountGroup" -H "Authorization: Bearer $token" -H "Content-Type: application/json"

# Добавить групы счетов

curl -X POST -H "Authorization: Bearer $token" -H "Content-Type: application/json" -d '{
"name": "Sample Standard Account Group",
"isDeleted": 0
}' http://localhost/api/DefoultAccountGroup/addStandardAccountGroup

# Обновить групы счетов

curl -X POST -H "Authorization: Bearer $token" -H "Content-Type: application/json" -d '{
"id": "68",
"name": "Sample Standard Account Gro22up",
"isDeleted": 0
}' http://localhost/api/DefoultAccountGroup/updateStandardAccountGroup

# Удалить групы счетов

curl -X POST -H "Authorization: Bearer $token" -H "Content-Type: application/json" -d '{
"id": "70"
}' http://localhost/api/DefoultAccountGroup/deleteStandardAccountGroup

---

# Стандратные планы счетов

# Получить план счетов

curl -X GET "http://localhost/api/DefoultAccountPlan/getAllStandardAccountsInPlan" -H "Authorization: Bearer $token" -H "Content-Type: application/json"

# Добавить планы счетов

curl -X POST -H "Authorization: Bearer $token" -H "Content-Type: application/json" -d '{
"code": "001",
"name": "Группа счетов 001",
"type": 1,
"unbalanced": 0,
"quantitative": 0,
"currency": 0,
"isDeleted": 0
}' http://localhost/api/DefoultAccountPlan/addStandardAccountPlan

# Обновить планы счетов

curl -X POST -H "Authorization: Bearer $token" -H "Content-Type: application/json" -d '{
"id": "68",
"code": "001",
"name": "План счетов 001",
"type": 1,
"unbalanced": 0,
"quantitative": 0,
"currency": 0,
"isDeleted": 0
}' http://localhost/api/DefoultAccountPlan/updateStandardAccountPlan

# Удалить планы счетов

curl -X POST -H "Authorization: Bearer $token" -H "Content-Type: application/json" -d '{
"id": "70"
}' http://localhost/api/DefoultAccountPlan/deleteStandardAccountPlan

---

# Планы счетов

# Добавить план счетов

curl -X POST \
 -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vIiwiYXVkIjoiaHR0cDovLyIsImlhdCI6MTcwOTIyMzkwMywiZXhwIjoxNzA5MjI3NTAzLCJkYXRhIjp7InVzZXJJZCI6IjIifX0.A_OEzBqavuFjrYByaJA7Jbx6EXHU5Fbv7zr7QrV7HVA" \
 -H "Content-Type: application/json" \
 -d '{
"EGRPOUId": 9999999,
"Code": 123,
"Name": "Тест",
"Type": "АП",
"NonBalance": 1,
"Quantity": 1,
"Currency": 1,
"AccruedOrRecognized": 1,
"VATPurpose": 1,
"AccruedAmount": 1,
"Subaccount1": "Борода",
"Subaccount2": "Лопата",
"Subaccount3": "Мопед",
"IsDeleted": 0
}' \
 http://localhost/api/EnterpriseAccountPlans/addEnterpriseAccountPlan

# Получить планы счетов которые относятся к предприятию (Я НЕ ДОБАВИЛ ПРОВЕРКУ Я ЭТОМ ПРЕДПРИЯТИИ ИЛИ НЕТ)

curl -X POST -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vIiwiYXVkIjoiaHR0cDovLyIsImlhdCI6MTcxMTQ4MjQ0MywiZXhwIjoxNzExNDg2MDQzLCJkYXRhIjp7InVzZXJJZCI6IjIifX0.kEtbnp61f5Va9WFQxOEEptRje7TjyMcOiS0AgnN3jkA" -H "Content-Type: application/json" -d '{
"eGRPOUId": "9999999"
}' http://localhost/api/EnterpriseAccountPlans/getAllEnterpriseAccountPlans

# Ковергентность по ид пользвателя

curl -X POST \
 http://localhost/api/user/switchToUser \
 -H 'Content-Type: application/json' \
 -H 'Authorization: Bearer $token' \
 -d '{
"userId": 123
}'

# ----------------------- Номенклатура ----------------

# Создать

curl -X POST http://localhost/api/nomenclature/create \
 -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8iLCJhdWQiOiJodHRwOlwvXC8iLCJpYXQiOjE3Mzg2NjQyODgsImV4cCI6MTczODY2Nzg4OCwiZGF0YSI6eyJ1c2VySWQiOiIyIn19.7_GchrJWNKhGEfoQCKF1ApF_5HhgCiGFqnfeOTgxO98" \
 -H "Content-Type: multipart/form-data" \
 -F "barcode=0101012312312321310" \
 -F "articleCode=010101" \
 -F "name=Номенклатура 2" \
 -F "group_name=Группа 2" \
 -F "type=Товар" \
 -F "unit_of_measurement=шт" \
 -F "description=Описание товара" \
 -F "category_id=20" \
 -F "specific_characteristics={\"цвет\":\"синий\",\"вес\":\"5 кг\"}" \
 -F "dimension_ranges=[]" \
 -F "images[]=@/home/dima/Desktop/DSC_7545.png" \
 -F "images[]=@/home/dima/Desktop/DSC_7545.png"

curl -X POST http://localhost/api/nomenclature/create \
 -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8iLCJhdWQiOiJodHRwOlwvXC8iLCJpYXQiOjE3Mzg2NjA1MTIsImV4cCI6MTczODY2NDExMiwiZGF0YSI6eyJ1c2VySWQiOiIyIn19.UNlee3CrV1h4QEW8EybzLLzV6t_RuSe8exCCFhh3dPE" \
 -H "Content-Type: multipart/form-data" \
 -F "barcode=DesmiseBar" \
 -F "articleCode=BAаSеE" \
 -F "name=Кроссовки" \
 -F "group_name=Обувь" \
 -F "type=товар" \
 -F "unit_of_measurement=пара" \
 -F "description=Стильные кроссовки для повседневного ношения." \
 -F "category_id=5" \
 -F "specific_characteristics={\"Материал\":\"Кожа\",\"Производитель\":\"Nike\"}" \
 -F "dimension_ranges=[{\"name\":\"Размер\",\"values\":[\"42\",\"43\",\"44\"]},{\"name\":\"Цвет\",\"values\":[\"Красный\"]}]" \
 -F "images[]=@/home/dima/Desktop/DSC_7545.png" \
 -F "images[]=@/home/dima/Desktop/DSC_7545.png"

# ---------

# Изменить

curl -X PUT http://localhost/api/nomenclature/editNomenclature \
 -H "Content-Type: application/json" \
 -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vIiwiYXVkIjoiaHR0cDovLyIsImlhdCI6MTczNTk4MjQyNywiZXhwIjoxNzM1OTg2MDI3LCJkYXRhIjp7InVzZXJJZCI6IjIifX0.Rc0aLWg_dYewJ94Eu-DeAzCeuaA3hq4qVRi6XopGO58" \
 -d '{
"id": 26,
"articleCode": "67890",
"barcode": "1234567890123",
"name": "ТЕСТ ТЕТСОВЫЙ 3333",
"group_name": "Обувь",
"type": "товар",
"unit_of_measurement": "шт",
"description": "Классическая мужская обувь",
"specific_characteristics": "{\"brand\": \"Adidas\", \"size\": \"44\", \"color\": \"белый\"}"
}'

# Удалить

curl -X DELETE http://localhost/api/nomenclature/deleteNomenclature \
-H "Content-Type: application/json" \
-H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
-d '{
"nomenclature_id": 1
}'

# Удалить фотографию номенклатури

curl -X PUT http://localhost/api/nomenclature/deleteNomenclatureImage \
 -H "Content-Type: application/json" \
 -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vIiwiYXVkIjoiaHR0cDovLyIsImlhdCI6MTczNTk4MjQyNywiZXhwIjoxNzM1OTg2MDI3LCJkYXRhIjp7InVzZXJJZCI6IjIifX0.Rc0aLWg_dYewJ94Eu-DeAzCeuaA3hq4qVRi6XopGO58" \
 -d '{
"image_id": 123,
"nomenclature_id": 456
}'

# Получить все своей компании

curl -X GET http://localhost/api/nomenclature/listNomenclatures \
-H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8iLCJhdWQiOiJodHRwOlwvXC8iLCJpYXQiOjE3Mzg3NTkxOTAsImV4cCI6MTczODc2Mjc5MCwiZGF0YSI6eyJ1c2VySWQiOiIyIn19.XsKR0ZPBew_6NERMVWZ8WpY9kXLCNkD1juNhHdmnpqk"

# Получить по ид

curl -X GET "http://localhost/api/nomenclature/getById?id=141" \
 -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8iLCJhdWQiOiJodHRwOlwvXC8iLCJpYXQiOjE3MzgyMzU0NjYsImV4cCI6MTczODIzOTA2NiwiZGF0YSI6eyJ1c2VySWQiOiIyIn19.lY5w5-M8eia68npcqsuGve9Rzr8oJ-TWmUEu5rs_SmQ" \
 -H "Content-Type: application/json"

# Фото номелкатуры

curl -X GET "http://localhost/api/nomenclature/getImage?path=/var/www/erp/upload/1/img_67a1e004eda84_img_67a1e004282308.97429329.png" \
 -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8iLCJhdWQiOiJodHRwOlwvXC8iLCJpYXQiOjE3Mzg2NjIxMzQsImV4cCI6MTczODY2NTczNCwiZGF0YSI6eyJ1c2VySWQiOiIyIn19.lptTniPWBEvlPa2sjS3pFAnzreAXIEGdFJFwttIG02w" \
 -H "Content-Type: application/json" \
 --output downloaded_image.jpg

# ---------------------Категории ------------------------с

# Создать

curl -X POST http://localhost/api/category/createCategory \
-H "Content-Type: application/json" \
-H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8iLCJhdWQiOiJodHRwOlwvXC8iLCJpYXQiOjE3Mzk0NDYzNzQsImV4cCI6MTczOTQ0OTk3NCwiZGF0YSI6eyJ1c2VySWQiOiIyIn19.JIt6nQU-ibpd4d72ED9hdrK86cya3MJv7T0gLSSbl54" \
-d '{
"includedInName": true,
"askQuantityOnSale": true,
"name": "Взуття",
"description": "Взуття зимнє",
"markupFromPrice": true,
"maxDiscountSet": true,
"extraChargeSet": true,
"priceRounding": "До найближчого цілого",
"roundingMinusOne": true,
"discountPercent": "6",
"markupWholesale": "6",
"markupPercent": "6",
"fiscal": true,
"excise": true,
"vatApplicable": true,
"vatRateCode": "0%",
"benefitCode": "Без пільг",
"vatExemptionReason": "Експорт",
"minOrderQuantity": "5",
"barcodeContainsQuantity": true,
"barcodeQuantityCoefficient": "7",
"articleCode": "65656651156"
}'

# Изменить

curl -X POST http://localhost/api/category/editCategory \
-H "Content-Type: application/json" \
-H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8iLCJhdWQiOiJodHRwOlwvXC8iLCJpYXQiOjE3Mzk0NjM2OTQsImV4cCI6MTczOTQ2NzI5NCwiZGF0YSI6eyJ1c2VySWQiOiIyIn19.15bDiB2WcPZ48y_1B0Kv9niWqWJAr03igNi_Bc7OIa4" \
-d '{
"id": "63",
"category_id": "63",
"name": "Взуття444444",
"description": "Взуття зимнє",
"includedInName": "0",
"askQuantityOnSale": "0",
"markupFromPrice": "0",
"maxDiscountSet": "0",
"extraChargeSet": "0",
"priceRounding": "Без округлення",
"roundingMinusOne": "0",
"discountPercent": "6",
"markupWholesale": "6",
"markupPercent": "4",
"fiscal": "0",
"excise": "0",
"vatApplicable": "0",
"vatRateCode": "0%",
"benefitCode": "Без пільг",
"vatExemptionReason": "Експорт",
"minOrderQuantity": "5",
"barcodeContainsQuantity": "0",
"barcodeQuantityCoefficient": "13",
"articleCode": "651156156"
}'

# Удалить

curl -X DELETE http://localhost/api/category/deleteCategory \
-H "Content-Type: application/json" \
-H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
-d '{
"category_id": 1
}'

# Получить список

curl -X GET http://localhost/api/category/listCategories \
-H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8iLCJhdWQiOiJodHRwOlwvXC8iLCJpYXQiOjE3Mzk4Njk1MDcsImV4cCI6MTczOTg3MzEwNywiZGF0YSI6eyJ1c2VySWQiOiIyIn19.GkbWkqCHKJNlI4Mxxa003sB6Uex9WvATobElv2dvYsM"

# Получить по ид

curl -X POST "http://localhost/api/category/getCategoryById" \
 -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8iLCJhdWQiOiJodHRwOlwvXC8iLCJpYXQiOjE3MzgyMzU0NjYsImV4cCI6MTczODIzOTA2NiwiZGF0YSI6eyJ1c2VySWQiOiIyIn19.lY5w5-M8eia68npcqsuGve9Rzr8oJ-TWmUEu5rs_SmQ" \
 -H "Content-Type: application/json" \
 -d '{"id": 1}'

# ----------------------- Характеристики категорий -------------

# Добавить

curl -X POST http://localhost/api/category/addCharacteristic \
-H "Content-Type: application/json" \
-H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vIiwiYXVkIjoiaHR0cDovLyIsImlhdCI6MTczNTU3MDY1NSwiZXhwIjoxNzM1NTc0MjU1LCJkYXRhIjp7InVzZXJJZCI6IjIifX0.un88CJlRj7Ri7Ztcru7qaOCha8xljHF2JD5218qQm7c" \
-d '{
"category_id": 2,
"characteristics": [
{
"characteristic_name": "333",
"characteristic_type": "select",
"options": [
"1",
"2"
]
},
{
"characteristic_name": "dfgfdh",
"characteristic_type": "select",
"options": [
"3",
"4"
]
}
]
}'

# Получить

curl -X GET "http://localhost/api/category/getCharacteristics?category_id=1" \
-H "Content-Type: application/json" \
-H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vIiwiYXVkIjoiaHR0cDovLyIsImlhdCI6MTczNTg0MTQ4OSwiZXhwIjoxNzM1ODQ1MDg5LCJkYXRhIjp7InVzZXJJZCI6IjIifX0.ASBXjhilkmbCz0aizpDZdCQkEf0Bzx_eYr6YxEFpr7M"

# -----------------Размерные ряды ---------------

# Создать

curl -X POST http://localhost/api/dimension_range/create \
 -H "Content-Type: application/json" \
 -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vIiwiYXVkIjoiaHR0cDovLyIsImlhdCI6MTczNTgxMzk4NCwiZXhwIjoxNzM1ODE3NTg0LCJkYXRhIjp7InVzZXJJZCI6IjIifX0.ppLeTULPTyNNauYtFalF7MuTm7Tv-0qAkw4-BGZTsds"
-d '{
"category_characteristic_id": 1,
"name": "Размер обуви",
"description": "Размеры обуви в Евро"
}'

# Добавить значения для ряда

curl -X POST http://localhost/api/dimension_range/add \
 -H "Content-Type: application/json" \
 -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vIiwiYXVkIjoiaHR0cDovLyIsImlhdCI6MTczNTgxMzk4NCwiZXhwIjoxNzM1ODE3NTg0LCJkYXRhIjp7InVzZXJJZCI6IjIifX0.ppLeTULPTyNNauYtFalF7MuTm7Tv-0qAkw4-BGZTsds" \
 -d '{
"dimension_range_id": 1,
"value": "42",
"sort_order": 10
}'

# Получение Размерных Рядов по Категории

curl -X GET "http://localhost/api/dimension_range/get?category_characteristic_id=1" \
 -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8iLCJhdWQiOiJodHRwOlwvXC8iLCJpYXQiOjE3MzgwNTYyOTgsImV4cCI6MTczODA1OTg5OCwiZGF0YSI6eyJ1c2VySWQiOiIyIn19.aspsYe3OPBNU9rajgYha1JQ1NM2QuS3s0dxNuC3Fs90"

# ----------------- Склади ---------------

# Получить по ид

curl -X POST "http://localhost/api/warehouse/getWarehouseById" \
 -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8iLCJhdWQiOiJodHRwOlwvXC8iLCJpYXQiOjE3MzgyMzU0NjYsImV4cCI6MTczODIzOTA2NiwiZGF0YSI6eyJ1c2VySWQiOiIyIn19.lY5w5-M8eia68npcqsuGve9Rzr8oJ-TWmUEu5rs_SmQ" \
 -H "Content-Type: application/json" \
 -d '{"id": 1}'

# Получить список

curl -X GET http://localhost/api/warehouse/listWarehouses \
-H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8iLCJhdWQiOiJodHRwOlwvXC8iLCJpYXQiOjE3MzgwNTYzMzgsImV4cCI6MTczODA1OTkzOCwiZGF0YSI6eyJ1c2VySWQiOiIyIn19.4RZeJVS3EpLPg3J21Mz3Pb0jTtT8r0dRW_8AKap7ItM"
