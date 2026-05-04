-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Waktu pembuatan: 29 Apr 2026 pada 17.50
-- Versi server: 8.0.35
-- Versi PHP: 8.2.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `iform`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `api_client`
--

CREATE TABLE `api_client` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `api_client`
--

INSERT INTO `api_client` (`id`, `name`, `token`, `is_active`, `created_at`) VALUES
(1, 'scs-android', 'scs_android_2025_xxxxxxxxx', 1, 1766066801);

-- --------------------------------------------------------

--
-- Struktur dari tabel `auth_assignment`
--

CREATE TABLE `auth_assignment` (
  `item_name` varchar(64) NOT NULL,
  `user_id` int NOT NULL,
  `created_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `auth_assignment`
--

INSERT INTO `auth_assignment` (`item_name`, `user_id`, `created_at`) VALUES
('admin', 1, 1771086799),
('chief', 9, 1775566318),
('foreman', 7, 1771087403),
('manager', 10, 1775566318),
('subforeman', 8, 1775565284);

-- --------------------------------------------------------

--
-- Struktur dari tabel `auth_item`
--

CREATE TABLE `auth_item` (
  `name` varchar(64) NOT NULL,
  `type` int NOT NULL,
  `description` text,
  `rule_name` varchar(64) DEFAULT NULL,
  `data` text,
  `created_at` int DEFAULT NULL,
  `updated_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `auth_item`
--

INSERT INTO `auth_item` (`name`, `type`, `description`, `rule_name`, `data`, `created_at`, `updated_at`) VALUES
('accessAdmin', 2, 'Access admin panel', NULL, NULL, 1771084583, 1771084583),
('admin', 1, 'Admin', NULL, NULL, 1771084583, 1771084583),
('chief', 1, 'Chief', NULL, NULL, 1771084583, 1771084583),
('createUser', 2, 'Create user', NULL, NULL, 1771084583, 1771084583),
('deleteUser', 2, 'Delete user', NULL, NULL, 1771084583, 1771084583),
('foreman', 1, 'Foreman', NULL, NULL, 1771084583, 1771086987),
('manageChecksheet', 2, 'Manage checksheet', NULL, NULL, 1771084583, 1771084583),
('manager', 1, 'Manager', NULL, NULL, 1771084583, 1771084583),
('operator', 1, 'Operator', NULL, NULL, 1771084583, 1771084583),
('subforeman', 1, 'Subforeman', NULL, NULL, 1771084583, 1771084583),
('updateUser', 2, 'Update user', NULL, NULL, 1771084583, 1771084583),
('viewChecksheet', 2, 'View checksheet', NULL, NULL, 1771084583, 1771084583),
('viewDashboard', 2, 'View dashboard', NULL, NULL, 1771084583, 1771084583),
('viewUser', 2, 'View user', NULL, NULL, 1771084583, 1771084583);

-- --------------------------------------------------------

--
-- Struktur dari tabel `auth_item_child`
--

CREATE TABLE `auth_item_child` (
  `parent` varchar(64) NOT NULL,
  `child` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `auth_item_child`
--

INSERT INTO `auth_item_child` (`parent`, `child`) VALUES
('admin', 'accessAdmin'),
('manager', 'chief'),
('admin', 'createUser'),
('admin', 'deleteUser'),
('chief', 'foreman'),
('foreman', 'manageChecksheet'),
('subforeman', 'manageChecksheet'),
('admin', 'manager'),
('subforeman', 'operator'),
('foreman', 'subforeman'),
('admin', 'updateUser'),
('foreman', 'viewChecksheet'),
('operator', 'viewChecksheet'),
('foreman', 'viewDashboard'),
('operator', 'viewDashboard'),
('manager', 'viewUser'),
('operator', 'viewUser');

-- --------------------------------------------------------

--
-- Struktur dari tabel `auth_rule`
--

CREATE TABLE `auth_rule` (
  `name` varchar(64) NOT NULL,
  `data` text,
  `created_at` int DEFAULT NULL,
  `updated_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `checksheet_answer`
--

CREATE TABLE `checksheet_answer` (
  `id` int NOT NULL,
  `instance_id` int NOT NULL,
  `item_id` varchar(50) NOT NULL,
  `value` tinyint NOT NULL,
  `note` text,
  `created_at` int NOT NULL,
  `updated_at` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `checksheet_answer`
--

INSERT INTO `checksheet_answer` (`id`, `instance_id`, `item_id`, `value`, `note`, `created_at`, `updated_at`) VALUES
(1, 5, 'CHK-001', 1, NULL, 1767716488, 1767716488),
(2, 5, 'CHK-002', 1, NULL, 1767716488, 1767716488),
(3, 5, 'CHK-003', 1, NULL, 1767716488, 1767716488),
(4, 5, 'CHK-004', 1, NULL, 1767716488, 1767716488),
(5, 5, 'CHK-005', 1, NULL, 1767716488, 1767716488),
(6, 5, 'CHK-006', 1, NULL, 1767716488, 1767716488),
(7, 5, 'CHK-007', 1, NULL, 1767716488, 1767716488),
(8, 5, 'CHK-008', 1, NULL, 1767716488, 1767716488),
(9, 5, 'CHK-009', 1, NULL, 1767716488, 1767716488),
(10, 5, 'CHK-010', 1, NULL, 1767716488, 1767716488),
(11, 5, 'CHK-011', 1, NULL, 1767716488, 1767716488),
(12, 5, 'CHK-012', 1, NULL, 1767716488, 1767716488),
(13, 5, 'CHK-013', 1, NULL, 1767716488, 1767716488),
(14, 5, 'CHK-014', 1, NULL, 1767716488, 1767716488),
(15, 5, 'CHK-015', 1, NULL, 1767716488, 1767716488),
(16, 5, 'CHK-016', 1, NULL, 1767716488, 1767716488),
(17, 5, 'CHK-017', 1, NULL, 1767716488, 1767716488),
(18, 5, 'CHK-018', 1, NULL, 1767716488, 1767716488),
(19, 5, 'CHK-019', 1, NULL, 1767716488, 1767716488),
(20, 5, 'CHK-020', 1, NULL, 1767716488, 1767716488),
(21, 5, 'CHK-021', 1, NULL, 1767716488, 1767716488),
(22, 5, 'CHK-022', 1, NULL, 1767716488, 1767716488),
(23, 5, 'CHK-023', 1, NULL, 1767716488, 1767716488),
(24, 5, 'CHK-024', 1, NULL, 1767716488, 1767716488),
(25, 5, 'CHK-025', 1, NULL, 1767716488, 1767716488),
(26, 5, 'CHK-026', 1, NULL, 1767716488, 1767716488),
(27, 5, 'CHK-027', 1, NULL, 1767716488, 1767716488),
(28, 5, 'CHK-028', 1, NULL, 1767716488, 1767716488),
(29, 5, 'CHK-029', 1, NULL, 1767716488, 1767716488),
(30, 5, 'CHK-030', 1, NULL, 1767716488, 1767716488),
(31, 5, 'CHK-031', 1, NULL, 1767716488, 1767716488),
(32, 5, 'CHK-032', 1, NULL, 1767716488, 1767716488),
(33, 5, 'CHK-033', 1, NULL, 1767716488, 1767716488),
(34, 5, 'CHK-034', 1, NULL, 1767716488, 1767716488),
(35, 5, 'CHK-035', 1, NULL, 1767716488, 1767716488),
(36, 5, 'CHK-036', 1, NULL, 1767716488, 1767716488),
(37, 6, 'CHK-001', 1, NULL, 1767716824, 1767716824),
(38, 6, 'CHK-002', 1, NULL, 1767716824, 1767716824),
(39, 6, 'CHK-003', 1, NULL, 1767716824, 1767716824),
(40, 6, 'CHK-004', 1, NULL, 1767716824, 1767716824),
(41, 6, 'CHK-005', 1, NULL, 1767716824, 1767716824),
(42, 6, 'CHK-006', 1, NULL, 1767716824, 1767716824),
(43, 6, 'CHK-007', 1, NULL, 1767716824, 1767716824),
(44, 6, 'CHK-008', 1, NULL, 1767716824, 1767716824),
(45, 6, 'CHK-009', 1, NULL, 1767716824, 1767716824),
(46, 6, 'CHK-010', 1, NULL, 1767716824, 1767716824),
(47, 6, 'CHK-011', 1, NULL, 1767716824, 1767716824),
(48, 6, 'CHK-012', 1, NULL, 1767716824, 1767716824),
(49, 6, 'CHK-013', 1, NULL, 1767716824, 1767716824),
(50, 6, 'CHK-014', 1, NULL, 1767716824, 1767716824),
(51, 6, 'CHK-015', 1, NULL, 1767716824, 1767716824),
(52, 6, 'CHK-016', 1, NULL, 1767716824, 1767716824),
(53, 6, 'CHK-017', 1, NULL, 1767716824, 1767716824),
(54, 6, 'CHK-018', 1, NULL, 1767716824, 1767716824),
(55, 6, 'CHK-019', 1, NULL, 1767716824, 1767716824),
(56, 6, 'CHK-020', 1, NULL, 1767716824, 1767716824),
(57, 6, 'CHK-021', 1, NULL, 1767716824, 1767716824),
(58, 6, 'CHK-022', 1, NULL, 1767716824, 1767716824),
(59, 6, 'CHK-023', 1, NULL, 1767716824, 1767716824),
(60, 6, 'CHK-024', 1, NULL, 1767716824, 1767716824),
(61, 6, 'CHK-025', 1, NULL, 1767716824, 1767716824),
(62, 6, 'CHK-026', 1, NULL, 1767716824, 1767716824),
(63, 6, 'CHK-027', 1, NULL, 1767716824, 1767716824),
(64, 6, 'CHK-028', 1, NULL, 1767716824, 1767716824),
(65, 6, 'CHK-029', 1, NULL, 1767716824, 1767716824),
(66, 6, 'CHK-030', 1, NULL, 1767716824, 1767716824),
(67, 6, 'CHK-031', 1, NULL, 1767716824, 1767716824),
(68, 6, 'CHK-032', 1, NULL, 1767716824, 1767716824),
(69, 6, 'CHK-033', 1, NULL, 1767716824, 1767716824),
(70, 6, 'CHK-034', 1, NULL, 1767716824, 1767716824),
(71, 6, 'CHK-035', 1, NULL, 1767716824, 1767716824),
(72, 6, 'CHK-036', 1, NULL, 1767716824, 1767716824),
(73, 7, 'CHK-001', 1, NULL, 1767716849, 1767716849),
(74, 7, 'CHK-002', 1, NULL, 1767716849, 1767716849),
(75, 7, 'CHK-003', 1, NULL, 1767716849, 1767716849),
(76, 7, 'CHK-004', 1, NULL, 1767716849, 1767716849),
(77, 7, 'CHK-005', 1, NULL, 1767716849, 1767716849),
(78, 7, 'CHK-006', 1, NULL, 1767716849, 1767716849),
(79, 7, 'CHK-007', 1, NULL, 1767716849, 1767716849),
(80, 7, 'CHK-008', 1, NULL, 1767716849, 1767716849),
(81, 7, 'CHK-009', 1, NULL, 1767716849, 1767716849),
(82, 7, 'CHK-010', 1, NULL, 1767716849, 1767716849),
(83, 7, 'CHK-011', 1, NULL, 1767716849, 1767716849),
(84, 7, 'CHK-012', 1, NULL, 1767716849, 1767716849),
(85, 7, 'CHK-013', 1, NULL, 1767716849, 1767716849),
(86, 7, 'CHK-014', 1, NULL, 1767716849, 1767716849),
(87, 7, 'CHK-015', 1, NULL, 1767716849, 1767716849),
(88, 7, 'CHK-016', 1, NULL, 1767716849, 1767716849),
(89, 7, 'CHK-017', 1, NULL, 1767716849, 1767716849),
(90, 7, 'CHK-018', 1, NULL, 1767716849, 1767716849),
(91, 7, 'CHK-019', 1, NULL, 1767716849, 1767716849),
(92, 7, 'CHK-020', 1, NULL, 1767716849, 1767716849),
(93, 7, 'CHK-021', 1, NULL, 1767716849, 1767716849),
(94, 7, 'CHK-022', 1, NULL, 1767716849, 1767716849),
(95, 7, 'CHK-023', 1, NULL, 1767716849, 1767716849),
(96, 7, 'CHK-024', 1, NULL, 1767716849, 1767716849),
(97, 7, 'CHK-025', 1, NULL, 1767716849, 1767716849),
(98, 7, 'CHK-026', 1, NULL, 1767716849, 1767716849),
(99, 7, 'CHK-027', 1, NULL, 1767716849, 1767716849),
(100, 7, 'CHK-028', 1, NULL, 1767716849, 1767716849),
(101, 7, 'CHK-029', 1, NULL, 1767716849, 1767716849),
(102, 7, 'CHK-030', 1, NULL, 1767716849, 1767716849),
(103, 7, 'CHK-031', 1, NULL, 1767716849, 1767716849),
(104, 7, 'CHK-032', 1, NULL, 1767716849, 1767716849),
(105, 7, 'CHK-033', 1, NULL, 1767716849, 1767716849),
(106, 7, 'CHK-034', 1, NULL, 1767716849, 1767716849),
(107, 7, 'CHK-035', 1, NULL, 1767716849, 1767716849),
(108, 7, 'CHK-036', 1, NULL, 1767716849, 1767716849),
(109, 8, 'CHK-001', 1, NULL, 1767718116, 1767718116),
(110, 8, 'CHK-002', 1, NULL, 1767718116, 1767718116),
(111, 8, 'CHK-003', 1, NULL, 1767718116, 1767718116),
(112, 8, 'CHK-004', 1, NULL, 1767718116, 1767718116),
(113, 8, 'CHK-005', 1, NULL, 1767718116, 1767718116),
(114, 8, 'CHK-006', 1, NULL, 1767718116, 1767718116),
(115, 8, 'CHK-007', 1, NULL, 1767718116, 1767718116),
(116, 8, 'CHK-008', 1, NULL, 1767718116, 1767718116),
(117, 8, 'CHK-009', 1, NULL, 1767718116, 1767718116),
(118, 8, 'CHK-010', 1, NULL, 1767718116, 1767718116),
(119, 8, 'CHK-011', 1, NULL, 1767718116, 1767718116),
(120, 8, 'CHK-012', 1, NULL, 1767718116, 1767718116),
(121, 8, 'CHK-013', 1, NULL, 1767718116, 1767718116),
(122, 8, 'CHK-014', 1, NULL, 1767718116, 1767718116),
(123, 8, 'CHK-015', 1, NULL, 1767718116, 1767718116),
(124, 8, 'CHK-016', 1, NULL, 1767718116, 1767718116),
(125, 8, 'CHK-017', 1, NULL, 1767718116, 1767718116),
(126, 8, 'CHK-018', 1, NULL, 1767718116, 1767718116),
(127, 8, 'CHK-019', 1, NULL, 1767718116, 1767718116),
(128, 8, 'CHK-020', 1, NULL, 1767718116, 1767718116),
(129, 8, 'CHK-021', 1, NULL, 1767718116, 1767718116),
(130, 8, 'CHK-022', 1, NULL, 1767718116, 1767718116),
(131, 8, 'CHK-023', 1, NULL, 1767718116, 1767718116),
(132, 8, 'CHK-024', 1, NULL, 1767718116, 1767718116),
(133, 8, 'CHK-025', 1, NULL, 1767718116, 1767718116),
(134, 8, 'CHK-026', 1, NULL, 1767718116, 1767718116),
(135, 8, 'CHK-027', 1, NULL, 1767718116, 1767718116),
(136, 8, 'CHK-028', 1, NULL, 1767718116, 1767718116),
(137, 8, 'CHK-029', 1, NULL, 1767718116, 1767718116),
(138, 8, 'CHK-030', 1, NULL, 1767718116, 1767718116),
(139, 8, 'CHK-031', 1, NULL, 1767718116, 1767718116),
(140, 8, 'CHK-032', 1, NULL, 1767718116, 1767718116),
(141, 8, 'CHK-033', 1, NULL, 1767718116, 1767718116),
(142, 8, 'CHK-034', 1, NULL, 1767718116, 1767718116),
(143, 8, 'CHK-035', 1, NULL, 1767718116, 1767718116),
(144, 8, 'CHK-036', 1, NULL, 1767718116, 1767718116);

-- --------------------------------------------------------

--
-- Struktur dari tabel `checksheet_instance`
--

CREATE TABLE `checksheet_instance` (
  `id` int NOT NULL,
  `template_id` int NOT NULL,
  `mesin_id` int NOT NULL,
  `tanggal` date NOT NULL,
  `shift` tinyint NOT NULL,
  `operator_id` varchar(50) DEFAULT NULL,
  `status` enum('draft','submitted','approved') NOT NULL DEFAULT 'draft',
  `created_at` int NOT NULL,
  `updated_at` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `checksheet_instance`
--

INSERT INTO `checksheet_instance` (`id`, `template_id`, `mesin_id`, `tanggal`, `shift`, `operator_id`, `status`, `created_at`, `updated_at`) VALUES
(2, 1, 22, '2026-01-06', 1, 'OP-TEST', 'submitted', 1767716292, 1767716292),
(3, 1, 22, '2026-01-06', 1, 'OP-TEST', 'submitted', 1767716308, 1767716308),
(4, 1, 22, '2026-01-06', 1, 'OP-TEST', 'submitted', 1767716402, 1767716402),
(5, 1, 22, '2026-01-06', 1, 'OP-TEST', 'submitted', 1767716488, 1767716488),
(6, 1, 22, '2026-01-06', 1, 'OP-TEST', 'submitted', 1767716824, 1767716824),
(7, 1, 22, '2026-01-06', 1, 'OP-TEST', 'submitted', 1767716849, 1767716849),
(8, 1, 22, '2026-01-06', 1, 'OP-TEST', 'submitted', 1767718116, 1767718116);

-- --------------------------------------------------------

--
-- Struktur dari tabel `checksheet_item`
--

CREATE TABLE `checksheet_item` (
  `id` int NOT NULL,
  `item_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `template_id` int NOT NULL,
  `section_id` int NOT NULL,
  `label` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('checklist','number','text_input','okng') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'checklist',
  `symbol_id` int DEFAULT NULL,
  `symbol_id_2` int DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `excel_row_base` int NOT NULL,
  `shift_json` json NOT NULL,
  `instruction_json` json NOT NULL,
  `created_at` int NOT NULL,
  `updated_at` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `checksheet_item`
--

INSERT INTO `checksheet_item` (`id`, `item_code`, `template_id`, `section_id`, `label`, `type`, `symbol_id`, `symbol_id_2`, `sort_order`, `excel_row_base`, `shift_json`, `instruction_json`, `created_at`, `updated_at`) VALUES
(31, 'ITEM-14-15-1773841469', 14, 15, 'BOX LAMPU', 'number', 2, NULL, 1, 1, '\"[\\\"1\\\",\\\"2\\\",\\\"3\\\"]\"', '\"{\\\"standard\\\":[\\\"[0,5 ~ 0,6 Mpa]\\\\t\\\\t\\\"],\\\"cara\\\":[\\\"LIHAT & BERSIHKAN\\\"],\\\"frekuensi\\\":[\\\"1X \\\\/SHIFT\\\"],\\\"note\\\":[]}\"', 1773841469, 1773846396),
(32, 'ITEM-14-15-1773841505', 14, 15, 'MEJA KERJA', 'checklist', NULL, NULL, 2, 2, '\"[\\\"1\\\",\\\"2\\\",\\\"3\\\"]\"', '\"{\\\"standard\\\":[\\\"BERSIH DARI KOTORAN\\\"],\\\"cara\\\":[\\\"LIHAT & CHECK\\\"],\\\"frekuensi\\\":[\\\"1X \\\\/SHIFT\\\"],\\\"note\\\":[]}\"', 1773841505, 1773841521),
(33, 'ITEM-14-15-1773841543', 14, 15, 'JIG BEARING A & B', 'checklist', NULL, NULL, 3, 3, '\"[\\\"1\\\",\\\"2\\\",\\\"3\\\"]\"', '\"{\\\"standard\\\":[\\\"BERSIH DARI KOTORAN\\\",\\\"TIDAK AUS & TIDAK OBLAK\\\"],\\\"cara\\\":[\\\"LIHAT & CHECK\\\",\\\"LIHAAT\\\"],\\\"frekuensi\\\":[\\\"1X \\\\/SHIFT\\\",\\\"1X 1 SHIFT\\\"],\\\"note\\\":[],\\\"conditions\\\":[{\\\"standard\\\":\\\"BERSIH DARI KOTORAN\\\",\\\"cara\\\":\\\"LIHAT & CHECK\\\",\\\"frekuensi\\\":\\\"1X \\\\/SHIFT\\\",\\\"note\\\":\\\"\\\"},{\\\"standard\\\":\\\"TIDAK AUS & TIDAK OBLAK\\\",\\\"cara\\\":\\\"LIHAAT\\\",\\\"frekuensi\\\":\\\"1X 1 SHIFT\\\",\\\"note\\\":\\\"\\\"}]}\"', 1773841543, 1773849833),
(34, 'ITEM-14-15-1773841563', 14, 15, '\"MATA TORQUE PIN A & B\"			 			 			', 'checklist', NULL, NULL, 4, 4, '\"[\\\"1\\\",\\\"2\\\",\\\"3\\\"]\"', '\"{\\\"standard\\\":[\\\"BERSIH DARI KOTORAN\\\"],\\\"cara\\\":[\\\"LIHAT & CHECK\\\"],\\\"frekuensi\\\":[\\\"1X \\\\/SHIFT\\\"],\\\"note\\\":[]}\"', 1773841563, 1773841577),
(35, 'ITEM-14-15-1773841584', 14, 15, 'TEMPAT LOCTITE			', 'checklist', NULL, NULL, 5, 5, '\"[\\\"1\\\",\\\"2\\\",\\\"3\\\"]\"', '\"{\\\"standard\\\":[\\\"BERSIH DARI KOTORAN\\\"],\\\"cara\\\":[\\\"LIHAT & CHECK\\\"],\\\"frekuensi\\\":[\\\"1X \\\\/SHIFT\\\"],\\\"note\\\":[]}\"', 1773841584, 1773841595),
(36, 'ITEM-14-15-1773841604', 14, 15, 'CYLINDER JIG BEARING A & B			 			 			', 'checklist', NULL, NULL, 6, 6, '\"[\\\"1\\\",\\\"2\\\",\\\"3\\\"]\"', '\"{\\\"standard\\\":[\\\"Pemastian body & seal bocor\\\"],\\\"cara\\\":[\\\"LIHAT & BERSIHKAN\\\"],\\\"frekuensi\\\":[\\\"1X \\\\/SHIFT\\\"],\\\"note\\\":[]}\"', 1773841604, 1773841617),
(37, 'ITEM-14-15-1773841632', 14, 15, 'SELANG DAN COUPLER			 			 			', 'checklist', NULL, NULL, 7, 7, '\"[\\\"1\\\",\\\"2\\\",\\\"3\\\"]\"', '\"{\\\"standard\\\":[\\\"Pemastian body & seal bocor\\\"],\\\"cara\\\":[\\\"LIHAT & CHECK\\\"],\\\"frekuensi\\\":[\\\"1X \\\\/SHIFT\\\"],\\\"note\\\":[]}\"', 1773841632, 1773841644),
(38, 'ITEM-14-15-1773841652', 14, 15, 'BAWAH MEJA PRODUKSI			 			 			', 'checklist', NULL, NULL, 8, 8, '\"[\\\"1\\\",\\\"2\\\",\\\"3\\\"]\"', '\"{\\\"standard\\\":[\\\"[0,5 ~ 0,6 Mpa]\\\"],\\\"cara\\\":[\\\"LIHAT & CHECK\\\"],\\\"frekuensi\\\":[\\\"1X \\\\/SHIFT\\\"],\\\"note\\\":[]}\"', 1773841652, 1773841662),
(39, 'ITEM-14-15-1773841669', 14, 15, 'KOLONG MEJA			 			 			', 'checklist', NULL, NULL, 9, 9, '\"[\\\"1\\\",\\\"2\\\",\\\"3\\\"]\"', '\"{\\\"standard\\\":[\\\"Pemastian body & seal bocor\\\"],\\\"cara\\\":[\\\"LIHAT & CHECK\\\"],\\\"frekuensi\\\":[\\\"1X \\\\/SHIFT\\\"],\\\"note\\\":[]}\"', 1773841669, 1773841680),
(40, 'ITEM-14-15-1773841688', 14, 15, '\"AREA TORQUE  NUT RUNNER\"			 			 			', 'checklist', NULL, NULL, 10, 10, '\"[\\\"1\\\",\\\"2\\\",\\\"3\\\"]\"', '\"{\\\"standard\\\":[\\\"[0,5 ~ 0,6 Mpa]\\\\t\\\\t\\\"],\\\"cara\\\":[\\\"Mesin dapat memastikan NG\\\\t \\\"],\\\"frekuensi\\\":[\\\"1X \\\\/SHIFT\\\"],\\\"note\\\":[]}\"', 1773841688, 1773841701);

-- --------------------------------------------------------

--
-- Struktur dari tabel `checksheet_result`
--

CREATE TABLE `checksheet_result` (
  `id` int NOT NULL,
  `template_id` int NOT NULL,
  `mesin` varchar(100) NOT NULL,
  `shift` varchar(10) NOT NULL,
  `submitted_at` datetime NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `created_by` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `checksheet_result`
--

INSERT INTO `checksheet_result` (`id`, `template_id`, `mesin`, `shift`, `submitted_at`, `file_path`, `created_at`, `created_by`) VALUES
(23, 14, 'BRACKET_AUTO-001-KRW-2025', 'Shift 1', '2026-04-29 23:39:59', NULL, '2026-04-29 23:39:59', NULL),
(24, 14, 'BRACKET_AUTO-001-KRW-2025', 'Shift 3', '2026-04-30 00:31:40', NULL, '2026-04-30 00:31:40', NULL),
(25, 14, 'BRACKET_AUTO-001-KRW-2025', 'Shift 2', '2026-04-30 00:34:04', NULL, '2026-04-30 00:34:04', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `checksheet_result_item`
--

CREATE TABLE `checksheet_result_item` (
  `id` int NOT NULL,
  `result_id` int NOT NULL,
  `item_id` int NOT NULL,
  `item_code` varchar(100) NOT NULL,
  `raw_value` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `checksheet_result_item`
--

INSERT INTO `checksheet_result_item` (`id`, `result_id`, `item_id`, `item_code`, `raw_value`, `created_at`) VALUES
(26, 3, 20, 'item_20', 'OK', '2026-02-17 21:41:38'),
(27, 3, 21, 'item_21', 'OK', '2026-02-17 21:41:38'),
(28, 3, 22, 'item_22', 'OK', '2026-02-17 21:41:38'),
(29, 3, 23, 'item_23', 'OK', '2026-02-17 21:41:38'),
(30, 3, 24, 'item_24', 'OK', '2026-02-17 21:41:38'),
(31, 3, 25, 'item_25', 'OK', '2026-02-17 21:41:38'),
(32, 3, 26, 'item_26', 'OK', '2026-02-17 21:41:38'),
(33, 3, 27, 'item_27', 'shif3', '2026-02-17 21:41:38'),
(34, 4, 16, 'item_16', 'OK', '2026-02-17 21:44:27'),
(35, 4, 28, 'ITEM-10-9-1770392569', '45', '2026-02-17 21:44:27'),
(36, 4, 29, 'ITEM-10-9-1770393211', 'tes', '2026-02-17 21:44:27'),
(37, 5, 16, 'item_16', 'OK', '2026-02-22 09:08:39'),
(38, 5, 28, 'ITEM-10-9-1770392569', '338', '2026-02-22 09:08:39'),
(39, 5, 29, 'ITEM-10-9-1770393211', 'irie', '2026-02-22 09:08:39'),
(40, 6, 4, 'item_4', 'OK', '2026-03-01 17:40:29'),
(41, 6, 5, 'item_5', 'OK', '2026-03-01 17:40:29'),
(42, 6, 6, 'item_6', 'OK', '2026-03-01 17:40:29'),
(43, 6, 7, 'item_7', 'OK', '2026-03-01 17:40:29'),
(44, 7, 16, 'item_16', 'OK', '2026-03-16 21:29:27'),
(45, 7, 28, 'ITEM-10-9-1770392569', '45', '2026-03-16 21:29:27'),
(46, 7, 29, 'ITEM-10-9-1770393211', '67', '2026-03-16 21:29:27'),
(47, 8, 4, 'item_4', 'OK', '2026-03-16 21:30:41'),
(48, 8, 5, 'item_5', 'OK', '2026-03-16 21:30:41'),
(49, 8, 6, 'item_6', 'OK', '2026-03-16 21:30:41'),
(50, 8, 7, 'item_7', 'OK', '2026-03-16 21:30:41'),
(51, 9, 17, 'item_17', 'OK', '2026-03-16 21:46:11'),
(52, 9, 18, 'item_18', 'OK', '2026-03-16 21:46:11'),
(53, 9, 19, 'item_19', 'OK', '2026-03-16 21:46:11'),
(54, 9, 20, 'item_20', 'OK', '2026-03-16 21:46:11'),
(55, 9, 21, 'item_21', 'OK', '2026-03-16 21:46:11'),
(56, 9, 22, 'item_22', 'OK', '2026-03-16 21:46:11'),
(57, 9, 23, 'item_23', 'OK', '2026-03-16 21:46:11'),
(58, 9, 24, 'item_24', 'OK', '2026-03-16 21:46:11'),
(59, 9, 25, 'item_25', 'OK', '2026-03-16 21:46:11'),
(60, 9, 26, 'item_26', 'OK', '2026-03-16 21:46:11'),
(61, 9, 27, 'item_27', '34', '2026-03-16 21:46:11'),
(62, 10, 32, 'ITEM-14-15-1773841505', 'OK', '2026-03-18 21:00:31'),
(63, 10, 33, 'ITEM-14-15-1773841543', 'OK', '2026-03-18 21:00:31'),
(64, 10, 34, 'ITEM-14-15-1773841563', 'OK', '2026-03-18 21:00:31'),
(65, 10, 35, 'ITEM-14-15-1773841584', 'OK', '2026-03-18 21:00:31'),
(66, 10, 36, 'ITEM-14-15-1773841604', 'OK', '2026-03-18 21:00:31'),
(67, 10, 37, 'ITEM-14-15-1773841632', 'OK', '2026-03-18 21:00:31'),
(68, 10, 38, 'ITEM-14-15-1773841652', 'OK', '2026-03-18 21:00:31'),
(69, 10, 39, 'ITEM-14-15-1773841669', 'OK', '2026-03-18 21:00:31'),
(70, 10, 40, 'ITEM-14-15-1773841688', 'OK', '2026-03-18 21:00:31'),
(71, 10, 31, 'ITEM-14-15-1773841469', 'OK', '2026-03-18 21:00:31'),
(72, 11, 32, 'ITEM-14-15-1773841505', 'OK', '2026-03-18 21:20:09'),
(73, 11, 33, 'ITEM-14-15-1773841543', 'OK', '2026-03-18 21:20:09'),
(74, 11, 34, 'ITEM-14-15-1773841563', 'OK', '2026-03-18 21:20:09'),
(75, 11, 35, 'ITEM-14-15-1773841584', 'OK', '2026-03-18 21:20:09'),
(76, 11, 36, 'ITEM-14-15-1773841604', 'OK', '2026-03-18 21:20:09'),
(77, 11, 37, 'ITEM-14-15-1773841632', 'OK', '2026-03-18 21:20:09'),
(78, 11, 38, 'ITEM-14-15-1773841652', 'OK', '2026-03-18 21:20:09'),
(79, 11, 39, 'ITEM-14-15-1773841669', 'OK', '2026-03-18 21:20:09'),
(80, 11, 40, 'ITEM-14-15-1773841688', 'OK', '2026-03-18 21:20:09'),
(81, 11, 31, 'ITEM-14-15-1773841469', 'OK', '2026-03-18 21:20:09'),
(82, 12, 32, 'ITEM-14-15-1773841505', 'OK', '2026-03-18 21:28:50'),
(83, 12, 33, 'ITEM-14-15-1773841543', 'OK', '2026-03-18 21:28:50'),
(84, 12, 34, 'ITEM-14-15-1773841563', 'OK', '2026-03-18 21:28:50'),
(85, 12, 35, 'ITEM-14-15-1773841584', 'OK', '2026-03-18 21:28:50'),
(86, 12, 36, 'ITEM-14-15-1773841604', 'OK', '2026-03-18 21:28:50'),
(87, 12, 37, 'ITEM-14-15-1773841632', 'OK', '2026-03-18 21:28:50'),
(88, 12, 38, 'ITEM-14-15-1773841652', 'OK', '2026-03-18 21:28:50'),
(89, 12, 39, 'ITEM-14-15-1773841669', 'OK', '2026-03-18 21:28:50'),
(90, 12, 40, 'ITEM-14-15-1773841688', 'OK', '2026-03-18 21:28:50'),
(91, 12, 31, 'ITEM-14-15-1773841469', 'OK', '2026-03-18 21:28:50'),
(92, 13, 32, 'ITEM-14-15-1773841505', 'OK', '2026-03-18 21:32:09'),
(93, 13, 33, 'ITEM-14-15-1773841543', 'OK', '2026-03-18 21:32:09'),
(94, 13, 34, 'ITEM-14-15-1773841563', 'OK', '2026-03-18 21:32:09'),
(95, 13, 35, 'ITEM-14-15-1773841584', 'OK', '2026-03-18 21:32:09'),
(96, 13, 36, 'ITEM-14-15-1773841604', 'OK', '2026-03-18 21:32:09'),
(97, 13, 37, 'ITEM-14-15-1773841632', 'OK', '2026-03-18 21:32:09'),
(98, 13, 38, 'ITEM-14-15-1773841652', 'OK', '2026-03-18 21:32:09'),
(99, 13, 39, 'ITEM-14-15-1773841669', 'OK', '2026-03-18 21:32:09'),
(100, 13, 40, 'ITEM-14-15-1773841688', 'OK', '2026-03-18 21:32:09'),
(101, 13, 31, 'ITEM-14-15-1773841469', 'OK', '2026-03-18 21:32:09'),
(102, 14, 32, 'ITEM-14-15-1773841505', 'OK', '2026-03-18 22:03:30'),
(103, 14, 33, 'ITEM-14-15-1773841543', 'OK', '2026-03-18 22:03:30'),
(104, 14, 34, 'ITEM-14-15-1773841563', 'OK', '2026-03-18 22:03:30'),
(105, 14, 35, 'ITEM-14-15-1773841584', 'OK', '2026-03-18 22:03:30'),
(106, 14, 36, 'ITEM-14-15-1773841604', 'OK', '2026-03-18 22:03:30'),
(107, 14, 37, 'ITEM-14-15-1773841632', 'OK', '2026-03-18 22:03:30'),
(108, 14, 38, 'ITEM-14-15-1773841652', 'OK', '2026-03-18 22:03:30'),
(109, 14, 39, 'ITEM-14-15-1773841669', 'OK', '2026-03-18 22:03:30'),
(110, 14, 40, 'ITEM-14-15-1773841688', 'OK', '2026-03-18 22:03:30'),
(111, 14, 31, 'ITEM-14-15-1773841469', 'OK', '2026-03-18 22:03:30'),
(112, 15, 32, 'ITEM-14-15-1773841505', 'OK', '2026-03-18 22:08:29'),
(113, 15, 33, 'ITEM-14-15-1773841543', 'OK', '2026-03-18 22:08:29'),
(114, 15, 34, 'ITEM-14-15-1773841563', 'OK', '2026-03-18 22:08:29'),
(115, 15, 35, 'ITEM-14-15-1773841584', 'OK', '2026-03-18 22:08:29'),
(116, 15, 36, 'ITEM-14-15-1773841604', 'OK', '2026-03-18 22:08:29'),
(117, 15, 37, 'ITEM-14-15-1773841632', 'OK', '2026-03-18 22:08:29'),
(118, 15, 38, 'ITEM-14-15-1773841652', 'OK', '2026-03-18 22:08:29'),
(119, 15, 39, 'ITEM-14-15-1773841669', 'OK', '2026-03-18 22:08:29'),
(120, 15, 40, 'ITEM-14-15-1773841688', 'OK', '2026-03-18 22:08:29'),
(121, 15, 31, 'ITEM-14-15-1773841469', '45', '2026-03-18 22:08:29'),
(122, 16, 32, 'ITEM-14-15-1773841505', 'OK', '2026-03-19 04:39:50'),
(123, 16, 33, 'ITEM-14-15-1773841543', 'OK', '2026-03-19 04:39:50'),
(124, 16, 34, 'ITEM-14-15-1773841563', 'OK', '2026-03-19 04:39:50'),
(125, 16, 35, 'ITEM-14-15-1773841584', 'OK', '2026-03-19 04:39:50'),
(126, 16, 36, 'ITEM-14-15-1773841604', 'OK', '2026-03-19 04:39:50'),
(127, 16, 37, 'ITEM-14-15-1773841632', 'OK', '2026-03-19 04:39:50'),
(128, 16, 38, 'ITEM-14-15-1773841652', 'OK', '2026-03-19 04:39:50'),
(129, 16, 39, 'ITEM-14-15-1773841669', 'OK', '2026-03-19 04:39:50'),
(130, 16, 40, 'ITEM-14-15-1773841688', 'OK', '2026-03-19 04:39:50'),
(131, 16, 31, 'ITEM-14-15-1773841469', '12', '2026-03-19 04:39:50'),
(132, 17, 32, 'ITEM-14-15-1773841505', 'OK', '2026-04-11 22:08:16'),
(133, 17, 33, 'ITEM-14-15-1773841543', 'OK', '2026-04-11 22:08:16'),
(134, 17, 34, 'ITEM-14-15-1773841563', 'OK', '2026-04-11 22:08:16'),
(135, 17, 35, 'ITEM-14-15-1773841584', 'OK', '2026-04-11 22:08:16'),
(136, 17, 36, 'ITEM-14-15-1773841604', 'OK', '2026-04-11 22:08:16'),
(137, 17, 37, 'ITEM-14-15-1773841632', 'OK', '2026-04-11 22:08:16'),
(138, 17, 38, 'ITEM-14-15-1773841652', 'OK', '2026-04-11 22:08:16'),
(139, 17, 39, 'ITEM-14-15-1773841669', 'OK', '2026-04-11 22:08:16'),
(140, 17, 40, 'ITEM-14-15-1773841688', 'OK', '2026-04-11 22:08:16'),
(141, 17, 31, 'ITEM-14-15-1773841469', '44', '2026-04-11 22:08:16'),
(142, 18, 32, 'ITEM-14-15-1773841505', 'OK', '2026-04-21 18:24:06'),
(143, 18, 33, 'ITEM-14-15-1773841543', 'OK', '2026-04-21 18:24:06'),
(144, 18, 34, 'ITEM-14-15-1773841563', 'OK', '2026-04-21 18:24:06'),
(145, 18, 35, 'ITEM-14-15-1773841584', 'OK', '2026-04-21 18:24:06'),
(146, 18, 36, 'ITEM-14-15-1773841604', 'OK', '2026-04-21 18:24:06'),
(147, 18, 37, 'ITEM-14-15-1773841632', 'OK', '2026-04-21 18:24:06'),
(148, 18, 38, 'ITEM-14-15-1773841652', 'OK', '2026-04-21 18:24:06'),
(149, 18, 39, 'ITEM-14-15-1773841669', 'OK', '2026-04-21 18:24:06'),
(150, 18, 40, 'ITEM-14-15-1773841688', 'OK', '2026-04-21 18:24:06'),
(151, 18, 31, 'ITEM-14-15-1773841469', '04', '2026-04-21 18:24:06'),
(152, 19, 32, 'ITEM-14-15-1773841505', 'OK', '2026-04-21 18:26:34'),
(153, 19, 33, 'ITEM-14-15-1773841543', 'OK', '2026-04-21 18:26:34'),
(154, 19, 34, 'ITEM-14-15-1773841563', 'OK', '2026-04-21 18:26:34'),
(155, 19, 35, 'ITEM-14-15-1773841584', 'OK', '2026-04-21 18:26:34'),
(156, 19, 36, 'ITEM-14-15-1773841604', 'OK', '2026-04-21 18:26:34'),
(157, 19, 37, 'ITEM-14-15-1773841632', 'OK', '2026-04-21 18:26:34'),
(158, 19, 38, 'ITEM-14-15-1773841652', 'OK', '2026-04-21 18:26:34'),
(159, 19, 39, 'ITEM-14-15-1773841669', 'OK', '2026-04-21 18:26:34'),
(160, 19, 40, 'ITEM-14-15-1773841688', 'OK', '2026-04-21 18:26:34'),
(161, 19, 31, 'ITEM-14-15-1773841469', '300', '2026-04-21 18:26:34'),
(162, 20, 32, 'ITEM-14-15-1773841505', 'OK', '2026-04-29 22:16:43'),
(163, 20, 33, 'ITEM-14-15-1773841543', 'OK', '2026-04-29 22:16:43'),
(164, 20, 34, 'ITEM-14-15-1773841563', 'OK', '2026-04-29 22:16:43'),
(165, 20, 35, 'ITEM-14-15-1773841584', 'OK', '2026-04-29 22:16:43'),
(166, 20, 36, 'ITEM-14-15-1773841604', 'OK', '2026-04-29 22:16:43'),
(167, 20, 37, 'ITEM-14-15-1773841632', 'OK', '2026-04-29 22:16:43'),
(168, 20, 38, 'ITEM-14-15-1773841652', 'OK', '2026-04-29 22:16:43'),
(169, 20, 39, 'ITEM-14-15-1773841669', 'OK', '2026-04-29 22:16:43'),
(170, 20, 40, 'ITEM-14-15-1773841688', 'OK', '2026-04-29 22:16:43'),
(171, 20, 31, 'ITEM-14-15-1773841469', '4', '2026-04-29 22:16:43'),
(172, 21, 32, 'ITEM-14-15-1773841505', 'OK', '2026-04-29 22:21:57'),
(173, 21, 33, 'ITEM-14-15-1773841543', 'OK', '2026-04-29 22:21:57'),
(174, 21, 34, 'ITEM-14-15-1773841563', 'OK', '2026-04-29 22:21:57'),
(175, 21, 35, 'ITEM-14-15-1773841584', 'OK', '2026-04-29 22:21:57'),
(176, 21, 36, 'ITEM-14-15-1773841604', 'OK', '2026-04-29 22:21:57'),
(177, 21, 37, 'ITEM-14-15-1773841632', 'OK', '2026-04-29 22:21:57'),
(178, 21, 38, 'ITEM-14-15-1773841652', 'OK', '2026-04-29 22:21:57'),
(179, 21, 39, 'ITEM-14-15-1773841669', 'OK', '2026-04-29 22:21:57'),
(180, 21, 40, 'ITEM-14-15-1773841688', 'OK', '2026-04-29 22:21:57'),
(181, 21, 31, 'ITEM-14-15-1773841469', '5', '2026-04-29 22:21:57'),
(182, 22, 32, 'ITEM-14-15-1773841505', 'OK', '2026-04-29 22:38:31'),
(183, 22, 33, 'ITEM-14-15-1773841543', 'OK', '2026-04-29 22:38:31'),
(184, 22, 34, 'ITEM-14-15-1773841563', 'OK', '2026-04-29 22:38:31'),
(185, 22, 35, 'ITEM-14-15-1773841584', 'OK', '2026-04-29 22:38:31'),
(186, 22, 36, 'ITEM-14-15-1773841604', 'OK', '2026-04-29 22:38:31'),
(187, 22, 37, 'ITEM-14-15-1773841632', 'OK', '2026-04-29 22:38:31'),
(188, 22, 38, 'ITEM-14-15-1773841652', 'OK', '2026-04-29 22:38:31'),
(189, 22, 39, 'ITEM-14-15-1773841669', 'OK', '2026-04-29 22:38:31'),
(190, 22, 40, 'ITEM-14-15-1773841688', 'OK', '2026-04-29 22:38:31'),
(191, 22, 31, 'ITEM-14-15-1773841469', '3', '2026-04-29 22:38:31'),
(192, 23, 32, 'ITEM-14-15-1773841505', 'OK', '2026-04-29 23:39:59'),
(193, 23, 33, 'ITEM-14-15-1773841543', 'OK', '2026-04-29 23:39:59'),
(194, 23, 34, 'ITEM-14-15-1773841563', 'OK', '2026-04-29 23:39:59'),
(195, 23, 35, 'ITEM-14-15-1773841584', 'OK', '2026-04-29 23:39:59'),
(196, 23, 36, 'ITEM-14-15-1773841604', 'OK', '2026-04-29 23:39:59'),
(197, 23, 37, 'ITEM-14-15-1773841632', 'OK', '2026-04-29 23:39:59'),
(198, 23, 38, 'ITEM-14-15-1773841652', 'OK', '2026-04-29 23:39:59'),
(199, 23, 39, 'ITEM-14-15-1773841669', 'OK', '2026-04-29 23:39:59'),
(200, 23, 40, 'ITEM-14-15-1773841688', 'OK', '2026-04-29 23:39:59'),
(201, 23, 31, 'ITEM-14-15-1773841469', '1', '2026-04-29 23:39:59'),
(202, 24, 32, 'ITEM-14-15-1773841505', 'OK', '2026-04-30 00:31:40'),
(203, 24, 33, 'ITEM-14-15-1773841543', 'OK', '2026-04-30 00:31:40'),
(204, 24, 34, 'ITEM-14-15-1773841563', 'OK', '2026-04-30 00:31:40'),
(205, 24, 35, 'ITEM-14-15-1773841584', 'OK', '2026-04-30 00:31:40'),
(206, 24, 36, 'ITEM-14-15-1773841604', 'OK', '2026-04-30 00:31:40'),
(207, 24, 37, 'ITEM-14-15-1773841632', 'OK', '2026-04-30 00:31:40'),
(208, 24, 38, 'ITEM-14-15-1773841652', 'OK', '2026-04-30 00:31:40'),
(209, 24, 39, 'ITEM-14-15-1773841669', 'OK', '2026-04-30 00:31:40'),
(210, 24, 40, 'ITEM-14-15-1773841688', 'OK', '2026-04-30 00:31:40'),
(211, 24, 31, 'ITEM-14-15-1773841469', '3', '2026-04-30 00:31:40'),
(212, 25, 32, 'ITEM-14-15-1773841505', 'OK', '2026-04-30 00:34:04'),
(213, 25, 33, 'ITEM-14-15-1773841543', 'OK', '2026-04-30 00:34:04'),
(214, 25, 34, 'ITEM-14-15-1773841563', 'OK', '2026-04-30 00:34:04'),
(215, 25, 35, 'ITEM-14-15-1773841584', 'OK', '2026-04-30 00:34:04'),
(216, 25, 36, 'ITEM-14-15-1773841604', 'OK', '2026-04-30 00:34:04'),
(217, 25, 37, 'ITEM-14-15-1773841632', 'OK', '2026-04-30 00:34:04'),
(218, 25, 38, 'ITEM-14-15-1773841652', 'OK', '2026-04-30 00:34:04'),
(219, 25, 39, 'ITEM-14-15-1773841669', 'OK', '2026-04-30 00:34:04'),
(220, 25, 40, 'ITEM-14-15-1773841688', 'OK', '2026-04-30 00:34:04'),
(221, 25, 31, 'ITEM-14-15-1773841469', '2', '2026-04-30 00:34:04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `checksheet_section`
--

CREATE TABLE `checksheet_section` (
  `id` int NOT NULL,
  `template_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` int NOT NULL,
  `updated_at` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `checksheet_section`
--

INSERT INTO `checksheet_section` (`id`, `template_id`, `title`, `sort_order`, `created_at`, `updated_at`) VALUES
(15, 14, 'START CHECK', 1, 1773841459, 1773841459);

-- --------------------------------------------------------

--
-- Struktur dari tabel `checksheet_symbol`
--

CREATE TABLE `checksheet_symbol` (
  `id` int NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `image_path` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `checksheet_symbol`
--

INSERT INTO `checksheet_symbol` (`id`, `code`, `name`, `description`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 'symbol-02', 'Recycle', 'Resycle Use', '/uploads/symbols/symbol-02.png', 1, '2026-01-01 16:18:25', '2026-01-01 16:18:25'),
(4, 'symbol-N', 'symbol-N', 'symbol-N', '/uploads/symbols/symbol-n.png', 1, '2026-01-03 21:05:25', '2026-01-03 21:05:25'),
(5, 'symbol-Q', 'symbol-Q', 'symbol-Q', '/uploads/symbols/symbol-q.png', 1, '2026-01-03 21:06:23', '2026-01-03 21:06:23');

-- --------------------------------------------------------

--
-- Struktur dari tabel `checksheet_template`
--

CREATE TABLE `checksheet_template` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `mesin_id` int NOT NULL,
  `version` varchar(50) NOT NULL,
  `excel_template_path` varchar(255) DEFAULT NULL,
  `status` enum('draft','active','archived') DEFAULT 'draft',
  `created_at` int NOT NULL,
  `updated_at` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `checksheet_template`
--

INSERT INTO `checksheet_template` (`id`, `name`, `mesin_id`, `version`, `excel_template_path`, `status`, `created_at`, `updated_at`) VALUES
(14, 'BRACKET AUTO 1', 24, '1', NULL, 'active', 1773841440, 1773841440);

-- --------------------------------------------------------

--
-- Struktur dari tabel `checksheet_template_map`
--

CREATE TABLE `checksheet_template_map` (
  `id` int NOT NULL,
  `form_template_id` int NOT NULL,
  `item_code` varchar(100) NOT NULL,
  `sheet_name` varchar(100) NOT NULL,
  `excel_cell` varchar(10) NOT NULL,
  `created_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `daftar_mesin`
--

CREATE TABLE `daftar_mesin` (
  `id` int NOT NULL,
  `no_mesin` varchar(64) NOT NULL,
  `nama_mesin` varchar(255) NOT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `lokasi` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `vendor` varchar(255) DEFAULT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `tgl_last_maintenance` date DEFAULT NULL,
  `next_maintenance_due` date DEFAULT NULL,
  `qr_code_path` varchar(255) DEFAULT NULL,
  `created_at` int NOT NULL,
  `updated_at` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `daftar_mesin`
--

INSERT INTO `daftar_mesin` (`id`, `no_mesin`, `nama_mesin`, `kategori`, `lokasi`, `status`, `vendor`, `serial_number`, `tgl_last_maintenance`, `next_maintenance_due`, `qr_code_path`, `created_at`, `updated_at`) VALUES
(22, 'MC-001-KRW-2025', 'MC Assy Line 1 Karawang', 'Assembly', 'Assembling Karawang F1', 'active', '-', '-', NULL, '2025-12-01', 'qrcode/22.png', 1766238843, 1766238843),
(23, 'MC-010-KRW-2025', 'MC Assy Line 2 Karawang', 'Assembly', 'Assembling Karawang F1', 'active', '-', NULL, NULL, NULL, 'qrcode/23.png', 1766286346, 1767783955),
(24, 'BRACKET_AUTO-001-KRW-2025', 'BRACKET_AUTO-001-KRW-2025', 'Assembly', 'Assembling Karawang F1', 'active', NULL, NULL, NULL, NULL, 'qrcode/24.png', 1768620848, 1768620848);

-- --------------------------------------------------------

--
-- Struktur dari tabel `form_result`
--

CREATE TABLE `form_result` (
  `id` int NOT NULL,
  `template_id` int DEFAULT NULL,
  `no_mesin` varchar(64) NOT NULL,
  `operator` varchar(64) NOT NULL,
  `tanggal` date NOT NULL,
  `shift` varchar(10) NOT NULL,
  `created_at` int NOT NULL,
  `updated_at` int NOT NULL,
  `approval_status` varchar(32) NOT NULL DEFAULT 'submitted',
  `leader_id` int DEFAULT NULL,
  `leader_approved_at` int DEFAULT NULL,
  `supervisor_id` int DEFAULT NULL,
  `supervisor_approved_at` int DEFAULT NULL,
  `chief_id` int DEFAULT NULL,
  `chief_approved_at` int DEFAULT NULL,
  `manager_id` int DEFAULT NULL,
  `manager_approved_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `form_result`
--

INSERT INTO `form_result` (`id`, `template_id`, `no_mesin`, `operator`, `tanggal`, `shift`, `created_at`, `updated_at`, `approval_status`, `leader_id`, `leader_approved_at`, `supervisor_id`, `supervisor_approved_at`, `chief_id`, `chief_approved_at`, `manager_id`, `manager_approved_at`) VALUES
(27, NULL, 'MCH-NEW-1', 'Rian', '2026-04-07', 'A', 1775566494, 1775566494, 'approved', 8, 1775567199, 7, 1775567628, 9, 1775567658, 10, 1775567705),
(28, NULL, 'MCH-NEW-2', 'Andi', '2026-04-07', 'A', 1775566594, 1775566594, 'approved', 8, 1775566604, 7, 1775567628, 9, 1775567658, 10, 1775567705),
(29, NULL, 'MCH-NEW-3', 'Budi', '2026-04-07', 'A', 1775566694, 1775566694, 'approved', 8, 1775566704, 7, 1775566714, 9, 1775567658, 10, 1775567705),
(30, NULL, 'MCH-NEW-4', 'Cici', '2026-04-07', 'A', 1775566794, 1775566794, 'approved', 8, 1775566804, 7, 1775566814, 9, 1775566824, 10, 1775567705),
(31, NULL, 'MCH-NEW-5', 'Dewi', '2026-04-07', 'A', 1775566894, 1775566894, 'approved', 8, 1775566904, 7, 1775566914, 9, 1775566924, 10, 1775566934),
(38, NULL, 'MCH-DUMMY-1', 'Operator Dummy 1', '2026-04-07', 'Pagi', 1775396667, 1775483067, 'approved', 8, 1775224928, 7, 1775311328, 9, 1775397728, 10, 1775571499),
(39, NULL, 'MCH-DUMMY-2', 'Operator Dummy 2', '2026-04-07', 'Siang', 1775310267, 1775483067, 'approved', 8, 1775311328, 7, 1775397728, 9, 1775484128, 10, 1775571499),
(40, NULL, 'MCH-DUMMY-1', 'Operator Dummy 1', '2026-04-07', 'Pagi', 1775396667, 1775483067, 'approved', 8, 1775397728, 7, 1775484128, 9, 1775570683, 10, 1775571499),
(41, NULL, 'MCH-DUMMY-2', 'Operator Dummy 2', '2026-04-07', 'Siang', 1775310267, 1775483067, 'approved', 8, 1775397728, 7, 1775484128, 9, 1775566928, 10, 1775571499);

-- --------------------------------------------------------

--
-- Struktur dari tabel `form_result_detail`
--

CREATE TABLE `form_result_detail` (
  `id` int NOT NULL,
  `form_result_id` int NOT NULL,
  `field_name` varchar(255) NOT NULL,
  `field_value` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `form_template`
--

CREATE TABLE `form_template` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `mesin_id` int DEFAULT NULL,
  `schema_json` longtext NOT NULL,
  `source_file` varchar(255) DEFAULT NULL,
  `master_pdf_path` varchar(255) DEFAULT NULL,
  `status` enum('draft','active','archived') NOT NULL DEFAULT 'draft',
  `version` int NOT NULL DEFAULT '1',
  `created_at` int NOT NULL,
  `updated_at` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `form_template`
--

INSERT INTO `form_template` (`id`, `name`, `description`, `mesin_id`, `schema_json`, `source_file`, `master_pdf_path`, `status`, `version`, `created_at`, `updated_at`) VALUES
(22, '', NULL, NULL, '{\n    \"version\": 1,\n    \"generated_at\": \"2026-04-29 23:28:10\",\n    \"source\": \"builder\",\n    \"builder_template_id\": 14,\n    \"builder_template_name\": \"BRACKET AUTO 1\",\n    \"items\": [\n        {\n            \"item_id\": \"CHK-001\",\n            \"builder_item_code\": \"ITEM-14-15-1773841469\",\n            \"no\": 1,\n            \"section\": \"START CHECK\",\n            \"label\": \"BOX LAMPU\",\n            \"standard\": \"[0,5 ~ 0,6 Mpa]\",\n            \"cara\": \"LIHAT & BERSIHKAN\",\n            \"conditions\": [\n                {\n                    \"standard\": \"[0,5 ~ 0,6 Mpa]\",\n                    \"cara\": \"LIHAT & BERSIHKAN\",\n                    \"frekuensi\": \"1X \\/SHIFT\",\n                    \"note\": \"\",\n                    \"excel\": {\n                        \"sheet\": \"11 SCS MESIN BRACKET AUTO 1\",\n                        \"row\": 9,\n                        \"cell\": \"P9\",\n                        \"mapping_strategy\": \"manual_condition_override\"\n                    }\n                }\n            ],\n            \"source_row\": 18,\n            \"required\": false,\n            \"excel\": {\n                \"sheet\": \"11 SCS MESIN BRACKET AUTO 1\",\n                \"row\": 9,\n                \"source_cell\": \"B18\",\n                \"cell\": \"P9\",\n                \"mapping_strategy\": \"manual_condition_override\"\n            },\n            \"frequency\": \"1X \\/SHIFT\",\n            \"input_type\": \"number\"\n        },\n        {\n            \"item_id\": \"CHK-002\",\n            \"builder_item_code\": \"ITEM-14-15-1773841505\",\n            \"no\": 2,\n            \"section\": \"START CHECK\",\n            \"label\": \"MEJA KERJA\",\n            \"standard\": \"BERSIH DARI KOTORAN\",\n            \"cara\": \"LIHAT & CHECK\",\n            \"conditions\": [\n                {\n                    \"standard\": \"BERSIH DARI KOTORAN\",\n                    \"cara\": \"LIHAT & CHECK\",\n                    \"frekuensi\": \"1X \\/SHIFT\",\n                    \"note\": \"\",\n                    \"excel\": {\n                        \"sheet\": \"11 SCS MESIN BRACKET AUTO 1\",\n                        \"row\": 12,\n                        \"cell\": \"P12\",\n                        \"mapping_strategy\": \"manual_condition_override\"\n                    }\n                }\n            ],\n            \"source_row\": 21,\n            \"required\": false,\n            \"excel\": {\n                \"sheet\": \"11 SCS MESIN BRACKET AUTO 1\",\n                \"row\": 12,\n                \"source_cell\": \"B21\",\n                \"cell\": \"P12\",\n                \"mapping_strategy\": \"manual_condition_override\"\n            },\n            \"frequency\": \"1X \\/SHIFT\",\n            \"input_type\": \"check\"\n        },\n        {\n            \"item_id\": \"CHK-003\",\n            \"builder_item_code\": \"ITEM-14-15-1773841543\",\n            \"no\": 3,\n            \"section\": \"START CHECK\",\n            \"label\": \"JIG BEARING A & B\",\n            \"standard\": \"BERSIH DARI KOTORAN | TIDAK AUS & TIDAK OBLAK\",\n            \"cara\": \"LIHAT & CHECK | LIHAAT\",\n            \"conditions\": [\n                {\n                    \"standard\": \"BERSIH DARI KOTORAN\",\n                    \"cara\": \"LIHAT & CHECK\",\n                    \"frekuensi\": \"1X \\/SHIFT\",\n                    \"note\": \"\",\n                    \"excel\": {\n                        \"sheet\": \"11 SCS MESIN BRACKET AUTO 1\",\n                        \"row\": 15,\n                        \"cell\": \"P15\",\n                        \"mapping_strategy\": \"manual_condition_override\"\n                    }\n                },\n                {\n                    \"standard\": \"TIDAK AUS & TIDAK OBLAK\",\n                    \"cara\": \"LIHAAT\",\n                    \"frekuensi\": \"1X 1 SHIFT\",\n                    \"note\": \"\",\n                    \"excel\": {\n                        \"sheet\": \"11 SCS MESIN BRACKET AUTO 1\",\n                        \"row\": 18,\n                        \"cell\": \"P18\",\n                        \"mapping_strategy\": \"manual_condition_override\"\n                    }\n                }\n            ],\n            \"source_row\": 24,\n            \"required\": false,\n            \"excel\": {\n                \"sheet\": \"11 SCS MESIN BRACKET AUTO 1\",\n                \"row\": 15,\n                \"source_cell\": \"B24\",\n                \"cell\": \"P15\",\n                \"mapping_strategy\": \"manual_condition_override\"\n            },\n            \"frequency\": \"1X \\/SHIFT | 1X 1 SHIFT\",\n            \"input_type\": \"check\"\n        },\n        {\n            \"item_id\": \"CHK-004\",\n            \"builder_item_code\": \"ITEM-14-15-1773841563\",\n            \"no\": 4,\n            \"section\": \"START CHECK\",\n            \"label\": \"\\\"MATA TORQUE PIN A & B\\\"\",\n            \"standard\": \"BERSIH DARI KOTORAN\",\n            \"cara\": \"LIHAT & CHECK\",\n            \"conditions\": [\n                {\n                    \"standard\": \"BERSIH DARI KOTORAN\",\n                    \"cara\": \"LIHAT & CHECK\",\n                    \"frekuensi\": \"1X \\/SHIFT\",\n                    \"note\": \"\",\n                    \"excel\": {\n                        \"sheet\": \"11 SCS MESIN BRACKET AUTO 1\",\n                        \"row\": 21,\n                        \"cell\": \"P21\",\n                        \"mapping_strategy\": \"manual_condition_override\"\n                    }\n                }\n            ],\n            \"source_row\": 30,\n            \"required\": false,\n            \"excel\": {\n                \"sheet\": \"11 SCS MESIN BRACKET AUTO 1\",\n                \"row\": 21,\n                \"source_cell\": \"B30\",\n                \"cell\": \"P21\",\n                \"mapping_strategy\": \"manual_condition_override\"\n            },\n            \"frequency\": \"1X \\/SHIFT\",\n            \"input_type\": \"check\"\n        },\n        {\n            \"item_id\": \"CHK-005\",\n            \"builder_item_code\": \"ITEM-14-15-1773841584\",\n            \"no\": 5,\n            \"section\": \"START CHECK\",\n            \"label\": \"TEMPAT LOCTITE\",\n            \"standard\": \"BERSIH DARI KOTORAN\",\n            \"cara\": \"LIHAT & CHECK\",\n            \"conditions\": [\n                {\n                    \"standard\": \"BERSIH DARI KOTORAN\",\n                    \"cara\": \"LIHAT & CHECK\",\n                    \"frekuensi\": \"1X \\/SHIFT\",\n                    \"note\": \"\",\n                    \"excel\": {\n                        \"sheet\": \"11 SCS MESIN BRACKET AUTO 1\",\n                        \"row\": 24,\n                        \"cell\": \"P24\",\n                        \"mapping_strategy\": \"manual_condition_override\"\n                    }\n                }\n            ],\n            \"source_row\": 33,\n            \"required\": false,\n            \"excel\": {\n                \"sheet\": \"11 SCS MESIN BRACKET AUTO 1\",\n                \"row\": 24,\n                \"source_cell\": \"B33\",\n                \"cell\": \"P24\",\n                \"mapping_strategy\": \"manual_condition_override\"\n            },\n            \"frequency\": \"1X \\/SHIFT\",\n            \"input_type\": \"check\"\n        },\n        {\n            \"item_id\": \"CHK-006\",\n            \"builder_item_code\": \"ITEM-14-15-1773841604\",\n            \"no\": 6,\n            \"section\": \"START CHECK\",\n            \"label\": \"CYLINDER JIG BEARING A & B\",\n            \"standard\": \"Pemastian body & seal bocor\",\n            \"cara\": \"LIHAT & BERSIHKAN\",\n            \"conditions\": [\n                {\n                    \"standard\": \"Pemastian body & seal bocor\",\n                    \"cara\": \"LIHAT & BERSIHKAN\",\n                    \"frekuensi\": \"1X \\/SHIFT\",\n                    \"note\": \"\",\n                    \"excel\": {\n                        \"sheet\": \"11 SCS MESIN BRACKET AUTO 1\",\n                        \"row\": 27,\n                        \"cell\": \"P27\",\n                        \"mapping_strategy\": \"manual_condition_override\"\n                    }\n                }\n            ],\n            \"source_row\": 36,\n            \"required\": false,\n            \"excel\": {\n                \"sheet\": \"11 SCS MESIN BRACKET AUTO 1\",\n                \"row\": 27,\n                \"source_cell\": \"B36\",\n                \"cell\": \"P27\",\n                \"mapping_strategy\": \"manual_condition_override\"\n            },\n            \"frequency\": \"1X \\/SHIFT\",\n            \"input_type\": \"check\"\n        },\n        {\n            \"item_id\": \"CHK-007\",\n            \"builder_item_code\": \"ITEM-14-15-1773841632\",\n            \"no\": 7,\n            \"section\": \"START CHECK\",\n            \"label\": \"SELANG DAN COUPLER\",\n            \"standard\": \"Pemastian body & seal bocor\",\n            \"cara\": \"LIHAT & CHECK\",\n            \"conditions\": [\n                {\n                    \"standard\": \"Pemastian body & seal bocor\",\n                    \"cara\": \"LIHAT & CHECK\",\n                    \"frekuensi\": \"1X \\/SHIFT\",\n                    \"note\": \"\",\n                    \"excel\": {\n                        \"sheet\": \"11 SCS MESIN BRACKET AUTO 1\",\n                        \"row\": 30,\n                        \"cell\": \"P30\",\n                        \"mapping_strategy\": \"manual_condition_override\"\n                    }\n                }\n            ],\n            \"source_row\": 39,\n            \"required\": false,\n            \"excel\": {\n                \"sheet\": \"11 SCS MESIN BRACKET AUTO 1\",\n                \"row\": 30,\n                \"source_cell\": \"B39\",\n                \"cell\": \"P30\",\n                \"mapping_strategy\": \"manual_condition_override\"\n            },\n            \"frequency\": \"1X \\/SHIFT\",\n            \"input_type\": \"check\"\n        },\n        {\n            \"item_id\": \"CHK-008\",\n            \"builder_item_code\": \"ITEM-14-15-1773841652\",\n            \"no\": 8,\n            \"section\": \"START CHECK\",\n            \"label\": \"BAWAH MEJA PRODUKSI\",\n            \"standard\": \"[0,5 ~ 0,6 Mpa]\",\n            \"cara\": \"LIHAT & CHECK\",\n            \"conditions\": [\n                {\n                    \"standard\": \"[0,5 ~ 0,6 Mpa]\",\n                    \"cara\": \"LIHAT & CHECK\",\n                    \"frekuensi\": \"1X \\/SHIFT\",\n                    \"note\": \"\",\n                    \"excel\": {\n                        \"sheet\": \"11 SCS MESIN BRACKET AUTO 1\",\n                        \"row\": 33,\n                        \"cell\": \"P33\",\n                        \"mapping_strategy\": \"manual_condition_override\"\n                    }\n                }\n            ],\n            \"source_row\": 42,\n            \"required\": false,\n            \"excel\": {\n                \"sheet\": \"11 SCS MESIN BRACKET AUTO 1\",\n                \"row\": 33,\n                \"source_cell\": \"B42\",\n                \"cell\": \"P33\",\n                \"mapping_strategy\": \"manual_condition_override\"\n            },\n            \"frequency\": \"1X \\/SHIFT\",\n            \"input_type\": \"check\"\n        },\n        {\n            \"item_id\": \"CHK-009\",\n            \"builder_item_code\": \"ITEM-14-15-1773841669\",\n            \"no\": 9,\n            \"section\": \"START CHECK\",\n            \"label\": \"KOLONG MEJA\",\n            \"standard\": \"Pemastian body & seal bocor\",\n            \"cara\": \"LIHAT & CHECK\",\n            \"conditions\": [\n                {\n                    \"standard\": \"Pemastian body & seal bocor\",\n                    \"cara\": \"LIHAT & CHECK\",\n                    \"frekuensi\": \"1X \\/SHIFT\",\n                    \"note\": \"\",\n                    \"excel\": {\n                        \"sheet\": \"11 SCS MESIN BRACKET AUTO 1\",\n                        \"row\": 44,\n                        \"cell\": \"P44\",\n                        \"mapping_strategy\": \"manual_condition_override\"\n                    }\n                }\n            ],\n            \"source_row\": 45,\n            \"required\": false,\n            \"excel\": {\n                \"sheet\": \"11 SCS MESIN BRACKET AUTO 1\",\n                \"row\": 44,\n                \"source_cell\": \"B45\",\n                \"cell\": \"P44\",\n                \"mapping_strategy\": \"manual_condition_override\"\n            },\n            \"frequency\": \"1X \\/SHIFT\",\n            \"input_type\": \"check\"\n        },\n        {\n            \"item_id\": \"CHK-010\",\n            \"builder_item_code\": \"ITEM-14-15-1773841688\",\n            \"no\": 10,\n            \"section\": \"START CHECK\",\n            \"label\": \"\\\"AREA TORQUE  NUT RUNNER\\\"\",\n            \"standard\": \"[0,5 ~ 0,6 Mpa]\",\n            \"cara\": \"Mesin dapat memastikan NG\",\n            \"conditions\": [\n                {\n                    \"standard\": \"[0,5 ~ 0,6 Mpa]\",\n                    \"cara\": \"Mesin dapat memastikan NG\",\n                    \"frekuensi\": \"1X \\/SHIFT\",\n                    \"note\": \"\",\n                    \"excel\": {\n                        \"sheet\": \"11 SCS MESIN BRACKET AUTO 1\",\n                        \"row\": 47,\n                        \"cell\": \"P47\",\n                        \"mapping_strategy\": \"manual_condition_override\"\n                    }\n                }\n            ],\n            \"source_row\": 48,\n            \"required\": false,\n            \"excel\": {\n                \"sheet\": \"11 SCS MESIN BRACKET AUTO 1\",\n                \"row\": 47,\n                \"source_cell\": \"B48\",\n                \"cell\": \"P47\",\n                \"mapping_strategy\": \"manual_condition_override\"\n            },\n            \"frequency\": \"1X \\/SHIFT\",\n            \"input_type\": \"check\"\n        }\n    ],\n    \"excel_structure\": {\n        \"sheets\": [\n            \"11 SCS MESIN BRACKET AUTO 1\"\n        ],\n        \"cell_ranges\": {\n            \"11 SCS MESIN BRACKET AUTO 1\": {\n                \"max_row\": 291,\n                \"max_col\": \"AW\"\n            }\n        },\n        \"detected_at\": \"2026-04-29 22:48:54\"\n    }\n}', 'uploads/templates/1777477733_BRACKET_AUTO_1_rev001.xlsx', NULL, 'active', 1, 1777477734, 1777480090);

-- --------------------------------------------------------

--
-- Struktur dari tabel `machine_template`
--

CREATE TABLE `machine_template` (
  `id` int NOT NULL,
  `no_mesin` varchar(64) NOT NULL,
  `template_id` int NOT NULL,
  `created_at` int NOT NULL,
  `updated_at` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `machine_template`
--

INSERT INTO `machine_template` (`id`, `no_mesin`, `template_id`, `created_at`, `updated_at`) VALUES
(7, 'BRACKET_AUTO-001-KRW-2025', 16, 1773841815, 1773851093);

-- --------------------------------------------------------

--
-- Struktur dari tabel `menu`
--

CREATE TABLE `menu` (
  `id` int NOT NULL,
  `name` varchar(128) NOT NULL,
  `parent` int DEFAULT NULL,
  `route` varchar(255) DEFAULT NULL,
  `order` int DEFAULT NULL,
  `data` blob
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Struktur dari tabel `migration`
--

CREATE TABLE `migration` (
  `version` varchar(180) NOT NULL,
  `apply_time` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `migration`
--

INSERT INTO `migration` (`version`, `apply_time`) VALUES
('m000000_000000_base', 1763736908),
('m140602_111327_create_menu_table', 1771082064),
('m160312_050000_create_user', 1771082064),
('m231128_000001_create_user_table', 1764245301),
('m241121_000001_create_form_template_table', 1763737190),
('m250214_000000_create_rbac_tables', 1771084577),
('m250214_000001_add_template_id_to_form_result', 1771085568),
('m260316_000002_add_unique_no_mesin_to_machine_template', 1773678461),
('m260407_000001_add_approval_fields_to_form_result', 1775564404);

-- --------------------------------------------------------

--
-- Struktur dari tabel `user`
--

CREATE TABLE `user` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `auth_key` varchar(255) DEFAULT NULL,
  `role` enum('operator','subforeman','foreman','chief','manager','admin') NOT NULL DEFAULT 'operator',
  `pin_hash` varchar(255) DEFAULT NULL,
  `require_pin` tinyint(1) DEFAULT '0',
  `shift_code` varchar(10) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data untuk tabel `user`
--

INSERT INTO `user` (`id`, `username`, `fullname`, `password_hash`, `auth_key`, `role`, `pin_hash`, `require_pin`, `shift_code`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Administrator', '$2y$13$HLJYgN/Aq0sBRnfsxyg2SerYUaK9pkWxtvlPFD/El7lTL8Bi4AkSy', 'K6KxJLRBzs5q83nlov0yEsOxrZdTuY5j', 'admin', NULL, 1, '', 1, '2025-11-27 19:09:49', '2026-01-17 17:02:49'),
(7, 'Sarno', 'sarno', '$2y$13$9W67SO8lxMRojCfo9OsOC.9QDoogOatQuGGfbuSrZYgY4wBbhJFNq', 'bk3p1TnyeLT9sc0Jksw-rrvC2fw6c5Rz', 'foreman', '$2y$10$6.9cxoavDzuHmjAPhy31oeOYkPIddylXbYA1jg3tXmfq5fdFgtxJm', 1, NULL, 1, '2026-02-14 23:34:41', '2026-04-07 20:57:42'),
(8, 'Heri Iswanto', 'Heri Iswanto', '$2y$13$eMOGF2qL9PGFWwzTFq3vNu/iB6xtIIeCRYr1F/acAi6p0.gIaxLUm', 'pqvNxIJkIKM87vSDkmPWEfxQv9a_CsRL', 'subforeman', '$2y$13$CoCZhDzmBT5y/UYCIiyMQ.FtPORW9bo3LVGWHJ2w/HrxH7uLD2Hqe', 1, NULL, 1, '2026-04-07 19:30:47', '2026-04-07 21:09:56'),
(9, 'Agus Supriyanto', 'Agus Supriyanto', '$2y$13$edOp9pp9Dk2lAdgIzRwoweBofOQ5dHyiyUOH.Mualyu0DlcjzYQ.2', 'ffuLp19sCPHEoDTsmocSTJZtBuEHHw1h', 'chief', '$2y$13$..99eEwwL.D0E9loZaPfp.zK3g90kwgpLqU8rwbTud6PsRSs/Mcou', 1, NULL, 1, '2026-04-07 19:39:32', '2026-04-07 21:10:20'),
(10, 'Anwar Fauzi', 'Anwar Fauzi', '$2y$13$cJSheH.WLFuhBGp54eyXD.0nu5S3XMp5BVs1rWuX5m9GW2/du6Twe', '-Sz6R7RlYfEyq1n7okmH722k78WflwYL', 'manager', '$2y$10$dhfp8mx1vTX8RIFZ6FFq5uJ5O81O4357i4QkMajAkXzzRkADCCOd2', 1, NULL, 1, '2026-04-07 19:42:03', '2026-04-07 21:17:42');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `api_client`
--
ALTER TABLE `api_client`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `auth_assignment`
--
ALTER TABLE `auth_assignment`
  ADD PRIMARY KEY (`item_name`,`user_id`),
  ADD KEY `fk_auth_assignment_user_id` (`user_id`);

--
-- Indeks untuk tabel `auth_item`
--
ALTER TABLE `auth_item`
  ADD PRIMARY KEY (`name`),
  ADD KEY `fk_auth_rule_item_name` (`rule_name`);

--
-- Indeks untuk tabel `auth_item_child`
--
ALTER TABLE `auth_item_child`
  ADD PRIMARY KEY (`parent`,`child`),
  ADD KEY `fk_auth_item_child_child` (`child`);

--
-- Indeks untuk tabel `auth_rule`
--
ALTER TABLE `auth_rule`
  ADD PRIMARY KEY (`name`);

--
-- Indeks untuk tabel `checksheet_answer`
--
ALTER TABLE `checksheet_answer`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_instance_item` (`instance_id`,`item_id`);

--
-- Indeks untuk tabel `checksheet_instance`
--
ALTER TABLE `checksheet_instance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_instance_template` (`template_id`),
  ADD KEY `idx_instance_mesin` (`mesin_id`);

--
-- Indeks untuk tabel `checksheet_item`
--
ALTER TABLE `checksheet_item`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_item_code` (`item_code`),
  ADD KEY `idx_item_template` (`template_id`),
  ADD KEY `idx_item_section` (`section_id`),
  ADD KEY `fk_item_symbol` (`symbol_id`);

--
-- Indeks untuk tabel `checksheet_result`
--
ALTER TABLE `checksheet_result`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_result_file_path` (`file_path`);

--
-- Indeks untuk tabel `checksheet_result_item`
--
ALTER TABLE `checksheet_result_item`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_result_item` (`result_id`,`item_id`),
  ADD KEY `result_id` (`result_id`);

--
-- Indeks untuk tabel `checksheet_section`
--
ALTER TABLE `checksheet_section`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_section_template` (`template_id`);

--
-- Indeks untuk tabel `checksheet_symbol`
--
ALTER TABLE `checksheet_symbol`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indeks untuk tabel `checksheet_template`
--
ALTER TABLE `checksheet_template`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `checksheet_template_map`
--
ALTER TABLE `checksheet_template_map`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_map` (`form_template_id`,`item_code`);

--
-- Indeks untuk tabel `daftar_mesin`
--
ALTER TABLE `daftar_mesin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `no_mesin` (`no_mesin`);

--
-- Indeks untuk tabel `form_result`
--
ALTER TABLE `form_result`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_form_result_template_id` (`template_id`),
  ADD KEY `fk_form_result_leader` (`leader_id`),
  ADD KEY `fk_form_result_supervisor` (`supervisor_id`),
  ADD KEY `fk_form_result_chief` (`chief_id`),
  ADD KEY `fk_form_result_manager` (`manager_id`);

--
-- Indeks untuk tabel `form_result_detail`
--
ALTER TABLE `form_result_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `form_result_id` (`form_result_id`);

--
-- Indeks untuk tabel `form_template`
--
ALTER TABLE `form_template`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_form_template_mesin` (`mesin_id`);

--
-- Indeks untuk tabel `machine_template`
--
ALTER TABLE `machine_template`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_machine_template_no_mesin` (`no_mesin`),
  ADD KEY `template_id` (`template_id`);

--
-- Indeks untuk tabel `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent` (`parent`);

--
-- Indeks untuk tabel `migration`
--
ALTER TABLE `migration`
  ADD PRIMARY KEY (`version`);

--
-- Indeks untuk tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx-user-username` (`username`),
  ADD KEY `idx-user-role` (`role`),
  ADD KEY `idx-user-status` (`status`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `api_client`
--
ALTER TABLE `api_client`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `checksheet_answer`
--
ALTER TABLE `checksheet_answer`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- AUTO_INCREMENT untuk tabel `checksheet_instance`
--
ALTER TABLE `checksheet_instance`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT untuk tabel `checksheet_item`
--
ALTER TABLE `checksheet_item`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT untuk tabel `checksheet_result`
--
ALTER TABLE `checksheet_result`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT untuk tabel `checksheet_result_item`
--
ALTER TABLE `checksheet_result_item`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=222;

--
-- AUTO_INCREMENT untuk tabel `checksheet_section`
--
ALTER TABLE `checksheet_section`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `checksheet_symbol`
--
ALTER TABLE `checksheet_symbol`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `checksheet_template`
--
ALTER TABLE `checksheet_template`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `checksheet_template_map`
--
ALTER TABLE `checksheet_template_map`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `daftar_mesin`
--
ALTER TABLE `daftar_mesin`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT untuk tabel `form_result`
--
ALTER TABLE `form_result`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT untuk tabel `form_result_detail`
--
ALTER TABLE `form_result_detail`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT untuk tabel `form_template`
--
ALTER TABLE `form_template`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT untuk tabel `machine_template`
--
ALTER TABLE `machine_template`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT untuk tabel `menu`
--
ALTER TABLE `menu`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `auth_assignment`
--
ALTER TABLE `auth_assignment`
  ADD CONSTRAINT `fk_auth_assignment_item_name` FOREIGN KEY (`item_name`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_auth_assignment_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `auth_item`
--
ALTER TABLE `auth_item`
  ADD CONSTRAINT `fk_auth_rule_item_name` FOREIGN KEY (`rule_name`) REFERENCES `auth_rule` (`name`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `auth_item_child`
--
ALTER TABLE `auth_item_child`
  ADD CONSTRAINT `fk_auth_item_child_child` FOREIGN KEY (`child`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_auth_item_child_parent` FOREIGN KEY (`parent`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `checksheet_answer`
--
ALTER TABLE `checksheet_answer`
  ADD CONSTRAINT `fk_answer_instance` FOREIGN KEY (`instance_id`) REFERENCES `checksheet_instance` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `checksheet_instance`
--
ALTER TABLE `checksheet_instance`
  ADD CONSTRAINT `fk_instance_mesin` FOREIGN KEY (`mesin_id`) REFERENCES `daftar_mesin` (`id`),
  ADD CONSTRAINT `fk_instance_template` FOREIGN KEY (`template_id`) REFERENCES `form_template` (`id`);

--
-- Ketidakleluasaan untuk tabel `checksheet_item`
--
ALTER TABLE `checksheet_item`
  ADD CONSTRAINT `fk_item_section` FOREIGN KEY (`section_id`) REFERENCES `checksheet_section` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_item_symbol` FOREIGN KEY (`symbol_id`) REFERENCES `checksheet_symbol` (`id`),
  ADD CONSTRAINT `fk_item_template` FOREIGN KEY (`template_id`) REFERENCES `checksheet_template` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `checksheet_result_item`
--
ALTER TABLE `checksheet_result_item`
  ADD CONSTRAINT `checksheet_result_item_ibfk_1` FOREIGN KEY (`result_id`) REFERENCES `checksheet_result` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `checksheet_section`
--
ALTER TABLE `checksheet_section`
  ADD CONSTRAINT `fk_section_template` FOREIGN KEY (`template_id`) REFERENCES `checksheet_template` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `checksheet_template_map`
--
ALTER TABLE `checksheet_template_map`
  ADD CONSTRAINT `checksheet_template_map_ibfk_1` FOREIGN KEY (`form_template_id`) REFERENCES `form_template` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `form_result`
--
ALTER TABLE `form_result`
  ADD CONSTRAINT `fk_form_result_chief` FOREIGN KEY (`chief_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_form_result_leader` FOREIGN KEY (`leader_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_form_result_manager` FOREIGN KEY (`manager_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_form_result_supervisor` FOREIGN KEY (`supervisor_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_form_result_template_id` FOREIGN KEY (`template_id`) REFERENCES `form_template` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `form_result_detail`
--
ALTER TABLE `form_result_detail`
  ADD CONSTRAINT `form_result_detail_ibfk_1` FOREIGN KEY (`form_result_id`) REFERENCES `form_result` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `form_template`
--
ALTER TABLE `form_template`
  ADD CONSTRAINT `fk_form_template_mesin` FOREIGN KEY (`mesin_id`) REFERENCES `daftar_mesin` (`id`) ON DELETE RESTRICT;

--
-- Ketidakleluasaan untuk tabel `machine_template`
--
ALTER TABLE `machine_template`
  ADD CONSTRAINT `machine_template_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `form_template` (`id`);

--
-- Ketidakleluasaan untuk tabel `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `menu` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
