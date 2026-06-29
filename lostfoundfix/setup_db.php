<?php
$host = 'localhost';
$username = 'root';
$password = '';

try {
    // 1. Connect without specifying a database first
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Drop the old database if it exists to ensure a clean slate
    $pdo->exec("DROP DATABASE IF EXISTS lost_found_db");
    
    // 3. Create the database fresh
    $pdo->exec("CREATE DATABASE lost_found_db DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    $pdo->exec("USE lost_found_db");

    // 4. Create Tables
    $pdo->exec("
        CREATE TABLE users (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            nama VARCHAR(100) NOT NULL,
            username VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('mahasiswa','petugas','admin') NOT NULL DEFAULT 'mahasiswa',
            foto_profil VARCHAR(255) DEFAULT 'https://images.unsplash.com/photo-1599566150163-29194dcaad36?auto=format&fit=crop&q=80&w=200&h=200',
            no_hp VARCHAR(20) DEFAULT NULL,
            UNIQUE KEY email (email),
            UNIQUE KEY username (username)
        ) ENGINE=InnoDB;
    ");

    $pdo->exec("
        CREATE TABLE found_items (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            nama_barang VARCHAR(100) NOT NULL,
            kategori ENUM('valuable','non-valuable') NOT NULL,
            deskripsi_singkat VARCHAR(255) NOT NULL,
            deskripsi_lengkap TEXT NOT NULL,
            lokasi VARCHAR(100) NOT NULL,
            waktu_ditemukan VARCHAR(100) NOT NULL,
            status ENUM('available','taken') NOT NULL DEFAULT 'available',
            foto VARCHAR(255) DEFAULT NULL
        ) ENGINE=InnoDB;
    ");

    $pdo->exec("
        CREATE TABLE lost_reports (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id INT(11) NOT NULL,
            nama_barang VARCHAR(100) NOT NULL,
            lokasi_hilang VARCHAR(100) NOT NULL,
            waktu_hilang VARCHAR(100) NOT NULL,
            deskripsi TEXT NOT NULL,
            kontak VARCHAR(50) NOT NULL,
            lampiran VARCHAR(255) DEFAULT NULL,
            status_verifikasi TINYINT(1) NOT NULL DEFAULT 0,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;
    ");

    $pdo->exec("
        CREATE TABLE pickups (
            id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            found_item_id INT(11) NOT NULL,
            nama_pengambil VARCHAR(100) NOT NULL,
            nim VARCHAR(50) NOT NULL,
            fakultas VARCHAR(100) NOT NULL,
            nama_petugas VARCHAR(100) NOT NULL,
            waktu_pengambilan VARCHAR(100) NOT NULL,
            foto_ktp VARCHAR(255) NOT NULL,
            foto_bukti VARCHAR(255) NOT NULL,
            FOREIGN KEY (found_item_id) REFERENCES found_items(id) ON DELETE CASCADE
        ) ENGINE=InnoDB;
    ");

    // 5. Insert Fresh Dummy Data (Users)
    $pdo->exec("
        INSERT INTO users (nama, username, email, password, role, no_hp) VALUES
        ('Indah Sekali', 'indah', 'indah@webmail.umm.ac.id', 'password123', 'mahasiswa', '081234567890'),
        ('Petugas Jaga', 'petugas1', 'petugas@umm.ac.id', 'password123', 'petugas', '081236336675'),
        ('Super Admin', 'admin', 'admin@umm.ac.id', 'password123', 'admin', '081234567890')
    ");

    // 6. Insert Fresh Dummy Data (Found Items)
    $pdo->exec("
        INSERT INTO found_items (nama_barang, kategori, deskripsi_singkat, deskripsi_lengkap, lokasi, waktu_ditemukan, status, foto) VALUES
        ('Dompet Hitam', 'non-valuable', 'Warna Hitam, Ditemukan di Tangga gkb 1...', 'Dompet berwarna Coklat/Hitam, dengan isi KTP atas nama Zainal Siregar Kapalaud, uang Rp.200.000, STNK sepeda motor Vario Hitam.', 'TANGGA TENGAH LANTAI 2 KE 3, DI GKB 1.', '19 Mei 2026 13:00', 'available', 'https://images.unsplash.com/photo-1627123424574-724758594e93?auto=format&fit=crop&q=80&w=800'),
        ('Kacamata', 'non-valuable', 'Warna Silver, Ditemukan di area tangga', 'Kacamata Bulat Frame Besi warna Silver, ditemukan di Toilet Cowok GKB 4.', 'Toilet Cowok, Lantai 3 GKB 4', '19 Mei 2026 13:00', 'available', 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&q=80&w=800'),
        ('Jam Tangan Rollex', 'valuable', 'Warna Silver Titanium, Ditemukan di Tangga...', 'Jam tangan berwarna silver titanium. Ditemukan di tangga lantai 1.', 'Tangga Lantai 1', '19 Mei 2026 10:00', 'available', 'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?auto=format&fit=crop&q=80&w=800'),
        ('HP iPhone 17 Pink', 'valuable', 'Warna Pink, Ditemukan di area tangga', 'HP iPhone 17 warna pink. Ada casing bening.', 'Area Tangga GKB 2', '19 Mei 2026 09:00', 'taken', 'https://images.unsplash.com/photo-1605236453806-6ff36851218e?auto=format&fit=crop&q=80&w=800')
    ");

    // 7. Insert Fresh Dummy Data (Lost Reports)
    $pdo->exec("
        INSERT INTO lost_reports (user_id, nama_barang, lokasi_hilang, waktu_hilang, deskripsi, kontak, lampiran, status_verifikasi) VALUES
        (1, 'Dompet Putih', 'Stadion UMM', '30 Mei 2026 Minggu, (07.00 WIB)', 'Disaat saya olahraga di Stadion UMM, saya kehilangan dompet berwarna Putih, dimana saya tidak terasa dompet tersebut jatuh.', '089877798308', 'NIM Pada KTM 202410370110000', 0),
        (1, 'STNK Motor', 'Area Parkir GKB 1', '1 Juni 2026', 'STNK No N 4711 S, Sepeda BAET Honda.', '081234567890', '-', 0),
        (1, 'Kunci Motor', 'Kantin', '2 Juni 2026', 'Kunci motor dengan gantungan kunci bertulis Menuju Surga.', '081234567890', '-', 1)
    ");

    echo "<div style='font-family:sans-serif; text-align:center; margin-top:50px;'>";
    echo "<h2 style='color:green;'>✅ Instalasi Database Berhasil!</h2>";
    echo "<p>Database <strong>lost_found_db</strong> telah dibersihkan dan dibuat ulang dengan data yang benar.</p>";
    echo "<p>Silakan klik tombol di bawah ini untuk pergi ke halaman Login.</p>";
    echo "<a href='login.php' style='display:inline-block; padding:10px 20px; background:#1e40af; color:white; text-decoration:none; border-radius:8px; font-weight:bold;'>Pergi ke Login</a>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div style='font-family:sans-serif; text-align:center; margin-top:50px;'>";
    echo "<h2 style='color:red;'>❌ Instalasi Gagal!</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>
