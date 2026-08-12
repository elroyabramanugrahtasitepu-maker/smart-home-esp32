<?php
// ==========================================
// update.php (Smart Home Control Handler)
// ==========================================
require 'db_connect.php';

if (isset($_GET['device']) && isset($_GET['state'])) {
    $device = $_GET['device'];
    $state  = intval($_GET['state']); // Nilai 1 atau 0

    // Validasi nama kolom perangkat yang diizinkan
    $allowed_devices = [
        'lampu_depan',
        'lampu_tidur',
        'lampu_tengah',
        'lampu_belakang',
        'kipas_angin',
        'kipas_auto',
        'bell',
        'servo'
    ];

    if (in_array($device, $allowed_devices)) {
        // Default SQL untuk update perangkat biasa
        $sql = "UPDATE control_center SET $device = $state WHERE id = 1";

        // SAFETY OVERRIDE: Jika perangkat dimatikan secara manual (state == 0),
        // matikan juga mode otomatisnya untuk mencegah sistem otomatis menyalakannya kembali.
        if ($state == 0) {
            if ($device == 'lampu_depan') {
                $sql = "UPDATE control_center SET lampu_depan = 0, lampu_depan_auto = 0 WHERE id = 1";
            } elseif ($device == 'lampu_tidur') {
                $sql = "UPDATE control_center SET lampu_tidur = 0, lampu_tidur_auto = 0 WHERE id = 1";
            } elseif ($device == 'lampu_tengah') {
                $sql = "UPDATE control_center SET lampu_tengah = 0, lampu_tengah_auto = 0 WHERE id = 1";
            } elseif ($device == 'lampu_belakang') {
                $sql = "UPDATE control_center SET lampu_belakang = 0, lampu_belakang_auto = 0 WHERE id = 1";
            } elseif ($device == 'kipas_angin') {
                $sql = "UPDATE control_center SET kipas_angin = 0, kipas_auto = 0 WHERE id = 1";
            }
        }

        // Eksekusi query ke database
        if ($conn->query($sql) === TRUE) {
            echo "success";
        } else {
            echo "error";
        }
    }
}
?>