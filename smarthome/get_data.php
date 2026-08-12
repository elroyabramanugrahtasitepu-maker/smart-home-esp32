<?php
require 'db_connect.php';

$result = $conn->query("SELECT * FROM control_center WHERE id=1");
$row = $result->fetch_assoc();

echo json_encode($row);