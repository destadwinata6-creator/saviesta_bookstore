-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 07, 2026 at 01:05 PM
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
-- Database: `saviesta`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `nama`, `email`, `password`, `created_at`) VALUES
(1, 'Administrator Saviesta', 'admin@gmail.com', 'admin123', '2026-10-24 11:29:16');

-- --------------------------------------------------------

--
-- Table structure for table `buku`
--

CREATE TABLE `buku` (
  `id` int(11) NOT NULL,
  `kategori_id` int(11) DEFAULT NULL,
  `judul` varchar(150) NOT NULL,
  `penulis` varchar(100) NOT NULL,
  `harga` int(11) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `deskripsi` text DEFAULT NULL,
  `cover` varchar(20) DEFAULT '#42553A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buku`
--

INSERT INTO `buku` (`id`, `kategori_id`, `judul`, `penulis`, `harga`, `stok`, `deskripsi`, `cover`) VALUES
(29, 3, 'Namaku Alam', 'Leila S. Chudori', 89000, 20, 'Novel pilihan Saviesta.', 'q2.jpg'),
(30, 1, 'Cantik Itu Luka', 'Eka Kurniawan', 150000, 0, 'Novel Indonesia pilihan.', 'c1.jpg'),
(31, 2, 'Malam Terakhir', 'Leila S. Chudori', 120000, 0, 'Buku non-fiksi pilihan.', 'f5.jpg'),
(32, 2, 'Dilan 1990', 'Pidi Baiq', 250000, 15, 'Novel populer pilihan.', 'HS1.jpg'),
(37, 1, 'Cara Ke Imo Dalam 2 hari', 'kyelee', 500000, 10, 'buku untuk orang orang supaya bisa cepet imo', 'IM2.jpeg'),
(38, 2, 'The Amazing Spiderman 2\r\n', 'Desta DN', 250000, 123, 'Peter Parker berusaha menjalani hidup sebagai Spider-Man sekaligus mempertahankan hubungannya dengan Gwen Stacy. Namun, kemunculan Electro dan rahasia masa lalu keluarganya membuat hidup Peter semakin rumit.', 'SPIDERMAN.jpeg'),
(39, 1, 'In Another Life', 'Grate B.', 150000, 452, 'I Love UU', 'Lov.jpeg'),
(40, 2, 'Mon Amourr', 'dees', 500000, 9, 'Sebuah cerita tentang jatuh cinta, bertahan, melepaskan, dan belajar menghargai diri sendiri.Buku ini menceritakan kisah seseorang yang rela melakukan apa saja demi orang yang dicintainya. Tentang perasaan yang begitu besar, harapan yang terus dipertahankan, dan perjuangan mendapatkan cinta yang belum tentu terbalas.', 'mow.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id` int(11) NOT NULL,
  `pesanan_id` int(11) NOT NULL,
  `buku_id` int(11) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id`, `pesanan_id`, `buku_id`, `jumlah`, `harga`) VALUES
(8, 6, 31, 1, 120000),
(9, 6, 32, 3, 250000),
(10, 7, 30, 1, 150000),
(11, 8, 31, 1, 120000),
(12, 8, 32, 1, 250000),
(13, 9, 31, 4, 120000),
(14, 10, 32, 1, 250000),
(15, 13, 32, 1, 250000),
(16, 14, 30, 1, 150000),
(17, 14, 32, 1, 250000),
(18, 15, 31, 1, 120000),
(19, 15, 32, 2, 250000),
(20, 16, 30, 11, 150000),
(21, 17, 40, 1, 500000),
(22, 18, 38, 1, 250000),
(23, 19, 38, 1, 250000);

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `nama` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id`, `nama`) VALUES
(1, 'Fiksi'),
(2, 'Non-Fiksi'),
(3, 'Puisi');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `telepon` varchar(30) NOT NULL,
  `metode` varchar(50) NOT NULL,
  `subtotal` int(11) NOT NULL,
  `ongkir` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'Menunggu Pembayaran',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `bukti_transfer` varchar(255) DEFAULT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id`, `user_id`, `nama`, `alamat`, `telepon`, `metode`, `subtotal`, `ongkir`, `total`, `status`, `created_at`, `bukti_transfer`, `bukti_pembayaran`) VALUES
(6, 1, 'Desta Dwi Natta', 'hhh', '08712713631', 'Transfer Bank', 870000, 15000, 885000, 'Pembayaran Berhasil', '2026-08-24 12:32:35', NULL, NULL),
(7, 1, 'Desta Dwi Natta', 'ffff', '08712713631', 'COD', 150000, 15000, 165000, 'Pembayaran Berhasil', '2026-08-24 12:34:44', NULL, NULL),
(8, 1, 'Desta Dwi Natta', 'fffff', '08712713631', 'COD', 370000, 15000, 385000, 'Pembayaran Berhasil', '2026-08-24 12:40:59', NULL, NULL),
(9, 1, 'Desta Dwi Natta', 'kkkk', '08712713631', 'GoPay', 480000, 15000, 495000, 'Pembayaran Berhasil', '2026-08-24 12:44:07', NULL, NULL),
(10, 1, 'Desta Dwi Natta', 'jl.cawang', '08712713631', 'COD', 250000, 15000, 265000, 'Pembayaran Berhasil', '2026-08-26 23:31:51', NULL, NULL),
(11, 1, 'Desta Dwi Natta', 'dssdc', '08712713631', 'Dana', 240000, 15000, 255000, 'Pembayaran Berhasil', '2026-08-27 00:02:00', NULL, NULL),
(12, 1, 'Desta Dwi Natta', 'dssdc', '08712713631', 'Dana', 240000, 15000, 255000, 'Pembayaran Berhasil', '2026-08-27 00:02:25', NULL, NULL),
(13, 1, 'Desta Dwi Natta', 'ghdd', '08712713631', 'GoPay', 250000, 15000, 265000, 'Pembayaran Berhasil', '2026-08-27 00:02:54', NULL, NULL),
(14, 1, 'fifi aulia', 'jln keren', '08712713631', 'COD', 400000, 15000, 415000, 'Pembayaran Berhasil', '2026-09-03 01:24:17', NULL, NULL),
(15, 1, 'Desta Dwi Natta', 'jl keren', '08712713631', 'GoPay', 620000, 15000, 635000, 'Dibatalkan', '2026-09-03 01:35:20', NULL, NULL),
(16, 1, 'sapira putri cesa', 'JL bulok', '08712713631', 'COD', 1650000, 15000, 1665000, 'Pembayaran Berhasil', '2026-10-24 10:41:13', NULL, NULL),
(17, 1, 'Desta Dwi Natta', 'jk.pondok bambuu', '08712713631', 'Transfer Bank', 500000, 15000, 515000, 'Pembayaran Berhasil', '2026-11-06 13:21:12', NULL, NULL),
(18, 1, 'Desta Dwi Natta', 'dfse', '08712713631', 'Transfer Bank', 250000, 15000, 265000, 'Pembayaran Berhasil', '2026-11-06 13:23:45', NULL, NULL),
(19, 1, 'Desta Dwi Natta', 'scwe', '08712713631', 'Transfer Bank', 250000, 15000, 265000, 'Pembayaran Berhasil', '2026-11-06 13:32:35', NULL, 'uploads/bukti_pembayaran/bukti_1_1793971955_eb328f6c.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `nama`, `email`, `password`, `created_at`) VALUES
(1, 'desta dwi natta', 'destadwintta@gmail.com', 'desta11', '2026-08-23 07:50:27');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kategori_id` (`kategori_id`);

--
-- Indexes for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pesanan_id` (`pesanan_id`),
  ADD KEY `buku_id` (`buku_id`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pesanan_ibfk_1` (`user_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

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
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `buku`
--
ALTER TABLE `buku`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `buku`
--
ALTER TABLE `buku`
  ADD CONSTRAINT `buku_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`);

--
-- Constraints for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_pesanan_ibfk_2` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`);

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
