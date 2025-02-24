-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: mysql
-- Время создания: Янв 05 2025 г., 09:52
-- Версия сервера: 8.0.40
-- Версия PHP: 8.2.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `bill_manager_c`
--

-- --------------------------------------------------------

--
-- Структура таблицы `AccountContacts`
--

CREATE TABLE `AccountContacts` (
  `id` int NOT NULL,
  `account_id` int NOT NULL,
  `contact_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Дамп данных таблицы `AccountContacts`
--

INSERT INTO `AccountContacts` (`id`, `account_id`, `contact_id`) VALUES
(1, 1, 16);

-- --------------------------------------------------------

--
-- Структура таблицы `Accounts`
--

CREATE TABLE `Accounts` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `industry` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `website` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone_office` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `billing_address_street` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `billing_address_city` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `billing_address_state` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `billing_address_postalcode` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `billing_address_country` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `shipping_address_street` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `shipping_address_city` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `shipping_address_state` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `shipping_address_postalcode` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `shipping_address_country` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `id_user_created` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `assigned_to_user_id` int DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `deleted` int DEFAULT '0',
  `user_delete` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `update_user` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Дамп данных таблицы `Accounts`
--

INSERT INTO `Accounts` (`id`, `name`, `industry`, `website`, `phone_office`, `billing_address_street`, `billing_address_city`, `billing_address_state`, `billing_address_postalcode`, `billing_address_country`, `shipping_address_street`, `shipping_address_city`, `shipping_address_state`, `shipping_address_postalcode`, `shipping_address_country`, `description`, `id_user_created`, `created_at`, `updated_at`, `assigned_to_user_id`, `email`, `deleted`, `user_delete`, `update_user`) VALUES
(1, 'Компания 1', 'Индустрия 1', 'http://www.company1.com', '+123456789', 'Улица 1', 'Город 1', 'Область 1', '12345', 'Страна 1', 'Адрес доставки 1', 'Город доставки 1', 'Область доставки 1', '54321', 'Страна доставки 1', 'Описание компании 1', 0, '2023-12-05 15:10:19', '2024-01-12 09:49:47', 0, NULL, 0, NULL, NULL),
(2, 'Компания 2', 'Индустрия 2', 'http://www.company2.com', '+987654321', 'Улица 2', 'Город 2', 'Область 2', '67890', 'Страна 2', 'Адрес доставки 2', 'Город доставки 2', 'Область доставки 2', '09876', 'Страна доставки 2', 'Описание компании 2', 0, '2023-12-05 15:10:19', '2024-01-12 09:49:48', 0, NULL, 0, NULL, NULL),
(20, 'Название компании', 'Отрасль', 'http://www.example.com', '1234567890', 'Улица', 'Город', 'Регион', 'Почтовый индекс', 'Страна', 'Улица (доставка)', 'Город (доставка)', 'Регион (доставка)', '54321', 'Страна (доставка)', 'Описание компании', 2, '2023-12-06 13:40:56', '2024-01-12 09:49:49', 0, NULL, 0, NULL, NULL),
(21, 'Название компании', 'Отрасль', 'http://www.example.com', '1234567890', 'Улица', 'Город', 'Регион', 'Почтовый индекс', 'Страна', 'Улица (доставка)', 'Город (доставка)', 'Регион (доставка)', '54321', 'Страна (доставка)', 'Описание компании', 2, '2023-12-06 13:46:26', '2024-01-12 09:49:50', 0, NULL, 0, NULL, NULL),
(22, 'Название компании', 'Отрасль', 'http://www.example.com', '1234567890', 'Улица', 'Город', 'Регион', 'Почтовый индекс', 'Страна', 'Улица (доставка)', 'Город (доставка)', 'Регион (доставка)', '54321', 'Страна (доставка)', 'Описание компании', 2, '2023-12-08 15:20:48', '2024-01-12 09:49:50', 0, NULL, 0, NULL, NULL),
(23, 'Нова назва-0', '2', 'http://www.newexample.com', '0625856633', 'Новая улица444', 'Новый городккк', 'Новый регион', '123123', 'Новая страна', 'Новая улица (доставка)', 'Новый город (доставка)', 'Новый регион (доставка)', '12345', 'Новая страна (доставка)', 'Новое описание компании', 2, '2023-12-08 15:24:47', '2024-01-24 11:30:29', 2, 'test@email.com', 0, NULL, 2),
(24, 'Нова назва-1', '2', 'http://www.newexample.com', '0625856633', 'Новая улица2', 'Новый городккк2', 'Новый регион2', '123123', 'Новая страна2', 'Новая улица (доставка)2', 'Новый город (доставка)', 'Новый регион (доставка)', '12345', 'Новая страна (доставка)', 'Новое описание компании', 2, '2023-12-08 15:24:47', '2024-01-24 11:56:28', 2, 'test@email.com', 1, '2', 2),
(25, 'Нова назва-2', '2', 'http://www.newexample.com', '0625856633', 'Новая улица2', 'Новый городккк2', 'Новый регион2', '123123', 'Новая страна2', 'Новая улица (доставка)2', 'Новый город (доставка)', 'Новый регион (доставка)', '12345', 'Новая страна (доставка)', 'Новое описание компании', 2, '2023-12-08 15:24:47', '2024-01-24 11:30:50', 2, 'test@email.com', 1, '2', 2),
(26, 'Нова назва-3', '2', 'http://www.newexample.com', '0625856633', 'Новая улица2', 'Новый городккк2', 'Новый регион2', '123123', 'Новая страна2', 'Новая улица (доставка)2', 'Новый город (доставка)', 'Новый регион (доставка)', '12345', 'Новая страна (доставка)', 'Новое описание компании', 2, '2023-12-08 15:24:47', '2024-01-24 11:30:50', 2, 'test@email.com', 1, '2', 2),
(27, 'dsfgdjhfgdfj', 'ldkjnfglkxdfnb', 'dsfgdjhfgdfj', 'ddddddd', '', 'залупа', '', '', 'Залукраїна', '', '', '', '', '', '', 2, '2024-01-24 11:41:25', '2024-01-24 11:54:58', NULL, 'fkljnlkj@jkdf.dfg', 1, '2', 2),
(28, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 11:43:26', '2024-01-24 11:52:52', NULL, NULL, 1, '2', NULL),
(29, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 12:13:31', '2024-01-24 12:17:37', NULL, NULL, 1, '2', NULL),
(30, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 12:19:26', '2024-01-24 12:19:37', NULL, NULL, 1, '2', NULL),
(31, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 12:40:33', '2024-01-24 13:13:39', NULL, NULL, 1, '2', NULL),
(32, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 12:40:36', '2024-01-24 12:51:59', NULL, NULL, 1, '2', NULL),
(33, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 12:40:38', '2024-01-24 12:43:50', NULL, NULL, 1, '2', NULL),
(34, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 12:53:32', '2024-01-24 12:54:15', NULL, NULL, 1, '2', NULL),
(35, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 12:53:35', '2024-01-24 12:53:44', NULL, NULL, 1, '2', NULL),
(36, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 13:16:11', '2024-01-24 13:16:24', NULL, NULL, 1, '2', NULL),
(37, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 13:16:13', '2024-01-24 13:16:36', NULL, NULL, 1, '2', NULL),
(38, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 13:17:06', '2024-01-24 13:17:18', NULL, NULL, 1, '2', NULL),
(39, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 13:17:08', '2024-01-24 13:17:18', NULL, NULL, 1, '2', NULL),
(40, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 13:17:34', '2024-01-24 13:17:53', NULL, NULL, 1, '2', NULL),
(41, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 13:17:36', '2024-01-24 13:17:53', NULL, NULL, 1, '2', NULL),
(42, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 13:17:38', '2024-01-24 13:17:47', NULL, NULL, 1, '2', NULL),
(43, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 13:28:21', '2024-01-24 13:28:21', NULL, NULL, 0, NULL, NULL),
(44, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 13:28:54', '2024-01-24 15:22:36', NULL, NULL, 1, '2', NULL),
(45, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 13:29:37', '2024-01-24 14:50:19', NULL, NULL, 1, '2', NULL),
(46, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 13:29:44', '2024-01-24 14:50:19', NULL, NULL, 1, '2', NULL),
(47, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 13:29:46', '2024-01-24 14:50:19', NULL, NULL, 1, '2', NULL),
(48, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 13:29:47', '2024-01-24 14:50:19', NULL, NULL, 1, '2', NULL),
(49, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 13:29:49', '2024-01-24 14:50:19', NULL, NULL, 1, '2', NULL),
(50, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 13:30:08', '2024-01-24 14:50:19', NULL, NULL, 1, '2', NULL),
(51, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 13:33:22', '2024-01-24 14:50:12', NULL, NULL, 1, '2', NULL),
(52, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 13:39:52', '2024-01-24 14:50:13', NULL, NULL, 1, '2', NULL),
(53, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 13:40:07', '2024-01-24 14:50:12', NULL, NULL, 1, '2', NULL),
(54, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 13:40:19', '2024-01-24 14:50:12', NULL, NULL, 1, '2', NULL),
(55, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 14:09:23', '2024-01-24 14:50:12', NULL, NULL, 1, '2', NULL),
(56, 'dsfgdjhfgdfj2222', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 14:09:36', '2024-01-24 14:50:12', NULL, NULL, 1, '2', NULL),
(57, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 14:47:56', '2024-01-24 14:50:12', NULL, NULL, 1, '2', NULL),
(58, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 14:49:12', '2024-01-24 14:50:12', NULL, NULL, 1, '2', NULL),
(59, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 14:49:32', '2024-01-24 14:50:12', NULL, NULL, 1, '2', NULL),
(60, 'dsfgdjhfgdfj', 'dddddd', 'dsfgdjhfgdfj', 'ddddddd', 'dfgfd', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 14:50:23', '2024-01-24 15:22:36', NULL, 'dfgjh@djhf.dfdf', 1, '2', 2),
(61, 'xcglkjn', 'lkjdnfg', 'kljdnfglkjdnf', 'lkjnsdgfkj', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-01-24 15:20:47', '2024-01-24 15:22:36', NULL, NULL, 1, '2', NULL),
(62, 'dfgjhf', 'hdfghdfghdfgh', 'fghdfghdfg', 'dfghndfg', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-03-04 15:53:05', '2024-03-04 15:53:05', NULL, NULL, 0, NULL, NULL),
(63, 'xdgf4', ';ldfjngldkfjng', 'ldkjfngldkjfng', 'ergfdfg', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-03-04 15:54:18', '2024-03-04 15:54:18', NULL, NULL, 0, NULL, NULL),
(64, '35ytrt', 'jk,shbdfg', 'dfsgdgf', 'fghfg', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-03-04 15:54:54', '2024-03-04 15:54:54', NULL, NULL, 0, NULL, NULL),
(65, 'fgh', 'dflkjgn', 'kjhbsdfgdf', 'fgh', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-03-04 15:56:57', '2024-03-04 16:11:26', NULL, 'dfgfd@kjdf.dfdf', 0, NULL, 2),
(66, 'dfhgfgh', 'lkjnlk', 'jhbkjbkjb', 'lkjbjlkhb', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-03-04 15:57:41', '2024-03-04 16:07:51', NULL, NULL, 1, '2', NULL),
(67, 'fdsghfg', 'lkjhb', 'kjhb', 'jkhb', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-03-04 15:58:32', '2024-03-04 16:07:51', NULL, NULL, 1, '2', NULL),
(68, 'we5tyufh', 'kjhb', 'kjhbkjb', 'jhsbgfdjkh', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-03-04 15:59:13', '2024-03-04 16:07:51', NULL, NULL, 1, '2', NULL),
(69, 'dghffgh;l@', 'lkhjxfg', 'kjhb', 'kjhb', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-03-04 15:59:55', '2024-03-04 16:07:51', NULL, NULL, 1, '2', NULL),
(70, 'dfhgfdgh', 'kj', 'kjhnkjnbk', 'kjb', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-03-04 16:00:40', '2024-03-04 16:06:56', NULL, NULL, 1, '2', NULL),
(71, 'edrerrytruhfg', 'lkjnfg', 'lkjndfgf', 'lkjnfg', '', '', '', '', '', '', '', '', '', '', '', 2, '2024-03-04 16:12:13', '2024-03-04 16:20:17', NULL, 'kjnsfdg@kljnd.fg', 0, NULL, 2);

-- --------------------------------------------------------

--
-- Структура таблицы `AccountsUsers`
--

CREATE TABLE `AccountsUsers` (
  `id` int NOT NULL,
  `account_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Дамп данных таблицы `AccountsUsers`
--

INSERT INTO `AccountsUsers` (`id`, `account_id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 23, 2, '2023-12-08 15:24:47', '2023-12-08 15:24:47'),
(2, 24, 2, '2023-12-08 15:24:47', '2023-12-08 15:24:47'),
(3, 25, 2, '2023-12-08 15:24:47', '2023-12-08 15:24:47'),
(4, 26, 2, '2023-12-08 15:24:47', '2023-12-08 15:24:47'),
(5, 27, 2, '2024-01-24 11:41:25', '2024-01-24 11:41:25'),
(6, 28, 2, '2024-01-24 11:43:26', '2024-01-24 11:43:26'),
(7, 29, 2, '2024-01-24 12:13:32', '2024-01-24 12:13:32'),
(8, 30, 2, '2024-01-24 12:19:26', '2024-01-24 12:19:26'),
(9, 31, 2, '2024-01-24 12:40:33', '2024-01-24 12:40:33'),
(10, 32, 2, '2024-01-24 12:40:36', '2024-01-24 12:40:36'),
(11, 33, 2, '2024-01-24 12:40:38', '2024-01-24 12:40:38'),
(12, 34, 2, '2024-01-24 12:53:32', '2024-01-24 12:53:32'),
(13, 35, 2, '2024-01-24 12:53:35', '2024-01-24 12:53:35'),
(14, 36, 2, '2024-01-24 13:16:11', '2024-01-24 13:16:11'),
(15, 37, 2, '2024-01-24 13:16:13', '2024-01-24 13:16:13'),
(16, 38, 2, '2024-01-24 13:17:06', '2024-01-24 13:17:06'),
(17, 39, 2, '2024-01-24 13:17:08', '2024-01-24 13:17:08'),
(18, 40, 2, '2024-01-24 13:17:34', '2024-01-24 13:17:34'),
(19, 41, 2, '2024-01-24 13:17:36', '2024-01-24 13:17:36'),
(20, 42, 2, '2024-01-24 13:17:38', '2024-01-24 13:17:38'),
(21, 43, 2, '2024-01-24 13:28:21', '2024-01-24 13:28:21'),
(22, 44, 2, '2024-01-24 13:28:54', '2024-01-24 13:28:54'),
(23, 45, 2, '2024-01-24 13:29:37', '2024-01-24 13:29:37'),
(24, 46, 2, '2024-01-24 13:29:44', '2024-01-24 13:29:44'),
(25, 47, 2, '2024-01-24 13:29:46', '2024-01-24 13:29:46'),
(26, 48, 2, '2024-01-24 13:29:48', '2024-01-24 13:29:48'),
(27, 49, 2, '2024-01-24 13:29:49', '2024-01-24 13:29:49'),
(28, 50, 2, '2024-01-24 13:30:08', '2024-01-24 13:30:08'),
(29, 51, 2, '2024-01-24 13:33:22', '2024-01-24 13:33:22'),
(30, 52, 2, '2024-01-24 13:39:52', '2024-01-24 13:39:52'),
(31, 53, 2, '2024-01-24 13:40:07', '2024-01-24 13:40:07'),
(32, 54, 2, '2024-01-24 13:40:19', '2024-01-24 13:40:19'),
(33, 55, 2, '2024-01-24 14:09:23', '2024-01-24 14:09:23'),
(34, 56, 2, '2024-01-24 14:09:36', '2024-01-24 14:09:36'),
(35, 57, 2, '2024-01-24 14:47:56', '2024-01-24 14:47:56'),
(36, 58, 2, '2024-01-24 14:49:12', '2024-01-24 14:49:12'),
(37, 59, 2, '2024-01-24 14:49:33', '2024-01-24 14:49:33'),
(38, 60, 2, '2024-01-24 14:50:23', '2024-01-24 14:50:23'),
(39, 61, 2, '2024-01-24 15:20:47', '2024-01-24 15:20:47'),
(40, 62, 2, '2024-03-04 15:53:05', '2024-03-04 15:53:05'),
(41, 63, 2, '2024-03-04 15:54:18', '2024-03-04 15:54:18'),
(42, 64, 2, '2024-03-04 15:54:54', '2024-03-04 15:54:54'),
(43, 65, 2, '2024-03-04 15:56:57', '2024-03-04 15:56:57'),
(44, 66, 2, '2024-03-04 15:57:41', '2024-03-04 15:57:41'),
(45, 67, 2, '2024-03-04 15:58:32', '2024-03-04 15:58:32'),
(46, 68, 2, '2024-03-04 15:59:13', '2024-03-04 15:59:13'),
(47, 69, 2, '2024-03-04 15:59:55', '2024-03-04 15:59:55'),
(48, 70, 2, '2024-03-04 16:00:40', '2024-03-04 16:00:40'),
(49, 71, 2, '2024-03-04 16:12:13', '2024-03-04 16:12:13');

-- --------------------------------------------------------

--
-- Структура таблицы `Categories`
--

CREATE TABLE `Categories` (
  `id` int NOT NULL,
  `company_id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `Categories`
--

INSERT INTO `Categories` (`id`, `company_id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 'Электроника', 'Категория для электронных товаров', '2024-12-19 12:28:06', '2024-12-19 12:28:06'),
(2, 1, 'qwewqe', 'Категория для электронных товаров', '2024-12-22 22:23:18', '2024-12-22 22:23:18');

-- --------------------------------------------------------

--
-- Структура таблицы `CategoryCharacteristics`
--

CREATE TABLE `CategoryCharacteristics` (
  `id` int UNSIGNED NOT NULL,
  `category_id` int NOT NULL,
  `characteristic_name` varchar(255) NOT NULL,
  `characteristic_type` enum('text','number','select') NOT NULL,
  `options` json DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `CategoryCharacteristics`
--

INSERT INTO `CategoryCharacteristics` (`id`, `category_id`, `characteristic_name`, `characteristic_type`, `options`, `created_at`, `updated_at`) VALUES
(1, 1, 'Цвет', 'select', '[\"Красный\", \"Синий\", \"Зеленый\"]', '2024-12-19 12:48:21', '2024-12-19 12:48:21'),
(5, 2, 'Обороты', 'select', '[\"100\", \"200\", \"300\"]', '2024-12-23 15:47:45', '2024-12-23 15:47:45'),
(11, 1, 'Разме3р', 'select', '[\"L\", \"M\", \"S\"]', '2024-12-25 12:50:27', '2024-12-26 11:34:07');

-- --------------------------------------------------------

--
-- Структура таблицы `Companies`
--

CREATE TABLE `Companies` (
  `id` int NOT NULL,
  `company_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_created_user_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Дамп данных таблицы `Companies`
--

INSERT INTO `Companies` (`id`, `company_name`, `address`, `company_created_user_id`, `created_at`) VALUES
(1, 'Torgsoft', 'Освободителей 3', 2, '2023-10-17 11:19:30'),
(2, 'test', 'test', 4, '2023-10-17 11:19:30'),
(32, 'test', 'test', 6, '2023-10-17 11:19:30'),
(123, 'Название Компании', 'Адрес Компании', 2, '2025-01-03 08:57:34');

-- --------------------------------------------------------

--
-- Структура таблицы `CompanyAccounts`
--

CREATE TABLE `CompanyAccounts` (
  `id` int NOT NULL,
  `companyId` int NOT NULL,
  `accountId` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Дамп данных таблицы `CompanyAccounts`
--

INSERT INTO `CompanyAccounts` (`id`, `companyId`, `accountId`) VALUES
(1, 1, 23),
(2, 1, 24),
(3, 1, 25),
(4, 1, 26),
(5, 1, 27),
(6, 1, 28),
(7, 1, 29),
(8, 1, 30),
(9, 1, 31),
(10, 1, 32),
(11, 1, 33),
(12, 1, 34),
(13, 1, 35),
(14, 1, 36),
(15, 1, 37),
(16, 1, 38),
(17, 1, 39),
(18, 1, 40),
(19, 1, 41),
(20, 1, 42),
(21, 1, 43),
(22, 1, 44),
(23, 1, 45),
(24, 1, 46),
(25, 1, 47),
(26, 1, 48),
(27, 1, 49),
(28, 1, 50),
(29, 1, 51),
(30, 1, 52),
(31, 1, 53),
(32, 1, 54),
(33, 1, 55),
(34, 1, 56),
(35, 1, 57),
(36, 1, 58),
(37, 1, 59),
(38, 1, 60),
(39, 1, 61),
(40, 1, 62),
(41, 1, 63),
(42, 1, 64),
(43, 1, 65),
(44, 1, 66),
(45, 1, 67),
(46, 1, 68),
(47, 1, 69),
(48, 1, 70),
(49, 1, 71);

-- --------------------------------------------------------

--
-- Структура таблицы `CompanyContacts`
--

CREATE TABLE `CompanyContacts` (
  `id` int NOT NULL,
  `companyId` int NOT NULL,
  `contact_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Дамп данных таблицы `CompanyContacts`
--

INSERT INTO `CompanyContacts` (`id`, `companyId`, `contact_id`) VALUES
(1, 1, 23),
(2, 1, 24),
(3, 1, 25),
(4, 1, 26),
(5, 1, 27),
(6, 1, 28),
(7, 1, 29),
(8, 1, 30),
(9, 1, 31),
(10, 1, 32),
(11, 1, 33),
(12, 1, 34),
(13, 1, 35),
(14, 1, 36),
(15, 1, 37),
(16, 1, 38),
(17, 1, 39),
(18, 1, 40),
(19, 1, 41),
(20, 1, 42),
(21, 1, 43),
(22, 1, 44),
(23, 1, 45),
(24, 1, 46),
(25, 1, 47),
(26, 1, 48),
(27, 1, 49),
(28, 1, 50),
(29, 1, 51),
(30, 1, 52),
(31, 1, 53),
(32, 1, 54),
(33, 1, 55),
(34, 1, 56),
(35, 1, 57),
(36, 1, 58),
(37, 1, 59),
(38, 1, 60),
(39, 1, 61),
(40, 1, 62),
(41, 1, 63),
(42, 1, 64),
(43, 1, 65),
(44, 1, 66),
(45, 1, 67),
(46, 1, 68),
(47, 1, 69),
(48, 1, 70),
(49, 1, 71);

-- --------------------------------------------------------

--
-- Структура таблицы `CompanyEnterprises`
--

CREATE TABLE `CompanyEnterprises` (
  `companyId` int NOT NULL,
  `eGRPOUId` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Дамп данных таблицы `CompanyEnterprises`
--

INSERT INTO `CompanyEnterprises` (`companyId`, `eGRPOUId`) VALUES
(1, '1231231231');

-- --------------------------------------------------------

--
-- Структура таблицы `CompanySubscriptions`
--

CREATE TABLE `CompanySubscriptions` (
  `id` int NOT NULL,
  `company_id` int NOT NULL,
  `subscription_type_id` int NOT NULL,
  `start_date` datetime NOT NULL,
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Дамп данных таблицы `CompanySubscriptions`
--

INSERT INTO `CompanySubscriptions` (`id`, `company_id`, `subscription_type_id`, `start_date`, `is_active`) VALUES
(1, 1, 2, '2023-11-07 08:19:36', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `company_file_server_mapping`
--

CREATE TABLE `company_file_server_mapping` (
  `company_id` int NOT NULL,
  `file_server_id` int DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `Contacts`
--

CREATE TABLE `Contacts` (
  `id` int NOT NULL,
  `salutation` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `first_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `second_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `department` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `account_id` int DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone_home` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone_mobile` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone_work` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone_other` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `fax` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `deleted` int NOT NULL DEFAULT '0',
  `assigned_to_user_id` int DEFAULT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Дамп данных таблицы `Contacts`
--

INSERT INTO `Contacts` (`id`, `salutation`, `first_name`, `second_name`, `last_name`, `title`, `department`, `account_id`, `email`, `phone_home`, `phone_mobile`, `phone_work`, `phone_other`, `fax`, `birthdate`, `description`, `deleted`, `assigned_to_user_id`, `created_by`, `created_at`, `updated_at`) VALUES
(3, 'Г-н', 'Иван', NULL, 'Иванов', 'Директор', 'Отдел продаж', 1, 'ivan@company1.com', '+1234567890', '+9876543210', '+1122334455', '+9988776655', '+1122334455', '1980-01-15', 'Описание контакта 1', 0, NULL, 0, '2023-12-05 13:10:34', '2023-12-05 13:10:34'),
(4, 'Г-н', 'Петр', NULL, 'Петров', 'Менеджер', 'Отдел маркетинга', 1, 'petr@company1.com', '+1111111111', '+9999999999', '+7777777777', '+5555555555', '+7777777777', '1990-03-20', 'Описание контакта 2', 0, NULL, 0, '2023-12-05 13:10:34', '2023-12-05 13:10:34'),
(5, NULL, 'Леонід', '', 'Кіллер', 'Инжинир', 'Инжинирия', 23, 'leon_1c_killer@ukr.net', NULL, '+38 (066) 666-66-66', '', '', NULL, NULL, '', 0, 2, 2, '2024-03-26 12:20:18', '2024-03-26 12:20:18');

-- --------------------------------------------------------

--
-- Структура таблицы `DimensionRanges`
--

CREATE TABLE `DimensionRanges` (
  `id` int UNSIGNED NOT NULL,
  `category_characteristic_id` int UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `DimensionRanges`
--

INSERT INTO `DimensionRanges` (`id`, `category_characteristic_id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 'Размер обуви', 'Размеры обуви в Евро', '2025-01-02 12:35:40', '2025-01-02 12:35:40');

-- --------------------------------------------------------

--
-- Структура таблицы `DimensionRangeValues`
--

CREATE TABLE `DimensionRangeValues` (
  `id` int UNSIGNED NOT NULL,
  `dimension_range_id` int UNSIGNED NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int UNSIGNED DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `DimensionRangeValues`
--

INSERT INTO `DimensionRangeValues` (`id`, `dimension_range_id`, `value`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 1, '42', 10, '2025-01-02 13:10:58', '2025-01-02 13:10:58'),
(2, 1, '43', 10, '2025-01-02 13:11:22', '2025-01-02 13:11:22');

-- --------------------------------------------------------

--
-- Структура таблицы `EnterpriseAccountPlans`
--

CREATE TABLE `EnterpriseAccountPlans` (
  `id` int NOT NULL,
  `eGRPOUId` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `non_balance` int DEFAULT '0',
  `quantity` int DEFAULT '0',
  `currency` int DEFAULT '0',
  `accrued_or_recognized` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `vat_purpose` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `accrued_amount` int DEFAULT '0',
  `subaccount1` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `subaccount2` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `subaccount3` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `Enterprises`
--

CREATE TABLE `Enterprises` (
  `enterpriseId` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `eGRPOU` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `individualTaxNumber` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Дамп данных таблицы `Enterprises`
--

INSERT INTO `Enterprises` (`enterpriseId`, `name`, `eGRPOU`, `individualTaxNumber`) VALUES
(11, 'test', '1231231231', NULL),
(12, 'PDV', '9999999', '123');

-- --------------------------------------------------------

--
-- Структура таблицы `file_servers`
--

CREATE TABLE `file_servers` (
  `id` int NOT NULL,
  `host` varchar(255) NOT NULL,
  `base_path` varchar(255) NOT NULL,
  `status` enum('active','full') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `FinancialDocuments`
--

CREATE TABLE `FinancialDocuments` (
  `financialDocumentId` int NOT NULL,
  `theDate` datetime NOT NULL,
  `number` int NOT NULL,
  `sumMoney` decimal(10,2) NOT NULL,
  `moveCategory` enum('Приходный','Расходный') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `theType` enum('Банк','Касса') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `partnerName` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `partnerId` int DEFAULT NULL,
  `currencyId` int DEFAULT NULL,
  `analisysChipherId` int DEFAULT NULL,
  `analysisChipherName` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `cashRegisterId` int DEFAULT NULL,
  `accountId` int DEFAULT NULL,
  `enterpriseId` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `comment` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `FOP2_Taxes`
--

CREATE TABLE `FOP2_Taxes` (
  `FOP2_Taxes_id` int NOT NULL,
  `Month` int NOT NULL,
  `Quarter` int NOT NULL,
  `Year` int NOT NULL,
  `Default_ZP` decimal(15,2) DEFAULT '6700.00',
  `eGRPOUId` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `EN_Tax_Rate` decimal(15,2) DEFAULT NULL,
  `ESV_Rate` int DEFAULT '22'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Дамп данных таблицы `FOP2_Taxes`
--

INSERT INTO `FOP2_Taxes` (`FOP2_Taxes_id`, `Month`, `Quarter`, `Year`, `Default_ZP`, `eGRPOUId`, `EN_Tax_Rate`, `ESV_Rate`) VALUES
(27, 1, 1, 2024, 3000.00, '1231231231', 18.50, 20);

-- --------------------------------------------------------

--
-- Структура таблицы `FOP3_Taxes`
--

CREATE TABLE `FOP3_Taxes` (
  `FOP3_Taxes_id` int NOT NULL,
  `Month` int NOT NULL,
  `Quarter` int NOT NULL,
  `Year` int NOT NULL,
  `Default_ZP` decimal(15,2) DEFAULT '6700.00',
  `eGRPOUId` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `EN_Tax_Rate` decimal(15,2) DEFAULT NULL,
  `ESV_Rate` int DEFAULT '22'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Дамп данных таблицы `FOP3_Taxes`
--

INSERT INTO `FOP3_Taxes` (`FOP3_Taxes_id`, `Month`, `Quarter`, `Year`, `Default_ZP`, `eGRPOUId`, `EN_Tax_Rate`, `ESV_Rate`) VALUES
(14, 5, 2, 2024, 8000.00, '1231231231', 18.50, 20);

-- --------------------------------------------------------

--
-- Структура таблицы `Integrations`
--

CREATE TABLE `Integrations` (
  `id` int NOT NULL,
  `companyId` int NOT NULL,
  `integrationType` enum('Torgsoft','Telegram','Binotel') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `settings` json DEFAULT NULL,
  `isActive` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Дамп данных таблицы `Integrations`
--

INSERT INTO `Integrations` (`id`, `companyId`, `integrationType`, `settings`, `isActive`, `created_at`, `updated_at`) VALUES
(12, 1, 'Torgsoft', '{\"host\": \"185.154.180.208\", \"port\": \"5000\"}', 1, '2023-11-28 14:28:53', '2024-01-10 10:38:55'),
(16, 32, 'Torgsoft', '{\"host\": \"185.154.180.208\", \"port\": \"5000\"}', 1, '2023-11-28 14:43:44', '2024-01-10 10:38:43');

-- --------------------------------------------------------

--
-- Структура таблицы `Kveds`
--

CREATE TABLE `Kveds` (
  `id` int NOT NULL,
  `number` varchar(11) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `main` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Дамп данных таблицы `Kveds`
--

INSERT INTO `Kveds` (`id`, `number`, `name`, `main`) VALUES
(31, '123453', 'Sample KVED2', 1),
(32, '123453', 'Sample KVED2', 1),
(33, '123453', 'Sample KVED2', 1),
(34, '123453', 'Sample KVED2', 1),
(35, '123453', 'Sample KVED2', 1),
(36, '123453', 'Sample KVED2', 1),
(37, '123453', 'Sample KVED2', 1),
(38, '123453', 'Sample KVED2', 1),
(39, '123453', 'Sample KVED2', 1),
(40, '123453', 'Sample KVED2', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `KvedsEnterprises`
--

CREATE TABLE `KvedsEnterprises` (
  `Enterprises` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `Kved` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Дамп данных таблицы `KvedsEnterprises`
--

INSERT INTO `KvedsEnterprises` (`Enterprises`, `Kved`) VALUES
('9999999', 31),
('9999999', 32),
('1231231231', 33),
('1231231231', 34),
('9999999', 35),
('9999999', 36),
('1231231231', 37),
('1231231231', 38),
('1231231231', 39),
('9999999', 40);

-- --------------------------------------------------------

--
-- Структура таблицы `Nomenclature`
--

CREATE TABLE `Nomenclature` (
  `id` int NOT NULL,
  `company_id` int NOT NULL,
  `nomenclature_code` varchar(8) NOT NULL,
  `arcticle` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `group_name` varchar(255) NOT NULL,
  `type` enum('товар','услуга','материал') NOT NULL,
  `unit_of_measurement` varchar(50) NOT NULL,
  `description` text,
  `category_id` int NOT NULL,
  `long_name` varchar(500) NOT NULL,
  `characteristics` json NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `Nomenclature`
--

INSERT INTO `Nomenclature` (`id`, `company_id`, `nomenclature_code`, `arcticle`, `name`, `group_name`, `type`, `unit_of_measurement`, `description`, `category_id`, `long_name`, `characteristics`, `created_at`, `updated_at`) VALUES
(16, 1, '05', '', 'color', 'Обувь', 'товар', 'шт', 'Классическая женская обувь', 1, 'Nike 42 черный', '[{\"id\": 0, \"name\": \"brand\", \"type\": \"text\", \"order\": 1, \"value\": \"Nike\"}, {\"id\": 0, \"name\": \"size\", \"type\": \"text\", \"order\": 2, \"value\": \"42\"}, {\"id\": 0, \"name\": \"color\", \"type\": \"text\", \"order\": 3, \"value\": \"черный\"}]', '2024-12-24 13:18:56', '2024-12-24 13:18:56'),
(17, 1, '06', '', 'color', 'Обувь', 'товар', 'шт', 'Классическая женская обувь', 1, 'Nike 42 черный', '[{\"id\": 0, \"name\": \"brand\", \"type\": \"text\", \"order\": 1, \"value\": \"Nike\"}, {\"id\": 0, \"name\": \"size\", \"type\": \"text\", \"order\": 2, \"value\": \"42\"}, {\"id\": 0, \"name\": \"color\", \"type\": \"text\", \"order\": 3, \"value\": \"черный\"}]', '2024-12-24 13:22:36', '2024-12-24 13:22:36'),
(19, 1, '07', '', 'цуйкйуйц', 'Электроника', 'услуга', 'йцуйцу', '', 2, '', '[]', '2024-12-24 13:25:00', '2024-12-24 13:25:00'),
(20, 1, '08', '', '5', 'Электроника', 'товар', 'йййййй', '', 2, '42 200', '[{\"id\": 0, \"name\": 4, \"type\": \"text\", \"order\": 1, \"value\": \"42\"}, {\"id\": 0, \"name\": 5, \"type\": \"text\", \"order\": 2, \"value\": \"200\"}]', '2024-12-24 13:41:17', '2024-12-24 13:41:17'),
(22, 1, '09', '', 'color', 'Обувь', 'товар', 'шт', 'Классическая женская обувь', 1, 'Nike 42 черный', '[{\"id\": 0, \"name\": \"brand\", \"type\": \"text\", \"order\": 1, \"value\": \"Nike\"}, {\"id\": 0, \"name\": \"size\", \"type\": \"text\", \"order\": 2, \"value\": \"42\"}, {\"id\": 0, \"name\": \"color\", \"type\": \"text\", \"order\": 3, \"value\": \"черный\"}]', '2024-12-24 15:53:57', '2024-12-24 15:53:57'),
(24, 1, '10', '', 'color', 'Обувь', 'товар', 'шт', 'Классическая женская обувь', 1, 'Nike 42 черный', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": null}, {\"id\": \"2\", \"name\": \"Размер\", \"type\": \"select\", \"order\": \"2\", \"value\": null}, {\"id\": \"3\", \"name\": \"Размер\", \"type\": \"select\", \"order\": \"3\", \"value\": null}, {\"id\": 0, \"name\": \"brand\", \"type\": \"text\", \"order\": 4, \"value\": \"Nike\"}, {\"id\": 0, \"name\": \"size\", \"type\": \"text\", \"order\": 5, \"value\": \"42\"}, {\"id\": 0, \"name\": \"color\", \"type\": \"text\", \"order\": 6, \"value\": \"черный\"}]', '2024-12-24 18:21:38', '2024-12-24 18:21:38'),
(25, 1, '00000100', '12345', 'color', 'Обувь', 'товар', 'шт', 'Классическая женская обувь', 1, 'Nike 42 черный', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": null}, {\"id\": 0, \"name\": \"brand\", \"type\": \"text\", \"order\": 3, \"value\": \"Nike\"}, {\"id\": 0, \"name\": \"size\", \"type\": \"text\", \"order\": 4, \"value\": \"42\"}, {\"id\": 0, \"name\": \"color\", \"type\": \"text\", \"order\": 5, \"value\": \"черный\"}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": null}]', '2024-12-28 10:49:36', '2024-12-28 10:49:36'),
(26, 1, '00000101', '67890', 'ТЕСТ ТЕТСОВЫЙ 3333', 'Обувь', 'товар', 'шт', 'Классическая мужская обувь', 1, 'Adidas 44 белый', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": null}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": null}, {\"id\": 0, \"name\": \"brand\", \"type\": \"text\", \"order\": 3, \"value\": \"Adidas\"}, {\"id\": 0, \"name\": \"size\", \"type\": \"text\", \"order\": 4, \"value\": \"44\"}, {\"id\": 0, \"name\": \"color\", \"type\": \"text\", \"order\": 5, \"value\": \"белый\"}]', '2024-12-28 10:54:19', '2025-01-03 14:44:57'),
(28, 1, '00000102', '67890', 'Мужская обувь', 'Обувь', 'товар', 'шт', 'Классическая мужская обувь', 5, 'Мужская обувь Adidas 44 белый', '{\"size\": \"44\", \"brand\": \"Adidas\", \"color\": \"белый\"}', '2024-12-28 11:22:48', '2024-12-28 11:22:48'),
(29, 1, '00000103', '123453', 'Женская обувь', 'Обувь', 'товар', 'шт', 'Классическая женская обувь', 1, 'Nike 42 черный', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": null}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": null}, {\"id\": 0, \"name\": \"brand\", \"type\": \"text\", \"order\": 3, \"value\": \"Nike\"}, {\"id\": 0, \"name\": \"size\", \"type\": \"text\", \"order\": 4, \"value\": \"42\"}, {\"id\": 0, \"name\": \"color\", \"type\": \"text\", \"order\": 5, \"value\": \"черный\"}]', '2024-12-29 13:07:12', '2024-12-29 13:07:12'),
(30, 1, '00000104', '1234q53', 'Женская обувь', 'Обувь', 'товар', 'шт', 'Классическая женская обувь', 1, 'Array', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": null}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": null}, {\"id\": 0, \"name\": \"Размер обуви\", \"type\": \"text\", \"order\": 3, \"value\": [42, 43, 44]}]', '2024-12-29 13:47:59', '2024-12-29 13:47:59'),
(31, 1, '00000105', '12й34q53', 'Женская обувь', 'Обувь', 'товар', 'шт', 'Классическая женская обувь', 1, '42, 43, 44', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": null}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": null}, {\"id\": 0, \"name\": \"Размер обуви\", \"type\": \"text\", \"order\": 3, \"value\": \"42, 43, 44\"}]', '2024-12-29 13:52:28', '2024-12-29 13:52:28'),
(32, 1, '00000106', '12й34йq53', 'Женская обувь', 'Обувь', 'товар', 'шт', 'Классическая женская обувь', 1, '42, 43, 44', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": null}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": null}, {\"id\": 0, \"name\": \"Размер обуви\", \"type\": \"text\", \"order\": 3, \"value\": \"42, 43, 44\"}]', '2024-12-29 13:54:08', '2024-12-29 13:54:08'),
(33, 1, '00000107', '12й34йq5в3', 'Женская обувь', 'Обувь', 'товар', 'шт', 'Классическая женская обувь', 1, '42, 43, 44', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": null}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": null}, {\"id\": 0, \"name\": \"Размер обуви\", \"type\": \"text\", \"order\": 3, \"value\": \"42, 43, 44\"}]', '2024-12-29 14:03:28', '2024-12-29 14:03:28'),
(34, 1, '00000108', '1zxc', 'Женская обувь', 'Обувь', 'товар', 'шт', 'Классическая женская обувь', 1, '42, 43, 44', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": null}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": null}, {\"id\": 0, \"name\": \"Размер обуви\", \"type\": \"text\", \"order\": 3, \"value\": \"42, 43, 44\"}]', '2024-12-29 14:07:28', '2024-12-29 14:07:28'),
(35, 1, '00000109', '1zxthgc', 'Женская обувь', 'Обувь', 'товар', 'шт', 'Классическая женская обувь', 1, '42, 43, 44', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": null}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": null}, {\"id\": 0, \"name\": \"Размер обуви\", \"type\": \"text\", \"order\": 3, \"value\": \"42, 43, 44\"}]', '2024-12-29 14:11:17', '2024-12-29 14:11:17'),
(36, 1, '00000110', '12й34йq5вe3', 'Женская обувь', 'Обувь', 'товар', 'шт', 'Классическая женская обувь', 1, 'Черный', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": \"Черный\"}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": null}]', '2024-12-29 18:55:20', '2024-12-29 18:55:20'),
(37, 1, '00000111', '12й34йq5вe3-42', 'Женская обувь', 'Обувь', 'товар', 'шт', 'Классическая женская обувь', 1, 'Черный 42', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": \"Черный\"}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": null}, {\"id\": 0, \"name\": \"Размер обуви\", \"type\": \"text\", \"order\": 3, \"value\": \"42\"}]', '2024-12-29 18:59:51', '2024-12-29 18:59:51'),
(38, 1, '00000112', '12й34йq5вe3-43', 'Женская обувь', 'Обувь', 'товар', 'шт', 'Классическая женская обувь', 1, 'Черный 43', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": \"Черный\"}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": null}, {\"id\": 0, \"name\": \"Размер обуви\", \"type\": \"text\", \"order\": 3, \"value\": \"43\"}]', '2024-12-29 18:59:51', '2024-12-29 18:59:51'),
(39, 1, '00000113', '12й34йq5вe3-44', 'Женская обувь', 'Обувь', 'товар', 'шт', 'Классическая женская обувь', 1, 'Черный 44', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": \"Черный\"}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": null}, {\"id\": 0, \"name\": \"Размер обуви\", \"type\": \"text\", \"order\": 3, \"value\": \"44\"}]', '2024-12-29 18:59:51', '2024-12-29 18:59:51'),
(40, 1, '00000114', 'ART-20250102-0001', '4243', 'Электроника', 'товар', 'rak', '', 1, 'Размер обуви L', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": \"Размер обуви\"}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": \"L\"}]', '2025-01-02 21:12:49', '2025-01-02 21:12:49'),
(43, 123, '00000001', 'BASE-Размер:42-Цвет:Красный', 'Кроссовки', 'Обувь', 'товар', 'пара', 'Стильные кроссовки для повседневного ношения.', 5, 'Кожа Nike 42 Красный', '[{\"id\": 0, \"name\": \"Материал\", \"type\": \"text\", \"order\": 1, \"value\": \"Кожа\"}, {\"id\": 0, \"name\": \"Производитель\", \"type\": \"text\", \"order\": 2, \"value\": \"Nike\"}, {\"id\": 0, \"name\": \"Размер\", \"type\": \"text\", \"order\": 3, \"value\": \"42\"}, {\"id\": 0, \"name\": \"Цвет\", \"type\": \"text\", \"order\": 4, \"value\": \"Красный\"}]', '2025-01-03 11:05:08', '2025-01-03 11:05:08'),
(44, 123, '00000002', 'BASE-Размер:42-Цвет:Синий', 'Кроссовки', 'Обувь', 'товар', 'пара', 'Стильные кроссовки для повседневного ношения.', 5, 'Кожа Nike 42 Синий', '[{\"id\": 0, \"name\": \"Материал\", \"type\": \"text\", \"order\": 1, \"value\": \"Кожа\"}, {\"id\": 0, \"name\": \"Производитель\", \"type\": \"text\", \"order\": 2, \"value\": \"Nike\"}, {\"id\": 0, \"name\": \"Размер\", \"type\": \"text\", \"order\": 3, \"value\": \"42\"}, {\"id\": 0, \"name\": \"Цвет\", \"type\": \"text\", \"order\": 4, \"value\": \"Синий\"}]', '2025-01-03 11:05:08', '2025-01-03 11:05:08'),
(45, 123, '00000003', 'BASE-Размер:43-Цвет:Красный', 'Кроссовки', 'Обувь', 'товар', 'пара', 'Стильные кроссовки для повседневного ношения.', 5, 'Кожа Nike 43 Красный', '[{\"id\": 0, \"name\": \"Материал\", \"type\": \"text\", \"order\": 1, \"value\": \"Кожа\"}, {\"id\": 0, \"name\": \"Производитель\", \"type\": \"text\", \"order\": 2, \"value\": \"Nike\"}, {\"id\": 0, \"name\": \"Размер\", \"type\": \"text\", \"order\": 3, \"value\": \"43\"}, {\"id\": 0, \"name\": \"Цвет\", \"type\": \"text\", \"order\": 4, \"value\": \"Красный\"}]', '2025-01-03 11:05:08', '2025-01-03 11:05:08'),
(46, 123, '00000004', 'BASE-Размер:43-Цвет:Синий', 'Кроссовки', 'Обувь', 'товар', 'пара', 'Стильные кроссовки для повседневного ношения.', 5, 'Кожа Nike 43 Синий', '[{\"id\": 0, \"name\": \"Материал\", \"type\": \"text\", \"order\": 1, \"value\": \"Кожа\"}, {\"id\": 0, \"name\": \"Производитель\", \"type\": \"text\", \"order\": 2, \"value\": \"Nike\"}, {\"id\": 0, \"name\": \"Размер\", \"type\": \"text\", \"order\": 3, \"value\": \"43\"}, {\"id\": 0, \"name\": \"Цвет\", \"type\": \"text\", \"order\": 4, \"value\": \"Синий\"}]', '2025-01-03 11:05:08', '2025-01-03 11:05:08'),
(47, 123, '00000005', 'BASE-Размер:44-Цвет:Красный', 'Кроссовки', 'Обувь', 'товар', 'пара', 'Стильные кроссовки для повседневного ношения.', 5, 'Кожа Nike 44 Красный', '[{\"id\": 0, \"name\": \"Материал\", \"type\": \"text\", \"order\": 1, \"value\": \"Кожа\"}, {\"id\": 0, \"name\": \"Производитель\", \"type\": \"text\", \"order\": 2, \"value\": \"Nike\"}, {\"id\": 0, \"name\": \"Размер\", \"type\": \"text\", \"order\": 3, \"value\": \"44\"}, {\"id\": 0, \"name\": \"Цвет\", \"type\": \"text\", \"order\": 4, \"value\": \"Красный\"}]', '2025-01-03 11:05:08', '2025-01-03 11:05:08'),
(48, 123, '00000006', 'BASE-Размер:44-Цвет:Синий', 'Кроссовки', 'Обувь', 'товар', 'пара', 'Стильные кроссовки для повседневного ношения.', 5, 'Кожа Nike 44 Синий', '[{\"id\": 0, \"name\": \"Материал\", \"type\": \"text\", \"order\": 1, \"value\": \"Кожа\"}, {\"id\": 0, \"name\": \"Производитель\", \"type\": \"text\", \"order\": 2, \"value\": \"Nike\"}, {\"id\": 0, \"name\": \"Размер\", \"type\": \"text\", \"order\": 3, \"value\": \"44\"}, {\"id\": 0, \"name\": \"Цвет\", \"type\": \"text\", \"order\": 4, \"value\": \"Синий\"}]', '2025-01-03 11:05:08', '2025-01-03 11:05:08'),
(49, 1, '00000115', 'ART-002', 'Номенклатура 2', 'Группа 2', 'товар', 'шт', 'Описание товара', 20, 'синий 5 кг', '[{\"id\": 0, \"name\": \"цвет\", \"type\": \"text\", \"order\": 1, \"value\": \"синий\"}, {\"id\": 0, \"name\": \"вес\", \"type\": \"text\", \"order\": 2, \"value\": \"5 кг\"}]', '2025-01-03 11:17:56', '2025-01-03 11:17:56'),
(50, 1, '00000116', 'фыв', 'фыв', 'Электроника', 'товар', 'фыв', 'фыв', 1, 'Размер обуви L', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": \"Размер обуви\"}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": \"L\"}]', '2025-01-03 11:22:52', '2025-01-03 11:22:52'),
(51, 1, '00000117', 'йцуфвфы', 'йцуй', 'Электроника', 'материал', 'фывф', 'вцйцу', 1, 'Размер обуви L', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": \"Размер обуви\"}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": \"L\"}]', '2025-01-03 11:28:39', '2025-01-03 11:28:39'),
(52, 1, '00000118', 'BAаSE-Размер:42-Цвет:Красный', 'Кроссовки', 'Обувь', 'товар', 'пара', 'Стильные кроссовки для повседневного ношения.', 5, 'Кожа Nike 42 Красный', '[{\"id\": 0, \"name\": \"Материал\", \"type\": \"text\", \"order\": 1, \"value\": \"Кожа\"}, {\"id\": 0, \"name\": \"Производитель\", \"type\": \"text\", \"order\": 2, \"value\": \"Nike\"}, {\"id\": 0, \"name\": \"Размер\", \"type\": \"text\", \"order\": 3, \"value\": \"42\"}, {\"id\": 0, \"name\": \"Цвет\", \"type\": \"text\", \"order\": 4, \"value\": \"Красный\"}]', '2025-01-03 11:30:13', '2025-01-03 11:30:13'),
(53, 1, '00000119', 'BAаSE-Размер:43-Цвет:Красный', 'Кроссовки', 'Обувь', 'товар', 'пара', 'Стильные кроссовки для повседневного ношения.', 5, 'Кожа Nike 43 Красный', '[{\"id\": 0, \"name\": \"Материал\", \"type\": \"text\", \"order\": 1, \"value\": \"Кожа\"}, {\"id\": 0, \"name\": \"Производитель\", \"type\": \"text\", \"order\": 2, \"value\": \"Nike\"}, {\"id\": 0, \"name\": \"Размер\", \"type\": \"text\", \"order\": 3, \"value\": \"43\"}, {\"id\": 0, \"name\": \"Цвет\", \"type\": \"text\", \"order\": 4, \"value\": \"Красный\"}]', '2025-01-03 11:30:13', '2025-01-03 11:30:13'),
(54, 1, '00000120', 'BAаSE-Размер:44-Цвет:Красный', 'Кроссовки', 'Обувь', 'товар', 'пара', 'Стильные кроссовки для повседневного ношения.', 5, 'Кожа Nike 44 Красный', '[{\"id\": 0, \"name\": \"Материал\", \"type\": \"text\", \"order\": 1, \"value\": \"Кожа\"}, {\"id\": 0, \"name\": \"Производитель\", \"type\": \"text\", \"order\": 2, \"value\": \"Nike\"}, {\"id\": 0, \"name\": \"Размер\", \"type\": \"text\", \"order\": 3, \"value\": \"44\"}, {\"id\": 0, \"name\": \"Цвет\", \"type\": \"text\", \"order\": 4, \"value\": \"Красный\"}]', '2025-01-03 11:30:13', '2025-01-03 11:30:13'),
(55, 1, '00000121', 'йцуйцу', 'йцу', 'Электроника', 'товар', 'йцуйцу', 'фывфвйцу', 1, 'Размер обуви L', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": \"Размер обуви\"}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": \"L\"}]', '2025-01-03 11:45:52', '2025-01-03 11:45:52'),
(56, 1, '00000122', 'цйуйуавыфа', 'ываываывйуй', 'Электроника', 'товар', 'йцуйцу', 'цйуйцу', 1, 'Красный L', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": \"Красный\"}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": \"L\"}]', '2025-01-03 11:50:05', '2025-01-03 11:50:05'),
(57, 1, '00000123', 'ART-0й02', 'Номенклатура 2', 'Группа 2', 'товар', 'шт', 'Описание товара', 20, 'синий 5 кг', '[{\"id\": 0, \"name\": \"цвет\", \"type\": \"text\", \"order\": 1, \"value\": \"синий\"}, {\"id\": 0, \"name\": \"вес\", \"type\": \"text\", \"order\": 2, \"value\": \"5 кг\"}]', '2025-01-03 14:13:17', '2025-01-03 14:13:17'),
(58, 1, '00000124', 'ART-0йц02', 'Номенклатура 2', 'Группа 2', 'товар', 'шт', 'Описание товара', 20, 'синий 5 кг', '[{\"id\": 0, \"name\": \"цвет\", \"type\": \"text\", \"order\": 1, \"value\": \"синий\"}, {\"id\": 0, \"name\": \"вес\", \"type\": \"text\", \"order\": 2, \"value\": \"5 кг\"}]', '2025-01-03 14:14:29', '2025-01-03 14:14:29'),
(59, 1, '00000125', 'BAаSеE-Размер:42-Цвет:Красный', 'Кроссовки', 'Обувь', 'товар', 'пара', 'Стильные кроссовки для повседневного ношения.', 5, 'Кожа Nike 42 Красный', '[{\"id\": 0, \"name\": \"Материал\", \"type\": \"text\", \"order\": 1, \"value\": \"Кожа\"}, {\"id\": 0, \"name\": \"Производитель\", \"type\": \"text\", \"order\": 2, \"value\": \"Nike\"}, {\"id\": 0, \"name\": \"Размер\", \"type\": \"text\", \"order\": 3, \"value\": \"42\"}, {\"id\": 0, \"name\": \"Цвет\", \"type\": \"text\", \"order\": 4, \"value\": \"Красный\"}]', '2025-01-03 14:45:17', '2025-01-03 14:45:17'),
(60, 1, '00000126', 'BAаSеE-Размер:43-Цвет:Красный', 'Кроссовки', 'Обувь', 'товар', 'пара', 'Стильные кроссовки для повседневного ношения.', 5, 'Кожа Nike 43 Красный', '[{\"id\": 0, \"name\": \"Материал\", \"type\": \"text\", \"order\": 1, \"value\": \"Кожа\"}, {\"id\": 0, \"name\": \"Производитель\", \"type\": \"text\", \"order\": 2, \"value\": \"Nike\"}, {\"id\": 0, \"name\": \"Размер\", \"type\": \"text\", \"order\": 3, \"value\": \"43\"}, {\"id\": 0, \"name\": \"Цвет\", \"type\": \"text\", \"order\": 4, \"value\": \"Красный\"}]', '2025-01-03 14:45:17', '2025-01-03 14:45:17'),
(61, 1, '00000127', 'BAаSеE-Размер:44-Цвет:Красный', 'Кроссовки', 'Обувь', 'товар', 'пара', 'Стильные кроссовки для повседневного ношения.', 5, 'Кожа Nike 44 Красный', '[{\"id\": 0, \"name\": \"Материал\", \"type\": \"text\", \"order\": 1, \"value\": \"Кожа\"}, {\"id\": 0, \"name\": \"Производитель\", \"type\": \"text\", \"order\": 2, \"value\": \"Nike\"}, {\"id\": 0, \"name\": \"Размер\", \"type\": \"text\", \"order\": 3, \"value\": \"44\"}, {\"id\": 0, \"name\": \"Цвет\", \"type\": \"text\", \"order\": 4, \"value\": \"Красный\"}]', '2025-01-03 14:45:17', '2025-01-03 14:45:17'),
(62, 1, '00000128', 'йцуйц', 'уйцуйцу', 'Электроника', 'товар', 'йцуйцу', 'йцу', 1, 'Размер обуви L', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": \"Размер обуви\"}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": \"L\"}]', '2025-01-03 17:04:38', '2025-01-03 17:04:38'),
(63, 1, '00000129', 'цкуццуеуекуеук', 'ккккккекек', 'Электроника', 'товар', 'кекуе', 'уеук', 1, 'Размер обуви M', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": \"Размер обуви\"}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": \"M\"}]', '2025-01-03 17:36:10', '2025-01-03 17:36:10'),
(64, 1, '00000130', 'уйкуеееееееееееее', 'ее', 'Электроника', 'товар', 'ее', 'еее', 1, 'Размер обуви L', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": \"Размер обуви\"}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": \"L\"}]', '2025-01-03 17:39:01', '2025-01-03 17:39:01'),
(65, 1, '00000131', 'уйцуйц', 'йцуйц', 'Электроника', 'товар', 'йцуйц', 'уйцу', 1, 'Размер обуви L', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": \"Размер обуви\"}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": \"L\"}]', '2025-01-03 18:11:52', '2025-01-03 18:11:52'),
(66, 1, '00000132', 'йцу', 'йцуйц', 'Электроника', 'товар', 'йцуйц', 'уйцу', 1, 'Размер обуви L', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": \"Размер обуви\"}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": \"L\"}]', '2025-01-03 18:57:51', '2025-01-03 18:57:51'),
(67, 1, '00000133', 'qwe', 'qweqw', 'Электроника', 'товар', 'qwe', 'wqeq', 1, 'Размер обуви L', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": \"Размер обуви\"}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": \"L\"}]', '2025-01-03 19:00:30', '2025-01-03 19:00:30'),
(68, 1, '00000134', 'йцуйу-Размер обуви:42', 'йцйу', 'Электроника', 'товар', 'йцуйуйцуц', 'йцу', 1, '42 L', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": \"\"}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": \"L\"}, {\"id\": 0, \"name\": \"Размер обуви\", \"type\": \"text\", \"order\": 3, \"value\": \"42\"}]', '2025-01-03 19:11:44', '2025-01-03 19:11:44'),
(69, 1, '00000135', 'йцуйу-Размер обуви:43', 'йцйу', 'Электроника', 'товар', 'йцуйуйцуц', 'йцу', 1, '43 L', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": \"\"}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": \"L\"}, {\"id\": 0, \"name\": \"Размер обуви\", \"type\": \"text\", \"order\": 3, \"value\": \"43\"}]', '2025-01-03 19:11:44', '2025-01-03 19:11:44'),
(70, 1, '00000136', 'ART-0йц02', 'Номенклатура 2', 'Группа 2', 'товар', 'шт', 'Описание товара', 20, 'синий 5 кг', '[{\"id\": 0, \"name\": \"цвет\", \"type\": \"text\", \"order\": 1, \"value\": \"синий\"}, {\"id\": 0, \"name\": \"вес\", \"type\": \"text\", \"order\": 2, \"value\": \"5 кг\"}]', '2025-01-04 11:21:03', '2025-01-04 11:21:03'),
(71, 1, '00000137', 'ART-0йц02', 'Номенклатура 2', 'Группа 2', 'товар', 'шт', 'Описание товара', 20, 'синий 5 кг', '[{\"id\": 0, \"name\": \"цвет\", \"type\": \"text\", \"order\": 1, \"value\": \"синий\"}, {\"id\": 0, \"name\": \"вес\", \"type\": \"text\", \"order\": 2, \"value\": \"5 кг\"}]', '2025-01-04 11:21:59', '2025-01-04 11:21:59'),
(72, 1, '00000138', 'цйуйцум2', 'йцуйцу', 'qwewqe', 'товар', 'м²', 'цйуцйу', 2, '100', '[{\"id\": \"5\", \"name\": \"Обороты\", \"type\": \"select\", \"order\": \"5\", \"value\": \"100\"}]', '2025-01-04 11:41:24', '2025-01-04 11:41:24'),
(73, 1, '00000139', 'йцуйцу', 'купекуеук', 'Электроника', 'материал', 'пара', '', 1, '42 L', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": \"\"}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": \"L\"}, {\"id\": 0, \"name\": \"Размер обуви\", \"type\": \"text\", \"order\": 3, \"value\": \"42\"}]', '2025-01-04 11:42:30', '2025-01-04 11:42:30'),
(74, 1, '00000140', 'йцуйцу', 'купекуеук', 'Электроника', 'материал', 'пара', '', 1, '43 L', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": \"\"}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": \"L\"}, {\"id\": 0, \"name\": \"Размер обуви\", \"type\": \"text\", \"order\": 3, \"value\": \"43\"}]', '2025-01-04 11:42:30', '2025-01-04 11:42:30'),
(75, 1, '00000141', 'BAаSеE', 'Кроссовки', 'Обувь', 'товар', 'пара', 'Стильные кроссовки для повседневного ношения.', 5, 'Кожа Nike 42 Красный', '[{\"id\": 0, \"name\": \"Материал\", \"type\": \"text\", \"order\": 1, \"value\": \"Кожа\"}, {\"id\": 0, \"name\": \"Производитель\", \"type\": \"text\", \"order\": 2, \"value\": \"Nike\"}, {\"id\": 0, \"name\": \"Размер\", \"type\": \"text\", \"order\": 3, \"value\": \"42\"}, {\"id\": 0, \"name\": \"Цвет\", \"type\": \"text\", \"order\": 4, \"value\": \"Красный\"}]', '2025-01-04 11:59:34', '2025-01-04 11:59:34'),
(76, 1, '00000142', 'BAаSеE', 'Кроссовки', 'Обувь', 'товар', 'пара', 'Стильные кроссовки для повседневного ношения.', 5, 'Кожа Nike 43 Красный', '[{\"id\": 0, \"name\": \"Материал\", \"type\": \"text\", \"order\": 1, \"value\": \"Кожа\"}, {\"id\": 0, \"name\": \"Производитель\", \"type\": \"text\", \"order\": 2, \"value\": \"Nike\"}, {\"id\": 0, \"name\": \"Размер\", \"type\": \"text\", \"order\": 3, \"value\": \"43\"}, {\"id\": 0, \"name\": \"Цвет\", \"type\": \"text\", \"order\": 4, \"value\": \"Красный\"}]', '2025-01-04 11:59:34', '2025-01-04 11:59:34'),
(77, 1, '00000143', 'BAаSеE', 'Кроссовки', 'Обувь', 'товар', 'пара', 'Стильные кроссовки для повседневного ношения.', 5, 'Кожа Nike 44 Красный', '[{\"id\": 0, \"name\": \"Материал\", \"type\": \"text\", \"order\": 1, \"value\": \"Кожа\"}, {\"id\": 0, \"name\": \"Производитель\", \"type\": \"text\", \"order\": 2, \"value\": \"Nike\"}, {\"id\": 0, \"name\": \"Размер\", \"type\": \"text\", \"order\": 3, \"value\": \"44\"}, {\"id\": 0, \"name\": \"Цвет\", \"type\": \"text\", \"order\": 4, \"value\": \"Красный\"}]', '2025-01-04 11:59:35', '2025-01-04 11:59:35'),
(78, 1, '00000144', 'eqweqweqw', 'qeqw', 'Электроника', 'товар', 'пара', '', 1, '42, 43 42 L', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": \"42, 43\"}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": \"L\"}, {\"id\": 0, \"name\": \"Размер обуви\", \"type\": \"text\", \"order\": 3, \"value\": \"42\"}]', '2025-01-04 12:09:02', '2025-01-04 12:09:02'),
(79, 1, '00000145', 'eqweqweqw', 'qeqw', 'Электроника', 'товар', 'пара', '', 1, '42, 43 43 L', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": \"42, 43\"}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": \"L\"}, {\"id\": 0, \"name\": \"Размер обуви\", \"type\": \"text\", \"order\": 3, \"value\": \"43\"}]', '2025-01-04 12:09:02', '2025-01-04 12:09:02'),
(80, 1, '00000146', 'яяя', 'ячсяся', 'Электроника', 'товар', 'шт.', '', 1, '42 L', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": \"42\"}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": \"L\"}]', '2025-01-04 12:11:59', '2025-01-04 12:11:59'),
(81, 1, '00000147', 'яяя', 'ячсяся', 'Электроника', 'товар', 'шт.', '', 1, '43 L', '[{\"id\": \"1\", \"name\": \"Цвет\", \"type\": \"select\", \"order\": \"1\", \"value\": \"43\"}, {\"id\": \"11\", \"name\": \"Разме3р\", \"type\": \"select\", \"order\": \"11\", \"value\": \"L\"}]', '2025-01-04 12:11:59', '2025-01-04 12:11:59');

-- --------------------------------------------------------

--
-- Структура таблицы `NomenclatureCharacteristics`
--

CREATE TABLE `NomenclatureCharacteristics` (
  `id` int NOT NULL,
  `nomenclature_id` int NOT NULL,
  `characteristic_name` varchar(255) NOT NULL,
  `characteristic_type` enum('text','number','select') NOT NULL,
  `options` json DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `PendingSubscriptionUpdates`
--

CREATE TABLE `PendingSubscriptionUpdates` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `old_subscription_id` int NOT NULL,
  `new_type_id` int NOT NULL,
  `amount_due` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `Permissions`
--

CREATE TABLE `Permissions` (
  `id` int NOT NULL,
  `permission_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Дамп данных таблицы `Permissions`
--

INSERT INTO `Permissions` (`id`, `permission_name`, `description`) VALUES
(1, 'Перегляд товарів', 'Може переглядати контент'),
(2, 'Редагувати товари', 'Може редагувати існуючий контент'),
(3, 'Створювати товари', 'Може створювати новий контент'),
(4, 'Видалення контенту', 'Може видаляти контент'),
(5, 'Управління користувачами', 'Може керувати акаунтами користувачів'),
(6, 'Управління налаштуваннями', 'Може змінювати налаштування системи'),
(7, 'Необмежений доступ', 'Може обходити всі обмеження'),
(8, 'Адміністрування перегляд редагування контенту', 'Може редагувати та переглядати весь контент, переглядати за ідентифікатором'),
(9, 'Призначення ролі', 'Може призначати ролі користувачам'),
(10, 'Обчислення податків власне', 'Може переглядати дані про податки для власної компанії'),
(11, 'Обчислення податків будь-яке', 'Може переглядати дані про податки для будь-якої компанії'),
(12, 'Отримання КВЕДів', 'Може отримати КВЕДи підприємства'),
(13, 'Додавання КВЕДа', 'Може додавати КВЕДи'),
(14, 'Видалення КВЕДа', 'Може видаляти КВЕДи'),
(15, 'Додавання стандартних груп для рахунків', 'Може додавати стандартні групи для розрахункових рахунків, використовується при створенні/виборі групи рахунків для підприємства'),
(16, 'Оновлення стандартних груп для рахунків', 'Може оновлювати стандартні групи для розрахункових рахунків, використовується при створенні/виборі групи рахунків для підприємства'),
(17, 'Видалення стандартних груп для рахунків', 'Може видаляти стандартні групи для розрахункових рахунків, використовується при створенні/виборі групи рахунків для підприємства'),
(18, 'Отримання стандартних планів рахунків', 'Може отримати стандартні плани рахунків'),
(19, 'Видалення стандартного плану рахунку', 'Може видаляти стандартні плани рахунків'),
(20, 'Оновлення стандартного плану рахунку', 'Може оновлювати стандартні плани рахунків'),
(21, 'Додавання стандартного плану рахунку', 'Може додавати стандартні плани рахунків'),
(22, 'Додавання планів рахунків для підприємств', 'Може додавати плани рахунків для підприємств'),
(23, 'Отримання планів рахунків для підприємств', 'Може отримати плани рахунків для підприємств'),
(24, 'Додавання плану рахунку для підприємства', 'Може додавати план рахунків для підприємства'),
(25, 'Видалення плану рахунку для підприємства', 'Може видаляти план рахунків для підприємства'),
(26, 'Оновлення плану рахунку для підприємства', 'Може оновлювати план рахунків для підприємства'),
(27, 'Отримання контрагентів', 'Може отримати список контрагентів для власної компанії'),
(28, 'Оновлення контрагента', 'Може змінювати дані контрагента'),
(29, 'Створення контрагента', 'Може створювати нових контрагентів у системі'),
(30, 'Видалення контрагента', 'Може видаляти контрагентів'),
(31, 'Отримання контактів', 'Може отримати список контактів'),
(32, 'Видалення контакту', 'Може видаляти контакти'),
(33, 'Створення контактів', 'Може створювати нові контакти у системі'),
(34, 'Оновлення контакту', 'Може змінювати дані контакту'),
(35, 'Перегляд інформації про компанію', 'Може переглядати інформацію про компанію'),
(36, 'Перегляд співробітників компанії', 'Може переглядати співробітників компанії'),
(37, 'Отримання ролі користувача', 'Може отримати роль користувача'),
(38, 'Змінити роль користувача', 'Може змінити роль користувача');

-- --------------------------------------------------------

--
-- Структура таблицы `ProductCardCharacteristics`
--

CREATE TABLE `ProductCardCharacteristics` (
  `id` int NOT NULL,
  `product_card_id` int NOT NULL,
  `characteristic_name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `ProductCards`
--

CREATE TABLE `ProductCards` (
  `id` int NOT NULL,
  `company_id` int NOT NULL,
  `nomenclature_id` int NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `sku` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `barcode` varchar(100) NOT NULL,
  `images` json DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `Roles`
--

CREATE TABLE `Roles` (
  `id` int NOT NULL,
  `company_id` int DEFAULT NULL,
  `role_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permissions` json DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `Roles`
--

INSERT INTO `Roles` (`id`, `company_id`, `role_name`, `description`, `permissions`, `is_default`, `updated_at`) VALUES
(1, 1, 'global_admin', 'Torgsoft Workers', '[\"SEE CONTENT\", \"EDIT CONTENT\\n\", \"CREATE CONTENT\", \"Перегляд товарів\", \"Редагувати товари\", \"Оновлення контакту\", \"Створювати товари\"]', 0, '2024-12-09 19:40:17');

-- --------------------------------------------------------

--
-- Структура таблицы `StandardAccountsPlanTable`
--

CREATE TABLE `StandardAccountsPlanTable` (
  `id` int NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `type` varchar(5) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `non_balance` int NOT NULL,
  `quantity` int NOT NULL,
  `currency` int NOT NULL,
  `accrued_or_recognized` int NOT NULL,
  `vat_purpose` int NOT NULL,
  `accrued_amount` int NOT NULL,
  `subaccount1` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `subaccount2` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `subaccount3` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `is_deleted` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Дамп данных таблицы `StandardAccountsPlanTable`
--

INSERT INTO `StandardAccountsPlanTable` (`id`, `code`, `name`, `type`, `non_balance`, `quantity`, `currency`, `accrued_or_recognized`, `vat_purpose`, `accrued_amount`, `subaccount1`, `subaccount2`, `subaccount3`, `is_deleted`) VALUES
(1, '100', 'Інвестиційна нерухомість', 'А', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(2, '101', 'Земельні ділянки', 'А', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(3, '102', 'Капітальні витрати на поліпшення земель', 'А', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(4, '103', 'Будинки та споруди', 'А', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(5, '104', 'Машини та обладнання', 'А', 0, 0, 0, 1, 1, 1, 'Основні засоби', '(про) Склади', '', 0),
(6, '105', 'Транспортні засоби', 'А', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(7, '106', 'Інструменти прилади та інвентар', 'А', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(8, '107', 'Тварини', 'А', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(9, '108', 'Багаторічні насадження', 'А', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(10, '1091', 'Інші основні засоби', 'А', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(11, '1092', 'Основні засоби призначені для продажу', 'А', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(12, '1111', 'Бібліотечні фонди (по кожному об\'єкту)', 'А', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(13, '1112', 'Бібліотечні фонди (кількісно)', 'А', 0, 1, 0, 1, 1, 1, 'Номенклатура', '', '', 0),
(14, '1121', 'Малоцінні необоротні матеріальні активи (по кожному об\'єкту)', 'А', 0, 0, 0, 1, 1, 1, 'Основні засоби', '(про) Склади', '', 0),
(15, '1122', 'Малоцінні необоротні матеріальні активи (кількісно)', 'А', 0, 1, 0, 1, 1, 1, 'Номенклатура', '', '', 0),
(16, '113', 'Тимчасові (нетитульні) споруди', 'А', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(17, '114', 'Природні ресурси', 'А', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(18, '115', 'Інвентарна тара', 'А', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(19, '116', 'Предмети прокату', 'А', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(20, '1171', 'Інші необоротні матеріальні активи', 'А', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(21, '1172', 'Необоротні матеріальні активи призначені для продажу', 'А', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(22, '121', 'Права на використання природних ресурсів', 'А', 0, 0, 0, 1, 1, 1, 'Нематеріальні активи', '', '', 0),
(23, '122', 'Права використання майна', 'А', 0, 0, 0, 1, 1, 1, 'Нематеріальні активи', '', '', 0),
(24, '123', 'Права на комерційні позначення', 'А', 0, 0, 0, 1, 1, 1, 'Нематеріальні активи', '', '', 0),
(25, '124', 'Права на об\'єкти промислової власності', 'А', 0, 0, 0, 1, 1, 1, 'Нематеріальні активи', '', '', 0),
(26, '125', 'Авторське право та суміжні з ним права', 'А', 0, 0, 0, 1, 1, 1, 'Нематеріальні активи', '', '', 0),
(27, '127', 'Інші нематеріальні активи', 'А', 0, 0, 0, 1, 1, 1, 'Нематеріальні активи', '', '', 0),
(28, '131', 'Зношування основних засобів', 'П', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(29, '1310', 'Знос інвестиційна нерухомість', 'П', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(30, '1311', 'Знос земельні ділянки', 'П', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(31, '1312', 'Знос капітальні витрати на поліпшення земель', 'П', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(32, '1313', 'Знос будинку та споруди', 'П', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(33, '1314', 'Знос машини та обладнання / computers FA', 'П', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(34, '1315', 'Знос транспортні засоби', 'П', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(35, '1316', 'Знос інструменти прилади та інвентар / Furniture & Office Equipment', 'П', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(36, '1317', 'Знос тварини', 'П', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(37, '1318', 'Зношування багаторічні насадження', 'П', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(38, '1321', 'Знос інших необоротних матеріальних активів (по кожному об\'єкту)', 'П', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(39, '1322', 'Зношення інших необоротних матеріальних активів (кількісно)', 'П', 0, 0, 0, 1, 1, 1, 'Номенклатура', '', '', 0),
(40, '1323', 'Зношення покращень орендованого приміщення', 'П', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(41, '133', 'Накопичена амортизація нематеріальних активів', 'П', 0, 0, 0, 1, 1, 1, 'Нематеріальні активи', '', '', 0),
(42, '134', 'Накопичена амортизація довгострокових біологічних активів', 'П', 0, 0, 0, 1, 1, 1, '', '', '', 0),
(43, '135', 'Знос інвестиційної нерухомості', 'П', 0, 0, 0, 1, 1, 1, 'Основні засоби', '', '', 0),
(44, '141', 'Інвестиції пов\'язаним сторонам за методом обліку участі у капіталі', 'А', 0, 0, 0, 0, 0, 0, 'Фінансові інвестиції', '', '', 0),
(45, '142', 'Інші інвестиції пов\'язаним сторонам', 'А', 0, 0, 0, 0, 0, 0, 'Фінансові інвестиції', '', '', 0),
(46, '143', 'Інвестиції непов\'язаним сторонам', 'А', 0, 0, 0, 0, 0, 0, 'Фінансові інвестиції', '', '', 0),
(47, '151', 'Капітальне будівництво', 'А', 0, 0, 0, 1, 1, 0, 'Об\'єкти будівництва', '(про) Статті витрат', '', 0),
(48, '1521', 'Придбання основних засобів', 'А', 0, 1, 0, 1, 1, 0, 'Номенклатура', 'Партії', 'Склади', 0),
(49, '1522', 'Виготовлення та модернізація основних засобів', 'А', 0, 0, 0, 1, 1, 0, 'Об\'єкти будівництва', '(про) Статті витрат', '', 0),
(50, '1531', 'Придбання інших необоротних матеріальних активів', 'А', 0, 1, 0, 1, 1, 0, 'Номенклатура', 'Партії', 'Склади', 0),
(51, '1532', 'Виготовлення та модернізація інших необоротних матеріальних активів', 'А', 0, 0, 0, 1, 1, 0, 'Об\'єкти будівництва', '(про) Статті витрат', '', 0),
(52, '1541', 'Придбання нематеріальних активів', 'А', 0, 0, 0, 1, 1, 0, 'Нематеріальні активи', '', '', 0),
(53, '1542', 'Виготовлення нематеріальних активів', 'А', 0, 0, 0, 1, 1, 0, 'Об\'єкти будівництва', '(про) Статті витрат', '', 0),
(54, '155', 'Придбання (вирощування) довгострокових біологічних активів', 'А', 0, 0, 0, 0, 0, 0, 'Біологічні активи', '', '', 0),
(55, '161', 'Довгострокові біологічні активи рослинництва, які оцінюються за справедливою вартістю', 'А', 0, 0, 0, 0, 0, 0, 'Біологічні активи', '', '', 0),
(56, '162', 'Довгострокові біологічні активи рослинництва, які оцінюються за первісною вартістю', 'А', 0, 0, 0, 0, 0, 0, 'Біологічні активи', '', '', 0),
(57, '163', 'Довгострокові біологічні активи тваринництва, які оцінюються за справедливою вартістю', 'А', 0, 0, 0, 0, 0, 0, 'Біологічні активи', '', '', 0),
(58, '164', 'Довгострокові біологічні активи тваринництва, які оцінюються за первісною вартістю', 'А', 0, 0, 0, 0, 0, 0, 'Біологічні активи', '', '', 0),
(59, '165', 'Незрілі довгострокові біологічні активи, які оцінюються за справедливою вартістю', 'А', 0, 0, 0, 0, 0, 0, 'Біологічні активи', '', '', 0),
(60, '166', 'Незрілі довгострокові біологічні активи, які оцінюються за первісною вартістю', 'А', 0, 0, 0, 0, 0, 0, 'Біологічні активи', '', '', 0),
(61, '17', 'Відстрочені податкові активи', 'А', 0, 0, 0, 0, 0, 0, 'Види податкової діяльності', 'Статті відстроченого податку. активів та зобов\'язань', '', 0),
(62, '181', 'Заборгованість за майно, передане у фінансову оренду', 'А', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', '', 0),
(63, '182', 'Довгострокові векселі отримані', 'А', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Цінні папери', '', 0),
(64, '183', 'Інша дебіторська заборгованість', 'А', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', '', 0),
(65, '1831', 'Інша дебіторська заборгованість (депозит)', 'А', 0, 0, 0, 0, 0, 0, 'Контрагенти', '(про) Договори', '', 0),
(66, '184', 'Інші необоротні активи', 'А', 0, 0, 0, 0, 0, 0, 'Інші необоротні активи', '', '', 0),
(67, '191', 'Гудвіл при придбанні', 'А', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(68, '193', 'Гудвіл при приватизації (корпоратизації)', 'АП', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(69, '200', 'Транспортно-заготівельні витрати (матеріали)', 'А', 0, 0, 0, 1, 1, 0, 'Номенклатурні групи', '(про) Статті витрат', '', 0),
(70, '201', 'Сировина і матеріали', 'А', 0, 1, 0, 1, 1, 0, 'Номенклатура', 'Партії', 'Склади', 0),
(71, '202', 'Покупні напівфабрикати та комплектуючі вироби', 'А', 0, 1, 0, 1, 1, 0, 'Номенклатура', 'Партії', 'Склади', 0),
(72, '203', 'Паливо', 'А', 0, 1, 0, 1, 1, 0, 'Номенклатура', 'Партії', 'Склади', 0),
(73, '204', 'Тара та тарні матеріали', 'А', 0, 1, 0, 1, 1, 0, 'Номенклатура', 'Партії', 'Склади', 0),
(74, '205', 'Будівельні матеріали', 'А', 0, 1, 0, 1, 1, 0, 'Номенклатура', 'Партії', 'Склади', 0),
(75, '206', 'Матеріали, передані у переробку', 'А', 0, 1, 0, 1, 1, 0, 'Контрагенти', 'Номенклатура', 'Партії', 0),
(76, '207', 'Запасні частини', 'А', 0, 1, 0, 1, 1, 0, 'Номенклатура', 'Партії', 'Склади', 0),
(77, '208', 'Матеріали сільськогосподарського призначення', 'А', 0, 1, 0, 1, 1, 0, 'Номенклатура', 'Партії', 'Склади', 0),
(78, '209', 'Інші матеріали', 'А', 0, 1, 0, 1, 1, 0, 'Номенклатура', 'Партії', 'Склади', 0),
(79, '211', 'Поточні біологічні активи рослинництва, які оцінюються за справедливою вартістю', 'А', 0, 1, 0, 0, 0, 0, 'Біологічні активи', '', '', 0),
(80, '212', 'Поточні біологічні активи тваринництва, які оцінюються за справедливою вартістю', 'А', 0, 1, 0, 0, 0, 0, 'Біологічні активи', '', '', 0),
(81, '213', 'Поточні біологічні активи тваринництва, які оцінюються за первісною вартістю', 'А', 0, 1, 0, 0, 0, 0, 'Біологічні активи', '', '', 0),
(82, '221', 'Малоцінні та швидкозношувані предмети на складах', 'А', 0, 1, 0, 1, 1, 0, 'Номенклатура', 'Партії', 'Склади', 0),
(83, '222', '(не використовується) Малоцінні та швидкозношувані предмети в експлуатації', 'А', 0, 1, 0, 0, 0, 0, 'Номенклатура', '', '', 0),
(84, '231', 'Основне виробництво', 'А', 0, 0, 0, 1, 1, 0, 'Підрозділи', 'Номенклатурні групи', '(про) Статті витрат', 0),
(85, '2311', 'CoWorking', 'А', 0, 0, 0, 1, 1, 0, 'Підрозділи', 'Номенклатурні групи', 'Статті витрат', 0),
(86, '2312', 'Innohub', 'А', 0, 0, 0, 1, 1, 0, 'Підрозділи', 'Номенклатурні групи', 'Статті витрат', 0),
(87, '2313', 'BU1', 'А', 0, 0, 0, 1, 1, 0, 'Підрозділи', 'Номенклатурні групи', '(про) Статті витрат', 0),
(88, '2314', 'BU2', 'А', 0, 0, 0, 1, 1, 0, 'Підрозділи', 'Номенклатурні групи', '(про) Статті витрат', 0),
(89, '2315', 'Суборенда', 'А', 0, 0, 0, 1, 1, 0, 'Підрозділи', 'Номенклатурні групи', 'Статті витрат', 0),
(90, '2316', 'Оренда', 'А', 0, 0, 0, 1, 1, 0, 'Підрозділи', 'Номенклатурні групи', 'Статті витрат', 0),
(91, '232', 'Допоміжні виробництва', 'А', 0, 0, 0, 1, 1, 0, 'Підрозділи', 'Номенклатурні групи', '(про) Статті витрат', 0),
(92, '233', 'Обслуговуючі виробництва', 'А', 0, 0, 0, 1, 1, 0, 'Підрозділи', 'Номенклатурні групи', '(про) Статті витрат', 0),
(93, '234', 'Давальницьке виробництво', 'А', 0, 1, 0, 1, 1, 0, 'Номенклатура', '', '', 0),
(94, '235', 'Обслуговування та ремонт необоротних активів', 'А', 0, 0, 0, 1, 1, 0, 'Об\'єкти будівництва', '(про) Статті витрат', '', 0),
(95, '24', 'Шлюб у виробництві', 'А', 0, 0, 0, 1, 1, 0, 'Підрозділи', 'Номенклатурні групи', '(про) Статті витрат', 0),
(96, '25', 'Напівфабрикати', 'А', 0, 1, 0, 1, 1, 0, 'Номенклатура', 'Партії', 'Склади', 0),
(97, '26', 'Готова продукція', 'А', 0, 1, 0, 1, 1, 0, 'Номенклатура', 'Партії', 'Склади', 0),
(98, '27', 'Продукція сільськогосподарського виробництва', 'А', 0, 1, 0, 1, 1, 0, 'Номенклатура', 'Партії', 'Склади', 0),
(99, '2801', 'Транспортно-заготівельні витрати (товари)', 'А', 0, 0, 0, 1, 1, 0, 'Номенклатурні групи', '(про) Статті витрат', '', 0),
(100, '2802', 'Транспортно-заготівельні витрати (у НТТ за продажною вартістю)', 'А', 0, 0, 0, 1, 1, 0, '(про) Статті витрат', '', '', 0),
(101, '281', 'Товари на складі', 'А', 0, 1, 0, 1, 1, 0, 'Номенклатура', 'Партії', 'Склади', 0),
(102, '2821', 'Товари у роздрібній торгівлі (в АТТ за продажною вартістю)', 'А', 0, 1, 0, 1, 1, 0, 'Номенклатура', 'Склади', 'Партії', 0),
(103, '2822', 'Товари у роздрібній торгівлі (у НТТ за продажною вартістю)', 'А', 0, 0, 0, 1, 1, 0, 'Склади', '', '', 0),
(104, '283', 'Товари на комісії', 'А', 0, 1, 0, 1, 1, 0, 'Контрагенти', 'Номенклатура', 'Партії', 0),
(105, '284', 'Тара під товарами', 'А', 0, 1, 0, 1, 1, 0, 'Номенклатура', 'Партії', 'Склади', 0),
(106, '2851', 'Торгова націнка в автоматизованих торгових точках', 'П', 0, 0, 0, 1, 1, 0, 'Номенклатура', 'Склади', 'Партії', 0),
(107, '2852', 'Торгова націнка в неавтоматизованих торгових точках', 'П', 0, 0, 0, 1, 1, 0, 'Склади', '', '', 0),
(108, '286', 'Необоротні активи та групи вибуття, які утримуються для продажу', 'А', 0, 0, 0, 1, 1, 1, 'Основні засоби', 'Номенклатура', '', 0),
(109, '289', 'Товари в торгівлі (за покупною вартістю)', 'А', 0, 1, 0, 1, 1, 0, 'Номенклатура', 'Партії', 'Склади', 0),
(110, '301', 'Каса у національній валюті', 'А', 0, 0, 0, 0, 0, 0, '(про) Статті руху грошових коштів', '', '', 0),
(111, '302', 'Каса в іноземній валюті', 'А', 0, 0, 1, 0, 0, 0, '(про) Статті руху грошових коштів', '', '', 0),
(112, '311', 'Поточні рахунки у національній валюті', 'А', 0, 0, 0, 0, 0, 0, 'Банківські рахунки', '(про) Статті руху грошових коштів', '', 0),
(113, '312', 'Поточні рахунки в іноземній валюті', 'А', 0, 0, 1, 0, 0, 0, 'Банківські рахунки', '(про) Статті руху грошових коштів', '', 0),
(114, '313', 'Інші рахунки у банку в національній валюті', 'А', 0, 0, 0, 0, 0, 0, 'Банківські рахунки', '(про) Статті руху грошових коштів', '', 0),
(115, '314', 'Інші рахунки у банку в іноземній валюті', 'А', 0, 0, 1, 0, 0, 0, 'Банківські рахунки', '(про) Статті руху грошових коштів', '', 0),
(116, '315', 'Спеціальні рахунки у національній валюті', 'А', 0, 0, 0, 0, 0, 0, 'Банківські рахунки', '(про) Статті руху грошових коштів', '', 0),
(117, '316', 'Спеціальні рахунки в іноземній валюті', 'А', 0, 0, 1, 0, 0, 0, '', '', '', 0),
(118, '331', 'Грошові документи у національній валюті', 'А', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(119, '332', 'Грошові документи в іноземній валюті', 'А', 0, 0, 1, 0, 0, 0, '', '', '', 0),
(120, '333', 'Кошти в дорозі в національній валюті', 'А', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Статті руху коштів', '', 0),
(121, '334', 'Кошти в дорозі в іноземній валюті', 'А', 0, 0, 1, 0, 0, 0, 'Контрагенти', 'Статті руху коштів', '', 0),
(122, '335', 'Електронні гроші, номіновані у національній валюті', 'А', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(123, '341', 'Короткострокові векселі, отримані у національній валюті', 'А', 0, 0, 0, 0, 0, 0, 'Цінні папери', '', '', 0),
(124, '342', 'Короткострокові векселі, одержані в іноземній валюті', 'А', 0, 0, 1, 0, 0, 0, 'Цінні папери', '', '', 0),
(125, '3511', 'Еквівалент грошових коштів (у національній валюті)', 'А', 0, 0, 0, 0, 0, 0, 'Фінансові інвестиції', '', '', 0),
(126, '3512', 'Еквівалент грошових коштів (в іноземній валюті)', 'А', 0, 0, 1, 0, 0, 0, 'Фінансові інвестиції', '', '', 0),
(127, '352', 'Інші поточні фінансові інвестиції', 'А', 0, 0, 0, 0, 0, 0, 'Фінансові інвестиції', '', '', 0),
(128, '361', 'Розрахунки з вітчизняними покупцями', 'АП', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', 'Документи розрахунків із контрагентами', 0),
(129, '362', 'Розрахунки з іноземними покупцями', 'АП', 0, 0, 1, 0, 0, 0, 'Контрагенти', 'Договори', 'Документи розрахунків із контрагентами', 0),
(130, '363', 'Розрахунки з учасниками ПФГ', 'АП', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', 'Документи розрахунків із контрагентами', 0),
(131, '364', 'Розрахунки щодо гарантійного забезпечення', 'АП', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(132, '368', 'Розрахунки з ФОП', 'АП', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', 'Документи розрахунків із контрагентами', 0),
(133, '3711', 'Розрахунки за виданими авансами (у національній валюті)', 'А', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', 'Документи розрахунків із контрагентами', 0),
(134, '3712', 'Розрахунки за виданими авансами (в іноземній валюті)', 'А', 0, 0, 1, 0, 0, 0, 'Контрагенти', 'Договори', 'Документи розрахунків із контрагентами', 0),
(135, '3721', 'Розрахунки з підзвітними особами у національній валюті', 'АП', 0, 0, 0, 0, 0, 0, 'Працівники організацій', 'Контрагенти', 'Договори', 0),
(136, '3722', 'Розрахунки з підзвітними особами в іноземній валюті', 'АП', 0, 0, 1, 0, 0, 0, 'Працівники організацій', 'Контрагенти', 'Договори', 0),
(137, '373', 'Розрахунки за нарахованими доходами', 'А', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', '', 0),
(138, '3731', 'Розрахунки з нарахованих доходів у національній валюті', 'А', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', '', 0),
(139, '3732', 'Розрахунки за нарахованими доходами в іноземній валюті', 'А', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', '', 0),
(140, '374', 'Розрахунки за претензіями', 'А', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', '', 0),
(141, '375', 'Розрахунки з відшкодування завданих збитків', 'А', 0, 0, 0, 0, 0, 0, 'Працівники організацій', '', '', 0),
(142, '376', 'Розрахунки з позик членам кредитних спілок', 'А', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', '', 0),
(143, '3771', 'Розрахунки з іншими дебіторами (у національній валюті)', 'АП', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', 'Документи розрахунків із контрагентами', 0),
(144, '37711', 'Розрахунки з іншими дебіторами (у національній валюті) СД', 'АП', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', 'Документи розрахунків із контрагентами', 0),
(145, '3772', 'Розрахунки з іншими дебіторами (в іноземній валюті)', 'АП', 0, 0, 1, 0, 0, 0, 'Контрагенти', 'Договори', 'Документи розрахунків із контрагентами', 0),
(146, '3773', 'Розрахунки з робітниками та службовцями з інших операцій', 'АП', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(147, '378', 'Розрахунки з державними цільовими фондами', 'АП', 0, 0, 0, 0, 0, 0, '(про) Статті податкових декларацій', 'Працівники організацій', '', 0),
(148, '379', 'Розрахунки з операцій з деривативами', 'АП', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(149, '381', 'Щодо заборгованості за товари, послуги, роботи', 'П', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(150, '382', 'Щодо заборгованості за розрахунки з бюджетом', 'П', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(151, '383', 'За заборгованістю за розрахунки з виданих авансів', 'П', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(152, '384', 'Щодо заборгованості за розрахунки за нарахованими доходами', 'П', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(153, '385', 'Щодо заборгованості за внутрішні розрахунки', 'П', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(154, '386', 'За іншою дебіторською заборгованістю', 'П', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(155, '39', 'Витрати майбутніх періодів', 'А', 0, 0, 0, 1, 1, 0, 'Витрати майбутніх періодів', 'Контрагенти', 'Договори', 0),
(156, '391', 'Витрати майбутніх періодів у гривнях', 'А', 0, 0, 0, 1, 1, 0, 'Витрати майбутніх періодів', 'Контрагенти', 'Договори', 0),
(157, '392', 'Витрати майбутніх періодів у доларах', 'А', 0, 0, 0, 1, 1, 0, 'Витрати майбутніх періодів', 'Контрагенти', 'Договори', 0),
(158, '401', 'Статутний капітал', 'П', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Цінні папери', '', 0),
(159, '402', 'Пайовий капітал', 'П', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Цінні папери', '', 0),
(160, '403', 'Інший зареєстрований капітал', 'П', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(161, '404', 'Внески до незареєстрованого статутного капіталу', 'П', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(162, '411', 'Дооцінка (уцінка) основних засобів', 'П', 0, 0, 0, 0, 0, 0, 'Основні засоби', '', '', 0),
(163, '412', 'Дооцінка (уцінка) нематеріальних активів', 'П', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(164, '413', 'Дооцінка (уцінка) фінансових інструментів', 'П', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(165, '414', 'Інший капітал у дооцінках', 'П', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(166, '421', 'Емісійний прибуток', 'П', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(167, '422', 'Інший вкладений капітал', 'П', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(168, '423', 'Накопичені курсові різниці', 'АП', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(169, '424', 'Безоплатно отримані НА', 'П', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(170, '425', 'Інший додатковий капітал', 'П', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(171, '43', 'Резервний капітал', 'П', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(172, '441', 'Нерозподілений прибуток', 'П', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(173, '442', 'Непокритий збиток', 'А', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(174, '443', 'Прибуток, використаний у звітному періоді', 'А', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(175, '451', 'Вилучені акції', 'А', 0, 0, 0, 0, 0, 0, 'Цінні папери', '', '', 0),
(176, '452', 'Вилучені вклади та паї', 'А', 0, 0, 0, 0, 0, 0, 'Цінні папери', '', '', 0),
(177, '453', 'Інший вилучений капітал', 'А', 0, 0, 0, 0, 0, 0, 'Цінні папери', '', '', 0),
(178, '46', 'Неоплачений капітал', 'А', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Цінні папери', '', 0),
(179, '471', 'Забезпечення виплат відпусток', 'П', 0, 0, 0, 0, 0, 0, '(про) Статті витрат', '', '', 0),
(180, '472', 'Додаткове пенсійне забезпечення', 'П', 0, 0, 0, 0, 0, 0, '(про) Статті витрат', '', '', 0),
(181, '473', 'Забезпечення гарантійних зобов\'язань', 'П', 0, 0, 0, 0, 0, 0, '(про) Статті витрат', '', '', 0),
(182, '474', 'Забезпечення інших витрат та платежів', 'П', 0, 0, 0, 0, 0, 0, '(про) Статті витрат', 'Контрагенти', '', 0),
(183, '475', 'Забезпечення, пов\'язане з інвестиційною діяльністю', 'П', 0, 0, 0, 0, 0, 0, '(про) Статті витрат', '', '', 0),
(184, '476', 'Забезпечення, пов\'язане із фінансовою діяльністю', 'П', 0, 0, 0, 0, 0, 0, '(про) Статті витрат', '', '', 0),
(185, '477', 'Забезпечення матеріального заохочення', 'П', 0, 0, 0, 0, 0, 0, '(про) Статті витрат', '', '', 0),
(186, '478', 'Забезпечення відновлення земельних ділянок', 'П', 0, 0, 0, 0, 0, 0, '(про) Статті витрат', '', '', 0),
(187, '479', 'Інше Забезпечення / Оther Provision accruals', 'П', 0, 0, 0, 0, 0, 0, '(про) Статті витрат', '', '', 0),
(188, '481', 'Кошти, звільнені від оподаткування', 'П', 0, 0, 0, 0, 0, 0, 'Призначення цільових засобів', '', '', 0),
(189, '482', 'Кошти з бюджету та державних цільових фондів', 'П', 0, 0, 0, 0, 0, 0, 'Призначення цільових засобів', '', '', 0),
(190, '483', 'Благодійна допомога', 'П', 0, 0, 0, 0, 0, 0, 'Призначення цільових засобів', '', '', 0),
(191, '484', 'Інші засоби цільового фінансування та цільових надходжень', 'П', 0, 0, 0, 0, 0, 0, 'Призначення цільових засобів', '', '', 0),
(192, '487', '(не використовується) Цільове фінансування та цільові надходження операційної діяльності', 'П', 0, 0, 0, 0, 0, 0, 'Призначення цільових засобів', '', '', 0),
(193, '488', '(не використовується) Цільове фінансування та цільові надходження інвестиційної діяльності', 'П', 0, 0, 0, 0, 0, 0, 'Призначення цільових засобів', '', '', 0),
(194, '489', '(не використовується) Цільове фінансування та цільові надходження фінансової діяльності', 'П', 0, 0, 0, 0, 0, 0, 'Призначення цільових засобів', '', '', 0),
(195, '491', 'Технічні резерви', 'П', 0, 0, 0, 0, 0, 0, 'Резерви', '', '', 0),
(196, '492', 'Резерви зі страхування життя', 'П', 0, 0, 0, 0, 0, 0, 'Резерви', '', '', 0),
(197, '493', 'Частка перестраховиків у технічних резервах', 'П', 0, 0, 0, 0, 0, 0, 'Резерви', '', '', 0),
(198, '494', 'Частка перестраховиків у резервах зі страхування життя', 'П', 0, 0, 0, 0, 0, 0, 'Резерви', '', '', 0),
(199, '495', 'Результат зміни технічних резервів', 'П', 0, 0, 0, 0, 0, 0, 'Резерви', '', '', 0),
(200, '496', 'Результат зміни резервів зі страхування життя', 'П', 0, 0, 0, 0, 0, 0, 'Резерви', '', '', 0),
(201, '501', 'Довгострокові кредити банків у національній валюті', 'П', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', '', 0),
(202, '502', 'Довгострокові кредити банків в іноземній валюті', 'П', 0, 0, 1, 0, 0, 0, 'Контрагенти', 'Договори', '', 0),
(203, '503', 'Відстрочені довгострокові кредити банків у національній валюті', 'П', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', '', 0),
(204, '504', 'Відстрочені довгострокові кредити банків в іноземній валюті', 'П', 0, 0, 1, 0, 0, 0, 'Контрагенти', 'Договори', '', 0),
(205, '505', 'Інші довгострокові позики у національній валюті', 'П', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', '', 0),
(206, '506', 'Інші довгострокові позики в іноземній валюті', 'П', 0, 0, 1, 0, 0, 0, 'Контрагенти', 'Договори', '', 0),
(207, '511', 'Довгострокові векселі, видані у національній валюті', 'П', 0, 0, 0, 0, 0, 0, 'Цінні папери', '', '', 0),
(208, '512', 'Довгострокові векселі, видані в іноземній валюті', 'П', 0, 0, 1, 0, 0, 0, 'Цінні папери', '', '', 0),
(209, '521', 'Зобов\'язання з облігацій', 'П', 0, 0, 0, 0, 0, 0, 'Цінні папери', '', '', 0),
(210, '522', 'Премія з випущених облігацій', 'П', 0, 0, 0, 0, 0, 0, 'Цінні папери', '', '', 0),
(211, '523', 'Дисконт з випущених облігацій', 'А', 0, 0, 0, 0, 0, 0, 'Цінні папери', '', '', 0),
(212, '531', 'Зобов\'язання з фінансової оренди', 'П', 0, 0, 0, 0, 0, 0, 'Контрагенти', '', '', 0),
(213, '532', 'Зобов\'язання з оренди цілісних майнових комплексів', 'П', 0, 0, 0, 0, 0, 0, 'Контрагенти', '', '', 0),
(214, '54', 'Відстрочені податкові зобов\'язання', 'П', 0, 0, 0, 0, 0, 0, 'Види податкової діяльності', 'Статті відстроченого податку. активів та зобов\'язань', '', 0),
(215, '55', 'Інші довгострокові зобов\'язання', 'П', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', '', 0),
(216, '601', 'Короткострокові кредити банків у національній валюті', 'П', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', '', 0),
(217, '602', 'Короткострокові кредити банків в іноземній валюті', 'П', 0, 0, 1, 0, 0, 0, 'Контрагенти', 'Договори', '', 0),
(218, '603', 'Відстрочені короткострокові кредити банків у національній валюті', 'П', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', '', 0),
(219, '604', 'Відстрочені короткострокові кредити банків в іноземній валюті', 'П', 0, 0, 1, 0, 0, 0, 'Контрагенти', 'Договори', '', 0),
(220, '605', 'Прострочені позики у національній валюті', 'П', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', '', 0),
(221, '606', 'Прострочені позики в іноземній валюті', 'П', 0, 0, 1, 0, 0, 0, 'Контрагенти', 'Договори', '', 0),
(222, '607', 'Короткострокові позики у національній валюті', 'П', 0, 0, 1, 0, 0, 0, 'Контрагенти', 'Договори', '', 0),
(223, '611', 'Поточна заборгованість із довгострокових зобов\'язань у національній валюті', 'П', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', '', 0),
(224, '612', 'Поточна заборгованість із довгострокових зобов\'язань в іноземній валюті', 'П', 0, 0, 1, 0, 0, 0, 'Контрагенти', 'Договори', '', 0),
(225, '621', 'Короткострокові векселі, видані у національній валюті', 'П', 0, 0, 0, 0, 0, 0, 'Цінні папери', '', '', 0),
(226, '622', 'Короткострокові векселі, видані в іноземній валюті', 'П', 0, 0, 1, 0, 0, 0, 'Цінні папери', '', '', 0),
(227, '631', 'Розрахунки з вітчизняними постачальниками', 'АП', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', 'Документи розрахунків із контрагентами', 0),
(228, '632', 'Розрахунки з іноземними постачальниками', 'АП', 0, 0, 1, 0, 0, 0, 'Контрагенти', 'Договори', 'Документи розрахунків із контрагентами', 0),
(229, '633', 'Розрахунки з учасниками ПФГ', 'АП', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', 'Документи розрахунків із контрагентами', 0),
(230, '6411', 'Розрахунки з ПДФО', 'АП', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(231, '6412', 'Розрахунки з ПДВ', 'АП', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(232, '6413', 'Розрахунки з податку на прибуток', 'АП', 0, 0, 0, 0, 0, 0, '(про) Статті податкових декларацій', '', '', 0),
(233, '6414', 'Розрахунки з єдиного податку', 'АП', 0, 0, 0, 0, 0, 0, '(про) Статті податкових декларацій', '', '', 0),
(234, '6415', 'Розрахунки з інших податків', 'АП', 0, 0, 0, 0, 0, 0, 'Податки', '', '', 0),
(235, '6416', 'Розрахунки з акцизу', 'АП', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(236, '6417', 'Розрахунки з суднових зборів', 'АП', 0, 0, 0, 0, 0, 0, '(про) Статті податкових декларацій', '', '', 0),
(237, '6418', 'Розрахунки єдиного податку (ФОП-контрагенти)', 'АП', 0, 0, 1, 0, 0, 0, 'Контрагенти', '', '', 0),
(238, '642', 'Розрахунки з обов\'язкових платежів', 'АП', 0, 0, 0, 0, 0, 0, 'Податки', '', '', 0),
(239, '6431', 'Податкові зобов\'язання', 'А', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', 'Документи розрахунків із контрагентами', 0),
(240, '6432', 'Податкові зобов\'язання не підтверджені', 'П', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', 'Документи розрахунків із контрагентами', 0),
(241, '6433', 'Коригування податкових зобов\'язань', 'АП', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(242, '6434', 'Податкові зобов\'язання (роздріб)', 'АП', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(243, '6435', 'Умовний продаж', 'АП', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(244, '6441', 'Податковий кредит', 'П', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', 'Документи розрахунків із контрагентами', 0),
(245, '6442', 'Податковий кредит не підтверджений', 'А', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', 'Документи розрахунків із контрагентами', 0),
(246, '6443', 'Коригування податкового кредиту', 'АП', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(247, '651', 'Розрахунки з загальнообов\'язкового державного соціального страхування', 'АП', 0, 0, 0, 0, 0, 0, 'Податки', '(про) Статті податкових декларацій', 'Працівники організацій', 0),
(248, '652', 'Розрахунки із соціального страхування', 'АП', 0, 0, 0, 0, 0, 0, 'Податки', '(про) Статті податкових декларацій', 'Працівники організацій', 0),
(249, '653', '(не використовується) Розрахунки зі страхування на випадок безробіття', 'АП', 0, 0, 0, 0, 0, 0, 'Податки', '(про) Статті податкових декларацій', '', 0),
(250, '654', 'Розрахунки з індивідуального страхування', 'АП', 0, 0, 0, 0, 0, 0, 'Працівники організацій', '', '', 0),
(251, '655', 'Розрахунки зі страхування майна', 'АП', 0, 0, 0, 0, 0, 0, '', '', '', 0),
(252, '656', 'Розрахунки зі страхування від нещасних випадків', 'АП', 0, 0, 0, 0, 0, 0, 'Податки', '(про) Статті податкових декларацій', '', 0),
(253, '657', 'Розрахунки з ЄСВ (ФОП-контрагенти)', 'АП', 0, 0, 1, 0, 0, 0, 'Контрагенти', '', '', 0),
(254, '661', 'Розрахунки із заробітної плати', 'П', 0, 0, 0, 0, 0, 0, 'Працівники організацій', '', '', 0),
(255, '662', 'Розрахунки за депонентами', 'П', 0, 0, 0, 0, 0, 0, 'Працівники організацій', '', '', 0),
(256, '663', 'Розрахунки з інших виплат', 'П', 0, 0, 0, 0, 0, 0, 'Працівники організацій', '', '', 0),
(257, '6641', 'Розрахунки з виплат контрагентам (укр.банки)', 'П', 0, 0, 1, 0, 0, 0, 'Контрагенти', 'Договори', '', 0),
(258, '6644', 'Розрахунки за GIG-контрактами', 'П', 0, 0, 1, 0, 0, 0, 'Працівники організацій', '', '', 0),
(259, '6651', 'Розрахунки з виплат контрагентам', 'П', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', '', 0),
(260, '671', 'Розрахунки з нарахованих дивідендів', 'П', 0, 0, 0, 0, 0, 0, 'Контрагенти', '', '', 0),
(261, '672', 'Розрахунки з інших виплат', 'П', 0, 0, 0, 0, 0, 0, 'Контрагенти', '', '', 0),
(262, '680', 'Розрахунки, пов\'язані з необоротними активами та групами вибуття, які утримуються для продажу', 'АП', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', 'Документи розрахунків із контрагентами', 0),
(263, '6811', 'Розрахунки з отриманих авансів (у національній валюті)', 'П', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', 'Документи розрахунків із контрагентами', 0),
(264, '6812', 'Розрахунки з отриманих авансів (в іноземній валюті)', 'П', 0, 0, 1, 0, 0, 0, 'Контрагенти', 'Договори', 'Документи розрахунків із контрагентами', 0),
(265, '682', 'Внутрішні розрахунки', 'АП', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', '', 0),
(266, '683', 'Внутрішньогосподарські розрахунки', 'АП', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', '', 0),
(267, '684', 'Розрахунки за нарахованими відсотками', 'П', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', '', 0),
(268, '6841', 'Розрахунки за нарахованими відсотками (у національній валюті)', 'П', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', 'Документи розрахунків із контрагентами', 0),
(269, '6842', 'Розрахунки за нарахованими відсотками (в іноземній валюті)', 'П', 0, 0, 1, 0, 0, 0, 'Контрагенти', 'Договори', 'Документи розрахунків із контрагентами', 0),
(270, '6851', 'Розрахунки з іншими кредиторами (у національній валюті)', 'АП', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', 'Документи розрахунків із контрагентами', 0),
(271, '68511', 'Розрахунки з іншими кредиторами (у національній валюті)', 'АП', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', 'Документи розрахунків із контрагентами', 0),
(272, '68512', 'Розрахунки з іншими кредиторами (у національній валюті)', 'АП', 0, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', 'Документи розрахунків із контрагентами', 0),
(273, '6852', 'Розрахунки з іншими кредиторами (в іноземній валюті)', 'АП', 0, 0, 1, 0, 0, 0, 'Контрагенти', 'Договори', 'Документи розрахунків із контрагентами', 0),
(274, '6853', 'Розрахунки за виконавчими документами', 'АП', 0, 0, 0, 0, 0, 0, 'Виконавчі документи', '', '', 0),
(275, '68541', 'Розрахунки з іншими кредиторами (у національній валюті)', 'АП', 0, 0, 1, 0, 0, 0, 'Контрагенти', 'Договори', 'Документи розрахунків із контрагентами', 0),
(276, '69', 'Доходи майбутніх періодів', 'П', 0, 0, 0, 0, 0, 0, 'Доходи майбутніх періодів', '', '', 0),
(277, '701', 'Дохід від готової продукції', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '(про) Номенклатурні групи', '', 0),
(278, '702', 'Дохід від реалізації товарів', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '(про) Номенклатурні групи', '', 0),
(279, '703', 'Дохід від реалізації робіт та послуг', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '(про) Номенклатурні групи', '', 0),
(280, '7031', 'Labour Revenue (Existing business)', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '(про) Номенклатурні групи', '', 0),
(281, '7032', 'Labour Revenue (New Business)', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '(про) Номенклатурні групи', '', 0),
(282, '7033', 'Other Revenue', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '(про) Номенклатурні групи', '', 0),
(283, '7034', 'Inter-Group Revenue', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '(про) Номенклатурні групи', '', 0),
(284, '704', 'Відрахування з доходу', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '(про) Номенклатурні групи', '', 0),
(285, '705', 'Перестрахування', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '', '', 0),
(286, '7091', 'Дохід від роздрібних продажів', 'П', 0, 0, 0, 0, 0, 0, '(про) Статті доходів', '(про) Номенклатурні групи', '', 0),
(287, '7092', 'Відрахування з доходу від роздрібного продажу', 'А', 0, 0, 0, 0, 0, 0, '(про) Статті доходів', '(про) Номенклатурні групи', '', 0),
(288, '710', 'Дохід від первісного визнання та від зміни вартості активів, що враховуються за справедливою вартістю', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '', '', 0),
(289, '711', 'Дохід від купівлі-продажу іноземної валюти', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '', '', 0),
(290, '712', 'Дохід від інших оборотних активів', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '(про) Номенклатурні групи', '', 0),
(291, '713', 'Дохід від операційної оренди активів', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '', '', 0),
(292, '714', 'Дохід від операційної курсової різниці', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '', '', 0),
(293, '715', 'Отримані штрафи пені неустойки', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '', '', 0),
(294, '716', 'Відшкодування раніше списаних активів', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '', '', 0),
(295, '717', 'Дохід від списання кредиторської заборгованості', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '', '', 0),
(296, '718', 'Дохід від безоплатно отриманих оборотних активів', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '', '', 0),
(297, '719', 'Інші доходи від операційної діяльності', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '', '', 0),
(298, '721', 'Дохід від інвестицій в асоційовані підприємства', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '', '', 0),
(299, '722', 'Дохід від спільної діяльності', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '', '', 0),
(300, '723', 'Дохід від інвестицій у дочірні підприємства', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '', '', 0),
(301, '731', 'Дивіденди отримані', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '', '', 0),
(302, '732', 'Відсотки отримані', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '', '', 0),
(303, '7321', 'Внутрішньогрупові відсотки отримані / Inter-Group Interests USD', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '', '', 0),
(304, '733', 'Інші доходи від фінансових операцій', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '', '', 0),
(305, '740', 'Дохід від зміни вартості фінансових інструментів', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '', '', 0),
(306, '741', 'Дохід від фінансових інвестицій', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '', '', 0),
(307, '742', 'Дохід від відновлення корисності активів', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '', '', 0),
(308, '744', 'Дохід від неопераційної курсової різниці', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '', '', 0),
(309, '745', 'Дохід від безкоштовно отриманих активів', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '', '', 0),
(310, '746', 'Інші доходи', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '', '', 0),
(311, '751', '(не використовується) Відшкодування збитків від надзвичайних подій', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '', '', 0),
(312, '752', '(не використовується) Інші надзвичайні доходи', 'П', 0, 0, 0, 1, 0, 0, '(про) Статті доходів', '', '', 0),
(313, '76', 'Страхові платежі', 'П', 0, 0, 0, 1, 0, 0, '', '', '', 0),
(314, '791', 'Результат операційної діяльності', 'АП', 0, 0, 0, 1, 0, 0, '', '', '', 0),
(315, '792', 'Результат фінансових операцій', 'АП', 0, 0, 0, 1, 0, 0, '', '', '', 0),
(316, '793', 'Результат іншої звичайної діяльності', 'АП', 0, 0, 0, 1, 0, 0, '', '', '', 0),
(317, '794', '(не використовується) Результат надзвичайних подій', 'АП', 0, 0, 0, 1, 0, 0, '', '', '', 0),
(318, '801', 'Витрати сировини та матеріалів', 'А', 0, 0, 0, 1, 1, 0, '(про) Статті витрат', '', '', 0),
(319, '802', 'Витрати покупних напівфабрикатів та комплектуючих виробів', 'А', 0, 0, 0, 1, 1, 0, '(про) Статті витрат', '', '', 0),
(320, '803', 'Витрати палива та енергії', 'А', 0, 0, 0, 1, 1, 0, '(про) Статті витрат', '', '', 0),
(321, '804', 'Витрати тари та тарних матеріалів', 'А', 0, 0, 0, 1, 1, 0, '(про) Статті витрат', '', '', 0),
(322, '805', 'Витрати будівельних матеріалів', 'А', 0, 0, 0, 1, 1, 0, '(про) Статті витрат', '', '', 0),
(323, '806', 'Витрати запасних частин', 'А', 0, 0, 0, 1, 1, 0, '(про) Статті витрат', '', '', 0),
(324, '807', 'Витрати матеріалів сільськогосподарського призначення', 'А', 0, 0, 0, 1, 1, 0, '(про) Статті витрат', '', '', 0),
(325, '808', 'Витрати товарів', 'А', 0, 0, 0, 1, 1, 0, '(про) Статті витрат', '', '', 0),
(326, '809', 'Інші матеріальні витрати', 'А', 0, 0, 0, 1, 1, 0, '(про) Статті витрат', '', '', 0),
(327, '811', 'Виплати за окладами та тарифами', 'А', 0, 0, 0, 1, 1, 0, '(про) Статті витрат', '', '', 0),
(328, '812', 'Премії та заохочення', 'А', 0, 0, 0, 1, 1, 0, '(про) Статті витрат', '', '', 0),
(329, '813', 'Компенсаційні виплати', 'А', 0, 0, 0, 1, 1, 0, '(про) Статті витрат', '', '', 0),
(330, '814', 'Оплата відпусток', 'А', 0, 0, 0, 1, 1, 0, '(про) Статті витрат', '', '', 0),
(331, '815', 'Оплата іншого невідпрацьованого часу', 'А', 0, 0, 0, 1, 1, 0, '(про) Статті витрат', '', '', 0),
(332, '816', 'Інші витрати на оплату праці', 'А', 0, 0, 0, 1, 1, 0, '(про) Статті витрат', '', '', 0),
(333, '821', 'Відрахування на загальнообов\'язкове державне соціальне страхування', 'А', 0, 0, 0, 1, 1, 0, '(про) Статті витрат', '', '', 0),
(334, '822', '(не використовується) Відрахування на соціальне страхування', 'А', 0, 0, 0, 1, 1, 0, '(про) Статті витрат', '', '', 0),
(335, '823', '(не використовується) Страхування на випадок безробіття', 'А', 0, 0, 0, 1, 1, 0, '(про) Статті витрат', '', '', 0),
(336, '824', 'Відрахування на індивідуальне страхування', 'А', 0, 0, 0, 1, 1, 0, '(про) Статті витрат', '', '', 0),
(337, '831', 'Амортизація основних засобів', 'А', 0, 0, 0, 1, 1, 0, '(про) Статті витрат', '', '', 0),
(338, '832', 'Амортизація інших необоротних матеріальних активів', 'А', 0, 0, 0, 1, 1, 0, '(про) Статті витрат', '', '', 0),
(339, '833', 'Амортизація нематеріальних активів', 'А', 0, 0, 0, 1, 1, 0, '(про) Статті витрат', '', '', 0),
(340, '84', 'Інші операційні витрати', 'А', 0, 0, 0, 1, 1, 0, '(про) Статті витрат', '', '', 0),
(341, '851', 'Інші витрати на елементи, податок на прибуток', 'А', 0, 0, 0, 1, 1, 0, '(про) Статті неопераційних витрат', '', '', 0),
(342, '852', '(не використовується) Інші витрати на елементи, надзвичайні витрати', 'А', 0, 0, 0, 1, 1, 0, '(про) Статті неопераційних витрат', '', '', 0),
(343, '901', 'Собівартість реалізованої готової продукції', 'А', 0, 0, 0, 1, 0, 0, '(про) Номенклатурні групи', '(про) Статті витрат', '', 0),
(344, '902', 'Собівартість реалізованих товарів', 'А', 0, 0, 0, 1, 0, 0, '(про) Номенклатурні групи', '(про) Статті витрат', '', 0),
(345, '903', 'Собівартість реалізованих робіт та послуг', 'А', 0, 0, 0, 1, 0, 0, '(про) Номенклатурні групи', '(про) Статті витрат', '', 0),
(346, '904', 'Страхові виплати', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті витрат', '', '', 0),
(347, '91', 'Загальновиробничі витрати', 'А', 0, 0, 0, 1, 1, 0, 'Підрозділи', '(про) Статті витрат', '', 0),
(348, '9101', 'Загальновиробничі Витрати / Overheads - Expenses', 'А', 0, 0, 0, 1, 1, 0, 'Підрозділи', '(про) Статті витрат', 'Номенклатурні групи', 0),
(349, '92', 'Адміністративні витрати', 'А', 0, 0, 0, 1, 0, 0, '(про) Підрозділи', '(про) Статті витрат', '', 0),
(350, '93', 'Витрати збут', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті витрат', '', '', 0),
(351, '940', 'Витрати від первісного визнання та від зміни вартості активів, що враховуються за справедливою вартістю', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті витрат', '', '', 0),
(352, '941', 'Витрати на дослідження та розробки', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті витрат', '', '', 0),
(353, '942', 'Витрати купівлю-продаж іноземної валюти', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті витрат', '', '', 0),
(354, '943', 'Собівартість реалізованих виробничих запасів', 'А', 0, 0, 0, 1, 0, 0, '(про) Номенклатурні групи', '(про) Статті витрат', '', 0),
(355, '944', 'Сумнівні та безнадійні борги', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті витрат', '', '', 0),
(356, '945', 'Витрати від операційної курсової різниці', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті витрат', '', '', 0),
(357, '946', 'Витрати від знецінення запасів', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті витрат', '', '', 0),
(358, '947', 'Нестачі та втрати від псування цінностей', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті витрат', '', '', 0),
(359, '948', 'Визнані штрафи, пені, неустойки', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті витрат', '', '', 0),
(360, '949', 'Інші витрати операційної діяльності', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті витрат', '', '', 0),
(361, '9491', 'Інші Expenses відносяться до Investors - below EBIDTA', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті витрат', '', '', 0),
(362, '9492', 'Expenses of past expenditures - після EBIDTA', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті витрат', '', '', 0),
(363, '9493', 'Financial Aid', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті витрат', '', '', 0),
(364, '951', 'Відсотки за кредит', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті неопераційних витрат', '', '', 0),
(365, '95101', 'Відсотки за Кредитами Пов\'язаних Особ/Loan Interst Related Parties', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті неопераційних витрат', '', '', 0),
(366, '95102', 'Відсотки за Кредитами/Loan Interst Third Parties', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті неопераційних витрат', '', '', 0),
(367, '95103', 'Комісії Банків Продаж та Купівля Валюти/Bank Charges - Currency Auction', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті неопераційних витрат', '', '', 0),
(368, '95104', 'Банківські Комісії Платежі / Bank Charges - Transfers', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті неопераційних витрат', '', '', 0),
(369, '95105', 'Курсові Різниці Рахунки та Каса /Rate Differences Cash', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті неопераційних витрат', '', '', 0),
(370, '95106', 'Курсові Різниці Баланси/Rate Differences Balances', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті неопераційних витрат', '', '', 0),
(371, '95108', 'Інші Фінансові Витрати та Доходи /Other Financial Expenses and Income', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті неопераційних витрат', '', '', 0),
(372, '95109', 'Внутрішньогрупові Відсотки за Кредитами /Loan Interst Inter-Group', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті неопераційних витрат', '', '', 0),
(373, '952', 'Інші фінансові витрати', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті неопераційних витрат', '', '', 0),
(374, '961', 'Втрати від інвестицій в асоційовані підприємства', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті неопераційних витрат', '', '', 0),
(375, '962', 'Втрати від спільної діяльності', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті неопераційних витрат', '', '', 0),
(376, '963', 'Втрати від інвестицій у дочірні підприємства', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті неопераційних витрат', '', '', 0),
(377, '970', 'Витрати від зміни вартості фінансових інструментів', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті неопераційних витрат', '', '', 0),
(378, '971', 'Собівартість реалізованих фінансових інвестицій', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті неопераційних витрат', '', '', 0),
(379, '972', 'Втрати від зменшення корисності активів', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті неопераційних витрат', '', '', 0),
(380, '974', 'Втрати від неопераційних курсових різниць', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті неопераційних витрат', '', '', 0),
(381, '975', 'Зниження необоротних активів та фінансових інвестицій', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті неопераційних витрат', '', '', 0),
(382, '976', 'Списання необоротних активів', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті неопераційних витрат', '', '', 0),
(383, '977', 'Інші витрати діяльності', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті неопераційних витрат', '', '', 0),
(384, '981', 'Податок на прибуток від усіх видів діяльності', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті неопераційних витрат', '', '', 0),
(385, '982', '(не використовується) Податок на прибуток від надзвичайних подій', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті неопераційних витрат', '', '', 0),
(386, '991', '(не використовується) Втрати від стихійного лиха', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті неопераційних витрат', '', '', 0),
(387, '992', '(не використовується) Втрати від техногенних катастроф та аварій', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті неопераційних витрат', '', '', 0),
(388, '993', '(не використовується) Інші надзвичайні витрати', 'А', 0, 0, 0, 1, 0, 0, '(про) Статті неопераційних витрат', '', '', 0),
(389, '1', 'Орендовані необоротні активи', 'А', 1, 0, 0, 0, 0, 0, 'Орендовані необоротні активи', '', '', 0),
(390, '2', 'Активи на відповідальному зберіганні', 'А', 1, 1, 0, 0, 0, 0, 'Контрагенти', 'Номенклатура', '', 0),
(391, '21', 'Устаткування прийняте для монтажу', 'А', 1, 1, 0, 0, 0, 0, 'Контрагенти', 'Номенклатура', '', 0),
(392, '221', 'Матеріали на складі', 'А', 1, 1, 0, 0, 0, 0, 'Контрагенти', 'Номенклатура', 'Склади', 0),
(393, '222', 'Матеріали, передані у виробництво', 'А', 1, 1, 0, 0, 0, 0, 'Контрагенти', 'Номенклатура', '', 0),
(394, '23', 'Матеріальні цінності на відповідальному зберіганні', 'А', 1, 1, 0, 0, 0, 0, 'Контрагенти', 'Номенклатура', '', 0),
(395, '241', 'Товари на складі', 'А', 1, 1, 0, 0, 0, 0, 'Партії', 'Номенклатура', 'Склади', 0),
(396, '242', 'Товари, передані на комісію', 'А', 1, 1, 0, 0, 0, 0, 'Партії', 'Контрагенти', 'Номенклатура', 0),
(397, '25', 'Матеріальні цінності довірителя', 'А', 1, 1, 0, 0, 0, 0, 'Контрагенти', 'Номенклатура', '', 0),
(398, '3', 'Контрактні зобов\'язання', 'П', 1, 0, 0, 0, 0, 0, 'Контрактні зобов\'язання', '', '', 0),
(399, '41', 'Непередбачені активи', 'А', 1, 0, 0, 0, 0, 0, '', '', '', 0),
(400, '42', 'Непередбачені зобов\'язання', 'П', 1, 0, 0, 0, 0, 0, '', '', '', 0),
(401, '5', 'Гарантії та забезпечення надані', 'А', 1, 0, 0, 0, 0, 0, 'Гарантії', '', '', 0),
(402, '6', 'Гарантії та забезпечення отримані', 'А', 1, 0, 0, 0, 0, 0, 'Гарантії', '', '', 0),
(403, '71', 'Списана дебіторська заборгованість', 'А', 1, 0, 0, 0, 0, 0, 'Контрагенти', '', '', 0),
(404, '72', 'Невідшкодовані недоліки та втрати від псування цінностей', 'А', 1, 0, 0, 0, 0, 0, 'Контрагенти', '', '', 0),
(405, '8', 'Бланки суворого обліку', 'А', 1, 1, 0, 0, 0, 0, 'Номенклатура', 'Склади', '', 0),
(406, '9', 'Амортизаційні відрахування', 'АП', 1, 0, 0, 0, 0, 0, '(про) Види використання амортизації', '', '', 0),
(407, 'КЗ', 'Коригування нормованих витрат (податковий облік)', 'А', 1, 0, 0, 1, 0, 0, '(про) Статті податкових декларацій', '', '', 0),
(408, 'МЦ', 'Малоцінні активи в експлуатації', 'А', 1, 1, 0, 1, 1, 1, 'Працівники організацій', 'Призначення використання', 'Партії малоцінки в експлуатації', 0),
(409, 'ОК', 'Закупівлі у платників єдиного податку через підзвітників', 'П', 1, 0, 0, 0, 0, 0, 'Контрагенти', 'Договори', '', 0),
(410, 'РІ', 'Різниці з податку на прибуток (ручні коригування)', 'АП', 1, 0, 0, 0, 0, 0, '(про) Статті податкових декларацій', '(про) Групи основних засобів', '', 0),
(411, 'УЗ', 'Зниження запасів', 'А', 1, 0, 0, 0, 0, 0, 'Номенклатура', 'Партії', '', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `Subscriptions`
--

CREATE TABLE `Subscriptions` (
  `id` int NOT NULL,
  `subscription_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cost` decimal(10,2) NOT NULL,
  `valid_for_days` int DEFAULT NULL,
  `company_id` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `max_users` int NOT NULL DEFAULT '5',
  `demo_period_days` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Дамп данных таблицы `Subscriptions`
--

INSERT INTO `Subscriptions` (`id`, `subscription_name`, `cost`, `valid_for_days`, `company_id`, `created_at`, `max_users`, `demo_period_days`) VALUES
(1, 'Один месяц', 300.00, 31, 1, '2023-10-31 11:27:14', 31, 31);

-- --------------------------------------------------------

--
-- Структура таблицы `SubscriptionTypes`
--

CREATE TABLE `SubscriptionTypes` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `valid_for_days` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `peeople_count` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Дамп данных таблицы `SubscriptionTypes`
--

INSERT INTO `SubscriptionTypes` (`id`, `name`, `valid_for_days`, `price`, `peeople_count`) VALUES
(1, 'Один місяць', 31, 300.00, 30),
(2, 'Три місяці', 60, 800.00, 30);

-- --------------------------------------------------------

--
-- Структура таблицы `UserCompanies`
--

CREATE TABLE `UserCompanies` (
  `user_id` int NOT NULL,
  `company_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Дамп данных таблицы `UserCompanies`
--

INSERT INTO `UserCompanies` (`user_id`, `company_id`) VALUES
(2, 1),
(3, 1),
(6, 32);

-- --------------------------------------------------------

--
-- Структура таблицы `UserRoles`
--

CREATE TABLE `UserRoles` (
  `user_id` int NOT NULL,
  `role_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Дамп данных таблицы `UserRoles`
--

INSERT INTO `UserRoles` (`user_id`, `role_id`) VALUES
(2, 1),
(3, 1),
(6, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `Users`
--

CREATE TABLE `Users` (
  `id` int NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `password` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `second_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Дамп данных таблицы `Users`
--

INSERT INTO `Users` (`id`, `username`, `email`, `phone_number`, `password`, `first_name`, `second_name`, `last_name`, `avatar_url`, `created_at`) VALUES
(2, 'sasha', 'test@example.com', '380639577038', '$2y$10$7mNKZJixlhipyZaxRyFgcOPjLg8vkaUIESQFOXqFgHe9bscyPQ9Ve', 'Александр', 'Игоревич', 'Кощенко', NULL, '2023-10-17 11:17:46'),
(3, 'petya_228_kiev', 'newemail@example.com', '33333333333', '$2y$10$7mNKZJixlhipyZaxRyFgcOPjLg8vkaUIESQFOXqFgHe9bscyPQ9Ve', 'Петя', 'Петров', 'Петренко', NULL, '2023-10-17 11:17:46'),
(4, 'ddddffd@dfnhf.dfdf', 'ddddffd@dfnhf.dfdf', NULL, '$2y$10$stYkV8TOw6TXXWfZDRt5GOWIiw4A5ts2SnIjAtTr4c33/tNTfvL7e', 'Fhhhff', 'LKB', 'lkjLJKHBKJhb', NULL, '2023-11-24 14:21:46'),
(6, 'RegTestNameUsername', 'regtest@example.com', NULL, '$2y$10$7mNKZJixlhipyZaxRyFgcOPjLg8vkaUIESQFOXqFgHe9bscyPQ9Ve', 'RegTestNameFirst', 'RegTestNameSecond', 'RegTestNameLast', NULL, '2024-01-17 08:12:53');

-- --------------------------------------------------------

--
-- Структура таблицы `WarehouseDocuments`
--

CREATE TABLE `WarehouseDocuments` (
  `id` int NOT NULL,
  `enterpriseId` int NOT NULL,
  `theDate` datetime NOT NULL,
  `number` int NOT NULL,
  `theForm` enum('приход','расход','другое') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `fromPartnerId` int DEFAULT NULL,
  `toPartnerId` int DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `vatTotal` decimal(10,2) DEFAULT NULL,
  `exciseTaxTotal` decimal(10,2) DEFAULT NULL,
  `currencyId` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `Warehouses`
--

CREATE TABLE `Warehouses` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `main_production` tinyint(1) DEFAULT NULL,
  `country` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `postal_address` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `photo` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `number` int DEFAULT NULL,
  `comment` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `manager` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `production` tinyint(1) DEFAULT NULL,
  `inactive` tinyint(1) DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Дамп данных таблицы `Warehouses`
--

INSERT INTO `Warehouses` (`id`, `name`, `main_production`, `country`, `postal_address`, `photo`, `number`, `comment`, `manager`, `production`, `inactive`, `email`, `phone`) VALUES
(1, 'Warehouse A', 1, 'Country A', 'Address A', 'photo1.jpg', 101, 'Main warehouse for production', 'John Doe', 1, 0, 'john@example.com', '123-456-7890'),
(2, 'Warehouse B', 0, 'Country B', 'Address B', 'photo2.jpg', 102, 'Additional warehouse', 'Jane Smith', 0, 1, 'jane@example.com', '987-654-3210');

-- --------------------------------------------------------

--
-- Структура таблицы `WarehousesDataAccess`
--

CREATE TABLE `WarehousesDataAccess` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `warehouse_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `WarehousesSections`
--

CREATE TABLE `WarehousesSections` (
  `id` int NOT NULL,
  `section_number` int DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `default_section` tinyint(1) DEFAULT NULL,
  `comment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `state` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `item` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `serial_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `account_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `unit_cost` decimal(10,2) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `warehouse_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `WarehousesSections`
--

INSERT INTO `WarehousesSections` (`id`, `section_number`, `name`, `default_section`, `comment`, `state`, `item`, `serial_number`, `account_number`, `quantity`, `unit_cost`, `amount`, `warehouse_id`) VALUES
(1, 101, 'Section A', 1, 'This is the default section', 'Active', 'Product A', 'SN123', 'ACC001', 100, 10.50, 1050.00, 1),
(2, 102, 'Section B', 0, 'Additional section', 'Inactive', 'Product B', 'SN456', 'ACC002', 75, 8.00, 600.00, 1),
(3, 201, 'Section X', 1, 'Default section for another warehouse', 'Active', 'Product X', 'SN789', 'ACC003', 50, 15.00, 750.00, 2);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `AccountContacts`
--
ALTER TABLE `AccountContacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `AccountContacts_ibfk_1` (`account_id`),
  ADD KEY `AccountContacts_ibfk_2` (`contact_id`);

--
-- Индексы таблицы `Accounts`
--
ALTER TABLE `Accounts`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `AccountsUsers`
--
ALTER TABLE `AccountsUsers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `Categories`
--
ALTER TABLE `Categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`);

--
-- Индексы таблицы `CategoryCharacteristics`
--
ALTER TABLE `CategoryCharacteristics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Индексы таблицы `Companies`
--
ALTER TABLE `Companies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `CompaniesUser_ibfk_1` (`company_created_user_id`);

--
-- Индексы таблицы `CompanyAccounts`
--
ALTER TABLE `CompanyAccounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_company_account` (`companyId`,`accountId`),
  ADD KEY `accountId` (`accountId`);

--
-- Индексы таблицы `CompanyContacts`
--
ALTER TABLE `CompanyContacts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_company_account` (`companyId`,`contact_id`),
  ADD KEY `accountId` (`contact_id`);

--
-- Индексы таблицы `CompanyEnterprises`
--
ALTER TABLE `CompanyEnterprises`
  ADD PRIMARY KEY (`companyId`,`eGRPOUId`),
  ADD KEY `eGRPOUId` (`eGRPOUId`) USING BTREE;

--
-- Индексы таблицы `CompanySubscriptions`
--
ALTER TABLE `CompanySubscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `subscription_type_id` (`subscription_type_id`);

--
-- Индексы таблицы `company_file_server_mapping`
--
ALTER TABLE `company_file_server_mapping`
  ADD PRIMARY KEY (`company_id`),
  ADD KEY `file_server_id` (`file_server_id`);

--
-- Индексы таблицы `Contacts`
--
ALTER TABLE `Contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_id` (`account_id`);

--
-- Индексы таблицы `DimensionRanges`
--
ALTER TABLE `DimensionRanges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category_characteristic_id` (`category_characteristic_id`);

--
-- Индексы таблицы `DimensionRangeValues`
--
ALTER TABLE `DimensionRangeValues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dimension_range_id` (`dimension_range_id`);

--
-- Индексы таблицы `EnterpriseAccountPlans`
--
ALTER TABLE `EnterpriseAccountPlans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `EnterpriseAccountPlans_ibfk_1` (`eGRPOUId`);

--
-- Индексы таблицы `Enterprises`
--
ALTER TABLE `Enterprises`
  ADD PRIMARY KEY (`enterpriseId`),
  ADD UNIQUE KEY `eGRPOU` (`eGRPOU`);

--
-- Индексы таблицы `file_servers`
--
ALTER TABLE `file_servers`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `FinancialDocuments`
--
ALTER TABLE `FinancialDocuments`
  ADD PRIMARY KEY (`financialDocumentId`);

--
-- Индексы таблицы `FOP2_Taxes`
--
ALTER TABLE `FOP2_Taxes`
  ADD PRIMARY KEY (`FOP2_Taxes_id`),
  ADD KEY `eGRPOUId_id` (`eGRPOUId`);

--
-- Индексы таблицы `FOP3_Taxes`
--
ALTER TABLE `FOP3_Taxes`
  ADD PRIMARY KEY (`FOP3_Taxes_id`),
  ADD KEY `eGRPOUId_id` (`eGRPOUId`);

--
-- Индексы таблицы `Integrations`
--
ALTER TABLE `Integrations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `companyId` (`companyId`);

--
-- Индексы таблицы `Kveds`
--
ALTER TABLE `Kveds`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `KvedsEnterprises`
--
ALTER TABLE `KvedsEnterprises`
  ADD PRIMARY KEY (`Enterprises`,`Kved`),
  ADD KEY `KvedEnterprises_ibfk_2` (`Kved`);

--
-- Индексы таблицы `Nomenclature`
--
ALTER TABLE `Nomenclature`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomenclature_code` (`nomenclature_code`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `idx_category_id` (`category_id`);

--
-- Индексы таблицы `NomenclatureCharacteristics`
--
ALTER TABLE `NomenclatureCharacteristics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nomenclature_id` (`nomenclature_id`);

--
-- Индексы таблицы `PendingSubscriptionUpdates`
--
ALTER TABLE `PendingSubscriptionUpdates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `old_subscription_id` (`old_subscription_id`),
  ADD KEY `new_type_id` (`new_type_id`);

--
-- Индексы таблицы `Permissions`
--
ALTER TABLE `Permissions`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `ProductCardCharacteristics`
--
ALTER TABLE `ProductCardCharacteristics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_card_id` (`product_card_id`);

--
-- Индексы таблицы `ProductCards`
--
ALTER TABLE `ProductCards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `nomenclature_id` (`nomenclature_id`);

--
-- Индексы таблицы `Roles`
--
ALTER TABLE `Roles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_role_id` (`id`);

--
-- Индексы таблицы `StandardAccountsPlanTable`
--
ALTER TABLE `StandardAccountsPlanTable`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `Subscriptions`
--
ALTER TABLE `Subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`);

--
-- Индексы таблицы `SubscriptionTypes`
--
ALTER TABLE `SubscriptionTypes`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `UserCompanies`
--
ALTER TABLE `UserCompanies`
  ADD PRIMARY KEY (`user_id`,`company_id`),
  ADD KEY `company_id` (`company_id`);

--
-- Индексы таблицы `UserRoles`
--
ALTER TABLE `UserRoles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `idx_user_id_role_id` (`user_id`,`role_id`);

--
-- Индексы таблицы `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `WarehouseDocuments`
--
ALTER TABLE `WarehouseDocuments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `enterpriseId` (`enterpriseId`);

--
-- Индексы таблицы `Warehouses`
--
ALTER TABLE `Warehouses`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `WarehousesDataAccess`
--
ALTER TABLE `WarehousesDataAccess`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- Индексы таблицы `WarehousesSections`
--
ALTER TABLE `WarehousesSections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `AccountContacts`
--
ALTER TABLE `AccountContacts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `Accounts`
--
ALTER TABLE `Accounts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT для таблицы `AccountsUsers`
--
ALTER TABLE `AccountsUsers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT для таблицы `Categories`
--
ALTER TABLE `Categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `CategoryCharacteristics`
--
ALTER TABLE `CategoryCharacteristics`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT для таблицы `Companies`
--
ALTER TABLE `Companies`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT для таблицы `CompanyAccounts`
--
ALTER TABLE `CompanyAccounts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT для таблицы `CompanyContacts`
--
ALTER TABLE `CompanyContacts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT для таблицы `CompanySubscriptions`
--
ALTER TABLE `CompanySubscriptions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `Contacts`
--
ALTER TABLE `Contacts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `DimensionRanges`
--
ALTER TABLE `DimensionRanges`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `DimensionRangeValues`
--
ALTER TABLE `DimensionRangeValues`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `EnterpriseAccountPlans`
--
ALTER TABLE `EnterpriseAccountPlans`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `Enterprises`
--
ALTER TABLE `Enterprises`
  MODIFY `enterpriseId` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT для таблицы `file_servers`
--
ALTER TABLE `file_servers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `FinancialDocuments`
--
ALTER TABLE `FinancialDocuments`
  MODIFY `financialDocumentId` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `FOP2_Taxes`
--
ALTER TABLE `FOP2_Taxes`
  MODIFY `FOP2_Taxes_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT для таблицы `FOP3_Taxes`
--
ALTER TABLE `FOP3_Taxes`
  MODIFY `FOP3_Taxes_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT для таблицы `Integrations`
--
ALTER TABLE `Integrations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT для таблицы `Kveds`
--
ALTER TABLE `Kveds`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT для таблицы `Nomenclature`
--
ALTER TABLE `Nomenclature`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT для таблицы `NomenclatureCharacteristics`
--
ALTER TABLE `NomenclatureCharacteristics`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `PendingSubscriptionUpdates`
--
ALTER TABLE `PendingSubscriptionUpdates`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `Permissions`
--
ALTER TABLE `Permissions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT для таблицы `ProductCardCharacteristics`
--
ALTER TABLE `ProductCardCharacteristics`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `ProductCards`
--
ALTER TABLE `ProductCards`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `Roles`
--
ALTER TABLE `Roles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=310102;

--
-- AUTO_INCREMENT для таблицы `StandardAccountsPlanTable`
--
ALTER TABLE `StandardAccountsPlanTable`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=412;

--
-- AUTO_INCREMENT для таблицы `Subscriptions`
--
ALTER TABLE `Subscriptions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `SubscriptionTypes`
--
ALTER TABLE `SubscriptionTypes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `Users`
--
ALTER TABLE `Users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `Categories`
--
ALTER TABLE `Categories`
  ADD CONSTRAINT `Categories_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `Companies` (`id`);

--
-- Ограничения внешнего ключа таблицы `CategoryCharacteristics`
--
ALTER TABLE `CategoryCharacteristics`
  ADD CONSTRAINT `CategoryCharacteristics_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `Categories` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `Companies`
--
ALTER TABLE `Companies`
  ADD CONSTRAINT `CompaniesUser_ibfk_1` FOREIGN KEY (`company_created_user_id`) REFERENCES `Users` (`id`);

--
-- Ограничения внешнего ключа таблицы `CompanyAccounts`
--
ALTER TABLE `CompanyAccounts`
  ADD CONSTRAINT `CompanyAccounts_ibfk_1` FOREIGN KEY (`companyId`) REFERENCES `Companies` (`id`),
  ADD CONSTRAINT `CompanyAccounts_ibfk_2` FOREIGN KEY (`accountId`) REFERENCES `Accounts` (`id`);

--
-- Ограничения внешнего ключа таблицы `CompanyContacts`
--
ALTER TABLE `CompanyContacts`
  ADD CONSTRAINT `CompanyContacts_ibfk_1` FOREIGN KEY (`companyId`) REFERENCES `Companies` (`id`);

--
-- Ограничения внешнего ключа таблицы `CompanyEnterprises`
--
ALTER TABLE `CompanyEnterprises`
  ADD CONSTRAINT `CompanyEnterprises_ibfk_1` FOREIGN KEY (`companyId`) REFERENCES `Companies` (`id`),
  ADD CONSTRAINT `CompanyEnterprises_ibfk_2` FOREIGN KEY (`eGRPOUId`) REFERENCES `Enterprises` (`eGRPOU`);

--
-- Ограничения внешнего ключа таблицы `CompanySubscriptions`
--
ALTER TABLE `CompanySubscriptions`
  ADD CONSTRAINT `CompanySubscriptions_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `Companies` (`id`),
  ADD CONSTRAINT `CompanySubscriptions_ibfk_2` FOREIGN KEY (`subscription_type_id`) REFERENCES `SubscriptionTypes` (`id`);

--
-- Ограничения внешнего ключа таблицы `company_file_server_mapping`
--
ALTER TABLE `company_file_server_mapping`
  ADD CONSTRAINT `company_file_server_mapping_ibfk_1` FOREIGN KEY (`file_server_id`) REFERENCES `file_servers` (`id`);

--
-- Ограничения внешнего ключа таблицы `Contacts`
--
ALTER TABLE `Contacts`
  ADD CONSTRAINT `Contacts_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `Accounts` (`id`);

--
-- Ограничения внешнего ключа таблицы `DimensionRanges`
--
ALTER TABLE `DimensionRanges`
  ADD CONSTRAINT `DimensionRanges_ibfk_1` FOREIGN KEY (`category_characteristic_id`) REFERENCES `CategoryCharacteristics` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `DimensionRangeValues`
--
ALTER TABLE `DimensionRangeValues`
  ADD CONSTRAINT `DimensionRangeValues_ibfk_1` FOREIGN KEY (`dimension_range_id`) REFERENCES `DimensionRanges` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `EnterpriseAccountPlans`
--
ALTER TABLE `EnterpriseAccountPlans`
  ADD CONSTRAINT `EnterpriseAccountPlans_ibfk_1` FOREIGN KEY (`eGRPOUId`) REFERENCES `Enterprises` (`eGRPOU`);

--
-- Ограничения внешнего ключа таблицы `FOP2_Taxes`
--
ALTER TABLE `FOP2_Taxes`
  ADD CONSTRAINT `FOP2_Taxes_ibfk_1` FOREIGN KEY (`eGRPOUId`) REFERENCES `Enterprises` (`eGRPOU`);

--
-- Ограничения внешнего ключа таблицы `FOP3_Taxes`
--
ALTER TABLE `FOP3_Taxes`
  ADD CONSTRAINT `FOP3_Taxes_ibfk_1` FOREIGN KEY (`eGRPOUId`) REFERENCES `Enterprises` (`eGRPOU`);

--
-- Ограничения внешнего ключа таблицы `Integrations`
--
ALTER TABLE `Integrations`
  ADD CONSTRAINT `Integrations_ibfk_1` FOREIGN KEY (`companyId`) REFERENCES `Companies` (`id`);

--
-- Ограничения внешнего ключа таблицы `KvedsEnterprises`
--
ALTER TABLE `KvedsEnterprises`
  ADD CONSTRAINT `KvedEnterprises_ibfk_1` FOREIGN KEY (`Enterprises`) REFERENCES `Enterprises` (`eGRPOU`),
  ADD CONSTRAINT `KvedEnterprises_ibfk_2` FOREIGN KEY (`Kved`) REFERENCES `Kveds` (`id`);

--
-- Ограничения внешнего ключа таблицы `Nomenclature`
--
ALTER TABLE `Nomenclature`
  ADD CONSTRAINT `Nomenclature_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `Companies` (`id`);

--
-- Ограничения внешнего ключа таблицы `NomenclatureCharacteristics`
--
ALTER TABLE `NomenclatureCharacteristics`
  ADD CONSTRAINT `NomenclatureCharacteristics_ibfk_1` FOREIGN KEY (`nomenclature_id`) REFERENCES `Nomenclature` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `PendingSubscriptionUpdates`
--
ALTER TABLE `PendingSubscriptionUpdates`
  ADD CONSTRAINT `PendingSubscriptionUpdates_fk0` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`),
  ADD CONSTRAINT `PendingSubscriptionUpdates_fk1` FOREIGN KEY (`old_subscription_id`) REFERENCES `CompanySubscriptions` (`id`),
  ADD CONSTRAINT `PendingSubscriptionUpdates_fk2` FOREIGN KEY (`new_type_id`) REFERENCES `SubscriptionTypes` (`id`);

--
-- Ограничения внешнего ключа таблицы `ProductCardCharacteristics`
--
ALTER TABLE `ProductCardCharacteristics`
  ADD CONSTRAINT `ProductCardCharacteristics_ibfk_1` FOREIGN KEY (`product_card_id`) REFERENCES `ProductCards` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `ProductCards`
--
ALTER TABLE `ProductCards`
  ADD CONSTRAINT `ProductCards_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `Companies` (`id`),
  ADD CONSTRAINT `ProductCards_ibfk_2` FOREIGN KEY (`nomenclature_id`) REFERENCES `Nomenclature` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `UserCompanies`
--
ALTER TABLE `UserCompanies`
  ADD CONSTRAINT `UserCompanies_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`),
  ADD CONSTRAINT `UserCompanies_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `Companies` (`id`);

--
-- Ограничения внешнего ключа таблицы `UserRoles`
--
ALTER TABLE `UserRoles`
  ADD CONSTRAINT `UserRoles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`);

--
-- Ограничения внешнего ключа таблицы `WarehousesDataAccess`
--
ALTER TABLE `WarehousesDataAccess`
  ADD CONSTRAINT `WarehousesDataAccess_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`id`),
  ADD CONSTRAINT `WarehousesDataAccess_ibfk_2` FOREIGN KEY (`warehouse_id`) REFERENCES `Warehouses` (`id`);

--
-- Ограничения внешнего ключа таблицы `WarehousesSections`
--
ALTER TABLE `WarehousesSections`
  ADD CONSTRAINT `WarehousesSections_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `Warehouses` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
