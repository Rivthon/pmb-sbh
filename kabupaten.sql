-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table laravel_pmb.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` char(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `image` text COLLATE utf8mb4_unicode_ci,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','pengguna') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pengguna',
  `verification_code` char(6) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `periode_id` bigint unsigned DEFAULT NULL,
  `gelombang_id` bigint unsigned DEFAULT NULL,
  `jurusan_id` bigint unsigned DEFAULT NULL,
  `kuesioner_id` bigint unsigned DEFAULT NULL,
  `provinsi_id` bigint unsigned DEFAULT NULL,
  `kabupaten_id` bigint unsigned DEFAULT NULL,
  `kecamatan_id` bigint unsigned DEFAULT NULL,
  `kelurahan_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_uuid_unique` (`uuid`),
  UNIQUE KEY `users_code_unique` (`code`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_periode_id_foreign` (`periode_id`),
  KEY `users_gelombang_id_foreign` (`gelombang_id`),
  KEY `users_jurusan_id_foreign` (`jurusan_id`),
  KEY `users_kuesioner_id_foreign` (`kuesioner_id`),
  KEY `users_provinsi_id_foreign` (`provinsi_id`),
  KEY `users_kecamatan_id_foreign` (`kecamatan_id`),
  KEY `users_kelurahan_id_foreign` (`kelurahan_id`),
  KEY `users_kabupaten_id_foreign` (`kabupaten_id`),
  CONSTRAINT `users_gelombang_id_foreign` FOREIGN KEY (`gelombang_id`) REFERENCES `gelombangs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_jurusan_id_foreign` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_kabupaten_id_foreign` FOREIGN KEY (`kabupaten_id`) REFERENCES `kabupaten` (`id_kab`) ON DELETE SET NULL,
  CONSTRAINT `users_kecamatan_id_foreign` FOREIGN KEY (`kecamatan_id`) REFERENCES `kecamatan` (`id_kec`) ON DELETE SET NULL,
  CONSTRAINT `users_kelurahan_id_foreign` FOREIGN KEY (`kelurahan_id`) REFERENCES `kelurahan` (`id_kel`) ON DELETE SET NULL,
  CONSTRAINT `users_kuesioner_id_foreign` FOREIGN KEY (`kuesioner_id`) REFERENCES `kuesioners` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_periode_id_foreign` FOREIGN KEY (`periode_id`) REFERENCES `periodes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_provinsi_id_foreign` FOREIGN KEY (`provinsi_id`) REFERENCES `provinsi` (`id_prov`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data exporting was unselected.

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
