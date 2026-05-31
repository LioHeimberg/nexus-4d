-- Nexus 4D Database Schema
-- Version: 1.0.0

-- Drop tables if exist (for clean install)
DROP TABLE IF EXISTS `event_participation`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `posts`;
DROP TABLE IF EXISTS `events`;
DROP TABLE IF EXISTS `bars`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `users`;

-- Users table
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'boss', 'member') NOT NULL DEFAULT 'member',
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_email` (`email`),
  INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sessions table
CREATE TABLE `sessions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `token` VARCHAR(255) NOT NULL UNIQUE,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `last_used` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_token` (`token`),
  INDEX `idx_user` (`user_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Events table
CREATE TABLE `events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `event_date` DATETIME NOT NULL,
  `location` VARCHAR(255) NOT NULL,
  `boss_id` INT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_event_date` (`event_date`),
  INDEX `idx_boss` (`boss_id`),
  FOREIGN KEY (`boss_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Posts table
CREATE TABLE `posts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `boss_id` INT NOT NULL,
  `published_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_published` (`published_at`),
  INDEX `idx_boss` (`boss_id`),
  FOREIGN KEY (`boss_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bars table (Rümli bars)
CREATE TABLE `bars` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `location` VARCHAR(255) NOT NULL,
  `boss_id` INT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_name` (`name`),
  INDEX `idx_boss` (`boss_id`),
  FOREIGN KEY (`boss_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reviews table
CREATE TABLE `reviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `reviewer_type` ENUM('guest', 'member', 'boss') NOT NULL DEFAULT 'guest',
  `reviewer_id` INT NULL,
  `target_user_id` INT NOT NULL,
  `event_id` INT NULL,
  `bar_id` INT NULL,
  `rating_friendly` TINYINT UNSIGNED NOT NULL CHECK (`rating_friendly` BETWEEN 1 AND 5),
  `rating_professional` TINYINT UNSIGNED NOT NULL CHECK (`rating_professional` BETWEEN 1 AND 5),
  `rating_overall` TINYINT UNSIGNED NOT NULL CHECK (`rating_overall` BETWEEN 1 AND 5),
  `comment` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_target_user` (`target_user_id`),
  INDEX `idx_event` (`event_id`),
  INDEX `idx_bar` (`bar_id`),
  INDEX `idx_created` (`created_at`),
  FOREIGN KEY (`target_user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`bar_id`) REFERENCES `bars`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`reviewer_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Event participation table
CREATE TABLE `event_participation` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `event_id` INT NOT NULL,
  `member_id` INT NOT NULL,
  `status` ENUM('yes', 'maybe', 'no') NOT NULL DEFAULT 'maybe',
  `voted_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_vote` (`event_id`, `member_id`),
  INDEX `idx_event` (`event_id`),
  INDEX `idx_member` (`member_id`),
  INDEX `idx_status` (`status`),
  FOREIGN KEY (`event_id`) REFERENCES `events`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`member_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Initial Admin User (password: admin123)
-- Generate this with: password_hash('admin123', PASSWORD_DEFAULT)
-- INSERT INTO `users` (`email`, `password_hash`, `role`, `first_name`, `last_name`)
-- VALUES ('admin@nexus4d.ch', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'Admin', 'User');
