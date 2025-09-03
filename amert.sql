-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 03, 2025 at 04:38 PM
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
-- Database: `amert`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `admin_name` text NOT NULL,
  `admin_username` text NOT NULL,
  `admin_password` varchar(50) DEFAULT NULL,
  `admin_email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `admin_name`, `admin_username`, `admin_password`, `admin_email`) VALUES
(0, 'Danish Hakimi', '13ddt23f1003', 'f1003', 'danishhakimi524@gmail.com'),
(1, 'Syafiq Adlan', '13ddt23f1051', 'syafiqpunya', 'syaadlan04@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_ic` varchar(20) NOT NULL,
  `customer_phone` varchar(15) NOT NULL,
  `customer_points` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`customer_id`, `customer_name`, `customer_ic`, `customer_phone`, `customer_points`, `created_at`) VALUES
(1, 'Iklil Farhan', '051126100243', '0155487795', 378, '2025-08-28 15:15:53'),
(2, 'Danish Hakimi', '050316060049', '0145494347', 2188, '2025-08-28 15:24:18'),
(3, 'Fauziah Basok', '891026554875', '0168874459', 766, '2025-08-29 15:01:49');

-- --------------------------------------------------------

--
-- Table structure for table `item_types`
--

CREATE TABLE `item_types` (
  `type_id` int(11) NOT NULL,
  `type_name` varchar(50) NOT NULL,
  `price_per_kg` decimal(10,2) NOT NULL,
  `points_per_kg` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_types`
--

INSERT INTO `item_types` (`type_id`, `type_name`, `price_per_kg`, `points_per_kg`) VALUES
(1, 'AB', 4.50, 45),
(2, 'Aluminium Enjin', 4.50, 45),
(3, 'Aluminium Grea', 6.00, 60),
(4, 'Aluminium Rim', 5.00, 50),
(5, 'Aluminium Sari', 2.00, 20),
(6, 'Aluminium Tangki', 4.00, 40),
(7, 'Aluminium Tin', 4.00, 40),
(8, 'Aluminium Wayar', 6.00, 60),
(9, 'Aluminium Wayar Kulit', 0.50, 5),
(10, 'Awning', 1.50, 15),
(11, 'Besi No.1', 0.85, 8),
(12, 'Besi No.2', 0.45, 4),
(13, 'Besi No.3', 0.20, 2),
(14, 'Kotak', 0.30, 3),
(15, 'Kertas', 0.30, 3),
(16, 'Plastik (K)', 0.50, 5),
(17, 'Plastik (L)', 0.20, 2),
(18, 'Plastik (PVC)', 0.20, 2),
(19, 'Plastik (ABS)', 0.20, 2),
(20, 'Plastik Tali', 0.20, 2),
(21, 'Linen Brek', 1.50, 15),
(22, 'Guni/Jumbo Bag', 3.00, 30),
(23, 'Steel 304', 4.00, 40),
(24, 'Steel 202', 0.50, 5),
(25, 'Steel Jaring', 0.50, 5),
(26, 'Tembaga Wayar (A1)', 22.00, 220),
(27, 'Tembaga Wayar (A)', 21.00, 210),
(28, 'Tembaga Bakar(Wayar B)', 20.00, 200),
(29, 'Tembaga Tokol (Kuning)', 12.00, 120),
(30, 'Tembaga Nipis', 10.00, 100),
(31, 'Tembaga Tangki', 10.00, 100),
(32, 'Tembaga Wayar Kulit', 5.00, 50),
(33, 'Timah', 2.00, 20),
(34, 'Tong Drum', 6.00, 60),
(35, 'E-Waste / TV LCD', 0.50, 5),
(36, 'Komputer', 0.30, 3),
(37, 'TV', 0.20, 2),
(38, 'Aircond', 1.20, 12),
(39, 'Mesin Basuh (Plastik)', 0.50, 5),
(40, 'Peti Sejuk (Besi No.3)', 0.20, 2),
(42, 'Alloy', 200.00, 0);

-- --------------------------------------------------------

--
-- Table structure for table `recycling_transactions`
--

CREATE TABLE `recycling_transactions` (
  `transaction_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `transaction_date` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `total_points` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(100) NOT NULL DEFAULT 'pending',
  `verified_by` varchar(100) NOT NULL DEFAULT 'system',
  `verified_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recycling_transactions`
--

INSERT INTO `recycling_transactions` (`transaction_id`, `customer_id`, `transaction_date`, `total_amount`, `total_points`, `created_at`, `status`, `verified_by`, `verified_at`) VALUES
(1, 2, '2025-08-28', 20.20, 196, '2025-08-28 15:29:31', 'pending', 'system', NULL),
(2, 3, '2025-08-29', 76.60, 766, '2025-08-29 15:02:30', 'pending', 'system', NULL),
(3, 1, '2025-08-30', 20.40, 198, '2025-08-30 02:59:40', 'pending', 'system', NULL),
(4, 2, '2025-09-01', 24.00, 240, '2025-09-01 13:39:33', 'pending', 'system', NULL),
(5, 2, '2025-09-03', 92.00, 920, '2025-09-03 08:39:42', 'paid', '5', '2025-09-03 22:36:45'),
(6, 2, '2025-09-03', 72.00, 720, '2025-09-03 14:08:03', 'paid', '5', '2025-09-03 22:33:13'),
(7, 2, '2025-09-03', 11.90, 112, '2025-09-03 14:09:09', 'paid', '5', '2025-09-03 22:33:16'),
(8, 1, '2025-09-03', 18.00, 180, '2025-09-03 14:22:00', 'paid', '5', '2025-09-03 22:32:10');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int(11) NOT NULL,
  `staff_name` varchar(100) NOT NULL,
  `staff_ic` varchar(50) DEFAULT NULL,
  `staff_password` varchar(50) DEFAULT NULL,
  `staff_dept` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `staff_name`, `staff_ic`, `staff_password`, `staff_dept`, `created_at`) VALUES
(4, 'Danish Hakimi', '050316060049', 'f1003', 'weight', '2025-09-03 05:56:39'),
(5, 'Syafiq Adlan', '050421101113', 'syafiqpunya', 'admin', '2025-09-03 05:56:39');

-- --------------------------------------------------------

--
-- Table structure for table `transaction_items`
--

CREATE TABLE `transaction_items` (
  `transaction_item_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `weight` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `points_earned` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction_items`
--

INSERT INTO `transaction_items` (`transaction_item_id`, `transaction_id`, `type_id`, `weight`, `subtotal`, `points_earned`) VALUES
(1, 1, 11, 12.00, 10.20, 96),
(2, 1, 4, 2.00, 10.00, 100),
(3, 2, 39, 8.00, 4.00, 40),
(4, 2, 35, 9.00, 4.50, 45),
(5, 2, 16, 9.00, 4.50, 45),
(6, 2, 18, 3.00, 0.60, 6),
(7, 2, 27, 3.00, 63.00, 630),
(8, 3, 12, 12.00, 5.40, 48),
(9, 3, 4, 3.00, 15.00, 150),
(10, 4, 33, 12.00, 24.00, 240),
(11, 5, 6, 23.00, 92.00, 920),
(12, 6, 8, 12.00, 72.00, 720),
(13, 7, 11, 14.00, 11.90, 112),
(14, 8, 1, 4.00, 18.00, 180);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `admin_username` (`admin_username`) USING HASH,
  ADD UNIQUE KEY `admin_email` (`admin_email`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `customer_ic` (`customer_ic`);

--
-- Indexes for table `item_types`
--
ALTER TABLE `item_types`
  ADD PRIMARY KEY (`type_id`);

--
-- Indexes for table `recycling_transactions`
--
ALTER TABLE `recycling_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`);

--
-- Indexes for table `transaction_items`
--
ALTER TABLE `transaction_items`
  ADD PRIMARY KEY (`transaction_item_id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `type_id` (`type_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `item_types`
--
ALTER TABLE `item_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `recycling_transactions`
--
ALTER TABLE `recycling_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `transaction_items`
--
ALTER TABLE `transaction_items`
  MODIFY `transaction_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `recycling_transactions`
--
ALTER TABLE `recycling_transactions`
  ADD CONSTRAINT `recycling_transactions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `transaction_items`
--
ALTER TABLE `transaction_items`
  ADD CONSTRAINT `transaction_items_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `recycling_transactions` (`transaction_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transaction_items_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `item_types` (`type_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
