<?php

header("Content-Type: application/json");

$device_id = "IOT-DEVICE-001";

$temperature = rand(250, 420) / 10;
$humidity = rand(300, 900) / 10;
$co = rand(100, 900) / 100000;
$lpg = rand(100, 1200) / 100000;
$smoke = rand(100, 900) / 1000;
$light_intensity = rand(0, 1000);
$motion_status = rand(0, 1);

$url = "http://localhost/iot-ai-sensor-system/api/insert_sensor.php";

$data = [
    "device_id" => $device_id,
    "temperature" => $temperature,
    "humidity" => $humidity,
    "co" => $co,
    "lpg" => $lpg,
    "smoke" => $smoke,
    "light_intensity" => $light_intensity,
    "motion_status" => $motion_status
];

$options = [
    "http" => [
        "header" => "Content-Type: application/x-www-form-urlencoded\r\n",
        "method" => "POST",
        "content" => http_build_query($data),
        "timeout" => 10
    ]
];

$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);

if ($response === false) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Gagal mengirim data sensor ke API"
    ]);
    exit;
}

echo $response;

?>