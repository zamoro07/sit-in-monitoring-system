-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2025 at 12:46 PM
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
-- Database: `sit-in`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `ADMIN_ID` int(11) NOT NULL,
  `USER_NAME` varchar(30) NOT NULL DEFAULT 'admin',
  `PASSWORD_HASH` varchar(30) NOT NULL DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`ADMIN_ID`, `USER_NAME`, `PASSWORD_HASH`) VALUES
(1, 'admin', '$2y$10$cJ/qQrJwN7BGzUHccJB5ruy');

-- --------------------------------------------------------

--
-- Table structure for table `announcement`
--

CREATE TABLE `announcement` (
  `ID` int(11) NOT NULL,
  `TITLE` varchar(255) NOT NULL,
  `CONTENT` text NOT NULL,
  `CREATED_DATE` date NOT NULL,
  `CREATED_BY` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcement`
--

INSERT INTO `announcement` (`ID`, `TITLE`, `CONTENT`, `CREATED_DATE`, `CREATED_BY`) VALUES
(5, '', 'GM', '2025-05-08', 'ADMIN'),
(6, '', 'gm', '2025-05-08', 'ADMIN'),
(7, '', 'gm', '2025-05-13', 'ADMIN'),
(8, '', 'test\r\n', '2025-05-13', 'ADMIN'),
(9, '', 'test again\r\n', '2025-05-14', 'ADMIN');

-- --------------------------------------------------------

--
-- Table structure for table `computer`
--

CREATE TABLE `computer` (
  `ID` int(11) NOT NULL,
  `LABORATORY` varchar(30) NOT NULL,
  `PC_NUM` int(11) NOT NULL,
  `STATUS` varchar(10) NOT NULL DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `computer`
--

INSERT INTO `computer` (`ID`, `LABORATORY`, `PC_NUM`, `STATUS`) VALUES
(22, 'lab517', 11, 'available'),
(23, 'lab530', 1, 'available'),
(24, 'lab528', 1, 'available'),
(25, 'lab517', 1, 'available'),
(26, 'lab517', 2, 'available'),
(27, 'lab524', 1, 'available'),
(28, 'lab526', 1, 'available'),
(29, 'lab530', 1, 'available'),
(30, 'lab524', 1, 'available'),
(31, 'lab544', 1, 'available'),
(32, 'lab528', 1, 'available'),
(33, 'lab517', 1, 'available'),
(34, 'lab524', 1, 'available'),
(35, 'lab524', 1, 'used'),
(36, 'lab524', 1, 'available');

-- --------------------------------------------------------

--
-- Table structure for table `curr_sitin`
--

CREATE TABLE `curr_sitin` (
  `SITIN_ID` int(11) NOT NULL,
  `IDNO` int(11) NOT NULL,
  `FULL_NAME` varchar(30) NOT NULL,
  `PURPOSE` enum('C Programming','C++ Programming','C# Programming','Java Programming','Php Programming','Python Programming','Database','Digital Logic & Design','Embedded System & IOT','System Integration & Architecture','Computer Application','Web Design & Development','Project Management') NOT NULL,
  `LABORATORY` enum('Lab 517','Lab 524','Lab 526','Lab 528','Lab 530','Lab 542','Lab 544') NOT NULL,
  `TIME_IN` time NOT NULL,
  `TIME_OUT` time DEFAULT NULL,
  `DATE` date NOT NULL,
  `STATUS` varchar(10) NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `curr_sitin`
--

INSERT INTO `curr_sitin` (`SITIN_ID`, `IDNO`, `FULL_NAME`, `PURPOSE`, `LABORATORY`, `TIME_IN`, `TIME_OUT`, `DATE`, `STATUS`) VALUES
(10, 1000, 'Jana Zamoro', 'Web Design & Development', 'Lab 517', '04:21:00', '04:32:03', '2025-05-08', 'Completed'),
(11, 2000, 'hamela sala', 'Embedded System & IOT', 'Lab 544', '04:31:25', '04:58:57', '2025-05-08', 'Completed'),
(12, 7000, 'erica  juarez', 'Java Programming', 'Lab 526', '04:41:03', '05:29:00', '2025-05-08', 'Completed'),
(13, 2000, 'hamela sala', 'C# Programming', 'Lab 524', '05:28:51', '05:46:08', '2025-05-08', 'Completed'),
(14, 1000, 'Jana Zamoro', 'Database', 'Lab 530', '05:46:31', '06:35:58', '2025-05-08', 'Completed'),
(15, 2000, 'Hamela Sala', 'Digital Logic & Design', 'Lab 530', '13:13:21', '13:22:27', '2025-05-13', 'Completed'),
(16, 1000, 'Jana Zamoro', 'C# Programming', 'Lab 530', '13:14:00', '13:22:24', '2025-05-13', 'Completed'),
(17, 7000, 'erica  juarez', 'Java Programming', 'Lab 528', '14:26:00', '15:00:46', '2025-05-13', 'Completed'),
(18, 2000, 'Hamela Sala', 'Project Management', 'Lab 544', '16:34:57', '16:35:40', '2025-05-13', 'Completed'),
(19, 7000, 'erica  juarez', 'Project Management', 'Lab 517', '17:39:00', '17:47:18', '2025-05-13', 'Completed'),
(20, 1000, 'Jana Zamoro', 'Project Management', 'Lab 517', '17:41:00', '17:47:18', '2025-05-13', 'Completed'),
(21, 1000, 'Jana Zamoro', 'Web Design & Development', 'Lab 524', '17:45:00', '17:47:20', '2025-05-13', 'Completed'),
(22, 1000, 'Jana Zamoro', 'Database', 'Lab 526', '17:47:00', '17:59:45', '2025-05-13', 'Completed'),
(23, 1000, 'Jana Zamoro', 'Computer Application', 'Lab 530', '18:11:00', '18:23:38', '2025-05-13', 'Completed'),
(24, 2000, 'hamela sala', 'Project Management', 'Lab 524', '19:37:00', '20:40:21', '2025-05-13', 'Completed'),
(25, 2000, 'hamela sala', 'C Programming', 'Lab 544', '22:12:00', '17:57:00', '2025-05-13', 'Completed'),
(26, 1000, 'Jana Zamoro', 'C++ Programming', 'Lab 528', '00:00:00', '18:07:41', '2025-05-14', 'Completed'),
(27, 7000, 'erica  juarez', 'Project Management', 'Lab 517', '17:45:00', '18:07:38', '2025-05-14', 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `FEEDBACK_ID` int(11) NOT NULL,
  `IDNO` int(11) NOT NULL,
  `LABORATORY` enum('Lab 517','Lab 524','Lab 526','Lab 528','Lab 530','Lab 542','Lab 544') NOT NULL,
  `DATE` date NOT NULL,
  `FEEDBACK` varchar(255) NOT NULL,
  `RATING` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`FEEDBACK_ID`, `IDNO`, `LABORATORY`, `DATE`, `FEEDBACK`, `RATING`) VALUES
(1, 2000, 'Lab 524', '2025-05-13', 'nice', 5),
(3, 7000, 'Lab 517', '2025-05-14', 'niceeeee', 5),
(4, 1000, 'Lab 528', '2025-05-14', 'eyyyy', 5);

-- --------------------------------------------------------

--
-- Table structure for table `lab_schedule`
--

CREATE TABLE `lab_schedule` (
  `SCHED_ID` int(11) NOT NULL,
  `DAY` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `LABORATORY` enum('Lab 517','Lab 524','Lab 526','Lab 528','Lab 530','Lab 542','Lab 544') NOT NULL,
  `TIME_START` time NOT NULL,
  `TIME_END` time NOT NULL,
  `SUBJECT` varchar(255) NOT NULL,
  `PROFESSOR` varchar(50) NOT NULL,
  `CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_schedule`
--

INSERT INTO `lab_schedule` (`SCHED_ID`, `DAY`, `LABORATORY`, `TIME_START`, `TIME_END`, `SUBJECT`, `PROFESSOR`, `CREATED_AT`) VALUES
(1, 'Wednesday', 'Lab 526', '16:30:00', '18:30:00', 'intprog', 'Sir gayo', '2025-05-13 14:20:05'),
(2, 'Tuesday', 'Lab 524', '10:30:00', '13:00:00', 'sysarch', 'Sir Jeff', '2025-05-13 14:23:09'),
(3, 'Monday', 'Lab 524', '19:00:00', '21:30:00', 'infosec', 'Sir F', '2025-05-13 15:11:57');

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `NOTIF_ID` int(11) NOT NULL,
  `USER_ID` int(11) DEFAULT NULL,
  `RESERVATION_ID` int(11) DEFAULT NULL,
  `ANNOUNCEMENT_ID` int(11) DEFAULT NULL,
  `MESSAGE` text DEFAULT NULL,
  `IS_READ` tinyint(1) NOT NULL DEFAULT 0,
  `CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`NOTIF_ID`, `USER_ID`, `RESERVATION_ID`, `ANNOUNCEMENT_ID`, `MESSAGE`, `IS_READ`, `CREATED_AT`) VALUES
(1, 5, 10, NULL, 'New reservation from Jana Zamoro for 517 on 2025-05-08', 1, '2025-05-07 20:21:55'),
(2, NULL, 10, NULL, 'Jana Zamoro (1st Year) has requested a reservation for 517 on 2025-05-08 at 04:21 for Web Design & Development.', 1, '2025-05-07 20:21:55'),
(3, 5, 10, NULL, 'Your reservation for 517 has been approved', 1, '2025-05-07 20:22:24'),
(4, 5, NULL, 5, 'Admin posted a new announcement', 1, '2025-05-07 20:59:11'),
(6, 7, NULL, 5, 'Admin posted a new announcement', 1, '2025-05-07 20:59:11'),
(7, 9, NULL, 5, 'Admin posted a new announcement', 0, '2025-05-07 20:59:11'),
(8, 10, NULL, 5, 'Admin posted a new announcement', 0, '2025-05-07 20:59:11'),
(9, 11, NULL, 5, 'Admin posted a new announcement', 0, '2025-05-07 20:59:11'),
(10, 12, NULL, 5, 'Admin posted a new announcement', 0, '2025-05-07 20:59:11'),
(11, 13, NULL, 5, 'Admin posted a new announcement', 0, '2025-05-07 20:59:11'),
(12, 14, NULL, 5, 'Admin posted a new announcement', 0, '2025-05-07 20:59:11'),
(13, 15, NULL, 5, 'Admin posted a new announcement', 0, '2025-05-07 20:59:11'),
(14, 16, NULL, 5, 'Admin posted a new announcement', 0, '2025-05-07 20:59:11'),
(19, 5, NULL, 6, 'Admin posted a new announcement', 1, '2025-05-07 21:06:56'),
(21, 7, NULL, 6, 'Admin posted a new announcement', 1, '2025-05-07 21:06:56'),
(22, 9, NULL, 6, 'Admin posted a new announcement', 0, '2025-05-07 21:06:56'),
(23, 10, NULL, 6, 'Admin posted a new announcement', 0, '2025-05-07 21:06:56'),
(24, 11, NULL, 6, 'Admin posted a new announcement', 0, '2025-05-07 21:06:56'),
(25, 12, NULL, 6, 'Admin posted a new announcement', 0, '2025-05-07 21:06:56'),
(26, 13, NULL, 6, 'Admin posted a new announcement', 0, '2025-05-07 21:06:56'),
(27, 14, NULL, 6, 'Admin posted a new announcement', 0, '2025-05-07 21:06:56'),
(28, 15, NULL, 6, 'Admin posted a new announcement', 0, '2025-05-07 21:06:56'),
(29, 16, NULL, 6, 'Admin posted a new announcement', 0, '2025-05-07 21:06:56'),
(30, 5, NULL, 7, 'Admin posted a new announcement', 1, '2025-05-13 05:01:17'),
(32, 7, NULL, 7, 'Admin posted a new announcement', 1, '2025-05-13 05:01:17'),
(33, 9, NULL, 7, 'Admin posted a new announcement', 0, '2025-05-13 05:01:17'),
(34, 10, NULL, 7, 'Admin posted a new announcement', 0, '2025-05-13 05:01:17'),
(35, 11, NULL, 7, 'Admin posted a new announcement', 0, '2025-05-13 05:01:17'),
(36, 12, NULL, 7, 'Admin posted a new announcement', 0, '2025-05-13 05:01:17'),
(37, 13, NULL, 7, 'Admin posted a new announcement', 0, '2025-05-13 05:01:17'),
(38, 14, NULL, 7, 'Admin posted a new announcement', 0, '2025-05-13 05:01:17'),
(39, 15, NULL, 7, 'Admin posted a new announcement', 0, '2025-05-13 05:01:17'),
(40, 16, NULL, 7, 'Admin posted a new announcement', 0, '2025-05-13 05:01:17'),
(45, 5, 11, NULL, 'New reservation from Jana Zamoro for 530 on 2025-05-13', 1, '2025-05-13 05:14:25'),
(46, NULL, 11, NULL, 'Jana Zamoro (1st Year) has requested a reservation for 530 on 2025-05-13 at 13:14 for C# Programming.', 1, '2025-05-13 05:14:25'),
(47, 5, 11, NULL, 'Your reservation for 530 has been approved', 1, '2025-05-13 05:14:39'),
(48, 12, 12, NULL, 'New reservation from erica  juarez for 528 on 2025-05-13', 0, '2025-05-13 06:27:00'),
(49, NULL, 12, NULL, 'erica  juarez (4th Year) has requested a reservation for 528 on 2025-05-13 at 14:26 for Java Programming.', 1, '2025-05-13 06:27:00'),
(50, 12, 12, NULL, 'Your reservation for 528 has been approved', 0, '2025-05-13 06:27:28'),
(51, 5, NULL, 8, 'Admin posted a new announcement', 1, '2025-05-13 08:33:34'),
(53, 7, NULL, 8, 'Admin posted a new announcement', 1, '2025-05-13 08:33:34'),
(54, 9, NULL, 8, 'Admin posted a new announcement', 0, '2025-05-13 08:33:34'),
(55, 10, NULL, 8, 'Admin posted a new announcement', 0, '2025-05-13 08:33:34'),
(56, 11, NULL, 8, 'Admin posted a new announcement', 0, '2025-05-13 08:33:34'),
(57, 12, NULL, 8, 'Admin posted a new announcement', 0, '2025-05-13 08:33:34'),
(58, 13, NULL, 8, 'Admin posted a new announcement', 0, '2025-05-13 08:33:34'),
(59, 14, NULL, 8, 'Admin posted a new announcement', 0, '2025-05-13 08:33:34'),
(60, 15, NULL, 8, 'Admin posted a new announcement', 0, '2025-05-13 08:33:34'),
(61, 16, NULL, 8, 'Admin posted a new announcement', 0, '2025-05-13 08:33:34'),
(66, 12, 41, NULL, 'New reservation from erica  juarez for 517 on 2025-05-13', 0, '2025-05-13 09:39:54'),
(67, NULL, 41, NULL, 'erica  juarez (4th Year) has requested a reservation for 517 on 2025-05-13 at 17:39 for Project Management.', 1, '2025-05-13 09:39:54'),
(68, 5, 43, NULL, 'New reservation from Jana Zamoro for 517 on 2025-05-13', 1, '2025-05-13 09:41:29'),
(69, NULL, 43, NULL, 'Jana Zamoro (1st Year) has requested a reservation for 517 on 2025-05-13 at 17:41 for Project Management.', 1, '2025-05-13 09:41:29'),
(70, 12, 41, NULL, 'Your reservation for 517 has been approved', 0, '2025-05-13 09:42:21'),
(71, 5, 43, NULL, 'Your reservation for 517 has been approved', 1, '2025-05-13 09:42:28'),
(72, 5, 46, NULL, 'New reservation from Jana Zamoro for 524 on 2025-05-13', 1, '2025-05-13 09:45:44'),
(73, NULL, 46, NULL, 'Jana Zamoro (1st Year) has requested a reservation for 524 on 2025-05-13 at 17:45 for Web Design & Development.', 1, '2025-05-13 09:45:44'),
(74, 5, 46, NULL, 'Your reservation for 524 has been approved', 1, '2025-05-13 09:46:04'),
(75, 5, 47, NULL, 'New reservation from Jana Zamoro for 526 on 2025-05-13', 1, '2025-05-13 09:47:50'),
(76, NULL, 47, NULL, 'Jana Zamoro (1st Year) has requested a reservation for 526 on 2025-05-13 at 17:47 for Database.', 1, '2025-05-13 09:47:50'),
(77, 5, 47, NULL, 'Your reservation for 526 has been approved', 1, '2025-05-13 09:49:48'),
(78, 5, 48, NULL, 'New reservation from Jana Zamoro for 530 on 2025-05-13', 1, '2025-05-13 10:11:09'),
(79, NULL, 48, NULL, 'Jana Zamoro (1st Year) has requested a reservation for 530 on 2025-05-13 at 18:11 for Computer Application.', 1, '2025-05-13 10:11:09'),
(80, 5, 48, NULL, 'Your reservation for 530 has been approved', 1, '2025-05-13 10:11:30'),
(81, 7, 49, NULL, 'New reservation from hamela sala for 524 on 2025-05-13', 1, '2025-05-13 11:37:56'),
(82, NULL, 49, NULL, 'hamela sala (4th Year) has requested a reservation for 524 on 2025-05-13 at 19:37 for Project Management.', 1, '2025-05-13 11:37:56'),
(83, 7, 49, NULL, 'Your reservation for 524 has been approved', 1, '2025-05-13 11:38:13'),
(84, 7, 50, NULL, 'New reservation from hamela sala for 544 on 2025-05-13', 1, '2025-05-13 14:12:46'),
(85, NULL, 50, NULL, 'hamela sala (4th Year) has requested a reservation for 544 on 2025-05-13 at 22:12 for C Programming.', 1, '2025-05-13 14:12:46'),
(86, 7, 50, NULL, 'Your reservation for 544 has been approved', 1, '2025-05-13 15:23:00'),
(87, 5, 51, NULL, 'New reservation from Jana Zamoro for 528 on 2025-05-14', 1, '2025-05-13 16:00:36'),
(88, NULL, 51, NULL, 'Jana Zamoro (1st Year) has requested a reservation for 528 on 2025-05-14 at 00:00 for C++ Programming.', 1, '2025-05-13 16:00:36'),
(89, 5, 51, NULL, 'Your reservation for 528 has been approved', 1, '2025-05-13 16:01:13'),
(90, 12, 52, NULL, 'New reservation from erica  juarez for 517 on 2025-05-14', 0, '2025-05-14 09:45:04'),
(91, NULL, 52, NULL, 'erica  juarez (4th Year) has requested a reservation for 517 on 2025-05-14 at 17:45 for Project Management.', 1, '2025-05-14 09:45:05'),
(92, 12, 52, NULL, 'Your reservation for 517 has been approved', 0, '2025-05-14 09:45:27'),
(93, 5, NULL, 9, 'Admin posted a new announcement', 1, '2025-05-14 10:10:20'),
(94, 7, NULL, 9, 'Admin posted a new announcement', 0, '2025-05-14 10:10:20'),
(95, 9, NULL, 9, 'Admin posted a new announcement', 0, '2025-05-14 10:10:20'),
(96, 10, NULL, 9, 'Admin posted a new announcement', 0, '2025-05-14 10:10:20'),
(97, 11, NULL, 9, 'Admin posted a new announcement', 0, '2025-05-14 10:10:20'),
(98, 12, NULL, 9, 'Admin posted a new announcement', 0, '2025-05-14 10:10:20'),
(99, 13, NULL, 9, 'Admin posted a new announcement', 0, '2025-05-14 10:10:20'),
(100, 14, NULL, 9, 'Admin posted a new announcement', 1, '2025-05-14 10:10:20'),
(101, 15, NULL, 9, 'Admin posted a new announcement', 0, '2025-05-14 10:10:20'),
(102, 16, NULL, 9, 'Admin posted a new announcement', 0, '2025-05-14 10:10:20'),
(108, 5, 53, NULL, 'New reservation from Jana Zamoro for 530 on 2025-05-14', 0, '2025-05-14 10:44:41'),
(109, NULL, 53, NULL, 'Jana Zamoro (1st Year) has requested a reservation for 530 on 2025-05-14 at 18:44 for Embedded System & IOT.', 0, '2025-05-14 10:44:41');

-- --------------------------------------------------------

--
-- Table structure for table `reservation`
--

CREATE TABLE `reservation` (
  `ID` int(11) NOT NULL,
  `IDNO` int(11) NOT NULL,
  `FULL_NAME` varchar(50) NOT NULL,
  `COURSE` varchar(30) NOT NULL,
  `YEAR_LEVEL` varchar(30) NOT NULL,
  `PURPOSE` varchar(30) NOT NULL,
  `LABORATORY` varchar(30) NOT NULL,
  `PC_NUM` int(11) NOT NULL,
  `DATE` date NOT NULL,
  `TIME_IN` time NOT NULL,
  `TIME_OUT` time NOT NULL,
  `STATUS` varchar(10) NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservation`
--

INSERT INTO `reservation` (`ID`, `IDNO`, `FULL_NAME`, `COURSE`, `YEAR_LEVEL`, `PURPOSE`, `LABORATORY`, `PC_NUM`, `DATE`, `TIME_IN`, `TIME_OUT`, `STATUS`) VALUES
(10, 1000, 'Jana Zamoro', 'BS IN COMPUTER SCIENCE', '1st Year', 'Web Design & Development', '517', 11, '2025-05-08', '04:21:00', '00:00:00', 'Approved'),
(11, 1000, 'Jana Zamoro', 'BS IN COMPUTER SCIENCE', '1st Year', 'C# Programming', '530', 1, '2025-05-13', '13:14:00', '00:00:00', 'Approved'),
(12, 7000, 'erica  juarez', 'BACHELOR OF ELEMENTARY EDUCATI', '4th Year', 'Java Programming', '528', 1, '2025-05-13', '14:26:00', '00:00:00', 'Approved'),
(41, 7000, 'erica  juarez', 'BACHELOR OF ELEMENTARY EDUCATI', '4th Year', 'Project Management', '517', 1, '2025-05-13', '17:39:00', '00:00:00', 'Approved'),
(43, 1000, 'Jana Zamoro', 'BS IN COMPUTER SCIENCE', '1st Year', 'Project Management', '517', 2, '2025-05-13', '17:41:00', '00:00:00', 'Approved'),
(46, 1000, 'Jana Zamoro', 'BS IN COMPUTER SCIENCE', '1st Year', 'Web Design & Development', '524', 1, '2025-05-13', '17:45:00', '00:00:00', 'Approved'),
(47, 1000, 'Jana Zamoro', 'BS IN COMPUTER SCIENCE', '1st Year', 'Database', '526', 1, '2025-05-13', '17:47:00', '00:00:00', 'Approved'),
(48, 1000, 'Jana Zamoro', 'BS IN COMPUTER SCIENCE', '1st Year', 'Computer Application', '530', 1, '2025-05-13', '18:11:00', '00:00:00', 'Approved'),
(49, 2000, 'hamela sala', 'BS IN INFORMATION TECHNOLOGY', '4th Year', 'Project Management', '524', 1, '2025-05-13', '19:37:00', '00:00:00', 'Approved'),
(50, 2000, 'hamela sala', 'BS IN INFORMATION TECHNOLOGY', '4th Year', 'C Programming', '544', 1, '2025-05-13', '22:12:00', '00:00:00', 'Approved'),
(51, 1000, 'Jana Zamoro', 'BS IN COMPUTER SCIENCE', '1st Year', 'C++ Programming', '528', 1, '2025-05-14', '00:00:00', '00:00:00', 'Approved'),
(52, 7000, 'erica  juarez', 'BACHELOR OF ELEMENTARY EDUCATI', '4th Year', 'Project Management', '517', 1, '2025-05-14', '17:45:00', '00:00:00', 'Approved'),
(53, 1000, 'Jana Zamoro', 'BS IN COMPUTER SCIENCE', '1st Year', 'Embedded System & IOT', '530', 1, '2025-05-14', '18:44:00', '00:00:00', 'Pending');

--
-- Triggers `reservation`
--
DELIMITER $$
CREATE TRIGGER `after_reservation_insert` AFTER INSERT ON `reservation` FOR EACH ROW BEGIN
    INSERT INTO notification (USER_ID, RESERVATION_ID, MESSAGE, IS_READ, CREATED_AT)
    VALUES ((SELECT STUD_NUM FROM users WHERE IDNO = NEW.IDNO), NEW.ID, 
            CONCAT('New reservation from ', NEW.FULL_NAME, ' for ', NEW.LABORATORY, ' on ', NEW.DATE), 
            0, NOW());
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_reservation_update` AFTER UPDATE ON `reservation` FOR EACH ROW BEGIN
    IF NEW.STATUS != OLD.STATUS THEN
        INSERT INTO notification (USER_ID, RESERVATION_ID, MESSAGE, IS_READ, CREATED_AT)
        VALUES ((SELECT STUD_NUM FROM users WHERE IDNO = NEW.IDNO), NEW.ID, 
                CONCAT('Your reservation for ', NEW.LABORATORY, ' has been ', LOWER(NEW.STATUS)), 
                0, NOW());
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `reservation_logs`
--

CREATE TABLE `reservation_logs` (
  `LOG_ID` int(11) NOT NULL,
  `IDNO` int(11) NOT NULL,
  `FULL_NAME` varchar(50) NOT NULL,
  `LABORATORY` varchar(30) NOT NULL,
  `PC_NUM` int(11) NOT NULL,
  `DATE` date NOT NULL,
  `TIME_IN` time NOT NULL,
  `STATUS` varchar(20) NOT NULL,
  `ACTION_DATE` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservation_logs`
--

INSERT INTO `reservation_logs` (`LOG_ID`, `IDNO`, `FULL_NAME`, `LABORATORY`, `PC_NUM`, `DATE`, `TIME_IN`, `STATUS`, `ACTION_DATE`) VALUES
(10, 1000, 'Jana Zamoro', '517', 11, '2025-05-08', '04:21:00', 'Approved', '2025-05-07 20:22:24'),
(11, 1000, 'Jana Zamoro', '530', 1, '2025-05-13', '13:14:00', 'Approved', '2025-05-13 05:14:39'),
(12, 7000, 'erica  juarez', '528', 1, '2025-05-13', '14:26:00', 'Approved', '2025-05-13 06:27:28'),
(13, 7000, 'erica  juarez', '517', 1, '2025-05-13', '17:39:00', 'Approved', '2025-05-13 09:42:21'),
(14, 1000, 'Jana Zamoro', '517', 2, '2025-05-13', '17:41:00', 'Approved', '2025-05-13 09:42:28'),
(15, 1000, 'Jana Zamoro', '524', 1, '2025-05-13', '17:45:00', 'Approved', '2025-05-13 09:46:04'),
(16, 1000, 'Jana Zamoro', '526', 1, '2025-05-13', '17:47:00', 'Approved', '2025-05-13 09:49:48'),
(17, 1000, 'Jana Zamoro', '530', 1, '2025-05-13', '18:11:00', 'Approved', '2025-05-13 10:11:30'),
(18, 2000, 'hamela sala', '524', 1, '2025-05-13', '19:37:00', 'Approved', '2025-05-13 11:38:13'),
(19, 2000, 'hamela sala', '544', 1, '2025-05-13', '22:12:00', 'Approved', '2025-05-13 15:23:00'),
(20, 1000, 'Jana Zamoro', '528', 1, '2025-05-14', '00:00:00', 'Approved', '2025-05-13 16:01:13'),
(21, 7000, 'erica  juarez', '517', 1, '2025-05-14', '17:45:00', 'Approved', '2025-05-14 09:45:27');

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `RESOURCES_ID` int(11) NOT NULL,
  `RESOURCES_NAME` varchar(255) NOT NULL,
  `PROFESSOR` varchar(100) DEFAULT NULL,
  `DESCRIPTION` text DEFAULT NULL,
  `RESOURCES_IMAGE` longblob DEFAULT NULL,
  `RESOURCES_LINK` varchar(255) DEFAULT NULL,
  `CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`RESOURCES_ID`, `RESOURCES_NAME`, `PROFESSOR`, `DESCRIPTION`, `RESOURCES_IMAGE`, `RESOURCES_LINK`, `CREATED_AT`) VALUES
(1, '113asd', 'kebs', 'first', NULL, 'https://images.app.goo.gl/CjimpLwodARFVNLEA', '2025-05-13 05:20:57'),
(2, 'asdwa', 'wick', '5465', NULL, 'https://www.wikipedia.org/', '2025-05-13 14:11:21');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `STUD_NUM` int(11) NOT NULL,
  `IDNO` int(11) NOT NULL,
  `LAST_NAME` varchar(30) NOT NULL,
  `FIRST_NAME` varchar(30) NOT NULL,
  `MID_NAME` varchar(30) NOT NULL,
  `COURSE` enum('BS IN ACCOUNTANCY','BS IN BUSINESS ADMINISTRATION','BS IN CRIMINOLOGY','BS IN CUSTOMS ADMINISTRATION','BS IN INFORMATION TECHNOLOGY','BS IN COMPUTER SCIENCE','BS IN OFFICE ADMINISTRATION','BS IN SOCIAL WORK','BACHELOR OF SECONDARY EDUCATION','BACHELOR OF ELEMENTARY EDUCATION') NOT NULL,
  `YEAR_LEVEL` enum('1st Year','2nd Year','3rd Year','4th Year') NOT NULL,
  `USER_NAME` varchar(30) NOT NULL,
  `PASSWORD_HASH` varchar(255) NOT NULL,
  `UPLOAD_IMAGE` longblob DEFAULT NULL,
  `EMAIL` varchar(30) NOT NULL,
  `ADDRESS` varchar(255) NOT NULL,
  `SESSION` int(11) NOT NULL DEFAULT 30,
  `POINTS` int(5) NOT NULL,
  `TOTAL_POINTS` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`STUD_NUM`, `IDNO`, `LAST_NAME`, `FIRST_NAME`, `MID_NAME`, `COURSE`, `YEAR_LEVEL`, `USER_NAME`, `PASSWORD_HASH`, `UPLOAD_IMAGE`, `EMAIL`, `ADDRESS`, `SESSION`, `POINTS`, `TOTAL_POINTS`) VALUES
(5, 1000, 'Zamoro', 'Jana', 'A', 'BS IN COMPUTER SCIENCE', '1st Year', 'jana', '$2y$10$ATMUh4nr/14WBLYThS5xOeAec6IxQkauU2GniAlLQKaBO/NnnjImS', 0x363832333135616161653935665f363765323266316633383166305f363764643861353763303132365f6d656f772e6a7067, 'jana@gmail.com', 'guadalupe', 30, 0, 10),
(7, 2000, 'sala', 'hamela', 'S', 'BS IN INFORMATION TECHNOLOGY', '4th Year', 'sala', '$2y$10$kkEznklT.Mwb6wLiXrB7Y.GGq6pab/LXRbNY4/5/C7.w6s/CEPUMy', 0x363832333266303133346366645f363830313031323737373065335f363764643861353763303132365f6d656f772e6a7067, 'sala@gmail.com', 'Labangon', 26, 0, 6),
(9, 4000, 'arias', 'irizcel', 'I', 'BS IN ACCOUNTANCY', '4th Year', 'arias', '$2y$10$U.8vA.puO7UGFqT6csMkhOptlppGeUO84Jpqo06428L0sz6K89cC6', 0x696d6167652e6a7067, '', '', 30, 0, 0),
(10, 4000, 'arias', 'irizcel', 'I', 'BS IN ACCOUNTANCY', '4th Year', 'arias', '$2y$10$KrR1gBJjDHf43zpAEe7BL.YV4L.Cv/KuehWqlAajuBFVut/HvGWly', 0x696d6167652e6a7067, '', '', 30, 0, 0),
(11, 12345678, 'cruz', 'donjee', 'Q', 'BS IN BUSINESS ADMINISTRATION', '2nd Year', 'donjee', '$2y$10$RUFAF0F3SzU6AH6qDwZs.uMl7w48yzq/lJ7qFeG8XC.RU37LvKLwe', 0x696d6167652e6a7067, '', '', 30, 0, 0),
(12, 7000, 'juarez', 'erica ', 'F', 'BACHELOR OF ELEMENTARY EDUCATION', '4th Year', 'erica', '$2y$10$j8f1R.CEge/io8C6i6twzOwMwg0z2MEY.osX2IphGAkZcALsIuyfO', 0x696d6167652e6a7067, '', '', 28, 2, 5),
(13, 8000, 'smith', 'john ', 'A', 'BS IN OFFICE ADMINISTRATION', '3rd Year', 'smith', '$2y$10$oIuTrTAZ8UqVQsQHeYIe6uKYOIBZjulcCq/Jic9HrtkhMpTr85.ju', 0x696d6167652e6a7067, '', '', 30, 0, 0),
(14, 9000, 'doe', 'jane', 'B', 'BS IN CRIMINOLOGY', '1st Year', 'doe', '$2y$10$aSE6IGtsSESHAKQFxZqQJunTkEC27UpaaskcfFYkrDzNKS64Jsva6', 0x696d6167652e6a7067, '', '', 30, 0, 0),
(15, 1100, 'Johnson', 'Micheal', 'B', 'BS IN COMPUTER SCIENCE', '4th Year', 'johnson', '$2y$10$K5beIg3VkRw.6lLmYGZcseeVoybxOXAaetsc5olc10QJNMzMpV09i', 0x696d6167652e6a7067, '', '', 30, 0, 0),
(16, 1110, 'Davis', 'Emily', 'D', 'BS IN BUSINESS ADMINISTRATION', '2nd Year', 'emily', '$2y$10$9iKfX3.BmWyswx4E/QrzzezfLoLsF2whqt/UKZ4vmGsYbq3g0WAmO', 0x696d6167652e6a7067, '', '', 30, 0, 0),
(17, 3000, 'Lopez', 'Nadine', 'R', 'BS IN INFORMATION TECHNOLOGY', '4th Year', 'nadine', '$2y$10$RWFayGFq7kRcXR2r2le.peqMy/lYsS7bDJeJiAemQj9PUzqdrq9s2', 0x363832343730653461303036385f363764663861393632366239325f363764643861353763303132365f6d656f772e6a7067, 'nadine@gmail.com', 'Cebu CIty', 30, 0, 0),
(18, 5000, 'mier', 'zaira', 'm', 'BS IN ACCOUNTANCY', '2nd Year', 'mier', '$2y$10$OpT1RLNd.DZ47BtGMY748uOJfOKmyMTbyUpL0w3ajds5mtAZJhPJC', 0x696d6167652e6a7067, '', '', 30, 0, 0),
(19, 1010, 'doe', 'john', 'J', 'BS IN CRIMINOLOGY', '1st Year', 'doe', '$2y$10$wxYLLlWY7t5zGGFw1Isam.TmkMS03m0dVfIJ0xPsYDdUG2.TkTgxi', 0x696d6167652e6a7067, '', '', 30, 0, 0),
(20, 1010, 'doe', 'john', 'J', 'BS IN CRIMINOLOGY', '1st Year', 'doe', '$2y$10$Wauzc5QkEHLhrDoujz2TX.1O8lfs56iHVd7S4uN6EicLW9tVoW5pK', 0x696d6167652e6a7067, '', '', 30, 0, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`ADMIN_ID`);

--
-- Indexes for table `announcement`
--
ALTER TABLE `announcement`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `computer`
--
ALTER TABLE `computer`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `curr_sitin`
--
ALTER TABLE `curr_sitin`
  ADD PRIMARY KEY (`SITIN_ID`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`FEEDBACK_ID`);

--
-- Indexes for table `lab_schedule`
--
ALTER TABLE `lab_schedule`
  ADD PRIMARY KEY (`SCHED_ID`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`NOTIF_ID`),
  ADD KEY `USER_ID` (`USER_ID`),
  ADD KEY `RESERVATION_ID` (`RESERVATION_ID`),
  ADD KEY `ANNOUNCEMENT_ID` (`ANNOUNCEMENT_ID`);

--
-- Indexes for table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `reservation_logs`
--
ALTER TABLE `reservation_logs`
  ADD PRIMARY KEY (`LOG_ID`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`RESOURCES_ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`STUD_NUM`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `ADMIN_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `announcement`
--
ALTER TABLE `announcement`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `computer`
--
ALTER TABLE `computer`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `curr_sitin`
--
ALTER TABLE `curr_sitin`
  MODIFY `SITIN_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `FEEDBACK_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `lab_schedule`
--
ALTER TABLE `lab_schedule`
  MODIFY `SCHED_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `NOTIF_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT for table `reservation`
--
ALTER TABLE `reservation`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `reservation_logs`
--
ALTER TABLE `reservation_logs`
  MODIFY `LOG_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `RESOURCES_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `STUD_NUM` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`USER_ID`) REFERENCES `users` (`STUD_NUM`) ON DELETE CASCADE,
  ADD CONSTRAINT `notification_ibfk_2` FOREIGN KEY (`RESERVATION_ID`) REFERENCES `reservation` (`ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `notification_ibfk_3` FOREIGN KEY (`ANNOUNCEMENT_ID`) REFERENCES `announcement` (`ID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
