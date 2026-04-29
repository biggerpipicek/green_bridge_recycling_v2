-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Počítač: 127.0.0.1
-- Vytvořeno: Stř 29. dub 2026, 13:42
-- Verze serveru: 10.4.32-MariaDB
-- Verze PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `green_bridge_recycling_v2`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `activity_log`
--

INSERT INTO `activity_log` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `description`, `created_at`) VALUES
(1, 1, 'login', 'user', 1, 'User #1 logged in', '2026-04-21 10:35:44'),
(2, 1, 'create incoming_order', 'order', 1, 'User #1 created Incoming order No.GBR-in-2026-00001', '2026-04-21 10:36:41'),
(3, 1, 'create incoming_order', 'order', 2, 'User #1 created Incoming order No.GBR-out-2026-00001', '2026-04-21 10:42:34'),
(4, 1, 'create incoming_order', 'order', 3, 'User #1 created Incoming order No.GBR-out-2026-00002', '2026-04-21 10:42:57'),
(5, 7, 'login', 'user', 7, 'User #7 logged in', '2026-04-23 10:07:02'),
(6, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order ', '2026-04-23 12:31:14'),
(7, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-23 12:31:20'),
(8, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-23 12:32:07'),
(9, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 400DE2FCF08D', '2026-04-23 12:32:17'),
(10, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order ', '2026-04-27 09:23:38'),
(11, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order ', '2026-04-27 09:24:36'),
(12, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order ', '2026-04-27 09:24:50'),
(13, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order ', '2026-04-27 09:24:51'),
(14, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order ', '2026-04-27 09:26:51'),
(15, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order ', '2026-04-27 09:26:53'),
(16, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order ', '2026-04-27 09:32:17'),
(17, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order ', '2026-04-27 09:32:21'),
(18, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-27 09:36:44'),
(19, 7, 'track_and_trace', 'order', 7, 'User #7 tried to track nothing', '2026-04-27 09:37:52'),
(20, 7, 'track_and_trace', 'order', 7, 'User #7 tried to track nothing', '2026-04-27 09:39:08'),
(21, 7, 'track_and_trace', 'order', 7, 'User #7 tried to track nothing', '2026-04-27 09:40:01'),
(22, 7, 'track_and_trace', 'order', 7, 'User #7 tried to track nothing', '2026-04-27 09:40:30'),
(23, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-27 09:40:36'),
(24, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-27 09:40:39'),
(25, 7, 'track_and_trace', 'order', 7, 'User #7 tried to track nothing', '2026-04-27 09:41:23'),
(26, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-27 09:41:26'),
(27, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-27 09:42:07'),
(28, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-27 09:45:23'),
(29, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-27 09:46:15'),
(30, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-27 10:00:58'),
(31, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-27 10:01:39'),
(32, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-27 10:02:30'),
(33, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-27 10:03:28'),
(34, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-27 10:03:36'),
(35, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-27 10:03:43'),
(36, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-27 10:04:49'),
(37, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-27 10:04:58'),
(38, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-27 10:05:13'),
(39, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-27 10:07:41'),
(40, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-27 10:07:46'),
(41, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-27 10:08:09'),
(42, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-27 10:08:47'),
(43, 7, 'track_and_trace', 'order', 7, 'User #7 tried to track nothing', '2026-04-27 10:08:53'),
(44, 7, 'update', 'partners', 1, 'Updated Client data: contact_info (\'w.ania@schredder.pl\' -> \'w.kania@schredder.pl\')', '2026-04-27 12:44:57'),
(45, 7, 'update', 'partners', 1, 'Updated Client data: contact_info (\'w.kania@schredder.pl\' -> \'w.ania@schredder.pl\')', '2026-04-27 12:45:37'),
(46, 7, 'update', 'partners', 1, 'Updated Client data: contact_info (\'w.ania@schredder.pl\' -> \'w.kania@schredder.pl\')', '2026-04-27 12:45:55'),
(47, 7, 'client', 'list', 7, 'User #7 added client Guhring - Sulkov to list', '2026-04-27 12:50:44'),
(48, 7, 'update', 'orders', 2, 'Updated Order: brutto_w (\'3.00\' -> \'12\'), netto_w (\'3.00\' -> \'9\')', '2026-04-27 14:02:14'),
(49, 7, 'client', 'list', 7, 'User #7 added client Guhring France to list', '2026-04-28 12:26:35'),
(50, 7, 'client', 'list', 7, 'User #7 added client Bodo CNC - Techni to list', '2026-04-28 12:27:44'),
(51, 7, 'client', 'list', 7, 'User #7 added client Roterberg to list', '2026-04-28 12:28:05'),
(52, 7, 'client', 'list', 7, 'User #7 added client MacoSteel to list', '2026-04-28 12:28:40'),
(53, 7, 'client', 'list', 7, 'User #7 added client Klingelnberg GmbH to list', '2026-04-28 12:29:21'),
(54, 7, 'client', 'list', 7, 'User #7 added client Fertigungsgerätebau A. Steinbach GmbH & Co. KG to list', '2026-04-28 12:30:01'),
(55, 7, 'client', 'list', 7, 'User #7 added client Mavidis to list', '2026-04-28 12:30:35'),
(56, 7, 'client', 'list', 7, 'User #7 added client Halter Outils de Coupe to list', '2026-04-28 12:31:10'),
(57, 7, 'client', 'list', 7, 'User #7 added client SKF Sealing Solutions GmbH to list', '2026-04-28 12:31:38'),
(58, 7, 'client', 'list', 7, 'User #7 added client Hennecke GmbH to list', '2026-04-28 12:32:03'),
(59, 7, 'create incoming_order', 'order', 4, 'User #7 created order No.GBR-in-2026-00002', '2026-04-28 12:37:04'),
(60, 7, 'track_and_trace', 'order', 7, 'User #7 tried to track nothing', '2026-04-28 12:50:42'),
(61, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-28 12:52:16'),
(62, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 400DE2FCF08D', '2026-04-28 12:52:53'),
(63, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order A060D0017AF4', '2026-04-28 12:52:57'),
(64, 7, 'track_and_trace', 'order', 7, 'User #7 tried invalid ID: vbcvb', '2026-04-28 12:53:01'),
(65, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order A060D0017AF4', '2026-04-28 12:53:19'),
(66, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 3407FFCFC320', '2026-04-28 12:53:29'),
(67, 7, 'create', 'orders', 7, 'Order create with documents.', '2026-04-28 13:47:13'),
(68, 7, 'create', 'orders', 8, 'User #7 processed order No. GBR-GUH-2026-00008', '2026-04-29 09:49:03'),
(69, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-29 09:59:45'),
(70, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-29 10:05:46'),
(71, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-29 10:05:47'),
(72, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-29 10:05:54'),
(73, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-29 10:06:47'),
(74, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-29 10:06:49'),
(75, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order A060D0017AF4', '2026-04-29 10:06:55'),
(76, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order A060D0017AF4', '2026-04-29 10:11:43'),
(77, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-29 10:11:47'),
(78, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-29 10:13:42'),
(79, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-29 10:13:44'),
(80, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-04-29 10:13:47'),
(81, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order A060D0017AF4', '2026-04-29 10:13:54'),
(82, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order A060D0017AF4', '2026-04-29 10:16:31'),
(83, 7, 'password', 'user', 7, 'User #7 changed its password', '2026-04-29 10:33:18'),
(84, 7, 'create incoming_order', 'order', 9, 'User #7 created Incoming order No.GBR-out-2026-00003', '2026-04-29 10:41:53');

-- --------------------------------------------------------

--
-- Struktura tabulky `inventory_movements`
--

CREATE TABLE `inventory_movements` (
  `id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `direction` enum('in','out') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabulky `materials`
--

CREATE TABLE `materials` (
  `id` int(11) NOT NULL,
  `item_code` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `materials`
--

INSERT INTO `materials` (`id`, `item_code`, `name`, `created_at`) VALUES
(1, 7401, 'End mills', '2026-04-20 12:08:16'),
(2, 7402, 'Inserts', '2026-04-20 12:18:42'),
(3, 7403, 'Pieces', '2026-04-20 12:18:50'),
(4, 7404, 'Sludge', '2026-04-20 12:19:17'),
(5, 7405, 'Shafted', '2026-04-20 12:19:45'),
(6, 7406, 'Braze', '2026-04-20 12:20:14'),
(7, 7407, 'With PCD', '2026-04-20 12:20:26'),
(8, 7408, 'Drill bits', '2026-04-20 12:21:02'),
(9, 7409, 'HSS', '2026-04-20 12:21:17'),
(10, 7410, 'Cermets', '2026-04-20 12:21:34'),
(11, 7411, 'Other', '2026-04-20 12:21:42');

-- --------------------------------------------------------

--
-- Struktura tabulky `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_no` varchar(50) NOT NULL,
  `track_id` varchar(16) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `partner_id` int(11) NOT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `currency` enum('EUR','USD','CZK','PLN','JPY') NOT NULL DEFAULT 'EUR',
  `pallet_no` int(11) DEFAULT 0,
  `netto_w` decimal(10,2) DEFAULT NULL,
  `brutto_w` decimal(10,2) DEFAULT NULL,
  `type` enum('in','out','guh-in','guh-out') NOT NULL,
  `approve_status` enum('approved','not approved') DEFAULT 'not approved',
  `order_status` enum('created','received','in process','completed','cancelled') DEFAULT 'created',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `orders`
--

INSERT INTO `orders` (`id`, `order_no`, `track_id`, `date`, `partner_id`, `price`, `currency`, `pallet_no`, `netto_w`, `brutto_w`, `type`, `approve_status`, `order_status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'GBR-in-2026-00001', '6409C4CA3330', '2026-04-21', 7, 152897.50, 'EUR', 2, 1342.00, 1382.00, 'in', 'approved', 'completed', 1, '2026-04-21 10:36:41', '2026-04-23 13:59:03'),
(2, 'GBR-out-2026-00001', '400DE2FCF08D', '2026-04-22', 8, 235.00, 'EUR', 1, 9.00, 12.00, 'out', 'approved', 'created', 1, '2026-04-21 10:42:34', '2026-04-27 14:02:14'),
(3, 'GBR-out-2026-00002', 'A060D0017AF4', '2026-04-23', 7, 1320.00, 'USD', 1, 95.00, 100.00, 'out', 'not approved', 'received', 1, '2026-04-21 10:42:57', '2026-04-23 13:58:35'),
(4, 'GBR-GUH-2026-00002', '3407FFCFC320', '2026-04-24', 20, 50.00, 'EUR', 1, 85.00, 90.00, 'guh-in', 'approved', 'received', 7, '2026-04-28 12:37:04', '2026-04-29 09:39:04'),
(7, 'GBR-GUH-2026-00003', 'TRK-703326', '2026-04-24', 20, 45.00, 'EUR', 1, 85.00, 90.00, 'guh-out', 'not approved', 'created', 7, '2026-04-28 13:47:13', '2026-04-29 09:39:13'),
(8, 'GBR-GUH-2026-00008', 'TRK-8979E7', '2026-04-28', 11, 65.00, 'USD', 1, 75.00, 82.00, 'guh-out', 'approved', 'completed', NULL, '2026-04-29 09:49:03', '2026-04-29 09:49:03'),
(9, 'GBR-out-2026-00003', '8E5595377125', '2026-04-30', 8, 150095.00, 'EUR', 14, 11644.00, 12567.00, 'out', 'approved', 'in process', 7, '2026-04-29 10:41:53', '2026-04-29 10:41:53');

-- --------------------------------------------------------

--
-- Struktura tabulky `order_attachments`
--

CREATE TABLE `order_attachments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `type` enum('img','doc') NOT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `order_attachments`
--

INSERT INTO `order_attachments` (`id`, `order_id`, `file_path`, `type`, `uploaded_by`, `created_at`) VALUES
(1, 1, 'order_attachments/in/GBR-in-2026-00001_doc_1_1776767801.jpg', 'img', 1, '2026-04-21 10:36:41'),
(2, 3, 'order_attachments/out/GBR-out-2026-00002_doc_1_1776768177.jpg', 'img', 1, '2026-04-21 10:42:57'),
(4, 7, 'uploads/orders/order_7_1777384033_0.zip', 'img', NULL, '2026-04-28 13:47:13'),
(5, 9, 'order_attachments/out/GBR-out-2026-00003_doc_1_1777459313.png', 'img', 7, '2026-04-29 10:41:53');

-- --------------------------------------------------------

--
-- Struktura tabulky `order_materials`
--

CREATE TABLE `order_materials` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `order_materials`
--

INSERT INTO `order_materials` (`id`, `order_id`, `material_id`, `quantity`) VALUES
(1, 1, 1, 382.50),
(2, 1, 2, 482.00),
(3, 1, 5, 235.00),
(4, 1, 6, 242.50),
(6, 3, 1, 95.00),
(7, 2, 1, 9.00),
(8, 4, 1, 45.00),
(9, 4, 2, 40.00),
(12, 7, 1, 45.00),
(13, 7, 2, 40.00),
(14, 8, 1, 75.00),
(15, 9, 1, 4240.00),
(16, 9, 2, 3937.00),
(17, 9, 7, 758.00),
(18, 9, 11, 858.00),
(19, 9, 4, 205.00),
(20, 9, 6, 798.00),
(21, 9, 11, 10.00),
(22, 9, 11, 6.00),
(23, 9, 11, 16.00),
(24, 9, 11, 22.00),
(25, 9, 3, 794.00);

-- --------------------------------------------------------

--
-- Struktura tabulky `partners`
--

CREATE TABLE `partners` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('customer','supplier') NOT NULL,
  `contact_info` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `partners`
--

INSERT INTO `partners` (`id`, `name`, `type`, `contact_info`, `created_at`) VALUES
(1, 'Schredder - Wojciech Kania', 'supplier', 'w.kania@schredder.pl', '2026-04-20 09:51:02'),
(2, 'Schredder - Michał Czaszy', 'supplier', 'm.podczaszy@schredder.pl', '2026-04-20 10:10:42'),
(3, 'Schredder - Christopher Jasinski', 'supplier', 'k.jasinski@schredder.pl', '2026-04-20 10:11:15'),
(4, 'Schredder - Adalbert Kania', 'supplier', 'w.kania@schredder.pl', '2026-04-20 10:11:27'),
(5, 'Schredder - Lukas Zajko', 'supplier', 'l.zajko@schredder.pl', '2026-04-20 10:11:42'),
(6, 'Schredder - Matthew Jasinski', 'supplier', 'm.jasinski@schredder.pl', '2026-04-20 10:11:59'),
(7, 'Schredder', 'supplier', 'biuro@schredder.pl', '2026-04-20 10:20:10'),
(8, 'Metallio SP', 'supplier', 'info@metallio.pl', '2026-04-20 10:30:29'),
(9, 'Metallio SP', 'supplier', 'info@metallio.pl', '2026-04-20 10:37:50'),
(10, 'Guhring - Sulkov', 'customer', 'informace@guehring.de', '2026-04-27 12:50:44'),
(11, 'Guhring France', 'supplier', 'info@guhring-france.com', '2026-04-28 12:26:35'),
(12, 'Bodo CNC - Techni', 'supplier', 'info@bodo-cnc-technik.de', '2026-04-28 12:27:44'),
(13, 'Roterberg', 'supplier', 'info@roterberg-maschinenbau.de', '2026-04-28 12:28:05'),
(14, 'MacoSteel', 'supplier', 'macometal@sapo.pt', '2026-04-28 12:28:40'),
(15, 'Klingelnberg GmbH', 'supplier', 'vorarbeiteratt.vorarbeiteratt@klingerlnberg.com', '2026-04-28 12:29:21'),
(16, 'Fertigungsgerätebau A. Steinbach GmbH & Co. KG', 'supplier', 'marcus.rosshirt@steinbach-gruppe.de', '2026-04-28 12:30:01'),
(17, 'Mavidis', 'supplier', 'msm-albstadt@gmx.de', '2026-04-28 12:30:35'),
(18, 'Halter Outils de Coupe', 'supplier', 'yves.hammecker@guhring-alsace.com', '2026-04-28 12:31:10'),
(19, 'SKF Sealing Solutions GmbH', 'supplier', 'Jochen.Friehe@skf.com', '2026-04-28 12:31:38'),
(20, 'Hennecke GmbH', 'supplier', 'ralf.boettcher@hennecke.com', '2026-04-28 12:32:03');

-- --------------------------------------------------------

--
-- Struktura tabulky `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `last_activity` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `last_activity`, `created_at`) VALUES
(1, 'admin', '$2y$10$hnwuvJZOrFeN865LfYCYHuo/6NdM9Z8o9CXhYFpgrDklaNBSBtWKy', 'phillips.m@greenbridgerecycling.com', NULL, '2026-04-17 10:55:30'),
(2, 'mirka', '$2y$10$FHxGjPJrJvKnyRndJPA1keMXWskD.pNQqkfympnsZ./JYDyc4OQJi', 'sneberkova.m@greenbridgerecycling.com', NULL, '2026-04-20 06:32:37'),
(3, 'andreas', '$2y$10$JZBf3uoQpnfR998cHPqQLeeaGl.EZ8aLUHZn5ENUYlns2LsdQCz0e', 'hellinger.a@greenbridgerecyclng.com', NULL, '2026-04-20 06:32:37'),
(6, 'magda', '$2y$10$mSeqYHMqnRERjbcbRc.Qve4NSi8KQDHDf0O8UKZjgi74UGOrMAQSW', 'info@greenbridgerecycling.com', NULL, '2026-04-20 06:34:19'),
(7, 'test_user', '$2y$10$kYswHhUgwrDL.TBFwiZwFu4coyQkjGcua6a.NT3rdww1ypHI/IwdK', 'test.u@greenbridgerecycling.com', '', '2026-04-20 06:34:19');

--
-- Indexy pro exportované tabulky
--

--
-- Indexy pro tabulku `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `material_id` (`material_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexy pro tabulku `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `item_code` (`item_code`);

--
-- Indexy pro tabulku `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_no` (`order_no`),
  ADD KEY `partner_id` (`partner_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexy pro tabulku `order_attachments`
--
ALTER TABLE `order_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexy pro tabulku `order_materials`
--
ALTER TABLE `order_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `material_id` (`material_id`);

--
-- Indexy pro tabulku `partners`
--
ALTER TABLE `partners`
  ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT pro tabulku `inventory_movements`
--
ALTER TABLE `inventory_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pro tabulku `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pro tabulku `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pro tabulku `order_attachments`
--
ALTER TABLE `order_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pro tabulku `order_materials`
--
ALTER TABLE `order_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT pro tabulku `partners`
--
ALTER TABLE `partners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pro tabulku `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD CONSTRAINT `inventory_movements_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`),
  ADD CONSTRAINT `inventory_movements_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;

--
-- Omezení pro tabulku `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`partner_id`) REFERENCES `partners` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Omezení pro tabulku `order_attachments`
--
ALTER TABLE `order_attachments`
  ADD CONSTRAINT `order_attachments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_attachments_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);

--
-- Omezení pro tabulku `order_materials`
--
ALTER TABLE `order_materials`
  ADD CONSTRAINT `order_materials_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_materials_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
