-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 20, 2024 at 04:48 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dispen`
--

-- --------------------------------------------------------

--
-- Table structure for table `angkatan`
--

CREATE TABLE `angkatan` (
  `id` int(11) NOT NULL,
  `tahun` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `angkatan`
--

INSERT INTO `angkatan` (`id`, `tahun`) VALUES
(1, 2019),
(2, 2020),
(3, 2021),
(4, 2022);

-- --------------------------------------------------------

--
-- Table structure for table `dosen`
--

CREATE TABLE `dosen` (
  `id` int(11) NOT NULL,
  `nama_dosen` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nip` varchar(20) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dosen`
--

INSERT INTO `dosen` (`id`, `nama_dosen`, `email`, `nip`, `image`) VALUES
(1, 'Dian Sa\'adillah Maylawati, S.Kom., M.T., Ph.D', 'diansaadillah@univ.ac.id', '198905262019032023', 'save.png'),
(2, 'Eki Ahmad Zaki Hamidi, S.T., M.T.', 'sumitraadriansyah@gmail.com', '197602222011011008', 'guibg.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `jurusan`
--

CREATE TABLE `jurusan` (
  `id` int(11) NOT NULL,
  `nama_jurusan` varchar(100) NOT NULL,
  `ketua_jurusan_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jurusan`
--

INSERT INTO `jurusan` (`id`, `nama_jurusan`, `ketua_jurusan_id`) VALUES
(1, 'Teknik Informatika', 1),
(2, 'Teknik Elektro', 2);

-- --------------------------------------------------------

--
-- Table structure for table `pengajuan`
--

CREATE TABLE `pengajuan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `angkatan_id` int(11) DEFAULT NULL,
  `dosen_id` int(11) DEFAULT NULL,
  `alasan` text NOT NULL,
  `tanggal_pengajuan` date NOT NULL,
  `dokumen_lampiran` varchar(255) NOT NULL,
  `status` enum('pending','disetujui','ditolak') DEFAULT 'pending',
  `tanggal_disetujui` date DEFAULT NULL,
  `keputusan_admin` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `nama_lengkap` varchar(255) NOT NULL,
  `nim` varchar(50) NOT NULL,
  `angkatan` varchar(4) NOT NULL,
  `email` varchar(255) NOT NULL,
  `jurusan` varchar(100) NOT NULL,
  `jurusan_id` int(11) DEFAULT NULL,
  `wakil_dekan_id` int(11) DEFAULT NULL,
  `tanggal_acc_ketua_jurusan` date DEFAULT NULL,
  `tanggal_acc_wakil_dekan` date DEFAULT NULL,
  `status_wadek` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengajuan`
--

INSERT INTO `pengajuan` (`id`, `user_id`, `angkatan_id`, `dosen_id`, `alasan`, `tanggal_pengajuan`, `dokumen_lampiran`, `status`, `tanggal_disetujui`, `keputusan_admin`, `updated_at`, `nama_lengkap`, `nim`, `angkatan`, `email`, `jurusan`, `jurusan_id`, `wakil_dekan_id`, `tanggal_acc_ketua_jurusan`, `tanggal_acc_wakil_dekan`, `status_wadek`) VALUES
(19, 3, NULL, NULL, 'mengikuti job fair', '2024-11-14', '2022-09-19.png', 'disetujui', NULL, NULL, '2024-11-07 03:58:30', 'Sumitra Adriansyah', '1217050137', '2021', 'sumitraadriansyah@gmail.com', 'Teknik Informatika', 1, NULL, '2024-11-07', '2024-11-07', 'disetujui final'),
(21, 3, NULL, NULL, 'izin', '2024-11-06', '2022-06-28 (3).png', 'disetujui', NULL, NULL, '2024-11-07 04:01:07', 'Sumitra Adriansyah', '1217050137', '2021', 'sumitraadriansyah@gmail.com', 'Teknik Informatika', 1, NULL, '2024-11-07', '2024-11-07', 'disetujui final'),
(23, 3, NULL, NULL, 'izin', '2024-11-06', '1663260369-picsay.jpg', 'disetujui', NULL, NULL, '2024-11-10 11:53:08', 'sumitra', '123456', '2021', 'sumitraadriansyah@gmail.com', '', 1, NULL, '2024-11-09', '2024-11-10', 'disetujui final'),
(24, 3, NULL, NULL, 'sakit', '2024-11-06', '166326567780369-picsay.jpg', 'disetujui', NULL, NULL, '2024-11-10 14:25:45', 'adrian', '12345', '2021', 'sumitraadriansyah@gmail.com', '', 2, NULL, '2024-11-07', '2024-11-10', 'disetujui final'),
(28, 3, NULL, NULL, 'izinnnnnnnnnnn', '2024-11-07', '672c928f1b100_clmsCertificate.pdf', 'disetujui', NULL, NULL, '2024-11-14 03:35:18', 'Siti Jahro', '1217050137', '2023', 'sumitraadriansyah@gmail.com', '', 2, NULL, '2024-11-14', NULL, 'pending'),
(29, 3, NULL, NULL, 'cek', '2024-11-09', '672f6ba3a9895_diagram state tbo(1).pdf', 'disetujui', NULL, NULL, '2024-11-10 11:42:42', 'Sumitra Adriansyah', '1217050137', '2021', 'sumitraadriansyah@gmail.com', '', 1, NULL, '2024-11-09', '2024-11-10', 'disetujui final'),
(30, 3, NULL, NULL, 'tes', '2024-11-09', '672f6e4c25481_diagram state tbo(2).pdf', 'disetujui', NULL, NULL, '2024-11-10 12:08:33', 'Sumitra Adriansyah', '1217050137', '2021', 'sumitraadriansyah@gmail.com', '', 1, NULL, '2024-11-09', '2024-11-10', 'ditolak'),
(34, 3, NULL, NULL, 'iziziziz', '2024-11-09', '672f7809d6a56_JURNAL HADITS TUGAS.pdf', 'disetujui', NULL, NULL, '2024-11-09 15:09:10', 'Sumitra Adriansyah', '1217050137', '2021', 'sumitraadriansyah@gmail.com', '', 1, NULL, '2024-11-09', NULL, 'pending'),
(35, 14, NULL, NULL, 'ixinixinxix', '2024-11-10', '6730b7d26a11e_utse.pdf', 'disetujui', NULL, NULL, '2024-11-10 13:42:02', 'Siti Jahro Maulidiyah', '1217050135', '2021', 'sitijauhro@gmail.com', '', 1, NULL, '2024-11-10', NULL, 'pending'),
(36, 3, NULL, NULL, 'c vfkjvfjvnfjv ', '2024-11-10', '6730c236c755a_quiz 9.pdf', 'disetujui', NULL, NULL, '2024-11-10 14:25:27', 'Sumitra Adriansyah', '1217050137', '2021', 'sumitraadriansyah@gmail.com', '', 1, NULL, '2024-11-10', NULL, 'pending'),
(37, 14, NULL, NULL, 'cjnjfnv', '2024-11-14', '6734fbd814a7e_72044-245804-1-PB id.pdf', 'pending', NULL, NULL, '2024-11-13 19:19:52', 'Siti Jahro Maulidiyah', '1217050135', '2023', 'sitijauhro@gmail.com', '', 2, NULL, NULL, NULL, NULL),
(38, 3, NULL, NULL, 'izin lomba tingkat nasional', '2024-11-14', '6735723e4ea5a_72044-245804-1-PB id.pdf', 'pending', NULL, NULL, '2024-11-14 03:45:02', 'Sumitra Adriansyah', '1217050137', '2021', 'sumitraadriansyah@gmail.com', '', 1, NULL, NULL, NULL, NULL),
(39, 14, NULL, NULL, 'izin lomba tingkat nasional', '2024-11-14', '673572d146256_72044-245804-1-PB id.pdf', 'disetujui', NULL, NULL, '2024-11-14 03:54:58', 'Siti Jahro Maulidiyah', '1217050135', '2021', 'szmaulidiyah15@gmail.com', '', 1, NULL, '2024-11-14', '2024-11-14', 'disetujui final'),
(40, 14, NULL, NULL, 'izin lomba', '2024-11-14', '6735750527d71_72044-245804-1-PB.pdf', 'pending', NULL, NULL, '2024-11-14 03:56:53', 'Siti Jahro Maulidiyah', '1217050135', '2021', 'szmaulidiyah15@gmail.com', '', 1, NULL, NULL, NULL, NULL),
(41, 14, NULL, NULL, 'izin', '2024-11-14', '67357c88ed7fa_72044-245804-1-PB id.pdf', 'disetujui', NULL, NULL, '2024-11-14 04:29:47', 'Siti Jahro Maulidiyah', '1217050135', '2021', 'szmaulidiyah15@gmail.com', '', 1, NULL, '2024-11-14', NULL, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `role` enum('mahasiswa','admin','kajur','wakil_dekan') DEFAULT 'mahasiswa',
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `dosen_id` int(11) DEFAULT NULL,
  `wakil_dekan_id` int(11) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `username`, `password`, `role`, `email`, `created_at`, `dosen_id`, `wakil_dekan_id`, `reset_token`) VALUES
(1, NULL, 'mahasiswa1', '12345', 'mahasiswa', 'mahasiswa1@univ.ac.id', '2024-10-27 17:35:12', NULL, NULL, NULL),
(2, NULL, 'admin1', '$2y$10$5Uk1QauK5XyB5g5OFjAy7eBedcgTzzvoKSZSEYCabP1KjhA8RKrl6', 'admin', 'admin@univ.ac.id', '2024-10-27 17:35:12', NULL, NULL, NULL),
(3, 'Sumitra Adriansyah', '1217050137', '$2y$10$jGe5ki.o6cyLWDkquovk8ON4M0j2hOiJ82BisF8S4EQICH.aMwUFq', 'mahasiswa', 'sumitraadriansyah@gmail.com', '2024-11-06 05:02:38', NULL, NULL, NULL),
(11, NULL, '198905262019032023', '$2y$10$VfsOUKZrkLsg4hO7SWjlxui/1FcmGZ1vHidJJFFgPsjBNrqeauRTS', 'kajur', 'kajur01@university.com', '2024-11-06 12:35:20', 1, NULL, NULL),
(12, NULL, '197602222011011008', '$2y$10$RhIZd55m4uinXLknAlrhleFv5d5EcdoEKxOiB4lEwlFH6fEAsIify', 'kajur', 'kajur01@university.com', '2024-11-06 12:36:41', 2, NULL, NULL),
(13, NULL, '197909302009121002', '$2y$10$B7PyRPHKOtKqE81YYcsxuep14IawaNFuXQ/iUzSSvWnajzK.RLoIS', 'wakil_dekan', 'wadek01@university.com', '2024-11-06 05:38:35', NULL, 1, NULL),
(14, 'Siti Jahro Maulidiyah', '1217050135', '$2y$10$M9i2NaVEMKxcxO4HdxiNvepLd.A.plX2W9CORJvu1k18wQhrVQDXi', 'mahasiswa', 'szmaulidiyah15@gmail.com', '2024-11-10 13:22:15', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wakil_dekan`
--

CREATE TABLE `wakil_dekan` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nip` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wakil_dekan`
--

INSERT INTO `wakil_dekan` (`id`, `nama`, `email`, `nip`) VALUES
(1, 'Undang Syaripudin, M.Kom.', 'wakildekan@univ.ac.id', '197909302009121002');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `angkatan`
--
ALTER TABLE `angkatan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `dosen`
--
ALTER TABLE `dosen`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jurusan`
--
ALTER TABLE `jurusan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ketua_jurusan_id` (`ketua_jurusan_id`);

--
-- Indexes for table `pengajuan`
--
ALTER TABLE `pengajuan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `angkatan_id` (`angkatan_id`),
  ADD KEY `dosen_id` (`dosen_id`),
  ADD KEY `pengajuan_ibfk_4` (`jurusan_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_dosen_id` (`dosen_id`),
  ADD KEY `fk_wakil_dekan_id` (`wakil_dekan_id`);

--
-- Indexes for table `wakil_dekan`
--
ALTER TABLE `wakil_dekan`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `angkatan`
--
ALTER TABLE `angkatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `dosen`
--
ALTER TABLE `dosen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `jurusan`
--
ALTER TABLE `jurusan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pengajuan`
--
ALTER TABLE `pengajuan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `wakil_dekan`
--
ALTER TABLE `wakil_dekan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `jurusan`
--
ALTER TABLE `jurusan`
  ADD CONSTRAINT `jurusan_ibfk_1` FOREIGN KEY (`ketua_jurusan_id`) REFERENCES `dosen` (`id`);

--
-- Constraints for table `pengajuan`
--
ALTER TABLE `pengajuan`
  ADD CONSTRAINT `pengajuan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `pengajuan_ibfk_2` FOREIGN KEY (`angkatan_id`) REFERENCES `angkatan` (`id`),
  ADD CONSTRAINT `pengajuan_ibfk_3` FOREIGN KEY (`dosen_id`) REFERENCES `dosen` (`id`),
  ADD CONSTRAINT `pengajuan_ibfk_4` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_dosen_id` FOREIGN KEY (`dosen_id`) REFERENCES `dosen` (`id`),
  ADD CONSTRAINT `fk_wakil_dekan_id` FOREIGN KEY (`wakil_dekan_id`) REFERENCES `wakil_dekan` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
