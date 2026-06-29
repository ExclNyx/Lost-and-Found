<?php
$host = 'localhost';
$dbname = 'lost_found_db';
$username = 'root'; // Sesuaikan jika menggunakan username lain di XAMPP
$password = '';     // Sesuaikan jika menggunakan password di XAMPP

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Auto-patch: pastikan kolom username ada di tabel users
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'username'");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("ALTER TABLE users ADD COLUMN username VARCHAR(50) NULL AFTER nama");
            $pdo->exec("UPDATE users SET username = 'indah' WHERE email = 'indah@webmail.umm.ac.id'");
            $pdo->exec("UPDATE users SET username = 'petugas1' WHERE email = 'petugas@umm.ac.id'");
            $pdo->exec("UPDATE users SET username = 'admin' WHERE email = 'admin@umm.ac.id'");
        }
    } catch (PDOException $e) {
        // Abaikan jika tabel users belum ada (akan ditangani di tempat lain)
    }

} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}
?>
