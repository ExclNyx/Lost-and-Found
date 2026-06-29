<?php
// Action handler for laporan verification by petugas
include 'includes/header.php';
require_once 'includes/db.php';

if ($userRole !== 'petugas' && $userRole !== 'admin') {
    header("Location: index.php"); exit;
}

$action = $_GET['action'] ?? '';
$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        if ($action === 'verify') {
            $pdo->prepare("UPDATE lost_reports SET status_verifikasi = 1 WHERE id = ?")->execute([$id]);
        } elseif ($action === 'unverify') {
            $pdo->prepare("UPDATE lost_reports SET status_verifikasi = 0 WHERE id = ?")->execute([$id]);
        }
    } catch(PDOException $e) {}
}

header("Location: index.php?tab=laporan");
exit;
