<?php

header("Content-Type: application/json");
require_once "../config/database.php";

$device_id = "IOT-DEVICE-001";

$temperature = rand(250, 420) / 10;
$humidity = rand(300, 900) / 10;
$co = rand(100, 900) / 100000;
$lpg = rand(100, 1200) / 100000;
$smoke = rand(100, 900) / 1000;
$light_intensity = rand(0, 1000);
$motion_status = rand(0, 1);

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

if (!$stmt->execute()) {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal menyimpan data sensor",
        "error" => $stmt->error
    ]);
    exit;
}

$sensor_id = $stmt->insert_id;
$stmt->close();

$inputData = [
    "temperature" => (float) $temperature,
    "humidity" => (float) $humidity,
    "co" => (float) $co,
    "lpg" => (float) $lpg,
    "smoke" => (float) $smoke,
    "light_intensity" => (float) $light_intensity,
    "motion_status" => (int) $motion_status
];

$jsonInput = json_encode($inputData);

$projectRoot = realpath(__DIR__ . "/..");

// Use correct path for Windows
$pythonPath = $projectRoot . (strpos(PHP_OS, 'WIN') === 0 ? "\\venv\\Scripts\\python.exe" : "/venv/bin/python");
$scriptPath = $projectRoot . "/ai_model/predict.py";

// Write JSON to temporary file to avoid command line escaping issues on Windows
$tempDir = sys_get_temp_dir();
$tempFile = $tempDir . DIRECTORY_SEPARATOR . 'iot_sensor_' . uniqid() . '.json';
if (file_put_contents($tempFile, $jsonInput) === false) {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal membuat temp file"
    ]);
    exit;
}

// Use temp file instead of command line argument
$command = escapeshellcmd($pythonPath) . " " .
           escapeshellarg($scriptPath) . " --file " .
           escapeshellarg($tempFile);

$output = shell_exec($command);

// Clean up temp file
@unlink($tempFile);

if ($output === null || trim($output) === "") {
    echo json_encode([
        "status" => "error",
        "message" => "Python script tidak menghasilkan output"
    ]);
    exit;
}

$prediction = json_decode($output, true);

if (!$prediction || $prediction["status"] !== "success") {
    echo json_encode([
        "status" => "error",
        "message" => "Prediksi gagal",
        "raw_output" => $output
    ]);
    exit;
}

$prediction_label = $prediction["prediction_label"];
$prediction_score = (float) $prediction["prediction_score"];

$prediction_reason = "Model AI memprediksi status " . $prediction_label .
    " berdasarkan fitur temperature, humidity, CO, LPG, smoke, light intensity, dan motion status.";

$updateStmt = $conn->prepare("
    UPDATE sensor_data
    SET prediction_label = ?, prediction_score = ?, prediction_reason = ?
    WHERE id = ?
");

$updateStmt->bind_param(
    "sdsi",
    $prediction_label,
    $prediction_score,
    $prediction_reason,
    $sensor_id
);

if (!$updateStmt->execute()) {
    echo json_encode([
        "status" => "error",
        "message" => "Gagal menyimpan hasil prediksi",
        "error" => $updateStmt->error
    ]);
    exit;
}

$updateStmt->close();

$finalStmt = $conn->prepare("
    SELECT * FROM sensor_data
    WHERE id = ?
    LIMIT 1
");

$finalStmt->bind_param("i", $sensor_id);
$finalStmt->execute();

$result = $finalStmt->get_result();
$finalData = $result->fetch_assoc();

$finalStmt->close();
$conn->close();

echo json_encode([
    "status" => "success",
    "message" => "Data sensor berhasil dibuat dan diprediksi otomatis",
    "data" => $finalData
]);

?>
