-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 07, 2025 at 10:52 AM
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
  `field_type` varchar(50) DEFAULT NULL COMMENT 'text, number, email, date, textarea',
  `is_required` tinyint(1) DEFAULT 0,
  `field_order` int(11) DEFAULT 0,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_form_fields`
--

INSERT INTO `document_form_fields` (`id`, `document_type_id`, `field_name`, `field_type`, `is_required`, `field_order`, `meta`, `created_at`) VALUES
(16, 8, 'Date', 'date', 1, 0, NULL, '2025-12-07 16:57:21'),
(17, 8, 'Organization president name', 'text', 1, 1, NULL, '2025-12-07 16:57:21'),
(18, 8, 'Name of the organization', 'text', 1, 2, NULL, '2025-12-07 16:57:21'),
(22, 12, 'NAME OF ORGANIZATION', 'text', 1, 0, NULL, '2025-12-07 17:12:14'),
(23, 12, 'Brief introduction', 'textarea', 1, 1, NULL, '2025-12-07 17:12:14'),
(24, 12, 'Vision', 'textarea', 1, 2, NULL, '2025-12-07 17:12:14'),
(25, 12, 'Mission', 'textarea', 1, 3, NULL, '2025-12-07 17:12:14'),
(26, 12, 'How does your organization\'s values and principles align with the vision and mission of the OSAS and the College? List potential areas of collaboratio', 'textarea', 1, 4, NULL, '2025-12-07 17:12:14'),
(27, 12, 'What programs, services and/or activities does your organization offer that contribute to the holistic development of JHCSCians?', 'textarea', 1, 5, NULL, '2025-12-07 17:12:14'),
(28, 12, 'Faculty Adviser:', 'text', 1, 6, NULL, '2025-12-07 17:12:14'),
(29, 12, 'Co-Adviser:', 'text', 1, 7, NULL, '2025-12-07 17:12:14'),
(30, 12, 'Email address:', 'email', 1, 8, NULL, '2025-12-07 17:12:14'),
(31, 12, 'Website or Facebook Page:', 'text', 1, 9, NULL, '2025-12-07 17:12:14'),
(32, 12, 'Social Media Handles/In-charge:', 'text', 1, 10, NULL, '2025-12-07 17:12:14'),
(33, 12, 'Publication (if any), Editor-in-Chief:', 'text', 0, 11, NULL, '2025-12-07 17:12:14'),
(34, 12, 'Year Established:', 'date', 1, 12, NULL, '2025-12-07 17:12:14'),
(35, 12, 'Membership Fee: ', 'number', 1, 13, NULL, '2025-12-07 17:12:14'),
(36, 12, 'Please provide the link to your organization\'s Google Drive that will be used for official communication with the OSAS Section for this academic year.', 'text', 1, 14, NULL, '2025-12-07 17:12:14'),
(37, 14, 'Academic Year', 'date', 1, 0, NULL, '2025-12-07 17:16:24'),
(38, 14, 'NAME OF ORGANIZATION', 'text', 1, 1, NULL, '2025-12-07 17:16:24'),
(39, 14, 'Name of Adviser', 'text', 1, 2, NULL, '2025-12-07 17:16:24'),
(40, 14, 'Birthday', 'date', 1, 3, NULL, '2025-12-07 17:16:24'),
(41, 14, 'Home Address', 'text', 1, 4, NULL, '2025-12-07 17:16:24'),
(42, 14, 'Mobile No.', 'number', 1, 5, NULL, '2025-12-07 17:16:24'),
(43, 14, 'Email Address', 'email', 1, 6, NULL, '2025-12-07 17:16:24'),
(44, 14, 'Department', 'text', 1, 7, NULL, '2025-12-07 17:16:24'),
(45, 14, 'Employment Status', 'text', 1, 8, NULL, '2025-12-07 17:16:24'),
(46, 15, 'Name of Student Organization', 'text', 1, 0, NULL, '2025-12-07 17:18:55'),
(47, 15, 'Your Name', 'text', 1, 1, NULL, '2025-12-07 17:18:55'),
(48, 15, 'Name of Student Organization', 'text', 1, 2, NULL, '2025-12-07 17:18:55'),
(49, 15, 'Department / College', 'text', 1, 3, NULL, '2025-12-07 17:18:55'),
(50, 16, 'Recipient\'s Name', 'text', 1, 0, NULL, '2025-12-07 17:22:12'),
(51, 16, 'Name of Student Organization', 'text', 1, 1, NULL, '2025-12-07 17:22:12'),
(52, 16, 'briefly describe the organization\'s mission, goals, and key activities', 'textarea', 1, 2, NULL, '2025-12-07 17:22:12'),
(53, 16, 'mention any specific values or goals of the university that align with the organization’s mission', 'textarea', 1, 3, NULL, '2025-12-07 17:22:12'),
(54, 16, 'Duration', 'text', 1, 4, NULL, '2025-12-07 17:22:12'),
(55, 16, 'highlight significant achievements, projects, or contributions', 'textarea', 1, 5, NULL, '2025-12-07 17:22:12'),
(56, 16, 'mention specific university values, goals, or missions that align with the organization’s activities', 'textarea', 1, 6, NULL, '2025-12-07 17:22:12'),
(57, 20, 'Type of Application: 1. New 2. Renewal 3. Replacement', 'number', 1, 0, NULL, '2025-12-07 17:41:33'),
(58, 20, 'VEHICLE OWNER:', 'text', 1, 1, NULL, '2025-12-07 17:41:33'),
(59, 20, 'HOME ADDRESS:', 'text', 1, 2, NULL, '2025-12-07 17:41:33'),
(60, 20, 'Cellphone No.', 'number', 0, 3, NULL, '2025-12-07 17:41:33'),
(61, 20, 'Email Address', 'email', 0, 4, NULL, '2025-12-07 17:41:33'),
(62, 20, 'Course/Program', 'text', 1, 5, NULL, '2025-12-07 17:41:33'),
(63, 20, 'Year Enrolled:', 'date', 1, 6, NULL, '2025-12-07 17:41:33'),
(64, 20, 'Gender:', 'text', 1, 7, NULL, '2025-12-07 17:41:33'),
(65, 20, 'Birth Date:', 'date', 1, 8, NULL, '2025-12-07 17:41:33'),
(66, 20, 'Student ID No.:', 'number', 1, 9, NULL, '2025-12-07 17:41:33'),
(67, 20, 'Driver’s License No.:', 'number', 1, 10, NULL, '2025-12-07 17:41:33'),
(68, 20, 'Vehicle OR No.: ', 'text', 1, 11, NULL, '2025-12-07 17:41:33'),
(69, 20, ' Number of Vehicles to be registered:', 'number', 1, 12, NULL, '2025-12-07 17:41:33'),
(70, 20, 'Previous Sticker No', 'number', 1, 13, NULL, '2025-12-07 17:41:33'),
(71, 20, 'Plate No.', 'text', 1, 14, NULL, '2025-12-07 17:41:33'),
(72, 20, 'Model/Make/Color', 'text', 1, 15, NULL, '2025-12-07 17:41:33'),
(73, 20, 'Authorized Driver Name:', 'text', 0, 16, NULL, '2025-12-07 17:41:33'),
(74, 20, 'License No:', 'text', 0, 17, NULL, '2025-12-07 17:41:33'),
(75, 20, 'Expiration:', 'text', 0, 18, NULL, '2025-12-07 17:41:33'),
(76, 21, 'Name', 'text', 1, 0, NULL, '2025-12-07 17:48:18'),
(77, 21, 'Course', 'text', 1, 1, NULL, '2025-12-07 17:48:18'),
(78, 21, 'Year Level', 'text', 1, 2, NULL, '2025-12-07 17:48:18'),
(79, 21, 'Address ', 'text', 1, 3, NULL, '2025-12-07 17:48:18'),
(80, 21, 'Cellphone No.:', 'number', 1, 4, NULL, '2025-12-07 17:48:18'),
(81, 21, 'Email:', 'email', 0, 5, NULL, '2025-12-07 17:48:18'),
(82, 21, 'Sex:', 'text', 1, 6, NULL, '2025-12-07 17:48:18'),
(83, 21, 'Height:', 'text', 1, 7, NULL, '2025-12-07 17:48:18'),
(84, 21, 'Weight:', 'text', 1, 8, NULL, '2025-12-07 17:48:18'),
(85, 21, 'Hair: ', 'text', 1, 9, NULL, '2025-12-07 17:48:18'),
(86, 21, 'Built:', 'text', 1, 10, NULL, '2025-12-07 17:48:18'),
(87, 21, 'Complexion:', 'text', 1, 11, NULL, '2025-12-07 17:48:18'),
(88, 21, 'Distinguishing marks on the face, if any:', 'text', 0, 12, NULL, '2025-12-07 17:48:18'),
(89, 21, 'Student ID No.: ', 'number', 1, 13, NULL, '2025-12-07 17:48:18'),
(90, 21, 'Valid up to:', 'date', 1, 14, NULL, '2025-12-07 17:48:18'),
(91, 21, 'ID No.:', 'number', 1, 15, NULL, '2025-12-07 17:48:18'),
(92, 21, 'Valid up to:', 'date', 1, 16, NULL, '2025-12-07 17:48:18'),
(93, 21, 'LTO License No.:', 'number', 1, 17, NULL, '2025-12-07 17:48:18'),
(94, 21, 'Valid up to:', 'date', 1, 18, NULL, '2025-12-07 17:48:18');

-- --------------------------------------------------------

--
-- Table structure for table `document_types`
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
-- Dumping data for table `document_types`
--

INSERT INTO `document_types` (`id`, `name`, `type_code`, `description`, `category`, `template_path`, `requirements`, `created_at`) VALUES
(8, 'Formal Application Letter', 'formal_application_letter', 'Formal Application Letter for organization', 'application', 'uploads/templates/69354171462db_RF1 - Formal Application Letter.pdf', NULL, '2025-12-07 16:57:21'),
(10, 'Registration Form - Renewal', 'registration_form_renewal', 'REGISTRATION FORM - For Renewal', 'request', 'uploads/templates/69354323485de_RF2.2 - REGISTRATION FORM - Renewal.pdf', NULL, '2025-12-07 17:04:35'),
(11, 'Registration Form - New Application', 'registration_form_new_application', 'Registration Form - New Application', 'request', 'uploads/templates/693543cc9f25d_RF2.1 - REGISTRATION FORM - New Application.pdf', NULL, '2025-12-07 17:07:24'),
(12, 'Organizational Profile', 'organizational_profile', 'Application for organizational profile', 'application', 'uploads/templates/693544ee1ed57_RF3 - Organizational Profile.pdf', NULL, '2025-12-07 17:12:14'),
(13, 'Calendar of Activities', 'calendar_of_activities', 'Calendar of Activities', 'request', 'uploads/templates/6935452f2187d_RF4 - Calendar of Activities.pdf', NULL, '2025-12-07 17:13:19'),
(14, 'Faculty Adviser- OSAS MOA', 'faculty_adviser_osas_moa', 'Faculty Adviser- OSAS MOA', 'application', 'uploads/templates/693545e860077_RF5 - Faculty Adviser- OSAS MOA.pdf', NULL, '2025-12-07 17:16:24'),
(15, 'Faculty Adviser- Acceptance Letter', 'faculty_adviser_acceptance_letter', 'Faculty Adviser- Acceptance Letter', 'application', 'uploads/templates/6935467f3a27b_RF6 - Faculty Adviser- Acceptance Letter.pdf', NULL, '2025-12-07 17:18:55'),
(16, 'Endorsement Letter', 'endorsement_letter', 'Endorsement Letter', 'application', 'uploads/templates/69354743f1ffc_RF7 - Endorsement Letter.pdf', NULL, '2025-12-07 17:22:11'),
(17, 'List of Founding Members', 'list_of_founding_members', 'List of Founding Members', 'request', 'uploads/templates/693547909003e_RF8 - List of Founding Members.pdf', NULL, '2025-12-07 17:23:28'),
(18, 'Semestral Report', 'semestral_report', 'Semestral Report', 'request', 'uploads/templates/693547ed42de6_RF9 - Semestral Report.pdf', NULL, '2025-12-07 17:25:01'),
(19, 'List of Officers', 'list_of_officers', 'List of Officers', 'request', 'uploads/templates/69354817de075_RF10 - List of Officers.pdf', NULL, '2025-12-07 17:25:43'),
(20, 'Vehicle Pass Application Form', 'vehicle_pass_application_form', 'Vehicle Pass Application Form', 'application', 'uploads/templates/69354bcdd6ad7_VEHICLE-PASS-APPLICATION-FORM.pdf', NULL, '2025-12-07 17:41:33'),
(21, 'Authorized Driver\'s Information Sheet', 'authorized_driver_s_information_sheet', 'Authorized Driver\'s Information Sheet', 'application', 'uploads/templates/69354d620cfbd_VEHICLE-PASS-APPLICATION-FORM.pdf', NULL, '2025-12-07 17:48:18');

-- --------------------------------------------------------

--
-- Table structure for table `requests`
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
-- Dumping data for table `required_documents`
--

INSERT INTO `required_documents` (`id`, `document_type_id`, `requirement_name`, `requirement_description`, `file_type`, `is_mandatory`, `created_at`) VALUES
(9, 14, '2 x 2 photo here', NULL, 'image', 1, '2025-12-07 17:16:24'),
(10, 20, 'Authorized Driver’s Information Sheet', NULL, 'pdf', 1, '2025-12-07 17:42:26'),
(11, 20, 'School ID', NULL, 'image', 1, '2025-12-07 17:42:26'),
(12, 20, 'LTO Driver’s License and Official Receipt', NULL, 'image', 1, '2025-12-07 17:42:44'),
(13, 21, '2x2 ID Photo', NULL, 'image', 1, '2025-12-07 17:48:18');

-- --------------------------------------------------------

--
-- Table structure for table `uploaded_files`
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
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `student_id`, `full_name`, `email`, `course`, `password_hash`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'S01245678', 'Admin Account', 'admin@example.com', 'Other', '$2y$10$tA65ru.IFrCnT9YHakDSD.rlwC78yLvYkhf/Wc9iKcUtE.phOAVyq', 'admin', 'active', '2025-12-07 11:18:22', '2025-12-07 09:00:03'),
(6, 'S01234568', 'Raymart T. Upao', 'upaoraymart2004@gmail.com', 'BSIT', '$2y$10$mf7QmCDOogEIlL96Rd4WoerPxA.wbLx.c.KIxjDrxEV6hUR5avq6y', 'student', 'active', '2025-12-07 14:41:10', '2025-12-07 06:41:10');

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
  ADD KEY `document_type_id` (`document_type_id`),
  ADD KEY `idx_field_order` (`field_order`);

--
-- Indexes for table `document_types`
--
ALTER TABLE `document_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `type_code` (`type_code`),
  ADD KEY `idx_category` (`category`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `request_id` (`request_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `document_type_id` (`document_type_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_submission_date` (`submission_date`);

--
-- Indexes for table `required_documents`
--
ALTER TABLE `required_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_type_id` (`document_type_id`);

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
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `application_form_data`
--
ALTER TABLE `application_form_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `document_form_fields`
--
ALTER TABLE `document_form_fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `document_types`
--
ALTER TABLE `document_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `required_documents`
--
ALTER TABLE `required_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `uploaded_files`
--
ALTER TABLE `uploaded_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
