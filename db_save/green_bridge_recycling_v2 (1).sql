-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Počítač: 127.0.0.1
-- Vytvořeno: Pon 08. čen 2026, 13:08
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
(2, 1, 'create', 'order', 1, 'User #1 created Incoming order No.GBR-in-2026-00001', '2026-04-21 10:36:41'),
(3, 1, 'create', 'order', 2, 'User #1 created Incoming order No.GBR-out-2026-00001', '2026-04-21 10:42:34'),
(4, 1, 'create', 'order', 3, 'User #1 created Incoming order No.GBR-out-2026-00002', '2026-04-21 10:42:57'),
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
(59, 7, 'create', 'order', 4, 'User #7 created order No.GBR-in-2026-00002', '2026-04-28 12:37:04'),
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
(84, 7, 'create', 'order', 9, 'User #7 created Incoming order No.GBR-out-2026-00003', '2026-04-29 10:41:53'),
(85, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order TRK-8979E7', '2026-04-29 12:46:53'),
(86, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:14:56'),
(87, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:25:15'),
(88, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:25:25'),
(89, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:25:47'),
(90, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:26:17'),
(91, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:26:45'),
(92, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:26:53'),
(93, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:26:58'),
(94, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:27:55'),
(95, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:28:08'),
(96, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:35:33'),
(97, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:35:49'),
(98, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:35:55'),
(99, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:35:58'),
(100, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:37:10'),
(101, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:42:07'),
(102, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:42:10'),
(103, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:42:14'),
(104, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:42:16'),
(105, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:42:24'),
(106, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:42:25'),
(107, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:42:55'),
(108, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:42:57'),
(109, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:43:01'),
(110, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:43:23'),
(111, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:43:25'),
(112, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:43:50'),
(113, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:43:52'),
(114, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:43:54'),
(115, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:43:55'),
(116, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:43:57'),
(117, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:43:58'),
(118, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:44:00'),
(119, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:44:02'),
(120, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:44:05'),
(121, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:44:05'),
(122, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:44:19'),
(123, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:46:00'),
(124, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:46:03'),
(125, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:46:14'),
(126, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:49:20'),
(127, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:49:31'),
(128, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:49:35'),
(129, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:49:43'),
(130, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-04-30 09:49:45'),
(131, 7, 'logout', 'user', 7, 'User #7 logged out', '2026-04-30 09:51:05'),
(132, 1, 'login', 'user', 1, 'User #1 logged in', '2026-04-30 09:51:31'),
(133, 1, 'track_and_trace', 'order', 1, 'User #1 tracked order A060D0017AF4', '2026-04-30 09:55:30'),
(134, 1, 'logout', 'user', 1, 'User #1 logged out', '2026-05-04 07:45:06'),
(135, 1, 'login', 'user', 1, 'User #1 logged in', '2026-05-04 12:12:25'),
(136, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-05-04 12:13:33'),
(137, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-05-04 12:13:37'),
(138, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-05-04 12:13:39'),
(139, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-05-04 12:13:41'),
(140, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-05-04 12:13:43'),
(141, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-05-04 12:13:45'),
(142, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-05-04 12:13:47'),
(143, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-05-04 12:13:48'),
(144, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-05-04 12:13:50'),
(145, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-05-04 12:13:51'),
(146, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-05-04 12:13:53'),
(147, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-05-04 12:14:12'),
(148, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-05-04 12:14:16'),
(149, 1, 'track_and_trace', 'order', 1, 'User #1 tracked order 6409C4CA3330', '2026-05-06 07:46:39'),
(150, 1, 'track_and_trace', 'order', 1, 'User #1 tracked order 6409C4CA3330', '2026-05-06 07:49:21'),
(151, 1, 'track_and_trace', 'order', 1, 'User #1 tracked order 6409C4CA3330', '2026-05-06 07:50:04'),
(152, 1, 'track_and_trace', 'order', 1, 'User #1 tracked order 6409C4CA3330', '2026-05-06 07:50:30'),
(153, 1, 'track_and_trace', 'order', 1, 'User #1 tracked order 6409C4CA3330', '2026-05-06 07:50:47'),
(154, 1, 'track_and_trace', 'order', 1, 'User #1 tracked order 6409C4CA3330', '2026-05-06 07:51:03'),
(155, 1, 'track_and_trace', 'order', 1, 'User #1 tracked order 6409C4CA3330', '2026-05-06 07:51:12'),
(156, 1, 'track_and_trace', 'order', 1, 'User #1 tracked order 6409C4CA3330', '2026-05-06 07:52:07'),
(157, 1, 'track_and_trace', 'order', 1, 'User #1 tracked order 6409C4CA3330', '2026-05-06 07:52:13'),
(158, 1, 'track_and_trace', 'order', 1, 'User #1 tracked order 6409C4CA3330', '2026-05-06 07:52:24'),
(159, 7, 'login', 'user', 7, 'User #7 logged in', '2026-05-06 07:54:27'),
(160, 7, 'track_and_trace', 'order', 7, 'User #7 tried invalid ID: Track', '2026-05-06 07:58:16'),
(161, 7, 'track_and_trace', 'order', 7, 'User #7 tried invalid ID: 340ffcfc320', '2026-05-06 07:58:35'),
(162, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 3407ffcfc320', '2026-05-06 07:58:47'),
(163, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 3407ffcfc320', '2026-05-06 07:59:25'),
(164, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 3407ffcfc320', '2026-05-06 07:59:27'),
(165, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-05-06 09:30:17'),
(166, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-05-06 09:38:06'),
(167, 1, 'track_and_trace', 'order', 1, 'User #1 tracked order TRK-703326', '2026-05-06 10:01:25'),
(168, 1, 'track_and_trace', 'order', 1, 'User #1 tracked order TRK-703326', '2026-05-06 10:01:28'),
(169, 1, 'track_and_trace', 'order', 1, 'User #1 tracked order TRK-703326', '2026-05-06 10:01:31'),
(170, 1, 'track_and_trace', 'order', 1, 'User #1 tracked order TRK-703326', '2026-05-06 10:01:31'),
(171, 1, 'track_and_trace', 'order', 1, 'User #1 tracked order TRK-703326', '2026-05-06 10:01:31'),
(172, 1, 'track_and_trace', 'order', 1, 'User #1 tracked order TRK-703326', '2026-05-06 10:01:32'),
(173, 1, 'track_and_trace', 'order', 1, 'User #1 tracked order TRK-703326', '2026-05-06 10:01:32'),
(174, 1, 'track_and_trace', 'order', 1, 'User #1 tracked order TRK-703326', '2026-05-06 10:01:32'),
(175, 1, 'track_and_trace', 'order', 1, 'User #1 tracked order TRK-703326', '2026-05-06 10:01:32'),
(176, 1, 'track_and_trace', 'order', 1, 'User #1 tracked order TRK-703326', '2026-05-06 10:01:32'),
(177, 1, 'track_and_trace', 'order', 1, 'User #1 tracked order TRK-703326', '2026-05-06 10:01:33'),
(178, 1, 'track_and_trace', 'order', 1, 'User #1 tracked order TRK-703326', '2026-05-06 10:01:33'),
(179, 1, 'track_and_trace', 'order', 1, 'User #1 tracked order TRK-703326', '2026-05-06 10:01:33'),
(180, 1, 'track_and_trace', 'order', 1, 'User #1 tracked order TRK-703326', '2026-05-06 10:01:33'),
(181, 1, 'track_and_trace', 'order', 1, 'User #1 tracked order TRK-703326', '2026-05-06 10:01:33'),
(182, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-05-06 10:01:37'),
(183, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-05-06 10:02:01'),
(184, 1, 'update', 'orders', 2, 'Updated Order: ', '2026-05-06 10:09:00'),
(185, 1, 'update', 'orders', 2, 'Updated Order: ', '2026-05-06 10:13:11'),
(186, 1, 'update', 'orders', 2, 'Updated Order: ', '2026-05-06 10:22:23'),
(187, 1, 'update', 'orders', 2, 'Updated Order: ', '2026-05-06 10:27:31'),
(188, 1, 'update', 'orders', 2, 'Updated Order: ', '2026-05-06 10:27:53'),
(189, 1, 'update', 'orders', 2, 'Updated Order: ', '2026-05-06 10:28:17'),
(190, 1, 'logout', 'user', 1, 'User #1 logged out', '2026-05-07 06:29:48'),
(191, 7, 'login', 'user', 7, 'User #7 logged in', '2026-05-07 07:45:09'),
(192, 7, 'logout', 'user', 7, 'User #7 logged out', '2026-05-07 07:45:53'),
(193, 7, 'login', 'user', 7, 'User #7 logged in', '2026-05-07 07:46:07'),
(194, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-05-20 13:49:15'),
(195, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order TRK-703326', '2026-05-20 13:49:21'),
(196, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order A060D0017AF4', '2026-05-20 13:49:26'),
(197, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 400DE2FCF08D', '2026-05-20 13:49:29'),
(198, 7, 'track_and_trace', 'order', 7, 'User #7 tried invalid ID: vbcvb', '2026-05-20 13:49:33'),
(199, 7, 'activity_check', 'user', 7, 'User #7 checked all his activities', '2026-05-20 13:49:43'),
(200, 7, 'logout', 'user', 7, 'User #7 logged out', '2026-05-20 13:50:26'),
(201, 1, 'login', 'user', 1, 'User #1 logged in', '2026-05-21 13:32:51'),
(202, 1, 'login', 'user', 1, 'User #1 logged in', '2026-05-21 13:36:05'),
(203, 7, 'login', 'user', 7, 'User #7 logged in', '2026-05-21 13:38:17'),
(204, 1, 'login', 'user', 1, 'User #1 logged in', '2026-05-21 13:59:54'),
(205, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-05-25 12:45:45'),
(206, 1, 'login', 'user', 1, 'User #1 logged in', '2026-06-01 13:09:41'),
(207, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-06-01 13:09:50'),
(208, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-06-01 13:09:59'),
(209, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-06-01 13:10:06'),
(210, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-06-01 13:10:13'),
(211, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-06-01 13:10:14'),
(212, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-06-01 13:10:15'),
(213, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-06-01 13:10:16'),
(214, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-06-01 13:10:18'),
(215, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-06-01 13:10:23'),
(216, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-06-01 13:10:24'),
(217, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-06-01 13:10:26'),
(218, 1, 'logout', 'user', 1, 'User #1 logged out', '2026-06-01 13:16:18'),
(219, 7, 'login', 'user', 7, 'User #7 logged in', '2026-06-01 13:26:52'),
(220, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order 6409C4CA3330', '2026-06-02 10:58:00'),
(221, 7, 'create', 'order', 12, 'User #7 created order No. GBR-GUH-2026-00012', '2026-06-02 11:12:02'),
(222, 7, 'update', 'orders', 12, 'Updated Order: approve_status (\'not approved\' -> \'approved\')', '2026-06-02 11:16:32'),
(223, 7, 'update', 'orders', 12, 'Updated Order: order_status (\'created\' -> \'received\')', '2026-06-02 11:23:56'),
(224, 7, 'update', 'orders', 12, 'Updated Order: order_status (\'received\' -> \'in process\')', '2026-06-02 11:24:25'),
(225, 7, 'update', 'orders', 12, 'Updated Order: order_status (\'in process\' -> \'completed\')', '2026-06-02 11:24:35'),
(226, 7, 'create', 'order', 13, 'User #7 created order No. GBR-GUH-2026-00013', '2026-06-02 11:58:43'),
(227, 7, 'update', 'orders', 13, 'Updated Order: order_status (\'created\' -> \'completed\')', '2026-06-02 11:59:51'),
(228, 7, 'create', 'order', 15, 'User #7 created order No. GBR-GUH-2026-00015', '2026-06-02 12:47:07'),
(229, 7, 'update', 'orders', 15, 'Updated Order: order_status (\'completed\' -> \'cancelled\')', '2026-06-02 12:47:31'),
(230, 7, 'create', 'order', 16, 'User #7 created order No. GBR-GUH-2026-00016', '2026-06-02 12:51:51'),
(231, 7, 'update', 'orders', 16, 'Updated Order: order_status (\'completed\' -> \'cancelled\')', '2026-06-02 12:52:04'),
(232, 7, 'update', 'orders', 16, 'Updated Order: ', '2026-06-02 12:56:26'),
(233, 7, 'update', 'orders', 16, 'Updated Order: ', '2026-06-02 12:56:29'),
(234, 7, 'create', 'order', 17, 'User #7 created order No. GBR-GUH-2026-00017', '2026-06-02 13:03:17'),
(235, 7, 'update', 'orders', 17, 'Updated Order: order_status (\'in process\' -> \'completed\')', '2026-06-02 13:03:31'),
(236, 7, 'update', 'orders', 16, 'Updated Order: netto_w (\'156.00\' -> \'186\')', '2026-06-05 11:53:07'),
(237, 7, 'update', 'orders', 16, 'Updated Order: order_status (\'cancelled\' -> \'completed\')', '2026-06-05 11:54:09'),
(238, 7, 'update', 'orders', 17, 'Updated Order: order_status (\'completed\' -> \'cancelled\')', '2026-06-05 11:55:03'),
(239, 7, 'update', 'orders', 17, 'Updated Order: order_status (\'cancelled\' -> \'completed\')', '2026-06-05 11:55:06'),
(240, 7, 'create', 'order', 18, 'User #7 created order No. GBR-GUH-2026-00018', '2026-06-05 12:09:17'),
(241, 7, 'create', 'order', 19, 'User #7 created order No. GBR-GUH-2026-00019', '2026-06-05 13:23:32'),
(242, 7, 'create', 'order', 20, 'User #7 created order No. GBR-GUH-2026-00020', '2026-06-05 13:26:47'),
(243, 7, 'update', 'orders', 20, 'Updated Order: approve_status (\'not approved\' -> \'approved\')', '2026-06-05 13:26:56'),
(244, 7, 'logout', 'user', 7, 'User #7 logged out', '2026-06-05 13:37:39'),
(245, 1, 'login', 'user', 1, 'User #1 logged in', '2026-06-05 13:37:51'),
(246, 1, 'activity_check', 'user', 1, 'User #1 checked all his activities', '2026-06-05 13:37:55'),
(247, 1, 'logout', 'user', 1, 'User #1 logged out', '2026-06-05 13:41:35'),
(248, 7, 'login', 'user', 7, 'User #7 logged in', '2026-06-05 13:52:45'),
(249, 7, 'create', 'order', 21, 'User #7 created order No. GBR-GUH-2026-00021', '2026-06-08 06:45:12'),
(250, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order TRK-63553B', '2026-06-08 06:45:28'),
(251, 7, 'update', 'orders', 21, 'Updated Order: order_status (\'created\' -> \'received\')', '2026-06-08 07:11:24'),
(252, 7, 'update', 'orders', 21, 'Updated Order: order_status (\'received\' -> \'in process\')', '2026-06-08 07:11:33'),
(253, 7, 'update', 'orders', 21, 'Updated Order: order_status (\'in process\' -> \'completed\')', '2026-06-08 07:11:39'),
(254, 7, 'update', 'orders', 21, 'Updated Order: approve_status (\'not approved\' -> \'approved\')', '2026-06-08 07:11:47'),
(255, 7, 'update', 'orders', 21, 'Updated Order: approve_status (\'approved\' -> \'not approved\')', '2026-06-08 07:12:01'),
(256, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order TRK-63553B', '2026-06-08 07:12:21'),
(257, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order TRK-63553B', '2026-06-08 07:39:08'),
(258, 7, 'update', 'orders', 21, 'Updated Order: order_status (\'completed\' -> \'created\')', '2026-06-08 07:39:21'),
(259, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order TRK-63553B', '2026-06-08 07:39:31'),
(260, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order TRK-63553B', '2026-06-08 09:40:53'),
(261, 7, 'update', 'orders', 21, 'Updated Order: approve_status (\'not approved\' -> \'approved\')', '2026-06-08 10:35:31'),
(262, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order TRK-63553B', '2026-06-08 10:35:36'),
(263, 7, 'update', 'orders', 21, 'Updated Order: order_status (\'created\' -> \'received\')', '2026-06-08 10:35:43'),
(264, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order TRK-63553B', '2026-06-08 10:35:46'),
(265, 7, 'update', 'orders', 21, 'Updated Order: order_status (\'received\' -> \'in process\')', '2026-06-08 10:35:55'),
(266, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order TRK-63553B', '2026-06-08 10:35:57'),
(267, 7, 'update', 'orders', 21, 'Updated Order: order_status (\'in process\' -> \'completed\')', '2026-06-08 10:36:05'),
(268, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order TRK-63553B', '2026-06-08 10:36:07'),
(269, 7, 'update', 'orders', 21, 'Updated Order: order_status (\'completed\' -> \'cancelled\')', '2026-06-08 10:36:11'),
(270, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order TRK-63553B', '2026-06-08 10:36:13'),
(271, 7, 'update', 'orders', 21, 'Updated Order: order_status (\'cancelled\' -> \'completed\')', '2026-06-08 10:36:19'),
(272, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order TRK-63553B', '2026-06-08 10:36:21'),
(273, 7, 'create', 'order', 22, 'User #7 created order No. GBR-GUH-2026-00022', '2026-06-08 10:45:07'),
(274, 7, 'update', 'orders', 22, 'Updated Order: approve_status (\'not approved\' -> \'approved\')', '2026-06-08 10:45:27'),
(275, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order TRK-63553B', '2026-06-08 11:00:37'),
(276, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order TRK-0BAB50', '2026-06-08 11:00:46'),
(277, 7, 'update', 'orders', 22, 'Updated Order: brutto_w (\'578.00\' -> \'583\')', '2026-06-08 11:01:13'),
(278, 7, 'track_and_trace', 'order', 7, 'User #7 tracked order TRK-0BAB50', '2026-06-08 11:01:17');

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

--
-- Vypisuji data pro tabulku `inventory_movements`
--

INSERT INTO `inventory_movements` (`id`, `material_id`, `order_id`, `quantity`, `direction`, `created_at`) VALUES
(63, 2, 21, 1000.00, 'in', '2026-06-08 10:36:19'),
(64, 1, 21, 450.00, 'in', '2026-06-08 10:36:19');

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
(21, 'GBR-GUH-2026-00021', 'TRK-63553B', '2026-06-09', 7, 265440.00, 'EUR', 3, 1450.00, 1530.00, 'guh-in', 'approved', 'completed', 7, '2026-06-08 06:45:12', '2026-06-08 10:36:19'),
(22, 'GBR-GUH-2026-00022', 'TRK-0BAB50', '2026-06-10', 13, 5500.00, 'EUR', 2, 510.00, 583.00, 'guh-out', 'approved', 'created', 7, '2026-06-08 10:45:07', '2026-06-08 11:01:13');

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
(99, 21, 2, 1000.00),
(100, 21, 1, 450.00),
(105, 22, 8, 260.00),
(106, 22, 2, 250.00);

-- --------------------------------------------------------

--
-- Struktura tabulky `order_status_history`
--

CREATE TABLE `order_status_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `changed_by` int(11) NOT NULL,
  `changed_at` datetime DEFAULT current_timestamp(),
  `note` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `order_status_history`
--

INSERT INTO `order_status_history` (`id`, `order_id`, `status`, `changed_by`, `changed_at`, `note`) VALUES
(1, 21, 'created', 7, '2026-06-08 09:39:21', NULL),
(2, 21, 'received', 7, '2026-06-08 12:35:43', NULL),
(3, 21, 'in process', 7, '2026-06-08 12:35:55', NULL),
(4, 21, 'completed', 7, '2026-06-08 12:36:05', NULL),
(5, 21, 'cancelled', 7, '2026-06-08 12:36:11', NULL),
(6, 21, 'completed', 7, '2026-06-08 12:36:19', NULL),
(7, 22, 'created', 7, '2026-06-08 12:45:07', NULL);

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
-- Struktura tabulky `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `status` enum('open','in_progress','closed') DEFAULT 'open',
  `created_by` int(11) NOT NULL,
  `assigned_to` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `tickets`
--

INSERT INTO `tickets` (`id`, `title`, `description`, `priority`, `status`, `created_by`, `assigned_to`, `created_at`, `updated_at`) VALUES
(6, 'test', 'test ticket for testing the ticket system.', 'medium', 'in_progress', 7, NULL, '2026-05-07 13:21:17', '2026-05-07 13:26:40');

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
(7, 'test_user', '$2y$10$kYswHhUgwrDL.TBFwiZwFu4coyQkjGcua6a.NT3rdww1ypHI/IwdK', 'test.u@greenbridgerecycling.com', '2026-06-08 13:01:52', '2026-04-20 06:34:19');

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
-- Indexy pro tabulku `order_status_history`
--
ALTER TABLE `order_status_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `partners`
--
ALTER TABLE `partners`
  ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `tickets`
--
ALTER TABLE `tickets`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=279;

--
-- AUTO_INCREMENT pro tabulku `inventory_movements`
--
ALTER TABLE `inventory_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT pro tabulku `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pro tabulku `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT pro tabulku `order_attachments`
--
ALTER TABLE `order_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pro tabulku `order_materials`
--
ALTER TABLE `order_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT pro tabulku `order_status_history`
--
ALTER TABLE `order_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pro tabulku `partners`
--
ALTER TABLE `partners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT pro tabulku `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
