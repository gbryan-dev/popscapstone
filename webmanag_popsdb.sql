-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 01, 2026 at 09:23 AM
-- Server version: 11.4.9-MariaDB
-- PHP Version: 8.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `webmanag_popsdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `application_id` int(11) NOT NULL,
  `client_id` varchar(50) NOT NULL,
  `ref_id` varchar(50) NOT NULL,
  `permit_for` varchar(100) DEFAULT NULL,
  `apply_date` varchar(50) DEFAULT NULL,
  `approval_date` varchar(50) DEFAULT NULL,
  `valid_until` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `rejection_date` text NOT NULL,
  `reason_of_rejection` text NOT NULL,
  `maplatitude` text NOT NULL,
  `maplongitude` text NOT NULL,
  `mapaddress` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clients_acc`
--

CREATE TABLE `clients_acc` (
  `client_id` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `created_at` varchar(50) NOT NULL,
  `role_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `application_id` int(11) DEFAULT NULL,
  `field_name` text NOT NULL,
  `file_name` longtext DEFAULT NULL,
  `file_extension` varchar(20) DEFAULT NULL,
  `file_size` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_reuploads`
--

CREATE TABLE `document_reuploads` (
  `reupload_id` int(11) NOT NULL,
  `log_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `field_name` varchar(255) NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_extension` varchar(20) DEFAULT NULL,
  `file_size` varchar(50) DEFAULT NULL,
  `uploaded_at` varchar(50) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `manufacturers_info`
--

CREATE TABLE `manufacturers_info` (
  `client_id` varchar(50) NOT NULL,
  `company_name` varchar(50) NOT NULL,
  `dealer_name` varchar(100) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `company_website` varchar(50) DEFAULT NULL,
  `company_address` text NOT NULL,
  `manufacturer_license_no` bigint(20) NOT NULL,
  `manufacturer_serial_no` bigint(20) NOT NULL,
  `manufacturer_expiry_date` date NOT NULL,
  `dealer_license_no` bigint(20) NOT NULL,
  `dealer_serial_no` bigint(20) NOT NULL,
  `dealer_expiry_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `client_id` varchar(50) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `ref_id` varchar(50) NOT NULL,
  `is_read` varchar(5) DEFAULT 'no',
  `created_at` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `officials_acc`
--

CREATE TABLE `officials_acc` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` varchar(50) NOT NULL,
  `role_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `officials_acc`
--

INSERT INTO `officials_acc` (`id`, `username`, `password`, `email`, `created_at`, `role_id`) VALUES
(1, 'director', '$2y$10$.UO2TQEJpJeVP0GQsCjwJOzpwXFcwFH047RH5tRLQl.aF54Ml63zi', 'bryangalamgam@gmail.com', 'Wednesday, January 21, 2026 at 05:32:14 AM', 1),
(2, 'inspector', '$2y$10$hX8c5ig24.WsipaDg.7W9eQ./LLUf9AwXO8AIyj7Yp0a4AJFFuqAq', 'inspector@gmail.com', 'Wednesday, January 21, 2026 at 05:32:45 AM', 2);

-- --------------------------------------------------------

--
-- Table structure for table `permit_sell_firecrackers`
--

CREATE TABLE `permit_sell_firecrackers` (
  `id` int(11) NOT NULL,
  `application_id` int(11) DEFAULT NULL,
  `date_issued` text DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `authorized_seller` varchar(255) DEFAULT NULL,
  `place_of_business` varchar(255) DEFAULT NULL,
  `type_of_permit` varchar(100) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `company_address` varchar(255) DEFAULT NULL,
  `company_license_number` varchar(100) DEFAULT NULL,
  `company_license_expiry` date DEFAULT NULL,
  `receipt_reference_number` varchar(100) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `date_paid` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permit_transport_pyrotechnics`
--

CREATE TABLE `permit_transport_pyrotechnics` (
  `id` int(11) NOT NULL,
  `application_id` int(11) DEFAULT NULL,
  `origin_location` varchar(255) DEFAULT NULL,
  `delivery_address` varchar(255) DEFAULT NULL,
  `delivery_purpose` varchar(255) DEFAULT NULL,
  `items_quantity` varchar(255) DEFAULT NULL,
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `date_paid` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `qr_docs`
--

CREATE TABLE `qr_docs` (
  `id` int(11) NOT NULL,
  `owner` varchar(50) NOT NULL,
  `license_number` varchar(20) NOT NULL,
  `application_type` varchar(50) NOT NULL,
  `validity_license` varchar(255) DEFAULT NULL,
  `qr_code_value` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `qr_docs`
--

INSERT INTO `qr_docs` (`id`, `owner`, `license_number`, `application_type`, `validity_license`, `qr_code_value`, `created_at`) VALUES
(6, 'Francis Aaron Jarantilla', '098765', 'Dealers', 'January 19, 2028 - January 19, 2030', 'FRA-098765-0DB2DCFA', '2026-01-23 03:37:59'),
(8, 'Frednyson Maghanoy', '567431', 'Manufacturer', 'January 26, 2026 - January 26, 2028', 'FRE-567431-B962E14E', '2026-01-23 03:58:06'),
(13, 'Byron James Abarabar', '976679', 'Retailer Permit', 'January 26, 2026 - January 26, 2028', 'BYR-976679-28F38B8C', '2026-01-26 03:09:13'),
(14, 'James Barcelona', '876567', 'Dealers', 'January 27, 2026 - January 27, 2028', 'JAM-876567-9641C48F', '2026-01-27 02:23:30');

-- --------------------------------------------------------

--
-- Table structure for table `retailers_info`
--

CREATE TABLE `retailers_info` (
  `client_id` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `gender` varchar(15) NOT NULL,
  `bdate` date NOT NULL,
  `address` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `review_logs`
--

CREATE TABLE `review_logs` (
  `log_id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `feedback_note` longtext DEFAULT NULL,
  `selected_documents` text DEFAULT NULL,
  `created_at` text DEFAULT NULL,
  `isdone` varchar(10) NOT NULL DEFAULT 'no',
  `remark_replies` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
(1, 'Director'),
(2, 'Inspector'),
(3, 'Retailer'),
(4, 'Manufacturer');

-- --------------------------------------------------------

--
-- Table structure for table `special_permit_display_fireworks`
--

CREATE TABLE `special_permit_display_fireworks` (
  `id` int(11) NOT NULL,
  `application_id` int(11) DEFAULT NULL,
  `display_datetime` datetime DEFAULT NULL,
  `display_purpose` varchar(255) DEFAULT NULL,
  `display_location` varchar(255) DEFAULT NULL,
  `pyro_technician` varchar(255) DEFAULT NULL,
  `fdo_licence_number` varchar(20) NOT NULL,
  `control_number` varchar(20) NOT NULL,
  `partner_police_station` varchar(255) DEFAULT NULL,
  `receipt_reference_number` varchar(100) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `pay_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `clients_acc`
--
ALTER TABLE `clients_acc`
  ADD PRIMARY KEY (`client_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_documents_application` (`application_id`);

--
-- Indexes for table `document_reuploads`
--
ALTER TABLE `document_reuploads`
  ADD PRIMARY KEY (`reupload_id`),
  ADD KEY `log_id` (`log_id`),
  ADD KEY `application_id` (`application_id`);

--
-- Indexes for table `manufacturers_info`
--
ALTER TABLE `manufacturers_info`
  ADD PRIMARY KEY (`client_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `officials_acc`
--
ALTER TABLE `officials_acc`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `permit_sell_firecrackers`
--
ALTER TABLE `permit_sell_firecrackers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`);

--
-- Indexes for table `permit_transport_pyrotechnics`
--
ALTER TABLE `permit_transport_pyrotechnics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`);

--
-- Indexes for table `qr_docs`
--
ALTER TABLE `qr_docs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `retailers_info`
--
ALTER TABLE `retailers_info`
  ADD PRIMARY KEY (`client_id`);

--
-- Indexes for table `review_logs`
--
ALTER TABLE `review_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `application_id` (`application_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `special_permit_display_fireworks`
--
ALTER TABLE `special_permit_display_fireworks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=436;

--
-- AUTO_INCREMENT for table `document_reuploads`
--
ALTER TABLE `document_reuploads`
  MODIFY `reupload_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `officials_acc`
--
ALTER TABLE `officials_acc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `permit_sell_firecrackers`
--
ALTER TABLE `permit_sell_firecrackers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permit_transport_pyrotechnics`
--
ALTER TABLE `permit_transport_pyrotechnics`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `qr_docs`
--
ALTER TABLE `qr_docs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `review_logs`
--
ALTER TABLE `review_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=150;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `special_permit_display_fireworks`
--
ALTER TABLE `special_permit_display_fireworks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients_acc` (`client_id`);

--
-- Constraints for table `clients_acc`
--
ALTER TABLE `clients_acc`
  ADD CONSTRAINT `clients_acc_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `fk_documents_application` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`);

--
-- Constraints for table `document_reuploads`
--
ALTER TABLE `document_reuploads`
  ADD CONSTRAINT `document_reuploads_ibfk_1` FOREIGN KEY (`log_id`) REFERENCES `review_logs` (`log_id`),
  ADD CONSTRAINT `document_reuploads_ibfk_2` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`);

--
-- Constraints for table `manufacturers_info`
--
ALTER TABLE `manufacturers_info`
  ADD CONSTRAINT `manufacturers_info_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients_acc` (`client_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients_acc` (`client_id`);

--
-- Constraints for table `officials_acc`
--
ALTER TABLE `officials_acc`
  ADD CONSTRAINT `officials_acc_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);

--
-- Constraints for table `permit_sell_firecrackers`
--
ALTER TABLE `permit_sell_firecrackers`
  ADD CONSTRAINT `permit_sell_firecrackers_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`);

--
-- Constraints for table `permit_transport_pyrotechnics`
--
ALTER TABLE `permit_transport_pyrotechnics`
  ADD CONSTRAINT `permit_transport_pyrotechnics_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`);

--
-- Constraints for table `retailers_info`
--
ALTER TABLE `retailers_info`
  ADD CONSTRAINT `retailers_info_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients_acc` (`client_id`);

--
-- Constraints for table `review_logs`
--
ALTER TABLE `review_logs`
  ADD CONSTRAINT `review_logs_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`);

--
-- Constraints for table `special_permit_display_fireworks`
--
ALTER TABLE `special_permit_display_fireworks`
  ADD CONSTRAINT `special_permit_display_fireworks_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
