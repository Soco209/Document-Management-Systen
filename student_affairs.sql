-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 15, 2025 at 12:00 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12
--
-- UPDATED VERSION - Includes all new features:
-- - Category-based document types (request/application)
-- - Dynamic form fields with multiple types
-- - File upload requirements with type restrictions
-- - REQ/APP ID prefixes based on category
-- - Enhanced admin and user functionality

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
-- Stores submitted data from dynamic form fields
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
-- Stores custom form field definitions created by admin
--

CREATE TABLE `document_form_fields` (
  `id` int(11) NOT NULL,
  `document_type_id` int(11) NOT NULL,
  `field_name` varchar(150) DEFAULT NULL,
  `field_type` varchar(50) DEFAULT NULL COMMENT 'text, number, email, date, textarea',
  `is_required` tinyint(1) DEFAULT 0,
  `field_order` int(11) DEFAULT 0,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `document_types`
-- Stores document type definitions
-- ENHANCED: Now includes category field for request/application distinction
--

CREATE TABLE `document_types` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type_code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT 'request' COMMENT 'request or application - determines ID prefix',
  `template_path` varchar(255) DEFAULT NULL,
  `requirements` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`requirements`)),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Sample data for table `document_types`
--

-- Sample data removed for document_types

-- --------------------------------------------------------

--
-- Table structure for table `requests`
-- Stores all document requests/applications
-- request_id now uses REQ- or APP- prefix based on document type category
--

CREATE TABLE `requests` (
  `id` int(11) NOT NULL,
  `request_id` varchar(50) NOT NULL COMMENT 'REQ-YYYYMMDD-XXXXX or APP-YYYYMMDD-XXXXX',
  `student_id` int(11) NOT NULL,
  `document_type_id` int(11) DEFAULT NULL,
  `purpose` text NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending' COMMENT 'Pending, Processing, Completed, Rejected, Approved',
  `submission_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_notes` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `required_documents`
-- Stores file upload requirements for document types
-- ENHANCED: Now includes file_type field for upload restrictions
--

CREATE TABLE `required_documents` (
  `id` int(11) NOT NULL,
  `document_type_id` int(11) NOT NULL,
  `requirement_name` varchar(150) DEFAULT NULL,
  `requirement_description` text DEFAULT NULL,
  `file_type` varchar(50) DEFAULT 'any' COMMENT 'image, pdf, docx, or any',
  `is_mandatory` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Sample data for table `required_documents`
--

-- Sample data removed for required_documents

-- --------------------------------------------------------

--
-- Table structure for table `uploaded_files`
-- Stores uploaded requirement files
--

CREATE TABLE `uploaded_files` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `requirement_name` varchar(150) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
-- Stores user accounts (students and admins)
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `role` varchar(30) DEFAULT 'student' COMMENT 'student or admin',
  `status` varchar(30) DEFAULT 'active' COMMENT 'active or inactive',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Sample data for table `users`
-- Default password for all sample users: password123
--

INSERT INTO `users` (`id`, `student_id`, `full_name`, `email`, `course`, `password_hash`, `role`, `status`, `created_at`) VALUES
(1, NULL, 'Admin User', 'admin@studentaffairs.edu', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', NOW()),
(2, 'S2025001', 'John Doe', 'john.doe@student.edu', 'BS Computer Science', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active', NOW()),
(3, 'S2025002', 'Jane Smith', 'jane.smith@student.edu', 'BS Information Technology', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'active', NOW());

-- --------------------------------------------------------

--
-- Indexes for dumped tables
--

ALTER TABLE `application_form_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`);

ALTER TABLE `document_form_fields`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_type_id` (`document_type_id`),
  ADD KEY `idx_field_order` (`field_order`);

ALTER TABLE `document_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_code` (`type_code`),
  ADD KEY `idx_category` (`category`);

ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `request_id` (`request_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `document_type_id` (`document_type_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_submission_date` (`submission_date`);

ALTER TABLE `required_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_type_id` (`document_type_id`);

ALTER TABLE `uploaded_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

ALTER TABLE `application_form_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `document_form_fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `document_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `required_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;

ALTER TABLE `uploaded_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

ALTER TABLE `application_form_data`
  ADD CONSTRAINT `application_form_data_ibfk_1` 
  FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE;

ALTER TABLE `document_form_fields`
  ADD CONSTRAINT `document_form_fields_ibfk_1` 
  FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`id`) ON DELETE CASCADE;

ALTER TABLE `requests`
  ADD CONSTRAINT `requests_ibfk_1` 
  FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `requests_ibfk_2` 
  FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`id`) ON DELETE SET NULL;

ALTER TABLE `required_documents`
  ADD CONSTRAINT `required_documents_ibfk_1` 
  FOREIGN KEY (`document_type_id`) REFERENCES `document_types` (`id`) ON DELETE CASCADE;

ALTER TABLE `uploaded_files`
  ADD CONSTRAINT `uploaded_files_ibfk_1` 
  FOREIGN KEY (`request_id`) REFERENCES `requests` (`id`) ON DELETE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

