-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 22, 2026 at 01:21 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dagangin`
--

-- --------------------------------------------------------

--
-- Table structure for table `ads`
--

CREATE TABLE `ads` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(15,2) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ads`
--

INSERT INTO `ads` (`id`, `user_id`, `category_id`, `title`, `description`, `price`, `location`, `created_at`) VALUES
(2, 1, 1, 'Lexus RX 270 (2014)', 'Lexus RX 270 Matic Hitam 2022\r\nFull original cat\r\nMesin kering dan terawat\r\nKilo meter 140rb record bengkel resmi Lexus\r\nInterior sangat bersih bebas bau rokok\r\nJok kulit + heater seat\r\nAC dingin\r\nPower back door\r\nKaki kaki senyap tidak ada bunyi\r\nPajak ON\r\nGaransi bebas Laka dan banjir\r\nKondisi tidak ada PR\r\nReady to party\r\nMengapa harus membeli unit kendaraan di KJM_ Mobil :\r\n1. Unit mobil mobil bekualitas\r\n2. Unit kendaraan sudah di inspeksi\r\nOleh ocospector\r\n3. Surat-surat ready dan dijamin\r\nkeabshannya\r\n4.Pelayanan Prioritas kepada konsumen\r\nKhusus Harga Paket kredit DP minim Rp190jt\r\nPaket kredit DP Minim\r\n4 tahun DP 10jt angsuran Rp. 8.1jt\r\n5 tahun DP 10jt angsuran Rp. 7.2jt', 190000000.00, 'Bandung', '2026-02-22 18:30:16'),
(3, 2, 8, 'PS5 digital - disk Second Mulus Like New imei tembus', 'Toko : FOG (Factory Of Game)\r\n\r\njalan raya kebayoran lama no 9 Factory of game depan petshop Pal 7\r\n\r\njakarta selatan\r\n\r\njam operasional jam 13:00 - 04:00 subuh\r\n\r\nDisk edition / Digital Edition\r\n\r\nKondisi :\r\n\r\nSecond Mulus like new Ex Inter\r\n\r\nSudah lolos sensor quality\r\n\r\nImei tembus unit mesin dan dus\r\n\r\nMesin Void bukan bongkaran atau servicesan\r\n\r\nSpek :\r\n\r\n- unit console ps5 cfi 10/11/12\r\n\r\n- Ds5 original\r\n\r\n- Kabel charging ps5\r\n\r\n- Kabel power\r\n\r\n- Kabel hdmi\r\n\r\n- Standing stand ps5\r\n\r\nFirmware update\r\n\r\nEx Garansi Jepang\r\n\r\nHarga :\r\n\r\nDigital : 6,3\r\n\r\nDisk : 6,8\r\n\r\nGaransi : 1 minggu', 6300000.00, 'Jakarta', '2026-02-22 19:13:47');

-- --------------------------------------------------------

--
-- Table structure for table `ad_images`
--

CREATE TABLE `ad_images` (
  `id` int(11) NOT NULL,
  `ad_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ad_images`
--

INSERT INTO `ad_images` (`id`, `ad_id`, `image_path`) VALUES
(4, 2, 'uploads/ads/img_699ae8c8052f2.png'),
(5, 2, 'uploads/ads/img_699ae8c8054e8.png'),
(6, 2, 'uploads/ads/img_699ae8c80568a.png'),
(7, 3, 'uploads/ads/img_699af2fbb2635.png'),
(8, 3, 'uploads/ads/img_699af2fbb27d5.png');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `icon` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `icon`) VALUES
(1, 'Mobil', 'bi-car-front'),
(2, 'Motor', 'bi-bicycle'),
(3, 'Properti', 'bi-house'),
(4, 'Elektronik', 'bi-cpu'),
(5, 'Komputer', 'bi-laptop'),
(6, 'Fashion Pria', 'bi-bag'),
(7, 'Fashion Wanita', 'bi-handbag'),
(8, 'Hobi & Game', 'bi-controller'),
(9, 'Olahraga', 'bi-trophy'),
(10, 'Kesehatan', 'bi-heart-pulse'),
(11, 'Kecantikan', 'bi-star'),
(12, 'Rumah Tangga', 'bi-house-heart'),
(13, 'Makanan & Minuman', 'bi-cup-hot'),
(14, 'Jasa', 'bi-briefcase'),
(15, 'Hewan Peliharaan', 'bi-heart'),
(16, 'Lainnya', 'bi-three-dots');

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `name`, `created_at`) VALUES
(80, 'Jakarta', '2026-02-22 16:31:49'),
(81, 'Surabaya', '2026-02-22 16:31:49'),
(82, 'Bandung', '2026-02-22 16:31:49'),
(83, 'Medan', '2026-02-22 16:31:49'),
(84, 'Semarang', '2026-02-22 16:31:49'),
(85, 'Makassar', '2026-02-22 16:31:49'),
(86, 'Palembang', '2026-02-22 16:31:49'),
(87, 'Tangerang', '2026-02-22 16:31:49'),
(88, 'Depok', '2026-02-22 16:31:49'),
(89, 'Bekasi', '2026-02-22 16:31:49'),
(90, 'Bogor', '2026-02-22 16:31:49'),
(91, 'Batam', '2026-02-22 16:31:49'),
(92, 'Pekanbaru', '2026-02-22 16:31:49'),
(93, 'Bandar Lampung', '2026-02-22 16:31:49'),
(94, 'Malang', '2026-02-22 16:31:49'),
(95, 'Yogyakarta', '2026-02-22 16:31:49'),
(96, 'Solo', '2026-02-22 16:31:49'),
(97, 'Denpasar', '2026-02-22 16:31:49'),
(98, 'Balikpapan', '2026-02-22 16:31:49'),
(99, 'Samarinda', '2026-02-22 16:31:49'),
(100, 'Pontianak', '2026-02-22 16:31:49'),
(101, 'Manado', '2026-02-22 16:31:49'),
(102, 'Mataram', '2026-02-22 16:31:49'),
(103, 'Kupang', '2026-02-22 16:31:49'),
(104, 'Jayapura', '2026-02-22 16:31:49'),
(105, 'Ambon', '2026-02-22 16:31:49'),
(106, 'Ternate', '2026-02-22 16:31:49'),
(107, 'Kendari', '2026-02-22 16:31:49'),
(108, 'Palu', '2026-02-22 16:31:49'),
(109, 'Gorontalo', '2026-02-22 16:31:49'),
(110, 'Jambi', '2026-02-22 16:31:49'),
(111, 'Pangkal Pinang', '2026-02-22 16:31:49'),
(112, 'Tanjung Pinang', '2026-02-22 16:31:49'),
(113, 'Banda Aceh', '2026-02-22 16:31:49'),
(114, 'Padang', '2026-02-22 16:31:49'),
(115, 'Bukittinggi', '2026-02-22 16:31:49'),
(116, 'Dumai', '2026-02-22 16:31:49'),
(117, 'Tanjung Balai Karimun', '2026-02-22 16:31:49');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `whatsapp`, `password`, `created_at`) VALUES
(1, 'Faisal Hawari', 'faisalhawari@gmail.com', '081224017174', '$2y$10$cgd9WrkwaTu0Z6CkCRgZt.ZwabO2Vs1NQ.2uYbLId1zHLcn05CB8u', '2026-02-22 18:01:41'),
(2, 'Steven WIliam', 'stevenwiliam@gmail.com', '088463829362', '$2y$10$IqTUcP9r/x0urKhTqBnj0uIlQquvDWyNokkoZYu/QNOeZvEOfbFGO', '2026-02-22 19:06:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ads`
--
ALTER TABLE `ads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `ad_images`
--
ALTER TABLE `ad_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ad_id` (`ad_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

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
-- AUTO_INCREMENT for table `ads`
--
ALTER TABLE `ads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ad_images`
--
ALTER TABLE `ad_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ads`
--
ALTER TABLE `ads`
  ADD CONSTRAINT `ads_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `ads_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `ad_images`
--
ALTER TABLE `ad_images`
  ADD CONSTRAINT `ad_images_ibfk_1` FOREIGN KEY (`ad_id`) REFERENCES `ads` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
