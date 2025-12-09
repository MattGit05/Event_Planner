-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 09, 2025 at 08:07 AM
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
-- Database: `final-project_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `qr_code` varchar(255) NOT NULL,
  `status` enum('absent','present') DEFAULT 'absent',
  `scanned_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `attendees` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`attendees`)),
  `qr_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `created_by`, `title`, `date`, `time`, `category`, `description`, `created_at`, `attendees`, `qr_path`) VALUES
(4, 0, 'MY_EVENT', '2026-01-02', '14:22:00', 'new year celebration', 'bring your own food', '2025-11-26 00:48:12', '[\"mama mo\"]', NULL),
(7, 1, 'MY_EVENT', '2025-11-12', '00:22:00', 'Acuantance', 'hffkdsfhkjds', '2025-11-29 02:20:45', '[\"dsldjsafjfjf\",\"ihafuhusfhsuf\",\"matthewgarduque@76gmail.com\",\"matthewgarduque@76gmail.com\",\"matthewgarduque@76gmail.com\",\"matthewgarduque@76gmail.com\"]', NULL),
(8, 1, 'birthday ni murphy', '2003-05-11', '20:30:00', 'birthday', 'LOCATION: CASIGURAN HALL', '2025-11-29 04:09:55', '[\"jnngzmn18@gmail.com\"]', NULL),
(15, 1, 'birthday ni murphy', '0003-12-31', '18:06:00', 'worktf', 'hbdfhbf', '2025-12-02 10:52:16', '[\"Mercy V. Garduque\"]', NULL),
(17, 1, 'MY_EVENT', '2025-12-05', '18:08:00', 'work', 'kjhdkjdhkd', '2025-12-02 19:08:30', '[\"Mercy V. Garduque\",\"jnngzmn@gn\",\"Matthew Legaspi\",\"System Administrator\",\"Kaye\"]', NULL),
(19, 1, 'Christmas', '4714-12-11', '07:36:00', 'Chirstmas', 'fhfiufh', '2025-12-03 20:52:54', '[\"System Administrator\",\"Matthew Legaspi\",\"Kaye\",\"jnngzmn@gn\",\"Mercy V. Garduque\"]', NULL),
(22, 1, 'Pasko', '0000-00-00', '19:06:00', 'wgwudgwduwgd', 'fhebfejebf', '2025-12-04 12:53:21', '[\"Mercy V. Garduque\",\"jnngzmn@gn\",\"Kaye\"]', NULL),
(28, 1, 'user-invited', '0271-12-05', '00:13:00', 'Training', 'jskjahdh', '2025-12-05 02:32:37', '[\"System Administrator\",\"Matthew Legaspi\",\"Kaye\",\"Mercy V. Garduque\"]', NULL),
(29, 13, 'test', '0054-12-04', '00:45:00', 'Training', 'location', '2025-12-05 06:40:48', '[\"System Administrator\",\"Mercy V. Garduque\"]', NULL),
(30, 1, 'hsajhvfaj', '0034-12-11', '12:13:00', 'Meeting', 't6t7', '2025-12-09 07:03:41', '[\"Matthew Legaspi\",\"Kaye\",\"Mercy V. Garduque\",\"testtest\"]', NULL),
(31, 1, 'tfytffyt', '0042-03-21', '16:21:00', 'work', 'uyuyut', '2025-12-09 07:04:31', '[\"mercy@gmail.com\"]', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `event_attendees`
--

CREATE TABLE `event_attendees` (
  `id` int(11) NOT NULL,
  `event_id` int(11) DEFAULT NULL,
  `attendee_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_budgets`
--

CREATE TABLE `event_budgets` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `allocated_budget` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_spent` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_budgets`
--

INSERT INTO `event_budgets` (`id`, `event_id`, `allocated_budget`, `total_spent`, `created_at`) VALUES
(1, 22, 0.00, 60000.00, '2025-12-07 02:09:38'),
(2, 15, 0.00, 550.00, '2025-12-07 03:07:25');

-- --------------------------------------------------------

--
-- Table structure for table `event_expenses`
--

CREATE TABLE `event_expenses` (
  `id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `category` varchar(100) DEFAULT 'Other',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `event_expenses`
--

INSERT INTO `event_expenses` (`id`, `event_id`, `item_name`, `amount`, `category`, `created_at`) VALUES
(1, 22, 'flowers', 5000.00, 'Decoration', '2025-12-07 02:09:38'),
(2, 22, 'Cultural', 50000.00, 'Venue', '2025-12-07 02:10:00'),
(3, 15, 'sabon mo', 50.00, 'Food', '2025-12-07 03:07:25'),
(4, 15, 'tawas mo', 500.00, 'Misc', '2025-12-07 03:07:46'),
(5, 22, 'hfhgf', 5000.00, 'Logistics', '2025-12-09 07:05:10');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `event_id` int(11) DEFAULT NULL COMMENT 'Nullable for general messages, required for event-related messages',
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `event_id`, `is_read`) VALUES
(1, 1, 12, 'hi', '2025-12-07 10:28:11', NULL, 0),
(2, 12, 1, 'hello boss', '2025-12-07 10:45:19', NULL, 0),
(3, 14, 1, 'good morning sir', '2025-12-08 02:18:39', NULL, 0),
(4, 14, 1, 'hi', '2025-12-08 02:50:02', 19, 0),
(5, 1, 14, 'good morning too', '2025-12-08 03:11:14', NULL, 0),
(6, 14, 1, 'what is the problem', '2025-12-08 03:17:13', 17, 0),
(7, 1, 14, 'the fuck', '2025-12-08 07:14:34', NULL, 0),
(8, 1, 12, 'hiihihih', '2025-12-09 07:01:25', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--




--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`, `role`, `last_active`, `default_view`, `notif_sound`, `qr_code`) VALUES
(1, 'Admin', 'admin@gmail.com', '$2y$10$ddMo6FnDxWykXprSeJLQsuvwV/T2UD4XNkLxjKAYYPjWMtNsD5fkS', '2025-11-25 02:03:15', 'admin', '2025-12-09 15:05:28', 'Dashboard', 'Default', NULL),
(6, 'System Administrator', 'tom@gmail.com', '$2y$10$5hOMFdogdIAdrEt13O96VOgbW6d1K5G4oxZIfMhj3/FymMZAl.b26', '2025-11-26 11:04:18', 'admin', '2025-11-28 08:18:32', 'Users', 'Ping', NULL),
(11, 'Matthew Legaspi', 'Legaspi@gmail.com', '$2y$10$hGMK9ygr0MM.GZGN5SFw7OLDMT4RKH6v4nKo4zUgmV8.v62CIDWSm', '2025-11-27 04:40:14', 'user', NULL, 'Dashboard', 'Default', NULL),
(12, 'Kaye', 'kaye@gmail.com', '$2y$10$Kyja/gknZyN9Z5oUyNFACO4XLbeCz7KlNPJq2hWfyh1pIt.6X7VGG', '2025-11-27 13:17:46', 'user', '2025-12-09 15:01:41', 'Dashboard', 'Default', NULL),
(13, 'jnngzmn@gn', 'murphy@gmail.com', '$2y$10$8NakLSIshgmCjV5cGohBv.g3LHv0LMxK5A5JtuBKeN8.l32BEA8jK', '2025-11-29 04:11:42', 'admin', '2025-12-07 18:48:47', 'Dashboard', 'Default', NULL),
(14, 'Mercy V. Garduque', 'mercy@gmail.com', '$2y$10$GlV0MYPJnlNXDtTr6buBuey9q4PkF/lCNZCrrCSyRd.ZAo2yK9qvS', '2025-12-02 10:44:31', 'user', '2025-12-09 07:27:53', 'Dashboard', 'Default', NULL),
(15, 'Arby', 'Jamoralin@gmail.com', '$2y$10$oQJxdL99b2XfffGUZGv0AOecmrcPuVPINqdkp//JGFhHFc4mNQ6QW', '2025-12-08 23:10:00', 'user', '2025-12-09 07:10:42', 'Dashboard', 'Default', NULL),
(16, 'kim', 'kim@gmail.com', '$2y$10$OVs4cMFuWvXKYgOj6Y9u2Ol46EwyAPCEgFa2YMdTdpOp0C5Bt.emi', '2025-12-08 23:31:14', 'user', '2025-12-09 07:34:12', 'Dashboard', 'Default', NULL),
(17, 'Anbe', 'Anabe@gmail.com', '$2y$10$B25ouTKGEsDkvHRAYqnmmOoML4KNFzL8CIK.aZIcj7od6Ww/Xzyf6', '2025-12-08 23:33:54', 'user', NULL, 'Dashboard', 'Default', NULL),
(18, 'Ana', 'Ana@gmail.com', '$2y$10$XSKDcbvCvHTPAg4cAjODxuDNUllws7mkkkAnkjdiF.Ffd9U7tcufG', '2025-12-08 23:47:52', 'user', NULL, 'Dashboard', 'Default', NULL),
(19, 'Ana', 'mommy@gmail.com', '$2y$10$hkJK4DwFTkK9lLpdWvNMGOb3LkCatO1o2sZF.NlEobbnBs1cp0rNi', '2025-12-08 23:59:21', 'user', NULL, 'Dashboard', 'Default', NULL),
(20, 'test', 'test@gmail.com', '$2y$10$AwJo6t0JkigW2LIXSAiH1.I.eiFnho2g/BREtNDIc9vaMQlYephvS', '2025-12-09 00:00:41', 'user', NULL, 'Dashboard', 'Default', NULL),
(21, 'test2', 'test2@gmail.com', '$2y$10$vvUywb6ZwaoTQ2VuRwD3xuidbiB4R6YYH0xMO0jb/cxi2rocH2fL2', '2025-12-09 00:01:59', 'user', NULL, 'Dashboard', 'Default', NULL),
(22, 'test3', 'test3@gmail.com', '$2y$10$KuAbFyEcOeik4xN6JpHFqe9r02WcCzotHO/jfzxjUY4zFnE7TLYpG', '2025-12-09 00:08:40', 'user', NULL, 'Dashboard', 'Default', NULL),
(23, 'test4', 'test$@gmail.com', '$2y$10$Rxgd7U2uyw9K7FCP2poS8OBJYlSyPgHItrwW.T3Mrt2vtOJhd5yvK', '2025-12-09 00:15:28', 'user', NULL, 'Dashboard', 'Default', NULL),
(24, 'test5', 'test5@gmail.com', '$2y$10$kMBajdvs3PjbN1ILDmqoDOip.f31E/WWtVEVcLzjUr7f2xMuekZAO', '2025-12-09 00:18:10', 'user', NULL, 'Dashboard', 'Default', NULL),
(25, 'testtest', 'testtest@gmail.com', '$2y$10$eM5mk0CL9eLtieDLiEC0Se0o8LpvwjTAWabjAZgCrWrZCKkIXA5ry', '2025-12-09 00:23:16', 'user', NULL, 'Dashboard', 'Default', NULL),
(26, 'testing', 'testing@gmail.com', '$2y$10$.pRijimgR/IQEVSskCuqROFoaKTU3CTzwUzmIcPaId5kYh9JHj2am', '2025-12-09 00:37:27', 'user', NULL, 'Dashboard', 'Default', NULL),
(27, 'testing', 'testin@gmail.com', '$2y$10$waqLQ16qiaSgHSV7kH06t.5JN9oRtuGqwo8seWv5ggOiqRMYCSuK6', '2025-12-09 00:39:46', 'user', NULL, 'Dashboard', 'Default', NULL),
(28, 'register', 'register@gmail.com', '$2y$10$YaZio5vmOUiC9WgihYZ/G.yCp7J3bMl.oGk03YHdaog6s5A07df/.', '2025-12-09 00:44:22', 'user', NULL, 'Dashboard', 'Default', NULL),
(29, 'moms', 'moms@gmail.com', '$2y$10$L6iy2MpVFm5Kpjnyq3gEneivb8caEXk7fOpVCm1I/aKxNfWzWpQci', '2025-12-09 00:52:42', 'user', '2025-12-09 08:55:26', 'Dashboard', 'Default', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `qr_code` (`qr_code`),
  ADD UNIQUE KEY `unique_attendance` (`event_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `event_attendees`
--
ALTER TABLE `event_attendees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- Indexes for table `event_budgets`
--
ALTER TABLE `event_budgets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_budget_event` (`event_id`);

--
-- Indexes for table `event_expenses`
--
ALTER TABLE `event_expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_expense_event` (`event_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `event_attendees`
--
ALTER TABLE `event_attendees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_budgets`
--
ALTER TABLE `event_budgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `event_expenses`
--
ALTER TABLE `event_expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_attendees`
--
ALTER TABLE `event_attendees`
  ADD CONSTRAINT `event_attendees_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_budgets`
--
ALTER TABLE `event_budgets`
  ADD CONSTRAINT `fk_budget_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `event_expenses`
--
ALTER TABLE `event_expenses`
  ADD CONSTRAINT `fk_expense_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
