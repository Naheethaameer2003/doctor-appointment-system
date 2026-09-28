-- ================================================================
--  zamzameer_db — Full Database Dump
--  System  : Dr. Zamzameer Appointment Booking
--  Server  : MariaDB 10.4.32  |  PHP 8.2.12
--  Charset : utf8mb4 (supports Tamil, Arabic, emoji)
--
--  HOW TO IMPORT:
--  phpMyAdmin : Select DB → Import → Choose file → Go
--  CLI        : mysql -u root -p zamzameer_db < zamzameer_db_dump.sql
-- ================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


-- ================================================================
--  TABLE: admin_users
--  Doctor / admin login accounts.
--  Used by: login.php (auth) + admin.php (session display)
--  ⚠️  Password must be bcrypt hash — never plain text!
-- ================================================================
CREATE TABLE `admin_users` (
  `id`         int(10) UNSIGNED NOT NULL,
  `username`   varchar(80)      NOT NULL,               -- display name in dashboard
  `email`      varchar(180)     NOT NULL,               -- used as login identifier
  `password`   varchar(255)     NOT NULL,               -- bcrypt hash via password_hash()
  `created_at` timestamp        NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin account (⚠️ change password after first login!)
INSERT INTO `admin_users` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'Dr. Zamzameer', 'admin@zamzameer.lk', '1234', '2026-06-13 08:28:22');


-- ================================================================
--  TABLE: appointments
--  Every patient booking lives here.
--  Written by: appointment.php | Managed by: admin.php
-- ================================================================
CREATE TABLE `appointments` (
  `id`         int(10) UNSIGNED NOT NULL,
  `ref_number` varchar(20)      NOT NULL,               -- human-readable ref e.g. ZAM-0001
  `name`       varchar(120)     NOT NULL,
  `email`      varchar(180)     NOT NULL,
  `phone`      varchar(30)      NOT NULL,
  `age`        tinyint(3) UNSIGNED DEFAULT NULL,        -- optional
  `address`    varchar(255)         DEFAULT NULL,        -- optional
  `appt_date`  date             NOT NULL,               -- chosen appointment date
  `appt_time`  varchar(15)      NOT NULL,               -- chosen slot e.g. "06:00 PM"
  `visit_type` varchar(60)      NOT NULL DEFAULT 'General Consultation',
  `symptoms`   text                     DEFAULT NULL,   -- patient's described reason
  `status`     enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp        NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample booking row (cancelled)
INSERT INTO `appointments` (`id`, `ref_number`, `name`, `email`, `phone`, `age`, `address`, `appt_date`, `appt_time`, `visit_type`, `symptoms`, `status`, `created_at`) VALUES
(2, 'ZAM-3985', 'mohamed akram', 'mohamedakramhsn@gmail.com', '0770422440', 21, '71/3 gelioya', '2026-06-13', '05:00 PM', 'Follow-up Visit', 'fever', 'cancelled', '2026-06-13 09:44:07');


-- ================================================================
--  TABLE: clinic_settings
--  Key-value store for clinic-wide configuration.
--  e.g. doctor_available flag, leave dates
--  Read/written by: admin.php, get_slots.php
-- ================================================================
CREATE TABLE `clinic_settings` (
  `id`            int(11)      NOT NULL,
  `setting_key`   varchar(100)     DEFAULT NULL,   -- config key e.g. 'doctor_available'
  `setting_value` varchar(255)     DEFAULT NULL,   -- config value e.g. '1' or '0'
  `leave_date`    date             DEFAULT NULL    -- doctor leave date (nullable)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Initial setting: doctor marked unavailable by default
INSERT INTO `clinic_settings` (`id`, `setting_key`, `setting_value`, `leave_date`) VALUES
(1, 'doctor_available', '0', NULL);


-- ================================================================
--  TABLE: contact_messages
--  Messages from the public via contact.php form.
--  Displayed in admin.php "Messages" section.
-- ================================================================
CREATE TABLE `contact_messages` (
  `id`         int(10) UNSIGNED NOT NULL,
  `name`       varchar(120)     NOT NULL,
  `email`      varchar(180)     NOT NULL,
  `phone`      varchar(20)          DEFAULT NULL,   -- optional
  `subject`    varchar(200)         DEFAULT NULL,   -- optional
  `message`    text             NOT NULL,
  `is_read`    tinyint(1)       NOT NULL DEFAULT 0, -- 0 = unread, 1 = read
  `created_at` timestamp        NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample messages (both marked as read)
INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `subject`, `message`, `is_read`, `created_at`) VALUES
(1, 'mohamed akram', 'mohamedakramhsn@gmail.com', '07704222440', 'no', 'fever', 1, '2026-06-14 06:49:23'),
(2, 'mohamed akram', 'mohamedakramhsn@gmail.com', '088888888888', 'ff', 'gggggggggggggggggggggggg', 1, '2026-06-14 06:57:16');


-- ================================================================
--  INDEXES
-- ================================================================

-- admin_users
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_email` (`email`);             -- email = login identifier

-- appointments
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ref_number` (`ref_number`),   -- no duplicate refs
  ADD KEY `idx_date_time` (`appt_date`,`appt_time`), -- speeds up slot availability query
  ADD KEY `idx_status` (`status`);                 -- speeds up admin filter queries

-- clinic_settings
ALTER TABLE `clinic_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);    -- one row per config key

-- contact_messages
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_is_read` (`is_read`);               -- speeds up unread count query


-- ================================================================
--  AUTO_INCREMENT VALUES
--  Restored so next inserts continue from the correct ID
-- ================================================================
ALTER TABLE `admin_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `appointments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `clinic_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `contact_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;