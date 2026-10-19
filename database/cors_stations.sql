-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 16, 2026 at 04:33 PM
-- Server version: 8.0.44
-- PHP Version: 8.4.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sacredio_corsnew`
--

-- --------------------------------------------------------

--
-- Table structure for table `cors_stations`
--

CREATE TABLE `cors_stations` (
  `id` bigint UNSIGNED NOT NULL,
  `ftp_account_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hero_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `partner_logo_primary` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `partner_logo_secondary` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `region` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `map_embed_url` text COLLATE utf8mb4_unicode_ci,
  `ftp_file_prefix` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ftp_local_directory` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `legacy_table` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_external` tinyint(1) NOT NULL DEFAULT '0',
  `external_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `last_synced_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cors_stations`
--

INSERT INTO `cors_stations` (`id`, `ftp_account_id`, `name`, `slug`, `code`, `provider`, `hero_text`, `partner_logo_primary`, `partner_logo_secondary`, `location`, `city`, `state_name`, `region`, `latitude`, `longitude`, `map_embed_url`, `ftp_file_prefix`, `ftp_local_directory`, `legacy_table`, `is_external`, `external_url`, `is_active`, `sort_order`, `last_synced_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Ikeja CORS', 'lagos', 'LA', 'Sacredion', 'Welcome to Ikeja CORS, Ikeja', 'images/slogo.png', 'images/tlogo.png', 'Opebi, Ikeja, Lagos State', 'Ikeja', 'Lagos State', 'South West', 6.5915380, 3.3389271, NULL, 'SACr', 'LAGOS', 'lagos_cors', 0, NULL, 1, 1, '2026-10-16 11:01:12', '2026-08-12 09:52:07', '2026-10-16 11:01:12'),
(2, 1, 'Awoyaya CORS', 'awoyaya', 'AW', 'Sacredion', 'Welcome to Awoyaya CORS, Awoyaya', 'images/slogo.png', 'images/tlogo.png', 'Awoyaya, Lagos State', 'Awoyaya', 'Lagos State', 'South West', 6.4681799, 3.7061761, NULL, 'AWOY', 'AWOY', 'awoy_cors', 0, NULL, 1, 2, '2026-10-16 11:07:06', '2026-08-12 09:52:07', '2026-10-16 11:07:06'),
(3, 1, 'Aba CORS', 'aba', 'AB', 'Sacredion', 'Welcome to Aba CORS, Aba', 'images/slogo.png', 'images/tlogo.png', 'Aba, Abia State', 'Aba', 'Abia State', 'South East', 5.5415444, 7.4152090, NULL, 'ABAA', 'ABAA', 'aba_cors', 0, NULL, 1, 3, '2026-10-16 11:12:44', '2026-08-12 09:52:07', '2026-10-16 11:12:44'),
(4, NULL, 'Abia CORS', 'abia', 'AN', 'NIS Abia', 'Welcome to Abia CORS, Umuahia', 'images/slogo.png', 'images/tlogo.png', 'NIS Secretariat, Umuahia, Abia State', 'Umuahia', 'Abia State', 'South East', 5.5260000, 7.4940000, NULL, NULL, 'ABIA', 'abia_cors', 0, NULL, 0, 4, '2026-08-12 12:52:22', '2026-08-12 09:52:07', '2026-08-12 12:52:22'),
(5, 1, 'Umuahia CORS', 'umuahia', 'UM', 'Sacredion', 'Welcome to Umuahia CORS, Umuahia', 'images/slogo.png', 'images/tlogo.png', 'Umuahia, Abia State', 'Umuahia', 'Abia State', 'South East', 5.5320000, 7.4860000, NULL, 'UMUA', 'UMUA', 'umuahia_cors', 0, NULL, 1, 5, '2026-10-16 11:17:49', '2026-08-12 09:52:07', '2026-10-16 11:17:49'),
(6, 1, 'Wuye CORS', 'abuja', 'FC', 'Sacredion', 'Welcome to Wuye CORS, Wuye', 'images/slogo.png', 'images/tlogo.png', 'Wuye, Abuja', 'Wuye', 'Abuja / F.C.T', 'North Central', 9.0537128, 7.4381840, NULL, 'ABJS', 'ABJS', 'abuja_cors', 0, NULL, 1, 6, '2026-10-16 11:22:43', '2026-08-12 09:52:07', '2026-10-16 11:22:43'),
(7, 1, 'Uyo CORS', 'uyo', 'AK', 'Sacredion', 'Welcome to Uyo CORS, Uyo', 'images/slogo.png', 'images/tlogo.png', 'Uyo, Akwa Ibom State', 'Uyo', 'Akwa Ibom State', 'South South', 5.0080524, 7.9625771, NULL, 'UYO', 'UYO', 'uyo_cors', 0, NULL, 1, 7, '2026-10-16 11:27:38', '2026-08-12 09:52:07', '2026-10-16 11:27:38'),
(8, 1, 'Yenagoa CORS', 'bayelsa', 'BY', 'Sacredion', 'Welcome to Yenagoa CORS, Yenagoa', 'images/slogo.png', 'images/tlogo.png', 'Yenagoa, Bayelsa State', 'Yenagoa', 'Bayelsa State', 'South South', 4.9382481, 6.0396930, NULL, 'BYSA', 'BYSA', 'bayelsa_cors', 0, NULL, 1, 8, '2026-10-16 11:31:59', '2026-08-12 09:52:07', '2026-10-16 11:31:59'),
(9, NULL, 'Ikom CORS', 'Ikom', 'CR', 'OSG Cross River', 'Welcome to Cross River CORS, Calabar', 'images/slogo.png', 'images/tlogo.png', 'Calabar, Cross River State', 'Ikom', 'Cross River State', 'South South', 5.9706510, 8.7199100, NULL, NULL, NULL, NULL, 1, 'https://www.osgcrossrivercors.net', 1, 9, NULL, '2026-08-12 09:52:07', '2026-08-12 09:52:07'),
(10, 1, 'Asaba CORS', 'asaba', 'AS', 'Sacredion', 'Welcome to Asaba CORS, Asaba', 'images/appsnlogo.png.png', 'images/NIS.png', 'Asaba, Delta State', 'Asaba', 'Delta State', 'South South', 6.1949978, 6.7165790, NULL, 'ASAB', 'ASAB', 'asaba_cors', 0, NULL, 1, 10, '2026-10-16 11:37:24', '2026-08-12 09:52:07', '2026-10-16 11:37:24'),
(11, 1, 'Warri CORS', 'warri', 'WR', 'Sacredion', 'Welcome to Warri CORS, Warri', 'images/appsnlogo.png.png', 'images/NIS.png', 'Warri, Delta State', 'Warri', 'Delta State', 'South South', 5.5678800, 5.8079104, NULL, 'WARR', 'WARR', 'warri_cors', 0, NULL, 1, 11, '2026-10-16 11:41:41', '2026-08-12 09:52:07', '2026-10-16 11:41:41'),
(12, 1, 'Abakaliki CORS', 'abakaliki', 'EB', 'Sacredion', 'Welcome to Abakaliki CORS, Abakaliki', 'images/slogo.png', 'images/tlogo.png', 'Abakaliki, Ebonyi State', 'Abakaliki', 'Ebonyi State', 'South East', 6.3150608, 8.1202672, NULL, 'ABAK', 'ABAK', 'ebonyi_cors', 0, NULL, 1, 12, '2026-10-16 11:46:43', '2026-08-12 09:52:07', '2026-10-16 11:46:43'),
(13, 1, 'Afikpo CORS', 'afikpo', 'AF', 'Sacredion', 'Welcome to Afikpo CORS, Afikpo', 'images/slogo.png', 'images/tlogo.png', 'Afikpo, Ebonyi State', 'Afikpo', 'Ebonyi State', 'South East', 5.8043941, 7.9140528, NULL, 'AFIK', 'AFIK', 'afik_cors', 0, NULL, 1, 13, '2026-10-16 11:51:09', '2026-08-12 09:52:07', '2026-10-16 11:51:09'),
(14, 1, 'Geosystems CORS', 'edo', 'ED', 'Geosystems', 'Welcome to Geosystems CORS, Benin City', 'images/gslogo.png', 'images/slogo.png', 'Second East Circular Road, Avbiama, Benin City, Edo State', 'Benin City', 'Edo State', 'South South', 6.3338872, 5.6274484, NULL, 'GEOS', 'EDO', 'edo_cors', 0, NULL, 1, 14, '2026-10-16 11:55:36', '2026-08-12 09:52:07', '2026-10-16 11:55:36'),
(15, 1, 'Jos CORS', 'jos', 'PL', 'Sacredion', 'Welcome to Jos CORS, Jos', 'images/appsnlogo.png', 'images/slogo.png', 'Jos, Plateau State', 'Jos', 'Plateau State', 'North Central', 9.9090819, 8.8862455, NULL, 'JOS', 'JOS', 'jos_cors', 0, NULL, 1, 15, '2026-10-16 12:00:08', '2026-08-12 09:52:07', '2026-10-16 12:00:08'),
(16, 1, 'Gidan Kwano CORS', 'minna', 'NG', 'FUT Minna', 'Welcome to Gidan Kwano CORS, Minna', 'images/futlogo.png', 'images/slogo.png', 'Federal University of Technology, Minna, Niger State', 'Minna', 'Niger State', 'North Central', 9.5348836, 6.4484184, NULL, 'FUTm', 'FUTM', 'minna_cors', 0, NULL, 1, 16, '2026-10-16 12:00:44', '2026-08-12 09:52:07', '2026-10-16 12:00:44'),
(17, 1, 'Ondo CORS', 'ondo', 'ON', 'Sacredion', 'Welcome to Ondo CORS, Ondo', 'images/slogo.png', 'images/tlogo.png', 'Ondo State', 'Ondo', 'Ondo State', 'South West', 7.0978797, 4.8263418, NULL, 'ONDO', 'ONDO', 'ondo_cors', 0, NULL, 1, 17, '2026-10-16 12:06:26', '2026-08-12 09:52:07', '2026-10-16 12:06:26'),
(18, 1, 'Osun CORS', 'osun', 'OS', 'Sacredion', 'Welcome to Osun CORS, Osogbo', 'images/slogo.png', 'images/tlogo.png', 'Osogbo, Osun State', 'Osogbo', 'Osun State', 'South West', 7.7525328, 4.5227416, NULL, 'OSUN', 'OSUN', 'osun_cors', 0, NULL, 1, 18, '2026-10-16 12:11:26', '2026-08-12 09:52:07', '2026-10-16 12:11:26'),
(19, 1, 'Ibadan CORS', 'ibadan', 'OY', 'Sacredion', 'Welcome to Ibadan CORS, Ibadan', 'images/slogo.png', 'images/tlogo.png', 'Ibadan, Oyo State', 'Ibadan', 'Oyo State', 'South West', 7.3713863, 3.8362837, NULL, 'IBAD', 'IBAD', 'oyo_cors', 0, NULL, 1, 19, '2026-10-16 12:16:49', '2026-08-12 09:52:07', '2026-10-16 12:16:49'),
(20, 1, 'Port Harcourt CORS', 'portharcourt', 'RI', 'Sacredion', 'Welcome to Port Harcourt CORS, Port Harcourt', 'images/slogo.png', 'images/tlogo.png', 'Port Harcourt, Rivers State', 'Port Harcourt', 'Rivers State', 'South South', 4.8156000, 7.0498000, NULL, 'PORT', 'PORT', 'portharcourt_cors', 0, NULL, 1, 20, '2026-10-16 12:22:07', '2026-08-12 09:52:07', '2026-10-16 12:22:07'),
(21, 1, 'Awka CORS', 'awka', 'AW', 'Sacredion', 'Welcome to Awka CORS, Anambra State', 'images/slogo.png', 'images/tlogo.png', 'Awka, Anambra State', 'Awka', 'Anambra State', 'South East', 6.2311200, 7.0945911, NULL, 'AWKA', 'AWKA', 'anambra_cors', 0, NULL, 1, 21, '2026-10-16 12:27:23', '2026-08-12 09:52:07', '2026-10-16 12:27:23'),
(22, NULL, 'Calabar CORS', 'calabar', 'CR', 'OSG Cross River', 'Welcome to Calabar CORS, Cross River', 'images/slogo.png', 'images/tlogo.png', 'Calabar, Cross River State', 'Calabar', 'Cross River State', 'South South', 4.9706000, 8.3268620, NULL, NULL, NULL, NULL, 1, 'https://www.osgcrossrivercors.net', 1, 9, NULL, '2026-08-12 09:52:07', '2026-08-12 09:52:07'),
(23, NULL, 'AFRIGIST CORS', 'ile-ife', 'CR', 'AFRIGIST CORS', 'Welcome to AFRIGIST CORS, Ile-Ife, Osun', 'images/slogo.png', 'images/tlogo.png', 'Ile-Ife, Osun State', 'ile-ife', 'Osun State', 'South West', 7.5054900, 4.5243150, NULL, NULL, NULL, NULL, 1, 'https://cors.afrigist.org', 1, 23, NULL, '2026-08-12 09:52:07', '2026-08-12 09:52:07'),
(24, 1, 'Lokoja CORS', 'lokoja', 'AB', 'Sacredion', 'Welcome to Lokoja CORS, Kogi State', 'images/slogo.png', 'images/tlogo.png', 'Lokoja, Kogi State', 'Lokoja', 'Kogi State', 'North Central', 7.8016602, 6.7413968, NULL, 'KOGI', 'KOGI', 'lokoja_cors', 0, NULL, 1, 24, '2026-10-16 11:12:44', '2026-08-12 09:52:07', '2026-10-16 11:12:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cors_stations`
--
ALTER TABLE `cors_stations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cors_stations_slug_unique` (`slug`),
  ADD KEY `cors_stations_ftp_account_id_foreign` (`ftp_account_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cors_stations`
--
ALTER TABLE `cors_stations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cors_stations`
--
ALTER TABLE `cors_stations`
  ADD CONSTRAINT `cors_stations_ftp_account_id_foreign` FOREIGN KEY (`ftp_account_id`) REFERENCES `ftp_accounts` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
