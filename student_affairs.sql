-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 07, 2025 at 01:17 PM
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
-- Database: `student_affairs`
--

-- --------------------------------------------------------

--
-- Table structure for table `application_form_data`
--

CREATE TABLE `application_form_data` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `field_name` varchar(150) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `field_value` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_form_fields`
--

CREATE TABLE `document_form_fields` (
  `id` int(11) NOT NULL,
  `document_type_id` int(11) NOT NULL,
  `field_name` varchar(150) DEFAULT NULL,
  `field_type` varchar(50) DEFAULT NULL,
  `is_required` tinyint(1) DEFAULT 0,
  `field_order` int(11) DEFAULT 0,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_templates`
--

CREATE TABLE `document_templates` (
  `id` int(11) NOT NULL,
  `type_id` int(11) DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `template_path` varchar(255) DEFAULT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_types`
--

CREATE TABLE `document_types` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type_code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT 'template',
  `template_path` varchar(255) DEFAULT NULL,
  `requirements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`requirements`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_types`
--

INSERT INTO `document_types` (`id`, `name`, `type_code`, `description`, `category`, `template_path`, `requirements`) VALUES
(3, 'Vehicle Permit', 'as', NULL, 'template', '/uploads/templates/68f27caa7a302_VEHICLE-PASS-APPLICATION-FORM.pdf', NULL),
(4, 'Formal Application Letter', 'formal_application_letter', NULL, 'template', '/uploads/templates/68f27b849ff1e_RF1 - Formal Application Letter.docx.pdf', NULL),
(6, 'Permit', 'permit', NULL, 'template', '/uploads/templates/68f312651debd_ContempReport.pdf', NULL),
(7, 'lopo', 'lopo', NULL, 'template', '/uploads/templates/68f31405854eb_Chapter1.pdf', NULL),
(8, 'momo', 'momo', NULL, 'template', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int(11) NOT NULL,
  `request_id` varchar(50) NOT NULL,
  `student_id` int(11) NOT NULL,
  `document_type_id` int(11) DEFAULT NULL,
  `purpose` text NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `request_id`, `student_id`, `document_type_id`, `purpose`, `status`, `submission_date`, `admin_notes`) VALUES
(16, 'REQ-20251107-VP1UM', 4, 3, 'ss', 'processing', '2025-11-07 09:45:50', ''),
(17, 'REQ-20251107-L2QBN', 4, 3, 'ss', 'completed', '2025-11-07 09:46:03', 'mad'),
(18, 'REQ-20251107-528HZ', 5, 3, 'ds', 'approved', '2025-11-07 10:00:42', 'adasas'),
(19, 'REQ-20251107-ZWLB3', 5, 3, 'sd', 'pending', '2025-11-07 10:00:53', 'oqjojqdojodj'),
(20, 'REQ-20251107-X73MJ', 5, 4, 'ad', 'processing', '2025-11-07 11:24:16', 'a');

-- --------------------------------------------------------

--
-- Table structure for table `required_documents`
--

CREATE TABLE `required_documents` (
  `id` int(11) NOT NULL,
  `document_type_id` int(11) NOT NULL,
  `requirement_name` varchar(150) DEFAULT NULL,
  `requirement_description` text DEFAULT NULL,
  `is_mandatory` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `required_documents`
--

INSERT INTO `required_documents` (`id`, `document_type_id`, `requirement_name`, `requirement_description`, `is_mandatory`) VALUES
(5, 4, 'Valid ID', NULL, 0),
(6, 4, '2x2', NULL, 0),
(7, 3, 'm', NULL, 0),
(8, 6, 'Valid ID', NULL, 0),
(9, 7, 'Valid ID', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `uploaded_files`
--

CREATE TABLE `uploaded_files` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `role` varchar(30) DEFAULT 'student',
  `status` varchar(30) DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `student_id`, `full_name`, `email`, `course`, `password_hash`, `role`, `status`, `created_at`, `updated_at`) VALUES
(4, 'S01234569', 'Raymart T. Upao', 'gaminghr209@gmail.com', 'BSED', '$2y$10$qZ03RL/udna1zL5HWHsLBeTTCE7EUwKNyJnl/ZVwxaAfqXKZNudpq', 'admin', 'active', '2025-11-07 15:57:57', '2025-11-07 12:16:40'),
(5, 'S01234561', 'Rukia', 'levirukia209@gmail.com', 'BSBA', '$2y$10$o5Bmz22j1UJ/YKzRJD1YyOI6.dKE7RXLaduQUkz8ITTu2ZGaoQvg.', 'student', 'active', '2025-11-07 18:00:16', '2025-11-07 10:00:16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `application_form_data`
--
ALTER TABLE `application_form_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`);

--
-- Indexes for table `document_form_fields`
--
ALTER TABLE `document_form_fields`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_type_id` (`document_type_id`);

--
-- Indexes for table `document_templates`
--
ALTER TABLE `document_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_id` (`type_id`);

--
-- Indexes for table `document_types`
--
ALTER TABLE `document_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_code` (`type_code`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `request_id` (`request_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `document_type_id` (`document_type_id`);

--
-- Indexes for table `required_documents`
--
ALTER TABLE `required_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `required_documents_ibfk_1` (`document_type_id`);

--
-- Indexes for table `uploaded_files`
--
ALTER TABLE `uploaded_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `student_id` (`student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `application_form_data`
--
ALTER TABLE `application_form_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `document_form_fields`
--
ALTER TABLE `document_form_fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `document_templates`
--
ALTER TABLE `document_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `document_types`
--
ALTER TABLE `document_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `required_documents`
--
ALTER TABLE `required_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `uploaded_files`
--
ALTER TABLE `uploaded_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `application_form_data`
--
ALTER TABLE `application_form_data`
  ADD CONSTRAINT `application_form_data_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_form_fields`
--
ALTER TABLE `document_form_fields`
  ADD CONSTRAINT `document_form_fields_ibfk_1` FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `document_templates`
--
ALTER TABLE `document_templates`
  ADD CONSTRAINT `document_templates_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `document_types` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `requests_ibfk_2` FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `required_documents`
--
ALTER TABLE `required_documents`
  ADD CONSTRAINT `required_documents_ibfk_1` FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `uploaded_files`
--
ALTER TABLE `uploaded_files`
  ADD CONSTRAINT `uploaded_files_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
