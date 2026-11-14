═══START═══
-- ============================================================
-- Abdul Hamid — Portfolio Database Schema
-- ============================================================
-- This file contains the schema + safe placeholder data for a
-- fresh local install. It does NOT contain:
--   • any real users / admin accounts
--   • any password hashes
--   • any real emails or phone numbers
--   • any contact form messages
--
-- To set up locally:
--   mysql -u root -p < db/schema.sql
-- Then create your own admin user (see README.md).
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


-- --------------------------------------------------------
-- Table structure: admin_users
-- (created empty — no seed data; register your own admin)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(80) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(190) NOT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `phone_number` varchar(40) DEFAULT NULL,
  `reset_code` varchar(20) DEFAULT NULL,
  `reset_code_expiry` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_username` (`username`),
  UNIQUE KEY `uniq_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- Table structure: about_content
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `about_content` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `section_key` varchar(64) NOT NULL,
  `title` varchar(190) DEFAULT NULL,
  `content` text NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_section` (`section_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `about_content` (`section_key`, `title`, `content`) VALUES
('intro', 'About Me', 'I am an Information Technology student passionate about web development, databases, and software solutions. This is placeholder text — replace it in the admin panel.'),
('bio', 'My Journey', 'After completing secondary education, I decided to pursue my passion for technology. I am building a strong foundation in web development, database management, and software engineering.');


-- --------------------------------------------------------
-- Table structure: skills
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `skills` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `level` tinyint(3) UNSIGNED NOT NULL DEFAULT 50,
  `category` varchar(40) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `skills` (`name`, `level`, `category`, `sort_order`) VALUES
('HTML / CSS', 85, 'Frontend', 1),
('JavaScript', 70, 'Frontend', 2),
('PHP', 75, 'Backend', 3),
('MySQL', 70, 'Database', 4),
('Git / GitHub', 65, 'Tools', 5);


-- --------------------------------------------------------
-- Table structure: projects
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `projects` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(190) NOT NULL,
  `slug` varchar(190) NOT NULL,
  `description` text DEFAULT NULL,
  `tech_stack` varchar(255) DEFAULT NULL,
  `project_url` varchar(255) DEFAULT NULL,
  `github_url` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `thumb_path` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `projects` (`title`, `slug`, `description`, `tech_stack`, `is_featured`, `sort_order`) VALUES
('Sample Project 1', 'sample-project-1', 'A placeholder project entry. Replace this with your own project details via the admin panel.', 'PHP, MySQL, CSS', 1, 1),
('Sample Project 2', 'sample-project-2', 'A second placeholder project. Edit or delete it in the admin panel.', 'JavaScript, HTML/CSS', 0, 2);


-- --------------------------------------------------------
-- Table structure: portfolio_images
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `portfolio_images` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `thumb_filename` varchar(255) DEFAULT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `category` varchar(40) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `uploaded_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- Table structure: contact_messages
-- (created empty — messages come from the live contact form)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `subject` varchar(190) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- Table structure: site_settings
-- (general site config — no secrets)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `site_settings` (
  `key_name` varchar(64) NOT NULL,
  `value_text` text DEFAULT NULL,
  PRIMARY KEY (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `site_settings` (`key_name`, `value_text`) VALUES
('site_title', 'Abdul Hamid — Portfolio'),
('site_tagline', 'Information Technology Student · Web Developer'),
('contact_email', 'your@email.com'),
('allow_contact', '1');


COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
═══END═══
