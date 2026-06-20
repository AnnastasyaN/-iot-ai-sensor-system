<?php

$host = "localhost";
$username = "root";
$password = "";
$database = "iot_sensor_db";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode([
        "status" => "error",
        "message" => "Koneksi database gagal",
        "error" => $conn->connect_error
    ]));
}

$conn->set_charset("utf8mb4");

?>