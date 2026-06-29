-- phpMyAdmin SQL Dump
-- version 5.2.0
-- Host: 127.0.0.1
-- Generation Time: Jun 23, 2026
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `lost_found_db`
--
CREATE DATABASE IF NOT EXISTS `lost_found_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `lost_found_db`;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('mahasiswa','petugas','admin') NOT NULL DEFAULT 'mahasiswa',
  `foto_profil` varchar(255) DEFAULT 'https://images.unsplash.com/photo-1599566150163-29194dcaad36?auto=format&fit=crop&q=80&w=200&h=200',
  `no_hp` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `users`
INSERT INTO `users` (`id`, `nama`, `username`, `email`, `password`, `role`, `foto_profil`, `no_hp`) VALUES
(1, 'Indah Sekali', 'indah', 'indah@webmail.umm.ac.id', 'password123', 'mahasiswa', 'https://images.unsplash.com/photo-1599566150163-29194dcaad36?auto=format&fit=crop&q=80&w=200&h=200', '081234567890'),
(2, 'Petugas Jaga', 'petugas1', 'petugas@umm.ac.id', 'password123', 'petugas', 'https://images.unsplash.com/photo-1599566150163-29194dcaad36?auto=format&fit=crop&q=80&w=200&h=200', '081236336675'),
(3, 'Super Admin', 'admin', 'admin@umm.ac.id', 'password123', 'admin', 'https://images.unsplash.com/photo-1599566150163-29194dcaad36?auto=format&fit=crop&q=80&w=200&h=200', '081234567890');

-- --------------------------------------------------------

--
-- Table structure for table `found_items`
--
CREATE TABLE `found_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_barang` varchar(100) NOT NULL,
  `kategori` enum('valuable','non-valuable') NOT NULL,
  `deskripsi_singkat` varchar(255) NOT NULL,
  `deskripsi_lengkap` text NOT NULL,
  `lokasi` varchar(100) NOT NULL,
  `waktu_ditemukan` varchar(100) NOT NULL,
  `status` enum('available','taken') NOT NULL DEFAULT 'available',
  `foto` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `found_items`
INSERT INTO `found_items` (`id`, `nama_barang`, `kategori`, `deskripsi_singkat`, `deskripsi_lengkap`, `lokasi`, `waktu_ditemukan`, `status`, `foto`) VALUES
(1, 'Dompet Hitam', 'non-valuable', 'Warna Hitam, Ditemukan di Tangga gkb 1...', 'Dompet berwarna Coklat/Hitam, dengan isi KTP atas nama Zainal Siregar Kapalaud, uang Rp.200.000, STNK sepeda motor Vario Hitam.', 'TANGGA TENGAH LANTAI 2 KE 3, DI GKB 1.', '19 Mei 2026 13:00', 'available', 'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&q=80&w=800'),
(2, 'Kacamata', 'non-valuable', 'Warna Silver, Ditemukan di area tangga', 'Kacamata Bulat Frame Besi warna Silver, ditemukan di Toilet Cowok GKB 4.', 'Toilet Cowok, Lantai 3 GKB 4', '19 Mei 2026 13:00', 'available', 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&q=80&w=800'),
(3, 'Jam Tangan Rollex', 'valuable', 'Warna Silver Titanium, Ditemukan di Tangga...', 'Jam tangan berwarna silver titanium. Ditemukan di tangga lantai 1.', 'Tangga Lantai 1', '19 Mei 2026 10:00', 'available', 'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&q=80&w=800'),
(4, 'HP iPhone 17 Pink', 'valuable', 'Warna Pink, Ditemukan di area tangga', 'HP iPhone 17 warna pink. Ada casing bening.', 'Area Tangga GKB 2', '19 Mei 2026 09:00', 'taken', 'https://images.unsplash.com/photo-1605236453806-6ff36851218e?auto=format&fit=crop&q=80&w=800');

-- --------------------------------------------------------

--
-- Table structure for table `lost_reports`
--
CREATE TABLE `lost_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `lokasi_hilang` varchar(100) NOT NULL,
  `waktu_hilang` varchar(100) NOT NULL,
  `deskripsi` text NOT NULL,
  `kontak` varchar(50) NOT NULL,
  `lampiran` varchar(255) DEFAULT NULL,
  `status_verifikasi` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table `lost_reports`
INSERT INTO `lost_reports` (`id`, `user_id`, `nama_barang`, `lokasi_hilang`, `waktu_hilang`, `deskripsi`, `kontak`, `lampiran`, `status_verifikasi`) VALUES
(1, 1, 'Dompet Putih', 'Stadion UMM', '30 Mei 2026 Minggu, (07.00 WIB)', 'Disaat saya olahraga di Stadion UMM, saya kehilangan dompet berwarna Putih, dimana saya tidak terasa dompet tersebut jatuh.', '089877798308', 'NIM Pada KTM 202410370110000', 0),
(2, 1, 'STNK Motor', 'Area Parkir GKB 1', '1 Juni 2026', 'STNK No N 4711 S, Sepeda BAET Honda.', '081234567890', '-', 0),
(3, 1, 'Kunci Motor', 'Kantin', '2 Juni 2026', 'Kunci motor dengan gantungan kunci bertulis Menuju Surga.', '081234567890', '-', 1);

-- --------------------------------------------------------

--
-- Table structure for table `pickups`
--
CREATE TABLE `pickups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `found_item_id` int(11) NOT NULL,
  `nama_pengambil` varchar(100) NOT NULL,
  `nim` varchar(50) NOT NULL,
  `fakultas` varchar(100) NOT NULL,
  `nama_petugas` varchar(100) NOT NULL,
  `waktu_pengambilan` varchar(100) NOT NULL,
  `foto_ktp` varchar(255) NOT NULL,
  `foto_bukti` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`found_item_id`) REFERENCES `found_items`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
