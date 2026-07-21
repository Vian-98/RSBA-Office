-- --------------------------------------------------------
-- Host:                         172.17.100.111
-- Server version:               10.11.11-MariaDB-ubu2204 - mariadb.org binary distribution
-- Server OS:                    debian-linux-gnu
-- HeidiSQL Version:             12.20.0.7320
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping data for table office.um_kategori: ~10 rows (approximately)
REPLACE INTO `um_kategori` (`id`, `nama`, `deskripsi`, `prefix`, `created_at`, `updated_at`) VALUES
	(1, 'BHP', 'Barang Habis Pakai', 'BHP', '2025-07-31 16:50:23', '2025-07-31 16:50:23'),
	(2, 'ATK', 'Alat Tulis Kantor', 'ATK', '2025-10-27 10:21:19', '2025-10-27 10:21:19'),
	(3, 'Komputer', 'Barang komputer dan perangkat komputer', 'COM', '2025-10-27 10:22:40', '2025-12-23 14:44:13'),
	(4, 'Printer', 'Printer', 'PRNT', '2025-10-27 10:23:27', '2025-12-23 14:44:19'),
	(5, 'Rumah Tangga', 'Barang kebutuhan rumah tangga', 'RT', '2025-10-27 10:24:11', '2025-12-23 14:43:56'),
	(6, 'Laptop', 'Laptop', 'LP', '2025-12-23 14:37:32', '2025-12-23 14:37:32'),
	(7, 'Material Sipil', 'Barang material sipil', 'MSP', '2025-12-23 14:40:48', '2025-12-23 14:40:48'),
	(8, 'Material Listrik', 'Barang material listrik', 'MLS', '2025-12-23 14:41:10', '2025-12-23 14:41:10'),
	(9, 'Elektronik', 'Barang elektronik', 'ELC', '2025-12-23 14:42:05', '2025-12-23 14:42:05'),
	(10, 'Alat kesehatan', 'Barang alat kesehatan', 'ALK', '2025-12-23 14:42:35', '2025-12-23 14:42:35');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
