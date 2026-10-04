CREATE DATABASE IF NOT EXISTS `ahass_booking` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ahass_booking`;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `service_packages`;

CREATE TABLE `service_packages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `bookings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `plate_number` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `motorcycle_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_date` date NOT NULL,
  `service_time` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_package_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bookings_service_package_id_foreign` (`service_package_id`),
  KEY `bookings_service_date_service_time_index` (`service_date`,`service_time`),
  CONSTRAINT `bookings_service_package_id_foreign` FOREIGN KEY (`service_package_id`) REFERENCES `service_packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `service_packages` (`id`, `name`, `price`, `created_at`, `updated_at`) VALUES
(1, 'Servis Rutin (Oli & Filter)', 150000.00, NOW(), NOW()),
(2, 'Ganti Oli Mesin', 85000.00, NOW(), NOW()),
(3, 'Tune Up', 120000.00, NOW(), NOW()),
(4, 'Servis Ringan', 100000.00, NOW(), NOW()),
(5, 'Servis Besar', 250000.00, NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;
