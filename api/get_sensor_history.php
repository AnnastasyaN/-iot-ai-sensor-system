<?php

header("Content-Type: application/json");
require_once "../config/database.php";

$limit = $_GET["limit"] ?? 20;
$limit = (int) $limit;

if ($limit <= 0 || $limit > 100) {
    $limit = 20;
}

$stmt = $conn->prepare("
    SELECT * FROM sensor_data
    ORDER BY id DESC
    LIMIT ?
");

$stmt->bind_param("i", $limit);
$stmt->execute();

$result = $stmt->get_result();

$data = [];

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $data
]);

$stmt->close();
$conn->close();

?>