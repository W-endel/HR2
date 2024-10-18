-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 14, 2024 at 09:29 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hr2`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_evaluations`
--

CREATE TABLE `admin_evaluations` (
  `id` int(10) UNSIGNED NOT NULL,
  `a_id` int(11) NOT NULL,
  `e_id` int(10) UNSIGNED NOT NULL,
  `department` varchar(255) NOT NULL,
  `quality` decimal(3,2) NOT NULL,
  `communication_skills` decimal(3,2) NOT NULL,
  `teamwork` decimal(3,2) NOT NULL,
  `punctuality` decimal(3,2) NOT NULL,
  `initiative` decimal(3,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_evaluations`
--

INSERT INTO `admin_evaluations` (`id`, `a_id`, `e_id`, `department`, `quality`, `communication_skills`, `teamwork`, `punctuality`, `initiative`) VALUES
(1, 1, 2, 'Finance Department', 3.67, 4.67, 3.67, 3.67, 4.33),
(2, 1, 3, 'Finance Department', 4.33, 4.00, 4.33, 4.00, 5.00),
(3, 1, 4, 'Finance Department', 3.33, 3.67, 3.00, 3.67, 4.67),
(4, 1, 5, 'Finance Department', 4.00, 5.00, 5.00, 4.67, 5.00),
(5, 1, 8, 'Finance Department', 4.67, 5.00, 5.00, 4.33, 5.00);

-- --------------------------------------------------------

--
-- Table structure for table `admin_recognition`
--

CREATE TABLE `admin_recognition` (
  `id` int(25) NOT NULL,
  `your_name` char(25) NOT NULL,
  `recipients_name` char(25) NOT NULL,
  `performance_area` char(25) NOT NULL,
  `recognition_message` char(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_register`
--

CREATE TABLE `admin_register` (
  `a_id` int(11) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `middlename` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `birthdate` date NOT NULL DEFAULT current_timestamp(),
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(100) NOT NULL,
  `position` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `available_leaves` int(11) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `pfp` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_register`
--

INSERT INTO `admin_register` (`a_id`, `firstname`, `middlename`, `lastname`, `birthdate`, `email`, `password`, `role`, `position`, `department`, `available_leaves`, `phone_number`, `address`, `pfp`) VALUES
(1, 'Wendel', '', 'Ureta', '2024-09-26', 'uretawendel@gmail.com', '$2y$10$38b57lPDe5wJIEpqTFRVMO8TA.sociVZL1W7TUm0wpLlh2JnWegIO', 'Admin', '', '', 25, '09123456789', 'Caloocan', ''),
(8, 'Thirdy', '', 'Murillo', '2024-10-13', 'rmmurillo2002@gmail.com', '$2y$10$tgJzAKfC6VBQ6v3I58b1weHy/H5EcWvO9oP5fin.5.fV4WXoQ4tDC', 'Admin', '', '', 0, '09123456789', 'Caloocan', ''),
(9, 'Steffano', '', 'Dizo', '2024-10-13', 'dsteffano011402@gmail.com', '$2y$10$0yyghPpJWHHNe0RA8Hd8QOiSeqQyqAhWG0BDh6oFDsD5o3sSZclTm', 'Admin', '', '', 0, '09123456789', 'Caloocan', '');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `e_id` int(11) NOT NULL,
  `e_name` varchar(255) NOT NULL,
  `e_role` varchar(255) NOT NULL,
  `e_time_in` datetime NOT NULL,
  `e_time_out` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_records`
--

CREATE TABLE `attendance_records` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(255) NOT NULL,
  `action` enum('Time In','Time Out') NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_register`
--

CREATE TABLE `employee_register` (
  `e_id` int(11) UNSIGNED NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `middlename` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `birthdate` date NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `available_leaves` int(11) DEFAULT 0,
  `phone_number` int(20) NOT NULL,
  `address` varchar(255) NOT NULL,
  `pfp` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_register`
--

INSERT INTO `employee_register` (`e_id`, `firstname`, `middlename`, `lastname`, `birthdate`, `email`, `password`, `role`, `position`, `department`, `available_leaves`, `phone_number`, `address`, `pfp`) VALUES
(1, 'Employee1', '', 'Test', '0000-00-00', 'Employee1@gmail.com', '$2y$10$opXjIX4IxwptSXOxVaB9du6d7glWiayBoji13qBOMAfVcqPFASjUG', 'Employee', 'IT Manager', 'IT Department', 17, 0, '', ''),
(2, 'Employee2', '', 'Test', '0000-00-00', 'Employee2@gmail.com', '$2y$10$Ax/otKl7YO3PZq4vII.hTuif3P8xV8DQgRTb3mJUPeKYWGPuhoXyC', 'Employee', 'Financial Educator', 'Finance Department', 12, 0, '', ''),
(3, 'Employee3', '', 'Test', '0000-00-00', 'Employee3@gmail.com', '$2y$10$ZKvHJ/tF77GHbwSmnYH6Ze9C7QulmH8xMGovezSv2adTK9QW61/s6', 'Employee', 'Loan Officer', 'Finance Department', 3, 0, '', ''),
(4, 'Employee4', '', 'Try', '0000-00-00', 'Employee4@gmail.com', '$2y$10$N9Br3iqD6E3NaIrDozIH6O09d2HoFDgH3jnp/MT/cb4FxLBG3gR1C', 'Employee', 'Loan Officer', 'Finance Department', 1, 0, '', ''),
(5, 'employee', '', 'hahaha', '0000-00-00', 'Employee0@gmail.com', '$2y$10$/BmjrS1Pqx/OxlnH0yhcjOW0QUT3eS0oWB6cHxMabtRV3cknhOFv2', 'Employee', 'Financial Educator', 'Finance Department', 20, 0, '', ''),
(6, 'Employeee', '', 'HR', '0000-00-00', 'uretawendel@gmail.com', '$2y$10$VygQ6W0nsRnuCrtLFsU6CeeN56g8efTp7lHWi96/HoCD3veht0eDe', 'Employee', 'Human Resources Manager', 'Human Resources Department', 0, 0, '', ''),
(7, 'Employee', '', 'OP', '0000-00-00', 'trylang@gmail.com', '$2y$10$MGE1G6NIqTTdI6UX1yAlhOV246LB3zX6xJnIDnxsSN17tjnewipR6', 'Employee', 'Operations Manager', 'Operations Department', 0, 0, '', ''),
(8, 'Thirdy', '', 'olete', '0000-00-00', 'thirdy@gmail.com', '$2y$10$72G.mEx7bvnz94b1M2xHNegYmb8e9ehFHJ8e3QU3bLrayEmdyoXqC', 'Employee', 'Field Officer', 'Finance Department', 0, 0, '', '');

-- --------------------------------------------------------

--
-- Table structure for table `leave_allocations`
--

CREATE TABLE `leave_allocations` (
  `crdt_id` int(11) NOT NULL,
  `role` varchar(255) NOT NULL,
  `leave_days` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_allocations`
--

INSERT INTO `leave_allocations` (`crdt_id`, `role`, `leave_days`) VALUES
(1, 'Admin', 25),
(2, 'Employee', 20),
(3, 'Admin', 25),
(4, 'Employee', 20);

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `leave_id` int(10) UNSIGNED NOT NULL,
  `e_id` int(10) UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `leave_type` enum('Annual Leave','Sick Leave','Family Leave') NOT NULL,
  `reason` text NOT NULL,
  `status` enum('Pending','Approved','Denied') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`leave_id`, `e_id`, `start_date`, `end_date`, `leave_type`, `reason`, `status`, `created_at`) VALUES
(1, 2, '2024-10-07', '2024-10-14', 'Annual Leave', 'vacation', 'Approved', '2024-10-05 11:17:45'),
(2, 3, '2024-10-07', '2024-10-12', 'Sick Leave', 'asd', 'Approved', '2024-10-05 12:50:18'),
(3, 3, '2024-11-01', '2024-11-06', 'Annual Leave', 'asd', 'Approved', '2024-10-05 13:21:42'),
(4, 3, '2024-12-23', '2024-12-30', 'Family Leave', 'try', 'Approved', '2024-10-05 14:18:29'),
(5, 4, '2024-11-01', '2024-11-05', 'Family Leave', 'try', 'Denied', '2024-10-05 14:32:14'),
(6, 4, '2024-11-01', '2024-11-05', 'Family Leave', 'try', 'Approved', '2024-10-05 14:32:54'),
(7, 4, '2024-10-16', '2024-10-23', 'Annual Leave', 'ASD', 'Approved', '2024-10-05 14:37:41'),
(8, 4, '2024-10-07', '2024-10-12', 'Sick Leave', 'ret', 'Approved', '2024-10-05 14:41:09'),
(9, 4, '2024-10-05', '2024-10-08', 'Sick Leave', 'sa', 'Approved', '2024-10-05 15:41:27'),
(10, 1, '2024-10-07', '2024-10-12', 'Family Leave', 'try', 'Denied', '2024-10-06 04:08:44'),
(11, 1, '2024-10-07', '2024-10-11', 'Annual Leave', 'try', 'Pending', '2024-10-06 14:20:12'),
(12, 1, '2024-10-22', '2024-10-31', 'Annual Leave', 'sick', 'Pending', '2024-10-07 20:36:07'),
(13, 1, '2024-10-14', '2024-10-16', 'Annual Leave', 'try', 'Approved', '2024-10-08 09:21:17'),
(14, 1, '2024-10-14', '2024-10-16', 'Family Leave', 'try', 'Pending', '2024-10-08 09:23:49'),
(15, 1, '2024-12-24', '2024-12-28', 'Family Leave', 'try', 'Pending', '2024-10-08 09:32:29'),
(16, 1, '2024-10-15', '2024-11-02', 'Family Leave', 'try', 'Pending', '2024-10-08 14:45:20');

-- --------------------------------------------------------

--
-- Table structure for table `non_working_days`
--

CREATE TABLE `non_working_days` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `description` varchar(255) NOT NULL,
  `type` enum('regular','irregular') DEFAULT 'irregular'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `non_working_days`
--

INSERT INTO `non_working_days` (`id`, `date`, `description`, `type`) VALUES
(1, '2019-01-01', 'New Year\'s Day', 'regular'),
(2, '2019-04-09', 'Araw ng Kagitingan', 'regular'),
(3, '2019-05-01', 'Labor Day', 'regular'),
(4, '2019-06-12', 'Independence Day', 'regular'),
(7, '2019-08-28', 'National Heroes Day', 'regular'),
(10, '2019-11-01', 'All Saint\'s Day', 'regular'),
(14, '2019-11-30', 'Bonifacio Day', 'regular'),
(20, '2019-12-25', 'Christmas Day', 'regular'),
(22, '2019-12-30', 'Rizal Day', 'regular'),
(26, '2020-01-01', 'New Year\'s Day', 'regular'),
(31, '2020-04-09', 'Araw ng Kagitingan', 'regular'),
(37, '2020-05-01', 'Labor Day', 'regular'),
(43, '2020-06-12', 'Independence Day', 'regular'),
(49, '2020-08-28', 'National Heroes Day', 'regular'),
(52, '2020-11-01', 'All Saint\'s Day', 'regular'),
(60, '2020-11-30', 'Bonifacio Day', 'regular'),
(62, '2020-12-25', 'Christmas Day', 'regular'),
(64, '2020-12-30', 'Rizal Day', 'regular'),
(72, '2021-01-01', 'New Year\'s Day', 'regular'),
(77, '2021-04-09', 'Araw ng Kagitingan', 'regular'),
(84, '2021-05-01', 'Labor Day', 'regular'),
(91, '2021-06-12', 'Independence Day', 'regular'),
(93, '2021-08-28', 'National Heroes Day', 'regular'),
(100, '2021-11-01', 'All Saint\'s Day', 'regular'),
(107, '2021-11-30', 'Bonifacio Day', 'regular'),
(110, '2021-12-25', 'Christmas Day', 'regular'),
(111, '2021-12-30', 'Rizal Day', 'regular'),
(117, '2022-01-01', 'New Year\'s Day', 'regular'),
(121, '2022-04-09', 'Araw ng Kagitingan', 'regular'),
(127, '2022-05-01', 'Labor Day', 'regular'),
(135, '2022-06-12', 'Independence Day', 'regular'),
(139, '2022-08-28', 'National Heroes Day', 'regular'),
(148, '2022-11-01', 'All Saint\'s Day', 'regular'),
(156, '2022-11-30', 'Bonifacio Day', 'regular'),
(157, '2022-12-25', 'Christmas Day', 'regular'),
(170, '2022-12-30', 'Rizal Day', 'regular'),
(176, '2023-01-01', 'New Year\'s Day', 'regular'),
(183, '2023-04-09', 'Araw ng Kagitingan', 'regular'),
(190, '2023-05-01', 'Labor Day', 'regular'),
(197, '2023-06-12', 'Independence Day', 'regular'),
(199, '2023-08-28', 'National Heroes Day', 'regular'),
(206, '2023-11-01', 'All Saint\'s Day', 'regular'),
(208, '2023-11-30', 'Bonifacio Day', 'regular'),
(215, '2023-12-25', 'Christmas Day', 'regular'),
(222, '2023-12-30', 'Rizal Day', 'regular'),
(229, '2024-01-01', 'New Year\'s Day', 'regular'),
(236, '2024-04-09', 'Araw ng Kagitingan', 'regular'),
(245, '2024-05-01', 'Labor Day', 'regular'),
(249, '2024-06-12', 'Independence Day', 'regular'),
(254, '2024-08-28', 'National Heroes Day', 'regular'),
(260, '2024-11-01', 'All Saint\'s Day', 'regular'),
(268, '2024-11-30', 'Bonifacio Day', 'regular'),
(274, '2024-12-25', 'Christmas Day', 'regular'),
(282, '2024-12-30', 'Rizal Day', 'regular'),
(289, '2025-01-01', 'New Year\'s Day', 'regular'),
(295, '2025-04-09', 'Araw ng Kagitingan', 'regular'),
(303, '2025-05-01', 'Labor Day', 'regular'),
(309, '2025-06-12', 'Independence Day', 'regular'),
(317, '2025-08-28', 'National Heroes Day', 'regular'),
(324, '2025-11-01', 'All Saint\'s Day', 'regular'),
(325, '2025-11-30', 'Bonifacio Day', 'regular'),
(334, '2025-12-25', 'Christmas Day', 'regular'),
(337, '2025-12-30', 'Rizal Day', 'regular'),
(338, '2026-01-01', 'New Year\'s Day', 'regular'),
(339, '2026-04-09', 'Araw ng Kagitingan', 'regular'),
(340, '2026-05-01', 'Labor Day', 'regular'),
(347, '2026-06-12', 'Independence Day', 'regular'),
(353, '2026-08-28', 'National Heroes Day', 'regular'),
(355, '2026-11-01', 'All Saint\'s Day', 'regular'),
(359, '2026-11-30', 'Bonifacio Day', 'regular'),
(366, '2026-12-25', 'Christmas Day', 'regular'),
(372, '2026-12-30', 'Rizal Day', 'regular'),
(379, '2027-01-01', 'New Year\'s Day', 'regular'),
(386, '2027-04-09', 'Araw ng Kagitingan', 'regular'),
(392, '2027-05-01', 'Labor Day', 'regular'),
(401, '2027-06-12', 'Independence Day', 'regular'),
(408, '2027-08-28', 'National Heroes Day', 'regular'),
(411, '2027-11-01', 'All Saint\'s Day', 'regular'),
(418, '2027-11-30', 'Bonifacio Day', 'regular'),
(425, '2027-12-25', 'Christmas Day', 'regular'),
(433, '2027-12-30', 'Rizal Day', 'regular'),
(438, '2028-01-01', 'New Year\'s Day', 'regular'),
(444, '2028-04-09', 'Araw ng Kagitingan', 'regular'),
(453, '2028-05-01', 'Labor Day', 'regular'),
(460, '2028-06-12', 'Independence Day', 'regular'),
(462, '2028-08-28', 'National Heroes Day', 'regular'),
(463, '2028-11-01', 'All Saint\'s Day', 'regular'),
(470, '2028-11-30', 'Bonifacio Day', 'regular'),
(478, '2028-12-25', 'Christmas Day', 'regular'),
(486, '2028-12-30', 'Rizal Day', 'regular'),
(493, '2029-01-01', 'New Year\'s Day', 'regular'),
(500, '2029-04-09', 'Araw ng Kagitingan', 'regular'),
(507, '2029-05-01', 'Labor Day', 'regular'),
(514, '2029-06-12', 'Independence Day', 'regular'),
(518, '2029-08-28', 'National Heroes Day', 'regular'),
(525, '2029-11-01', 'All Saint\'s Day', 'regular'),
(532, '2029-11-30', 'Bonifacio Day', 'regular'),
(534, '2029-12-25', 'Christmas Day', 'regular'),
(541, '2029-12-30', 'Rizal Day', 'regular'),
(1387, '2024-10-15', 'Birthday ni Thirdy', 'irregular');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `created_at`, `expires_at`) VALUES
(1, 'uretawendel@gmail.com', 'bd26e2cc098d851366cf7d142875b0d4d1290cfcfad4724ea4e3a8b7bce70d5d', '2024-10-12 16:49:05', '2024-10-12 11:49:05'),
(2, 'uretawendel@gmail.com', 'deed79d4d829a924303eb357b6e680338644648a1e98a4843b9c97e7ef9dd545', '2024-10-12 16:58:57', '2024-10-12 11:58:57'),
(3, 'uretawendel@gmail.com', '54836584a9621561935cc670d8fc04b22d82b73023f3d73fba7f524b83c6e906', '2024-10-12 17:02:12', '2024-10-12 09:02:12'),
(4, 'uretawendel@gmail.com', 'a567a1515ab356e2a2c1b6c03b07ebfe551d30d9442e6bb1646cf52ef36e4d02', '2024-10-12 17:17:21', '2024-10-12 16:17:21'),
(5, 'uretawendel@gmail.com', '430e62aadd97a61ccab930f2cb1175cd1c4b7e9c76e855ddc3159b1598f1ebd3', '2024-10-12 17:18:01', '2024-10-12 16:18:01'),
(8, 'uretawendel@gmail.com', '520445c7288a5837f575f55c4de9ecaa533baeabb759f3713c38127b05aa2e38', '2024-10-13 02:14:07', '2024-10-12 21:14:07'),
(10, 'uretawendel@gmail.com', 'c37054031c535957ad3aecc87e49ffdbef7c11c93b599f6e11f58c45a16199a1', '2024-10-13 02:20:02', '2024-10-13 03:20:02'),
(11, 'uretawendel@gmail.com', '5156ccb83c5756d5f50e5252c8d97f3fff9840f7de9444004128cf5f48fbb1c3', '2024-10-13 02:20:25', '2024-10-13 03:20:25'),
(13, 'uretawendel@gmail.com', 'b5fb45f7f9102a809a4797847c364c9088e29dd4f94bd643dca63801fce63396', '2024-10-13 02:21:55', '2024-10-13 03:21:55'),
(14, 'uretawendel@gmail.com', '807c92ac850f09aaf164253ee2d73527c756aaf4c1b5bcba5725b81fab3d2bca', '2024-10-13 02:31:38', '2024-10-13 02:41:38'),
(15, 'uretawendel@gmail.com', 'afc3e7c7029a53f305f9a17cfe0e7c07315c67617fa9b673ed8203b9af9451e0', '2024-10-13 02:34:52', '2024-10-13 02:39:52'),
(16, 'uretawendel@gmail.com', 'c9feb8e7991b8ebf26e865686bd8503d81c323c2abb9e00666f0874870147ec6', '2024-10-13 02:38:54', '2024-10-13 02:43:54'),
(17, 'rmmurillo2002@gmail.com', 'a22779d77e7f256f995eca529070217f8234616626154d741d65aad1abad3523', '2024-10-13 04:01:35', '2024-10-13 04:06:35'),
(18, 'dsteffano011402@gmail.com', '27531128ed63b9324f5b2839c5342fbb531e4c91a858a7dee8b8b4dec5c8ac10', '2024-10-13 04:11:30', '2024-10-13 04:16:30'),
(19, 'uretawendel@gmail.com', 'ee1d6107442d37053d6ebd51e12830fcfc7bfe14c7ceca4902160d0614cd218f', '2024-10-13 04:28:30', '2024-10-13 04:33:30'),
(20, 'uretawendel@gmail.com', '1e7f73fbff804b7f379d4cf2188567c80d1029bc5f33ced25be5eb23a53a5cac', '2024-10-13 04:30:49', '2024-10-13 04:35:49'),
(21, 'rmmurillo2002@gmail.com', '08266f64b12665d5e81a1159dbd20053b3fe9a1ea40e017dd9eed6483e618d99', '2024-10-13 04:39:37', '2024-10-13 04:44:37'),
(22, 'uretawendel@gmail.com', '180e7a464cd815e652abdc46fe94faea92b84da8b6b72e32b3e39e91d17d66d6', '2024-10-13 04:43:47', '2024-10-13 04:48:47'),
(23, 'uretawendel@gmail.com', '5ce61447154ef8239a8cb7b9d46e6a3ed71129a596b92836847fa6573e4e7cea', '2024-10-13 04:43:59', '2024-10-13 04:48:59'),
(24, 'rmmurillo2002@gmail.com', '9e290ff6aae11d0cf753496cd118382f9c320e6605b0706d0f1c2172be5612f4', '2024-10-13 04:44:34', '2024-10-13 04:49:34'),
(25, 'uretawendel@gmail.com', '5b9a86e7e2e6c47ba01901167918be0d13539719d129f01d7d04ab7b35a311d8', '2024-10-13 05:06:09', '2024-10-13 05:11:09'),
(26, 'uretawendel@gmail.com', 'a7557c62ca6958210efc3d4151c0cc52694f43f402d30d5841481f334808778c', '2024-10-13 05:18:03', '2024-10-13 05:23:03'),
(27, 'uretawendel@gmail.com', '3c1097d5e51028128d6b56e9d0285414dfde88cfe0da28b395bcf49d27cf283a', '2024-10-13 05:21:51', '2024-10-13 05:26:51'),
(28, 'uretawendel@gmail.com', '91baa25ddbfeb3fa88494ada1d059d7eee1b749a83433a731e513ac5dbd6ad88', '2024-10-13 05:22:50', '2024-10-13 05:27:50'),
(29, 'uretawendel@gmail.com', 'e975e7132355549fdb8ed0af2064edcda1f38364a277befe8ddc5474605f61e5', '2024-10-13 05:27:02', '2024-10-13 05:32:02'),
(30, 'uretawendel@gmail.com', 'd793d17c118f8bf884f7da6273e2c3450bec268b17d88c9fd6e8a3a3c16e9873', '2024-10-13 06:04:14', '2024-10-13 06:09:14'),
(31, 'uretawendel@gmail.com', '372751300655108ae27fe2395388d95f78be0f1ef9fc5a3759d72ad803521055', '2024-10-13 07:08:55', '2024-10-13 07:13:55'),
(32, 'uretawendel@gmail.com', '129138cd81e10a87dcffe1841728515fc550290c4303e5983ce7444bdb1a86b9', '2024-10-13 07:11:17', '2024-10-13 07:16:17'),
(33, 'uretawendel@gmail.com', '24cc2f77f52a300702c25d6a2047c21c8c48fa56b0849fc47e7616b0a1c0ca05', '2024-10-13 08:09:21', '2024-10-13 08:14:21'),
(34, 'uretawendel@gmail.com', '9e68fa55ff64eb8954084af14bc165fc31162b5b8ca6f0152f606fc13c7e9199', '2024-10-13 08:09:45', '2024-10-13 08:14:45'),
(35, 'uretawendel@gmail.com', 'fbd22ca8418cb9166b46f0228dc1d334d9c256510e36e6ff180bc5c5b926cff1', '2024-10-13 16:10:57', '2024-10-13 16:15:57'),
(36, 'uretawendel@gmail.com', 'e3d41ea2c6b1380858a39efd0fba59ff61e3c482f24e262e6b09aa9c09f3b9d9', '2024-10-13 16:11:28', '2024-10-13 16:16:28'),
(37, 'uretawendel@gmail.com', '40fdf60c62372aa0546b9b547c968d3ea529713c553e56595f3f148a49b992f4', '2024-10-13 16:21:41', '2024-10-13 16:26:41'),
(38, 'uretawendel@gmail.com', '22a624155f35ed6e66c838262805158954f21e196b8f75521184c3c614a08dc0', '2024-10-13 16:27:30', '2024-10-13 16:32:30'),
(39, 'uretawendel@gmail.com', '93aba11ddc9ca00b8a3388e2b5393b81c96fc718ac1887c378af8b0c324b08ef', '2024-10-13 16:43:03', '2024-10-13 16:48:03'),
(40, 'uretawendel@gmail.com', 'a98bb65ff22617a018e3db0de6d1ac97dbd8fbb26b33c152effccb3b2f1cc239', '2024-10-14 13:16:27', '2024-10-14 13:21:27'),
(41, 'uretawendel@gmail.com', 'b551cd842869cdeb76e95b418db626c003507982028f1e1957fa5ddb97da4e21', '2024-10-14 13:23:26', '2024-10-14 13:28:26'),
(42, 'uretawendel@gmail.com', 'e0d27204381ce824a9b4bb9663ae044a954d4585519595ab1f850b89badb7d8a', '2024-10-14 13:39:21', '2024-10-14 13:44:21'),
(43, 'uretawendel@gmail.com', 'b2f878b32d510bdfed2b36959a2106c4189ff2be3a2a71d5b7290a27359fca3e', '2024-10-14 13:41:06', '2024-10-14 13:42:06'),
(44, 'uretawendel@gmail.com', 'e3dd39bf5d7da5a74cf32712f5244b5472025129f236d780967d910c194bf8c3', '2024-10-14 13:43:10', '2024-10-14 13:44:10'),
(45, 'uretawendel@gmail.com', '883c0f50ba449a4b0f11284757d6886c48af3b102dc36a9620bda3969323a64a', '2024-10-14 13:56:16', '2024-10-14 13:57:16'),
(46, 'uretawendel@gmail.com', '874130e8f7e795d00c38561d236a96552bb4c0520f72d11b8c69a034a8340533', '2024-10-14 14:00:01', '2024-10-14 14:01:01'),
(47, 'uretawendel@gmail.com', '506518d92239d1fb5b2b0dec5c59b545bd79de885662f0f9240396e6c60e4ba3', '2024-10-14 14:03:35', '2024-10-14 14:04:35'),
(48, 'uretawendel@gmail.com', 'ef0d25ccb1ea171ea7845f6cd376361e9b2b3015244c5ae582ae053d0d2b8cea', '2024-10-14 14:06:13', '2024-10-14 14:07:13'),
(49, 'uretawendel@gmail.com', '7498dcdc38459b93d5f5fded260eb6c2870e5bf7ee7dbfe7664b55fb454dda65', '2024-10-14 14:06:18', '2024-10-14 14:07:18'),
(50, 'uretawendel@gmail.com', '79e7f79c3ef166d8db0912ffc829015d7553aca2ea62443626ae4b7c47d328ae', '2024-10-14 14:14:10', '2024-10-14 14:15:10'),
(51, 'uretawendel@gmail.com', '5ef7eb7aba0b00dd5dfc04e51b6c5c03f7d39e9b36a1339bfea79fed09022d67', '2024-10-14 14:17:38', '2024-10-14 14:17:58'),
(52, 'uretawendel@gmail.com', 'fdbf4f7a2860064ca23d6dc3f370dc1c23795b65c4dc770beb99b4143af1558a', '2024-10-14 14:18:11', '2024-10-14 14:18:31'),
(53, 'uretawendel@gmail.com', '689583f6b0596f78d54acb6686a7e3141f9a4861ff7a42cf701e126405460c33', '2024-10-14 14:18:49', '2024-10-14 14:19:19'),
(54, 'uretawendel@gmail.com', '474344936e4b04f7e5a5b3cc36322abad77aa1469e471e65ba1f56621aded526', '2024-10-14 14:23:00', '2024-10-14 14:23:30'),
(55, 'uretawendel@gmail.com', 'affd50e007a09d6b8c1e5b0196de0277c38253fbc566add1fb16c6ffa8db53b8', '2024-10-14 14:31:51', '2024-10-14 14:32:21'),
(56, 'uretawendel@gmail.com', 'bdaeb1152b1e7a67e320919f8a2c9d11659f30377bd6f372b454b5d59a9a3bde', '2024-10-14 14:33:38', '2024-10-14 14:34:08'),
(57, 'uretawendel@gmail.com', 'df4ac226829c31ddf27c0d9c7ea348ecdc750110b299b657273eda7fd3fe5697', '2024-10-14 14:50:34', '2024-10-14 14:51:04'),
(58, 'uretawendel@gmail.com', '5c1119b519fb7ff403fffa3e4b66c47e56f56988d4469f56e560f99bfa450c83', '2024-10-14 14:50:36', '2024-10-14 14:51:06'),
(59, 'uretawendel@gmail.com', '51fa880b2423fc0bd38ee5769d277e791d7a3af5effbab1370a653b858c19786', '2024-10-14 15:04:06', '2024-10-14 15:04:36'),
(60, 'uretawendel@gmail.com', '892d9f95a3ce9ad9f9a1c2b45f2c4e7363aeaf55919f70fe44b7521348847041', '2024-10-14 15:04:10', '2024-10-14 15:04:40'),
(61, 'uretawendel@gmail.com', 'df9be61bbece2fd5336f03a5b1969fc81deef675a92ffd98734581148238968f', '2024-10-14 15:04:26', '2024-10-14 15:04:56'),
(62, 'uretawendel@gmail.com', '2b949a5f2fafbc75c726d9059b672895b64baa8304a1a921b50f2be64b3fbcbd', '2024-10-14 15:04:58', '2024-10-14 15:05:28'),
(63, 'uretawendel@gmail.com', 'ad2d0012cdcc68cbc712da1e089cffa3685e1fa112386d20b7f00926f00a86bf', '2024-10-14 15:05:53', '2024-10-14 15:06:23'),
(64, 'uretawendel@gmail.com', '618f3b7361427559e09fa72d0d07b3f8fa8555c90d684b17d81561e46e872288', '2024-10-14 15:05:53', '2024-10-14 15:06:23'),
(65, 'uretawendel@gmail.com', '88c1c787de7071622b7a5f35c99e71aca7d3b8f97bb2d331f26ef2dd45586cf9', '2024-10-14 15:12:13', '2024-10-14 15:12:43'),
(66, 'uretawendel@gmail.com', 'c4ee29cd4a9caa9e329de42d60b6138242787b544d291a98acc76e061abcf7e4', '2024-10-14 15:18:50', '2024-10-14 15:19:20'),
(67, 'uretawendel@gmail.com', '6936ed4a47394cb7ce01df394afa93f0c9be3b512877fc1250aef912c32fbe7d', '2024-10-14 15:24:09', '2024-10-14 15:24:39'),
(68, 'uretawendel@gmail.com', 'eac501d960c9e17f664156527136e6096688ae2bca9e6cebe0b0a5f52f375a64', '2024-10-14 15:31:16', '2024-10-14 15:31:46'),
(69, 'uretawendel@gmail.com', 'a17208e1f274a76d08314b74d49f8d2a2b8f91e32541798ed084e1b1d4422191', '2024-10-14 15:32:35', '2024-10-14 15:35:35'),
(70, 'uretawendel@gmail.com', '473b9973edcb5a0783afa12602596d2cc28d740c69c184398065b16269ba4ff9', '2024-10-14 15:38:42', '2024-10-14 15:41:42'),
(71, 'uretawendel@gmail.com', 'a4baa93e7913b8db73907302b112456723b657ec69d098b7a06b8b182cc2ec90', '2024-10-14 15:40:02', '2024-10-14 15:43:02'),
(72, 'uretawendel@gmail.com', '2652b974ec032b986637f7a3218ff3907522454b81d556e6224c31c064d78c79', '2024-10-14 15:47:29', '2024-10-14 15:50:29'),
(73, 'uretawendel@gmail.com', '5b5231178994fb566f87f65f4c278e5e265d417b39dad35060a3153396852219', '2024-10-14 15:52:49', '2024-10-14 15:55:49'),
(74, 'uretawendel@gmail.com', '17870c8ea22c6dfa33eba487dd8ea8cc24aa5f9e64c96317d99f7a18886d8495', '2024-10-14 18:08:52', '2024-10-14 18:11:52'),
(75, 'uretawendel@gmail.com', '6254b31b012c125c3aef48db4b168d69b9fbd7ffa3026e37b009cd321bcc3edd', '2024-10-14 18:11:37', '2024-10-14 19:11:37'),
(76, 'uretawendel@gmail.com', '1bca056d6737746f4a3d5d27429b1a5c4441b60cb7a7f076b9643cbaed950aab', '2024-10-14 18:29:23', '2024-10-14 19:29:23'),
(77, 'uretawendel@gmail.com', 'd92640c818927fbef7330a20faec7e3adba9b713a43ecff4c6878f99ae95eb5f', '2024-10-14 18:32:07', '2024-10-14 18:37:07'),
(78, 'uretawendel@gmail.com', '5c8a94aa30c1abdb5b804b59e2e27410641eb1f730012d9c823b11d9cf9cc051', '2024-10-14 18:33:32', '2024-10-14 18:38:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_evaluations`
--
ALTER TABLE `admin_evaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `e_id` (`e_id`);

--
-- Indexes for table `admin_recognition`
--
ALTER TABLE `admin_recognition`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_register`
--
ALTER TABLE `admin_register`
  ADD PRIMARY KEY (`a_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employee_register`
--
ALTER TABLE `employee_register`
  ADD PRIMARY KEY (`e_id`);

--
-- Indexes for table `leave_allocations`
--
ALTER TABLE `leave_allocations`
  ADD PRIMARY KEY (`crdt_id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`leave_id`),
  ADD KEY `e_id` (`e_id`);

--
-- Indexes for table `non_working_days`
--
ALTER TABLE `non_working_days`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `date` (`date`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_evaluations`
--
ALTER TABLE `admin_evaluations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `admin_recognition`
--
ALTER TABLE `admin_recognition`
  MODIFY `id` int(25) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_register`
--
ALTER TABLE `admin_register`
  MODIFY `a_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance_records`
--
ALTER TABLE `attendance_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_register`
--
ALTER TABLE `employee_register`
  MODIFY `e_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `leave_allocations`
--
ALTER TABLE `leave_allocations`
  MODIFY `crdt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `leave_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `non_working_days`
--
ALTER TABLE `non_working_days`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3271;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_evaluations`
--
ALTER TABLE `admin_evaluations`
  ADD CONSTRAINT `admin_evaluations_ibfk_1` FOREIGN KEY (`e_id`) REFERENCES `employee_register` (`e_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`e_id`) REFERENCES `employee_register` (`e_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
