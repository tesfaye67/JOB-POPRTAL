-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 29, 2026 at 04:06 PM
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
-- Database: `tikuse_jobs`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','editor') DEFAULT 'admin',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `failed_login_attempts` int(11) NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `full_name`, `username`, `email`, `avatar`, `password`, `role`, `is_active`, `failed_login_attempts`, `locked_until`, `last_login_at`, `last_login_ip`, `created_at`) VALUES
(4, 'Main Admin', 'admin', 'admin@tikusejobs.com', '20260429154313-8646ea7f3870c27d.png', '$2y$10$nIFoPeiEB76Fol3KlBZ/Q.F9NNCpyNdm0uDRFmDPKYstVtZjidtkK', 'super_admin', 1, 0, NULL, '2026-04-29 17:02:37', '::1', '2026-04-29 12:58:56');

-- --------------------------------------------------------

--
-- Table structure for table `ad_slots`
--

CREATE TABLE `ad_slots` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slot_key` varchar(100) NOT NULL,
  `ad_code` longtext DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'inactive',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `company_category_id` int(11) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `name`, `slug`, `company_category_id`, `logo`, `website`, `description`, `created_at`, `updated_at`) VALUES
(2, 'Tesfaye Companey', 'tesfaye-companey', 1, '20260429153418-acda367c9ef81c2b.png', 'https://elelanajobs.com/job/enat-bank-sc-april-29-26/#google_vignette', 'hello', '2026-04-29 13:34:18', '2026-04-29 13:34:18');

-- --------------------------------------------------------

--
-- Table structure for table `company_categories`
--

CREATE TABLE `company_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_categories`
--

INSERT INTO `company_categories` (`id`, `name`, `created_at`) VALUES
(1, 'NGO', '2026-04-29 13:34:18');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `created_at`) VALUES
(1, 'tefaeye Deresse', 'tderesse67@gmail.com', 'Complian to your website', 'wqdad', '2026-04-29 13:44:56');

-- --------------------------------------------------------

--
-- Table structure for table `fields_of_study`
--

CREATE TABLE `fields_of_study` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fields_of_study`
--

INSERT INTO `fields_of_study` (`id`, `name`, `created_at`) VALUES
(2, 'IT', '2026-04-29 13:34:18');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `field_id` int(11) DEFAULT NULL,
  `company_category_id` int(11) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `job_type` enum('Full Time','Part Time','Contract','Internship','Freelance') DEFAULT 'Full Time',
  `short_description` text NOT NULL,
  `full_description` longtext DEFAULT NULL,
  `requirements` longtext DEFAULT NULL,
  `application_process` longtext DEFAULT NULL,
  `application_link` varchar(500) NOT NULL,
  `vacancy_image` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `status` enum('draft','published','expired') DEFAULT 'draft',
  `source_type` enum('company','agent','direct') DEFAULT 'direct',
  `posted_date` date DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `title`, `slug`, `company_id`, `field_id`, `company_category_id`, `location`, `job_type`, `short_description`, `full_description`, `requirements`, `application_process`, `application_link`, `vacancy_image`, `is_featured`, `status`, `source_type`, `posted_date`, `deadline`, `expiry_date`, `created_at`, `updated_at`) VALUES
(2, 'Test JOBS', 'test-jobs', 2, 2, 1, 'Addis Ababa', 'Full Time', 'Enat Bank SC External Vacancy Announcement\r\n\r\nJob Position 1 – Junior Customer Service Specialist\r\n\r\nRequired Qualification and Experience\r\nEducation:BA in Accounting, Banking & Insurance, Banking & Finance, Management, Business Administration, Sales, Marketing, Economics or related fields with minimum cumulative GPA of 3.00.\r\nNo prior experience is required.\r\nAge ≤ 30\r\nExit Exam is required. (Mandatory)\r\nWork Place: Asella', 'Enat Bank SC External Vacancy Announcement\r\n\r\nJob Position 1 – Junior Customer Service Specialist\r\n\r\nRequired Qualification and Experience\r\nEducation:BA in Accounting, Banking & Insurance, Banking & Finance, Management, Business Administration, Sales, Marketing, Economics or related fields with minimum cumulative GPA of 3.00.\r\nNo prior experience is required.\r\nAge ≤ 30\r\nExit Exam is required. (Mandatory)\r\nWork Place: Asella', 'Enat Bank SC External Vacancy Announcement\r\n\r\nJob Position 1 – Junior Customer Service Specialist\r\n\r\nRequired Qualification and Experience\r\nEducation:BA in Accounting, Banking & Insurance, Banking & Finance, Management, Business Administration, Sales, Marketing, Economics or related fields with minimum cumulative GPA of 3.00.\r\nNo prior experience is required.\r\nAge ≤ 30\r\nExit Exam is required. (Mandatory)\r\nWork Place: Asella', 'Enat Bank SC External Vacancy Announcement\r\n\r\nJob Position 1 – Junior Customer Service Specialist\r\n\r\nRequired Qualification and Experience\r\nEducation:BA in Accounting, Banking & Insurance, Banking & Finance, Management, Business Administration, Sales, Marketing, Economics or related fields with minimum cumulative GPA of 3.00.\r\nNo prior experience is required.\r\nAge ≤ 30\r\nExit Exam is required. (Mandatory)\r\nWork Place: Asella', 'https://elelanajobs.com/job-list/#google_vignette', NULL, 0, 'published', 'company', '2026-04-22', NULL, '2026-07-31', '2026-04-29 13:34:18', '2026-04-29 13:34:18');

-- --------------------------------------------------------

--
-- Table structure for table `job_views`
--

CREATE TABLE `job_views` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `event_type` enum('view','click') NOT NULL DEFAULT 'view',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `referrer` varchar(500) DEFAULT NULL,
  `viewed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_views`
--

INSERT INTO `job_views` (`id`, `job_id`, `event_type`, `ip_address`, `user_agent`, `referrer`, `viewed_at`) VALUES
(3, 2, 'view', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'http://localhost/JOB%20PORTAL%20-%20Copy/', '2026-04-29 13:34:29'),
(4, 2, 'click', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'http://localhost/JOB%20PORTAL%20-%20Copy/job-details.php?slug=test-jobs', '2026-04-29 13:34:33'),
(5, 2, 'view', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'http://localhost/JOB%20PORTAL%20-%20Copy/jobs.php', '2026-04-29 13:35:45'),
(6, 2, 'view', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', '', '2026-04-29 13:35:57');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `site_name` varchar(150) NOT NULL DEFAULT 'Tikuse Jobs',
  `site_logo` varchar(255) DEFAULT NULL,
  `hero_badge` varchar(150) DEFAULT NULL,
  `hero_title` varchar(255) DEFAULT NULL,
  `hero_highlight` varchar(120) DEFAULT NULL,
  `hero_subtitle` text DEFAULT NULL,
  `primary_cta_label` varchar(80) DEFAULT NULL,
  `secondary_cta_label` varchar(80) DEFAULT NULL,
  `hero_telegram_label` varchar(100) DEFAULT NULL,
  `stats_jobs_label` varchar(80) DEFAULT NULL,
  `stats_companies_label` varchar(80) DEFAULT NULL,
  `stats_categories_label` varchar(80) DEFAULT NULL,
  `stats_clicks_label` varchar(80) DEFAULT NULL,
  `quick_categories_label` varchar(120) DEFAULT NULL,
  `view_all_jobs_label` varchar(80) DEFAULT NULL,
  `view_all_categories_label` varchar(80) DEFAULT NULL,
  `browse_companies_label` varchar(80) DEFAULT NULL,
  `preview_clicks_label` varchar(100) DEFAULT NULL,
  `preview_expiry_value` varchar(40) DEFAULT NULL,
  `preview_expiry_label` varchar(100) DEFAULT NULL,
  `search_placeholder` varchar(160) DEFAULT NULL,
  `hero_preview_label` varchar(100) DEFAULT NULL,
  `hero_preview_title` varchar(120) DEFAULT NULL,
  `home_about_title` varchar(180) DEFAULT NULL,
  `home_about_text` text DEFAULT NULL,
  `home_about_cta_label` varchar(80) DEFAULT NULL,
  `home_about_point_1` varchar(180) DEFAULT NULL,
  `home_about_point_2` varchar(180) DEFAULT NULL,
  `home_about_point_3` varchar(180) DEFAULT NULL,
  `latest_jobs_title` varchar(150) DEFAULT NULL,
  `latest_jobs_subtitle` varchar(255) DEFAULT NULL,
  `categories_title` varchar(150) DEFAULT NULL,
  `categories_subtitle` varchar(255) DEFAULT NULL,
  `companies_title` varchar(150) DEFAULT NULL,
  `companies_subtitle` varchar(255) DEFAULT NULL,
  `telegram_card_title` varchar(150) DEFAULT NULL,
  `telegram_card_text` text DEFAULT NULL,
  `telegram_card_button_label` varchar(80) DEFAULT NULL,
  `why_title` varchar(150) DEFAULT NULL,
  `why_point_1` varchar(180) DEFAULT NULL,
  `why_point_2` varchar(180) DEFAULT NULL,
  `why_point_3` varchar(180) DEFAULT NULL,
  `telegram_link` varchar(255) DEFAULT NULL,
  `facebook_link` varchar(255) DEFAULT NULL,
  `tiktok_link` varchar(255) DEFAULT NULL,
  `instagram_link` varchar(255) DEFAULT NULL,
  `contact_email` varchar(150) DEFAULT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `footer_about` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `site_name`, `site_logo`, `hero_badge`, `hero_title`, `hero_highlight`, `hero_subtitle`, `primary_cta_label`, `secondary_cta_label`, `hero_telegram_label`, `stats_jobs_label`, `stats_companies_label`, `stats_categories_label`, `stats_clicks_label`, `quick_categories_label`, `view_all_jobs_label`, `view_all_categories_label`, `browse_companies_label`, `preview_clicks_label`, `preview_expiry_value`, `preview_expiry_label`, `search_placeholder`, `hero_preview_label`, `hero_preview_title`, `home_about_title`, `home_about_text`, `home_about_cta_label`, `home_about_point_1`, `home_about_point_2`, `home_about_point_3`, `latest_jobs_title`, `latest_jobs_subtitle`, `categories_title`, `categories_subtitle`, `companies_title`, `companies_subtitle`, `telegram_card_title`, `telegram_card_text`, `telegram_card_button_label`, `why_title`, `why_point_1`, `why_point_2`, `why_point_3`, `telegram_link`, `facebook_link`, `tiktok_link`, `instagram_link`, `contact_email`, `contact_phone`, `footer_about`, `created_at`, `updated_at`) VALUES
(1, 'Tikuse Jobs', '20260429154313-5dd99a2cf43ec7c8.png', 'Tesfaye', 'Tesfa Hero Title', 'Hero hiight', 'Herosubtitle', 'Primary Button Text', 'Secondary Button Text', 'Hero Telegram Link Text', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Search Your Carire', NULL, NULL, 'About', 'About Us', 'ABout Buttom', 'Point 1', 'Point 2', 'Point 3', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'https://t.me/tekus_jobs', 'https://web.facebook.com/tesfaye.deresse', 'https://www.tiktok.com/', 'https://www.instagram.com/tesfayeit', 'tderesse67@gmail.com', '+251967933958', 'Your global-style job search partner. Find. Apply. Succeed.', '2026-04-29 13:24:16', '2026-04-29 14:05:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `ad_slots`
--
ALTER TABLE `ad_slots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slot_key` (`slot_key`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `company_category_id` (`company_category_id`);

--
-- Indexes for table `company_categories`
--
ALTER TABLE `company_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fields_of_study`
--
ALTER TABLE `fields_of_study`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `field_id` (`field_id`),
  ADD KEY `company_category_id` (`company_category_id`);

--
-- Indexes for table `job_views`
--
ALTER TABLE `job_views`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ad_slots`
--
ALTER TABLE `ad_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `company_categories`
--
ALTER TABLE `company_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fields_of_study`
--
ALTER TABLE `fields_of_study`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `job_views`
--
ALTER TABLE `job_views`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `companies`
--
ALTER TABLE `companies`
  ADD CONSTRAINT `companies_ibfk_1` FOREIGN KEY (`company_category_id`) REFERENCES `company_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `jobs_ibfk_2` FOREIGN KEY (`field_id`) REFERENCES `fields_of_study` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `jobs_ibfk_3` FOREIGN KEY (`company_category_id`) REFERENCES `company_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `job_views`
--
ALTER TABLE `job_views`
  ADD CONSTRAINT `job_views_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
