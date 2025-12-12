-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 12, 2025 at 08:06 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vc_security`
--

-- --------------------------------------------------------

--
-- Table structure for table `scan_history`
--

CREATE TABLE `scan_history` (
  `id` int(11) NOT NULL,
  `system_id` int(11) NOT NULL,
  `severity` varchar(20) NOT NULL,
  `vulnerabilities` int(11) NOT NULL,
  `scan_date` varchar(20) NOT NULL,
  `crit` int(11) NOT NULL DEFAULT 0,
  `high` int(11) NOT NULL DEFAULT 0,
  `med` int(11) NOT NULL DEFAULT 0,
  `low` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `scan_history`
--

INSERT INTO `scan_history` (`id`, `system_id`, `severity`, `vulnerabilities`, `scan_date`, `crit`, `high`, `med`, `low`) VALUES
(52, 28, 'MEDIUM', 15, '05/12/2025', 0, 0, 1, 14),
(53, 28, 'LOW', 14, '05/12/2025', 0, 0, 0, 14),
(54, 30, 'HIGH', 11, '05/12/2025', 0, 1, 0, 10),
(55, 22, 'MEDIUM', 15, '03/09/2025', 0, 0, 15, 0),
(56, 23, 'NONE', 0, '09/10/2025', 0, 0, 0, 0),
(57, 24, 'HIGH', 9, '27/11/2025', 0, 9, 0, 0),
(58, 25, 'HIGH', 10, '27/10/2025', 0, 10, 0, 0),
(59, 26, 'CRITICAL', 11, '08/11/2025', 3, 8, 0, 0),
(60, 25, 'CRITICAL', 11, '27/11/2025', 1, 10, 0, 0),
(61, 26, 'HIGH', 5, '05/12/2025', 0, 5, 0, 0),
(62, 35, 'CRITICAL', 1, '10/12/2025', 1, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `systems`
--

CREATE TABLE `systems` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `website` varchar(255) NOT NULL,
  `severity` enum('CRITICAL','HIGH','MEDIUM','LOW','NONE') NOT NULL,
  `vulnerabilities` int(11) NOT NULL DEFAULT 0,
  `last_scan` varchar(20) NOT NULL,
  `need_auth` enum('YES','NO') NOT NULL,
  `auth_use` enum('YES','NO') NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `crit` int(11) NOT NULL DEFAULT 0,
  `high` int(11) NOT NULL DEFAULT 0,
  `med` int(11) NOT NULL DEFAULT 0,
  `low` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `systems`
--

INSERT INTO `systems` (`id`, `name`, `website`, `severity`, `vulnerabilities`, `last_scan`, `need_auth`, `auth_use`, `file_name`, `crit`, `high`, `med`, `low`) VALUES
(22, 'VulnCentral-NginX-Prod-Internal', '10.10.1.1', 'MEDIUM', 15, '03/09/2025', 'NO', 'NO', '1765521953_VC-SITES-CONFIDENTIAL.pdf', 0, 0, 15, 0),
(23, 'VulnCentral-FTPServer-Prod-Internal', '10.10.10.4', 'NONE', 0, '09/10/2025', 'YES', 'NO', '1765521580_VC-SITES-CONFIDENTIAL.pdf', 0, 0, 0, 0),
(24, 'VulnCentral-Karir-Prod-External', 'https://career.VulnCentral.co.id/', 'HIGH', 9, '27/11/2025', 'YES', 'YES', '1765521608_VC-SITES-CONFIDENTIAL.pdf', 0, 9, 0, 0),
(25, 'VulnCentral-Karir-UAT-External', '10.10.11.1', 'CRITICAL', 11, '27/11/2025', 'YES', 'YES', '1765521624_VC-SITES-CONFIDENTIAL.pdf', 1, 10, 0, 0),
(26, 'VulnCentral-VulnCentralMain-Prod-External', 'https://VulnCentral.com/', 'HIGH', 5, '05/12/2025', 'NO', 'NO', '1765521803_VC-SITES-CONFIDENTIAL.pdf', 0, 5, 0, 0),
(28, 'VulnCentral-ExampleID-UAT-External', 'VulnCentral.co.id', 'LOW', 14, '05/12/2025', 'NO', 'NO', '1765521808_VC-SITES-CONFIDENTIAL.pdf', 0, 0, 0, 14),
(30, 'VulnCentral-TaxCore-Prod-Internal', 'VulnCentral.com', 'HIGH', 11, '05/12/2025', 'YES', 'YES', '1765521813_VC-SITES-CONFIDENTIAL.pdf', 0, 1, 0, 10),
(35, 'VulnCentral-Sales-Prod-Internal', 'https://sales.vulncentral.io/', 'CRITICAL', 1, '10/12/2025', 'YES', 'YES', '1765522479_VC-SITES-CONFIDENTIAL.pdf', 1, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(64) NOT NULL,
  `role` enum('ADMIN','VIEW') NOT NULL DEFAULT 'VIEW'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'falah', '2cbda2e89b70a69025bb151263b01119add6a42fb043a9902d0ce59f5cd64667', 'ADMIN'),
(2, 'user', 'e606e38b0d8c19b24cf0ee3808183162ea7cd63ff7912dbb22b5e803286b4446', 'VIEW'),
(3, 'sulthan.falah', '469504aade67df15288dc878df40b1b03c92984b7ab97072145522258d72c018', 'ADMIN'),
(4, 'esa', '9ea1d720d238185ca454c055ec1a1a88d1ebd85558a726e35031e3a70964a61f', 'VIEW');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `scan_history`
--
ALTER TABLE `scan_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_scan_hist_system` (`system_id`);

--
-- Indexes for table `systems`
--
ALTER TABLE `systems`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `scan_history`
--
ALTER TABLE `scan_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `systems`
--
ALTER TABLE `systems`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `scan_history`
--
ALTER TABLE `scan_history`
  ADD CONSTRAINT `fk_scan_hist_system` FOREIGN KEY (`system_id`) REFERENCES `systems` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
