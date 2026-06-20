<?php

header("Content-Type: application/json");
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "message" => "Method harus POST"
    ]);
    exit;
}

$device_id = $_POST["device_id"] ?? null;
$temperature = $_POST["temperature"] ?? null;
$humidity = $_POST["humidity"] ?? null;
$co = $_POST["co"] ?? null;
$lpg = $_POST["lpg"] ?? null;
$smoke = $_POST["smoke"] ?? null;
$light_intensity = $_POST["light_intensity"] ?? null;
$motion_status = $_POST["motion_status"] ?? null;

if (
    $device_id === null ||
    $temperature === null ||
    $humidity === null ||
    $co === null ||
    $lpg === null ||
    $smoke === null ||
    $light_intensity === null ||
    $motion_status === null
) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "Data sensor belum lengkap"
    ]);
    exit;
}

$device_id = trim($device_id);
$temperature = (float) $temperature;
$humidity = (float) $humidity;
$co = (float) $co;
$lpg = (float) $lpg;
$smoke = (float) $smoke;
$light_intensity = (int) $light_intensity;
$motion_status = (int) $motion_status;

if ($motion_status !== 0 && $motion_status !== 1) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => "motion_status harus bernilai 0 atau 1"
    ]);
    exit;
}

$stmt = $conn->prepare("
    INSERT INTO sensor_data 
    (
        device_id,
        temperature,
        humidity,
        co,
        lpg,
        smoke,
        light_intensity,
        motion_status
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "sdddddii",
    $device_id,
    $temperature,
    $humidity,
    $co,
    $lpg,
    $smoke,
    $light_intensity,
    $motion_status
);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Data sensor berhasil disimpan",
        "insert_id" => $stmt->insert_id
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Gagal menyimpan data sensor",
        "error" => $stmt->error
    ]);
}

$stmt->close();
$conn->close();

?>