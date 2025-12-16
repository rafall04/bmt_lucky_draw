-- BMT Lucky Draw Database Backup
-- Generated: 2025-12-16 12:39:39 WIB
-- Database: bmt_lucky_draw_test
-- Backup Type: FULL (All tables except migrations)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


-- Table structure for table `activity_logs`
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_logs_user_id_index` (`user_id`),
  KEY `activity_logs_action_index` (`action`),
  KEY `activity_logs_created_at_index` (`created_at`),
  CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `activity_logs`
LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
INSERT INTO `activity_logs` (`id`,`user_id`,`action`,`description`,`ip_address`,`user_agent`,`created_at`,`updated_at`) VALUES (5,NULL,'create','System menambah peserta Keara Leffler (No Rek: 4486287598)','127.0.0.1','Symfony','2025-12-16 05:39:39','2025-12-16 05:39:39');
INSERT INTO `activity_logs` (`id`,`user_id`,`action`,`description`,`ip_address`,`user_agent`,`created_at`,`updated_at`) VALUES (6,NULL,'create','System menambah peserta Bella Towne (No Rek: 8863425994)','127.0.0.1','Symfony','2025-12-16 05:39:39','2025-12-16 05:39:39');
INSERT INTO `activity_logs` (`id`,`user_id`,`action`,`description`,`ip_address`,`user_agent`,`created_at`,`updated_at`) VALUES (7,NULL,'create','System menambah peserta Vicenta Rosenbaum (No Rek: 4594564014)','127.0.0.1','Symfony','2025-12-16 05:39:39','2025-12-16 05:39:39');
INSERT INTO `activity_logs` (`id`,`user_id`,`action`,`description`,`ip_address`,`user_agent`,`created_at`,`updated_at`) VALUES (8,NULL,'create','System menambah peserta Lucius Carroll (No Rek: 9434337622)','127.0.0.1','Symfony','2025-12-16 05:39:39','2025-12-16 05:39:39');
INSERT INTO `activity_logs` (`id`,`user_id`,`action`,`description`,`ip_address`,`user_agent`,`created_at`,`updated_at`) VALUES (9,NULL,'create','System menambah peserta Dr. Candida Hodkiewicz (No Rek: 2091673915)','127.0.0.1','Symfony','2025-12-16 05:39:39','2025-12-16 05:39:39');
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;


-- Table structure for table `backups`
DROP TABLE IF EXISTS `backups`;
CREATE TABLE `backups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `filename` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` varchar(255) DEFAULT NULL,
  `type` enum('full','partial') NOT NULL DEFAULT 'full',
  `description` text DEFAULT NULL,
  `status` enum('pending','completed','failed') NOT NULL DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `backups_user_id_foreign` (`user_id`),
  CONSTRAINT `backups_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `backups`
LOCK TABLES `backups` WRITE;
/*!40000 ALTER TABLE `backups` DISABLE KEYS */;
INSERT INTO `backups` (`id`,`user_id`,`filename`,`file_path`,`file_size`,`type`,`description`,`status`,`error_message`,`completed_at`,`created_at`,`updated_at`) VALUES (4,5,'backup_2025-12-16_123939_DyTvej.sql','',NULL,'full','Test backup','pending',NULL,NULL,'2025-12-16 05:39:39','2025-12-16 05:39:39');
/*!40000 ALTER TABLE `backups` ENABLE KEYS */;
UNLOCK TABLES;


-- Table structure for table `cache`
DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Table structure for table `cache_locks`
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Table structure for table `password_reset_tokens`
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Table structure for table `pesertas`
DROP TABLE IF EXISTS `pesertas`;
CREATE TABLE `pesertas` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `no_rekening` varchar(255) NOT NULL,
  `nama` varchar(255) NOT NULL,
  `alamat` text NOT NULL,
  `cabang` varchar(255) DEFAULT NULL,
  `status_menang` tinyint(1) NOT NULL DEFAULT 0,
  `hadiah_didapat` varchar(255) DEFAULT NULL,
  `waktu_menang` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pesertas_no_rekening_unique` (`no_rekening`),
  KEY `pesertas_status_menang_index` (`status_menang`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `pesertas`
LOCK TABLES `pesertas` WRITE;
/*!40000 ALTER TABLE `pesertas` DISABLE KEYS */;
INSERT INTO `pesertas` (`id`,`no_rekening`,`nama`,`alamat`,`cabang`,`status_menang`,`hadiah_didapat`,`waktu_menang`,`created_at`,`updated_at`,`deleted_at`) VALUES (1,4486287598,'Keara Leffler','3337 Mertz Unions
Reingerfort, MS 76778-1805','North Hiltonmouth',0,NULL,NULL,'2025-12-16 05:39:39','2025-12-16 05:39:39',NULL);
INSERT INTO `pesertas` (`id`,`no_rekening`,`nama`,`alamat`,`cabang`,`status_menang`,`hadiah_didapat`,`waktu_menang`,`created_at`,`updated_at`,`deleted_at`) VALUES (2,8863425994,'Bella Towne','6878 Lindgren Stream
Dickensport, GA 34163-6236','Lake Ryleigh',0,NULL,NULL,'2025-12-16 05:39:39','2025-12-16 05:39:39',NULL);
INSERT INTO `pesertas` (`id`,`no_rekening`,`nama`,`alamat`,`cabang`,`status_menang`,`hadiah_didapat`,`waktu_menang`,`created_at`,`updated_at`,`deleted_at`) VALUES (3,4594564014,'Vicenta Rosenbaum','33364 Swaniawski Radial Apt. 112
Libbyshire, MO 56354-1321','Mitchellhaven',0,NULL,NULL,'2025-12-16 05:39:39','2025-12-16 05:39:39',NULL);
INSERT INTO `pesertas` (`id`,`no_rekening`,`nama`,`alamat`,`cabang`,`status_menang`,`hadiah_didapat`,`waktu_menang`,`created_at`,`updated_at`,`deleted_at`) VALUES (4,9434337622,'Lucius Carroll','2180 Feil Well
Schneidermouth, MI 90032-1193','Fisherside',0,NULL,NULL,'2025-12-16 05:39:39','2025-12-16 05:39:39',NULL);
INSERT INTO `pesertas` (`id`,`no_rekening`,`nama`,`alamat`,`cabang`,`status_menang`,`hadiah_didapat`,`waktu_menang`,`created_at`,`updated_at`,`deleted_at`) VALUES (5,2091673915,'Dr. Candida Hodkiewicz','58140 Sigurd Stream
Cruickshankshire, RI 46366','Loycechester',0,NULL,NULL,'2025-12-16 05:39:39','2025-12-16 05:39:39',NULL);
/*!40000 ALTER TABLE `pesertas` ENABLE KEYS */;
UNLOCK TABLES;


-- Table structure for table `sessions`
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Table structure for table `settings`
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Table structure for table `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'operator',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `users`
LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`id`,`name`,`email`,`role`,`last_login_at`,`last_login_ip`,`email_verified_at`,`password`,`remember_token`,`created_at`,`updated_at`) VALUES (5,'Adriel Okuneva I','sophia.mann@example.net','admin',NULL,NULL,'2025-12-16 05:39:39','$2y$04$h2iVTh26mWLLijJpFllr4O8KuosXiakqj0BMWdoTymjrd4C3EnBn.','doiRfEr9dz','2025-12-16 05:39:39','2025-12-16 05:39:39');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
