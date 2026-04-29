-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Počítač: 127.0.0.1
-- Vytvořeno: Stř 22. dub 2026, 15:13
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
(4, 1, 'create incoming_order', 'order', 3, 'User #1 created Incoming order No.GBR-out-2026-00002', '2026-04-21 10:42:57');

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
  `type` enum('in','out') NOT NULL,
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
(1, 'GBR-in-2026-00001', '6409C4CA3330', '2026-04-21', 7, 152897.50, 'EUR', 2, 1342.00, 1382.00, 'in', 'approved', 'created', 1, '2026-04-21 10:36:41', '2026-04-21 10:36:41'),
(2, 'GBR-out-2026-00001', '400DE2FCF08D', '2026-04-22', 8, 235.00, 'EUR', 1, 3.00, 3.00, 'out', 'approved', 'created', 1, '2026-04-21 10:42:34', '2026-04-21 10:42:34'),
(3, 'GBR-out-2026-00002', 'A060D0017AF4', '2026-04-23', 7, 1320.00, 'EUR', 1, 95.00, 100.00, 'out', 'not approved', 'created', 1, '2026-04-21 10:42:57', '2026-04-21 10:42:57');

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
(2, 3, 'order_attachments/out/GBR-out-2026-00002_doc_1_1776768177.jpg', 'img', 1, '2026-04-21 10:42:57');

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
(5, 2, 1, 3.00),
(6, 3, 1, 95.00);

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
(9, 'Metallio SP', 'supplier', 'info@metallio.pl', '2026-04-20 10:37:50');

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
(7, 'test_user', '$2y$10$sTUOSHuJuCAZcYcGqQWIKeN9hKi45bk8/XY.0uytFDJLF3avNW2PS', 'test.u@greenbridgerecycling.com', '', '2026-04-20 06:34:19');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pro tabulku `order_attachments`
--
ALTER TABLE `order_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pro tabulku `order_materials`
--
ALTER TABLE `order_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pro tabulku `partners`
--
ALTER TABLE `partners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
