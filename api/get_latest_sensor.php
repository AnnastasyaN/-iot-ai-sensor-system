<?php

header("Content-Type: application/json");
require_once "../config/database.php";

$sql = "SELECT * FROM sensor_data ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo json_encode([
        "status" => "success",
        "data" => $result->fetch_assoc()
    ]);
} else {
    echo json_encode([
        "status" => "empty",
        "message" => "Belum ada data sensor"
    ]);
}

$conn->close();

?>