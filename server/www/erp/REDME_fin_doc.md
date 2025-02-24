# Документация API "Finances"

Документация описывает API для получения информации о финансовых документах.

## Содержание

-   [Получение списка финансовых документов](#получение-списка-финансовых-документов)
-   [Список удаленных финансовых документов](#список-удаленных-финансовых-документов)

## Получение списка финансовых документов

Информация о финансовых документах доступна через API.

### Запрос информации о финансовых документах

-   **Метод:** POST
-   **URL:** `/api/Fin/GetFiscalFinDocs`
-   **Параметры запроса:**
    -   `date1` (DateTime): Начало периода.
    -   `date2` (DateTime): Конец периода.
    -   curl -X POST http://10.3.0.36:5000/api/Fin/GetFiscalFinDocs -H "Content-Type: application/json" -d "{}"

#### Описание возвращаемых данных

Данные включают информацию о финансовых документах, включая их идентификаторы, даты, суммы и другие детали.

### Структура возвращаемых данных

-   `financialDocumentId` (int): Уникальный ключ документа.
-   `theDate` (DateTime): Дата.
-   `number` (int): Номер.
-   `sumMoney` (decimal): Сумма.
-   `moveCategory` (FinancialDocumentMoveCategoryEnum): Категория (приходный или расходный).
-   `theType` (FinancialDocumentTheTypeEnum): Тип (банк или касса).
-   `partner` (PartnerDto, опционально): Постачальник.
-   `currencyId` (int, опционально): Валюта.
-   `analysisCipher` (AnalisysChipherDto, опционально): Шифр анализа.

## Список удаленных финансовых документов

API также предоставляет информацию о удаленных финансовых документах.

### Получение списка удаленных документов

-   **Метод:** POST
-   **URL:** `/api/Fin/GetDeletedFinancialDocuments`
-   **Параметры запроса:**
    -   `date1` (DateTime, опционально): Дата начала периода.
    -   `date2` (DateTime, опционально): Дата окончания периода.

#### Структура возвращаемых данных

-   `id` (int): Уникальный ключ документа.
-   `deletionDate` (DateTime): Дата удаления.

-- Создание таблицы Enterprises
CREATE TABLE `Enterprises` (
`enterpriseId` INT NOT NULL,
`name` VARCHAR(255) NOT NULL,
`eGRPOU` VARCHAR(50),
`individualTaxNumber` VARCHAR(50),
PRIMARY KEY (`enterpriseId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Создание таблицы Invoices
CREATE TABLE `WarehouseDocuments (
  `id`INT NOT NULL,
 `enterpriseId`INT NOT NULL,
 `theDate`DATETIME NOT NULL,
 `number`INT NOT NULL,
 `theForm`ENUM('приход', 'расход', 'другое') NOT NULL,
 `fromPartnerId`INT,
 `toPartnerId`INT,
 `total`DECIMAL(10,2) NOT NULL,
 `vatTotal`DECIMAL(10,2),
 `exciseTaxTotal`DECIMAL(10,2),
 `currencyId` INT,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`enterpriseId`) REFERENCES Enterprises(`enterpriseId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Создание таблицы FinancialDocuments
CREATE TABLE `FinancialDocuments` (
`financialDocumentId` INT NOT NULL,
`theDate` DATETIME NOT NULL,
`number` INT NOT NULL,
`sumMoney` DECIMAL(10,2) NOT NULL,
`moveCategory` ENUM('приходный', 'расходный') NOT NULL,
`theType` ENUM('банк', 'касса') NOT NULL,
`partnerId` INT,
`currencyId` INT,
PRIMARY KEY (`financialDocumentId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Добавление демо данных в таблицу FinancialDocuments
INSERT INTO FinancialDocuments (financialDocumentId, theDate, number, sumMoney, moveCategory, theType, partnerId, currencyId) VALUES
(1, '2023-11-01', 201, 5000.00, 'Приход', 'Банк', 1, 1),
(2, '2023-11-02', 202, 3000.00, 'Расход', 'Касса', 2, 1);
