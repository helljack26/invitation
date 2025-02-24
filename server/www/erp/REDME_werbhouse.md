#

curl -X POST http://10.3.0.36:5000/api/Enterprise/GetAllEnterprises -H "Content-Type: application/json" -d "{}"

# Моя таблица

CREATE TABLE `warehouse_documents` (
`id` INT(11) NOT NULL AUTO_INCREMENT,
`enterpriseId` INT(11) NOT NULL,
`theDate` DATETIME NOT NULL,
`documentNumber` VARCHAR(100) NOT NULL,
`theForm` VARCHAR(100) NOT NULL,
`fromPartner` VARCHAR(255) DEFAULT NULL,
`toPartner` VARCHAR(255) DEFAULT NULL,
`total` DECIMAL(10,2) DEFAULT NULL,
`vatTotal` DECIMAL(10,2) DEFAULT NULL,
`exciseTaxTotal` DECIMAL(10,2) DEFAULT NULL,
`currencyId` INT(11) DEFAULT NULL,
`createDateTime` DATETIME NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Документация API "Invoices"

Документация описывает API для получения информации о складских документах, предприятиях и валютах.

## Содержание

-   [Информация о предприятиях](#информация-о-предприятиях)
-   [Получение списка складских документов](#получение-списка-складских-документов)
-   [Список валют](#список-валют)
-   [Список удаленных складских документов](#список-удаленных-складских-документов)

## Информация о предприятиях

В системе Торгсофт любой документ может принадлежать предприятию. В зависимости от режима учета, документы могут быть связаны или не связаны с предприятием.

### Получение списка предприятий

-   **Метод:** POST
-   **URL:** `/api/Enterprise/GetAllEnterprises`
-   **Возвращаемые данные:** Список описаний предприятий.
-   curl -X POST http://10.3.0.36:5000/api/Enterprise/GetAllEnterprises -H "Content-Type: application/json" -d "{}"

#### Описание объекта предприятия

-   `enterpriseId` (int): Уникальный ключ.
-   `name` (string): Название.
-   `eGRPOU` (string, опционально): ЄДРПОУ.
-   `individualTaxNumber` (string, опционально): Индивидуальный налоговый номер.

## Получение списка складских документов

Информация о складских документах доступна через API.

### Запрос информации о документах

-   **Метод:** POST
-   **URL:** `/api/Invoce/GetInvoices`
-   **Параметры запроса:**
    -   `date1` (DateTime): Начало периода (включительно).
    -   `date2` (DateTime): Конец периода (включительно).
    -   `enterpriseId` (int, опционально): Предприятие, для которого был создан документ.
    -   `isPayVAT` (bool): Является ли предприятие плательщиком НДС.
    -   curl -X POST http://10.3.0.36:5000/api/Invoce/GetInvoices -H "Content-Type: application/json" -d "{}"

#### Описание возвращаемых данных

Данные описывают структуру складского документа, включая информацию о Постачальниках, суммах и других деталях.

### Структура возвращаемых данных

-   `id` (int): Уникальный ключ документа.
-   `theDate` (DateTime): Дата документа в системе.
-   `number` (int): Номер.
-   `theForm` (InvoiceTheFormEnum): Тип документа.
-   `fromPartner` (PartnerDto): Постачальник отправитель.
-   `toPartner` (PartnerDto): Постачальник получатель.
-   `total` (decimal): Сумма.
-   `vatTotal` (decimal, опционально): Сумма НДС.
-   `exciseTaxTotal` (decimal, опционально): Сумма акциза.
-   `currencyId` (int, опционально): Валюта.
-   `createDateTime` (DateTime): Дата и время создания.

## Список валют

API предоставляет информацию о доступных валютах.

### Получение списка валют

-   **Метод:** POST
-   **URL:** `/api/Currency/GetCurrencyList`
-   **Возвращаемые данные:** Список описаний валют.

#### Структура возвращаемых данных

-   `currencyId` (int): Идентификатор валюты.
-   `name` (string): Название.
-   `numericCode` (string, опционально): Цифровой код.
-   `internationalCurrencyCode` (string, опционально): Международное обозначение.

## Список удаленных складских документов

API также предоставляет информацию о удаленных складских документах.

### Получение списка удаленных документов

-   **Метод:** POST
-   **URL:** `/api/Invoce/GetDeletedInvoices`
-   **Параметры запроса:**
    -   `date1` (DateTime, опционально): Дата начала периода.
    -   `date2` (DateTime, опционально): Дата окончания периода.

#### Структура возвращаемых данных

-   `id` (int): Уникальный ключ документа.
-   `deletionDate` (DateTime): Дата удаления.
    "TorgsoftConnection": "Data Source=;Database=TsDebug;User ID=sa;Password=1;MultipleActiveResultSets=true;TrustServerCertificate=True"
