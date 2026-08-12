<?php
require 'db_connect.php';

header('Content-Type: application/json');

// 1. Menerima data sensor dari ESP32 (Menggunakan metode GET agar mudah di-parse)
if (isset($_GET['suhu']) && isset($_GET['humi']) && isset($_GET['pintu']) && isset($_GET['gas'])) {
    $suhu = floatval($_GET['suhu']);
    $humi = floatval($_GET['humi']);
    
    // Konversi logic sensor hardware ke string teks dashboard
    $pintu = ($_GET['pintu'] == "1") ? 'TERBUKA' : 'TERTUTUP';
    $gas   = ($_GET['gas'] == "1") ? 'BAHAYA' : 'AMAN';

    // Update data sensor ke database row ID 1
    $update_sql = "UPDATE control_center SET suhu=$suhu, kelembaban=$humi, status_pintu='$pintu', status_gas='$gas' WHERE id=1";
    $conn->query($update_sql);
}

// 2. Mengambil data status relay/output terbaru untuk dikirim ke ESP32
$result = $conn->query("SELECT
lampu_depan,
lampu_tidur,
lampu_tengah,
lampu_belakang,
kipas_angin,
kipas_auto,
kipas_interval,
bell,
servo
FROM control_center
WHERE id=1");
$row = $result->fetch_assoc();

// Outputkan data ke ESP32 dalam format JSON
echo json_encode($row);
?>