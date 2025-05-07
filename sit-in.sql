-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 07, 2025 at 03:31 AM
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
(1, '', 'GOOD DAY CCS', '2025-03-24', 'ADMIN'),
(2, '', 'ATTENTION CCS', '2025-03-24', 'ADMIN'),
(3, '', 'goodday', '2025-04-11', 'ADMIN'),
(4, '', 'ATTENTION CCS STUDENT ', '2025-04-11', 'ADMIN');

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
  MODIFY `ADMIN_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `announcement`
--
ALTER TABLE `announcement`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `computer`
--
ALTER TABLE `computer`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `curr_sitin`
--
ALTER TABLE `curr_sitin`
  MODIFY `SITIN_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `FEEDBACK_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lab_schedule`
--
ALTER TABLE `lab_schedule`
  MODIFY `SCHED_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `NOTIF_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservation`
--
ALTER TABLE `reservation`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `reservation_logs`
--
ALTER TABLE `reservation_logs`
  MODIFY `LOG_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `RESOURCES_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `STUD_NUM` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
-- Add the following triggers and sample data after the existing code

-- Trigger to create a notification when a reservation is made
DELIMITER //
CREATE TRIGGER after_reservation_insert
AFTER INSERT ON reservation
FOR EACH ROW
BEGIN
    INSERT INTO notification (USER_ID, RESERVATION_ID, MESSAGE, IS_READ, CREATED_AT)
    VALUES ((SELECT STUD_NUM FROM users WHERE IDNO = NEW.IDNO), NEW.ID, 
            CONCAT('New reservation from ', NEW.FULL_NAME, ' for ', NEW.LABORATORY, ' on ', NEW.DATE), 
            0, NOW());
END //
DELIMITER ;


-- Trigger to create a notification when a reservation status changes
DELIMITER //
CREATE TRIGGER after_reservation_update
AFTER UPDATE ON reservation
FOR EACH ROW
BEGIN
    IF NEW.STATUS != OLD.STATUS THEN
        INSERT INTO notification (USER_ID, RESERVATION_ID, MESSAGE, IS_READ, CREATED_AT)
        VALUES ((SELECT STUD_NUM FROM users WHERE IDNO = NEW.IDNO), NEW.ID, 
                CONCAT('Your reservation for ', NEW.LABORATORY, ' has been ', LOWER(NEW.STATUS)), 
                0, NOW());
    END IF;
END //
DELIMITER ;

-- Add admin record
INSERT INTO `admin` (`ADMIN_ID`, `USER_NAME`, `PASSWORD_HASH`) VALUES
(1, 'admin', '$2y$10$cJ/qQrJwN7BGzUHccJB5ruyKKxIWhgXJNkJloRfaQimPbKkxWRl8S');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
