<?php
// ==========================================
// update_auto.php (Process Automation Timers)
// ==========================================
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Ambil data dari form untuk 4 Lampu (Checkbox centang & Jam)
    $lampu_depan_auto   = isset($_POST['lampu_depan_auto']) ? 1 : 0;
    $lampu_depan_on     = $_POST['lampu_depan_on'];
    $lampu_depan_off    = $_POST['lampu_depan_off'];

    $lampu_tidur_auto   = isset($_POST['lampu_tidur_auto']) ? 1 : 0;
    $lampu_tidur_on     = $_POST['lampu_tidur_on'];
    $lampu_tidur_off    = $_POST['lampu_tidur_off'];

    $lampu_tengah_auto  = isset($_POST['lampu_tengah_auto']) ? 1 : 0;
    $lampu_tengah_on    = $_POST['lampu_tengah_on'];
    $lampu_tengah_off   = $_POST['lampu_tengah_off'];

    $lampu_belakang_auto = isset($_POST['lampu_belakang_auto']) ? 1 : 0;
    $lampu_belakang_on  = $_POST['lampu_belakang_on'];
    $lampu_belakang_off = $_POST['lampu_belakang_off'];

    // Data Kipas Otomatis
    $kipas_auto         = isset($_POST['kipas_auto']) ? 1 : 0;
    $kipas_interval     = intval($_POST['kipas_interval']);

    // 2. Simpan murni hanya pengaturan jadwal dan mode otomatis ke database
    $sql = "UPDATE control_center SET 
            lampu_depan_auto = $lampu_depan_auto, lampu_depan_on = '$lampu_depan_on', lampu_depan_off = '$lampu_depan_off',
            lampu_tidur_auto = $lampu_tidur_auto, lampu_tidur_on = '$lampu_tidur_on', lampu_tidur_off = '$lampu_tidur_off',
            lampu_tengah_auto = $lampu_tengah_auto, lampu_tengah_on = '$lampu_tengah_on', lampu_tengah_off = '$lampu_tengah_off',
            lampu_belakang_auto = $lampu_belakang_auto, lampu_belakang_on = '$lampu_belakang_on', lampu_belakang_off = '$lampu_belakang_off',
            kipas_auto = $kipas_auto, kipas_interval = $kipas_interval 
            WHERE id = 1";

    if ($conn->query($sql) === TRUE) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>