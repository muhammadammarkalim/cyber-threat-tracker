-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 27, 2025 at 03:48 AM
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
-- Database: `cyberthreat_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--

CREATE TABLE `alerts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alerts`
--

INSERT INTO `alerts` (`id`, `title`, `message`, `type`, `created_at`) VALUES
(1, 'Immediate Patch Required', 'Apply patch KB5028943 for Windows systems now.', 'Warning', '2025-05-25 22:17:00'),
(2, 'New Ransomware Variant', 'Detected across hospital systems in multiple regions.', 'Critical', '2025-05-25 22:17:00'),
(3, 'Suspicious Network Activity', 'Unusual traffic from IP 172.16.254.1.', 'Alert', '2025-05-25 22:17:00'),
(5, 'Attach on Defender', 'update defender ', 'Critical', '2025-05-27 04:45:06'),
(6, 'new alert', 'system security update ', 'Critical', '2025-05-27 05:12:52'),
(7, 'Defender Alert', 'security issues ', 'Medium', '2025-05-27 05:58:42'),
(8, 'new alert', 'operating system issue ', 'Low', '2025-05-27 06:29:54');

-- --------------------------------------------------------

--
-- Table structure for table `countermeasures`
--

CREATE TABLE `countermeasures` (
  `countermeasure_id` int(11) NOT NULL,
  `countermeasure_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `industries`
--

CREATE TABLE `industries` (
  `industry_id` int(11) NOT NULL,
  `industry_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `action_timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`log_id`, `user_id`, `action`, `action_timestamp`) VALUES
(1, 39, 'Registered new user: mubashir', '2025-05-27 04:31:30'),
(2, 39, 'Submitted new threat: malware', '2025-05-27 04:32:32'),
(3, 30, 'Updated threat: malware (ID: 10)', '2025-05-27 04:36:43'),
(4, 30, 'Updated threat: malware (ID: 8)', '2025-05-27 04:37:07'),
(5, 30, 'Added new alert: Attach on Defender', '2025-05-27 04:44:49'),
(6, 30, 'Added new alert: Attach on Defender', '2025-05-27 04:45:06'),
(7, 30, 'Updated threat: malware (ID: 10)', '2025-05-27 04:50:14'),
(8, 30, 'Updated threat: malware (ID: 10)', '2025-05-27 04:50:20'),
(9, 30, 'Updated alert: Attach on Defender (ID: 4)', '2025-05-27 04:58:10'),
(10, 30, 'Deleted alert: Attach on Defender (ID: 4)', '2025-05-27 05:01:30'),
(11, 30, 'Deleted threat:  (ID: 7)', '2025-05-27 05:03:38'),
(12, 30, 'Admin added new threat: malware', '2025-05-27 05:06:24'),
(13, 30, 'Admin added new threat: malware', '2025-05-27 05:11:22'),
(14, 30, 'Added new alert: new alert', '2025-05-27 05:12:52'),
(15, 32, 'Submitted new threat: Ransome Attack', '2025-05-27 05:51:02'),
(16, 28, 'Added new alert: Defender Alert', '2025-05-27 05:58:42'),
(17, 28, 'Admin added new threat: Remote access attack', '2025-05-27 06:29:04'),
(18, 28, 'Added new alert: new alert', '2025-05-27 06:29:54'),
(19, 40, 'Registered new user: nasir', '2025-05-27 06:33:33');

-- --------------------------------------------------------

--
-- Table structure for table `threats`
--

CREATE TABLE `threats` (
  `id` int(11) NOT NULL,
  `threat_name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `severity` varchar(50) DEFAULT NULL,
  `affected_industry` varchar(100) DEFAULT NULL,
  `reported_date` date DEFAULT NULL,
  `submitted_by` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `threats`
--

INSERT INTO `threats` (`id`, `threat_name`, `description`, `severity`, `affected_industry`, `reported_date`, `submitted_by`) VALUES
(2, 'Ransomware X', 'Encrypts data and demands payment in crypto.', 'Critical', 'Healthcare', '2025-05-02', 'bob'),
(11, 'malware', 'window security', 'High', 'dev_changer', '2025-05-27', 'zaib'),
(12, 'malware', 'team attack', 'High', 'A4Tech', '2025-05-27', 'zaib'),
(13, 'Ransome Attack', 'powerful attack by Black_Hat hackers', 'High', 'all industries', '2025-05-27', 'nafay'),
(14, 'Remote access attack', 'Using Rat tools \r\navoid opening links', 'Critical', 'Microsoftt', '2025-05-27', 'ammar');

-- --------------------------------------------------------

--
-- Table structure for table `threattypes`
--

CREATE TABLE `threattypes` (
  `threat_type_id` int(11) NOT NULL,
  `threat_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_role` enum('student','govt_emp','it_cs','analyzer','admin') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `username`, `email`, `password`, `user_role`, `created_at`) VALUES
(28, 'Ammar', 'ammar', 'ammar@example.com', 'ammar123', 'admin', '2025-05-25 15:09:35'),
(29, 'Abdullah', 'abdullah', 'abdullah@example.com', 'abdullah123', 'admin', '2025-05-25 15:09:35'),
(30, 'Zaib', 'zaib', 'zaib@example.com', 'zaib123', 'admin', '2025-05-25 15:09:35'),
(31, 'MUHAMMAD AMMAR KALIM', 'ammarkaleem', 'ammarkaleem@gmail.com', '$2y$10$nWfGoeFCdwW0AC8Jix3uf.v8jNvxISZf1anaxVwWp5BtQS5abIKaO', 'student', '2025-05-25 15:32:22'),
(32, 'Mr Nafay', 'nafay', 'nafay@gmail.com', '$2y$10$hJkKb/ckrPDu.E6nE/SnBuVu6Ll5kU2U9vlF4kPf7iXwJJKL8FdyO', 'analyzer', '2025-05-25 15:42:59'),
(33, 'Admin User', 'admin', 'admin@example.com', '$2y$10$yTgzByX8kRlCj65E4zBqCeSBg/J9n9h1C9O0O07jcQewfDSc7xgJG', 'admin', '2025-05-25 22:05:35'),
(34, 'Alice Smith', 'alice', 'alice@example.com', '$2y$10$Qsb/SZy7SlhZ8nNCStXbGeX/rgMn1FkG1lPtjBWIGPLRQ9GuSLrE6', 'student', '2025-05-25 22:05:35'),
(35, 'Bob Khan', 'bob', 'bob@example.com', '$2y$10$Qsb/SZy7SlhZ8nNCStXbGeX/rgMn1FkG1lPtjBWIGPLRQ9GuSLrE6', 'it_cs', '2025-05-25 22:05:35'),
(36, 'Sara Malik', 'sara', 'sara@example.com', '$2y$10$Qsb/SZy7SlhZ8nNCStXbGeX/rgMn1FkG1lPtjBWIGPLRQ9GuSLrE6', 'govt_emp', '2025-05-25 22:05:35'),
(37, 'saad khan', 'saad', 'ahtshamjabbar2623@gmail.com', '$2y$10$dEmKdOcKyCmYEb/mj4D7rOuS1D90lmaA.RikjTfB/IaBgRP/m4zhq', 'student', '2025-05-26 02:30:51'),
(38, 'hariskaleem', 'haris', 'haris@gmail.com', '$2y$10$H5Vuqd.zQa5Y2NtWlxBxkOvPufH2R78VOjtYCy6bCklzzlzOUyfoq', 'it_cs', '2025-05-27 02:27:29'),
(39, 'mubashir ali', 'mubashir', 'mubi@gmail.com', '$2y$10$4pb69pXj0PSm.9uTQKCN/OQaA1pwqaItRvVrfefJ7IZIQ1DeUr/f2', 'student', '2025-05-27 04:31:30'),
(40, 'abdullah', 'nasir', 'abdullah@gmail.com', '$2y$10$I84vQVGx1i1H5SYUcX6jbu4RLOJOEhyt17ifnP495NSkTyVlfzYfW', 'it_cs', '2025-05-27 06:33:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `countermeasures`
--
ALTER TABLE `countermeasures`
  ADD PRIMARY KEY (`countermeasure_id`);

--
-- Indexes for table `industries`
--
ALTER TABLE `industries`
  ADD PRIMARY KEY (`industry_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `threats`
--
ALTER TABLE `threats`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `threattypes`
--
ALTER TABLE `threattypes`
  ADD PRIMARY KEY (`threat_type_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `countermeasures`
--
ALTER TABLE `countermeasures`
  MODIFY `countermeasure_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `industries`
--
ALTER TABLE `industries`
  MODIFY `industry_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `threats`
--
ALTER TABLE `threats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `threattypes`
--
ALTER TABLE `threattypes`
  MODIFY `threat_type_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
