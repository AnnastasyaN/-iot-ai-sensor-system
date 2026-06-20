=<?php

require_once "config/database.php";

$latestQuery = "SELECT * FROM sensor_data ORDER BY id DESC LIMIT 1";
$latestResult = $conn->query($latestQuery);
$latest = null;

if ($latestResult && $latestResult->num_rows > 0) {
    $latest = $latestResult->fetch_assoc();
}

$historyQuery = "SELECT * FROM sensor_data ORDER BY id DESC LIMIT 20";
$historyResult = $conn->query($historyQuery);

function predictionBadge($label) {
    if ($label === "NORMAL") {
        return "<span class='badge badge-normal'>NORMAL</span>";
    } elseif ($label === "WARNING") {
        return "<span class='badge badge-warning'>WARNING</span>";
    } elseif ($label === "DANGER") {
        return "<span class='badge badge-danger'>DANGER</span>";
    } else {
        return "<span class='badge badge-empty'>BELUM DIPREDIKSI</span>";
    }
}

function predictionCardClass($label) {
    if ($label === "NORMAL") {
        return "prediction-normal";
    } elseif ($label === "WARNING") {
        return "prediction-warning";
    } elseif ($label === "DANGER") {
        return "prediction-danger";
    } else {
        return "";
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>IoT Environmental Sensor Monitoring</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="container">

    <div class="header">
        <h1>IoT Environmental Sensor Monitoring</h1>
        <p>
            Dashboard simulasi sensor lingkungan berbasis PHP, MySQL, dan model AI dari Google Colab.
        </p>
    </div>

    <div class="action-bar">
        <button onclick="generateSensorData()">Generate Data Sensor</button>
        <button class="btn-secondary" onclick="predictSensorData()">Prediksi dengan AI</button>
        <button onclick="generateAndPredictNow()">Generate + Prediksi Sekarang</button>
        <button class="btn-secondary" onclick="startRealtime()">Start Realtime 30 Detik</button>
        <button class="btn-danger" onclick="stopRealtime()">Stop Realtime</button>
        <button class="btn-danger" onclick="window.location.reload()">Refresh Dashboard</button>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <h3>Status Realtime</h3>
        <p id="realtime-status" style="font-size: 16px; font-weight: normal;">
            Realtime belum aktif.
        </p>
        <p style="font-size: 16px; font-weight: normal;">
            Generate berikutnya dalam:
            <strong id="countdown">-</strong> detik
        </p>
    </div>

    <?php if ($latest): ?>

        <div class="cards">

            <div class="card">
                <h3>Temperature</h3>
                <p id="latest-temperature"><?= htmlspecialchars($latest["temperature"]); ?> °C</p>
            </div>

            <div class="card">
                <h3>Humidity</h3>
                <p id="latest-humidity"><?= htmlspecialchars($latest["humidity"]); ?> %</p>
            </div>

            <div class="card">
                <h3>CO</h3>
                <p id="latest-co"><?= htmlspecialchars($latest["co"]); ?></p>
            </div>

            <div class="card">
                <h3>LPG</h3>
                <p id="latest-lpg"><?= htmlspecialchars($latest["lpg"]); ?></p>
            </div>

            <div class="card">
                <h3>Smoke</h3>
                <p id="latest-smoke"><?= htmlspecialchars($latest["smoke"]); ?></p>
            </div>

            <div class="card">
                <h3>Light Intensity</h3>
                <p id="latest-light"><?= htmlspecialchars($latest["light_intensity"]); ?></p>
            </div>

            <div class="card">
                <h3>Motion Status</h3>
                <p id="latest-motion">
                    <?= $latest["motion_status"] == 1 ? "DETECTED" : "NOT DETECTED"; ?>
                </p>
            </div>

            <div class="card <?= predictionCardClass($latest["prediction_label"]); ?>">
                <h3>AI Prediction</h3>
                <p id="latest-prediction">
                    <?= predictionBadge($latest["prediction_label"]); ?>
                </p>
                <small>
                    Score:
                    <span id="latest-score"><?= htmlspecialchars($latest["prediction_score"] ?? "-"); ?></span>
                </small>
            </div>

            <div class="card reason-card">
                <h3>Prediction Reason</h3>
                <p id="latest-reason" style="font-size: 16px; font-weight: normal;">
                    <?= htmlspecialchars($latest["prediction_reason"] ?? "Belum ada alasan prediksi."); ?>
                </p>
            </div>

        </div>

    <?php else: ?>

        <div class="card" style="margin-bottom: 24px;">
            <h3>Data Sensor</h3>
            <p>Belum ada data sensor. Klik tombol Generate Data Sensor atau Start Realtime 30 Detik.</p>
        </div>

        <div class="cards">

            <div class="card">
                <h3>Temperature</h3>
                <p id="latest-temperature">-</p>
            </div>

            <div class="card">
                <h3>Humidity</h3>
                <p id="latest-humidity">-</p>
            </div>

            <div class="card">
                <h3>CO</h3>
                <p id="latest-co">-</p>
            </div>

            <div class="card">
                <h3>LPG</h3>
                <p id="latest-lpg">-</p>
            </div>

            <div class="card">
                <h3>Smoke</h3>
                <p id="latest-smoke">-</p>
            </div>

            <div class="card">
                <h3>Light Intensity</h3>
                <p id="latest-light">-</p>
            </div>

            <div class="card">
                <h3>Motion Status</h3>
                <p id="latest-motion">-</p>
            </div>

            <div class="card">
                <h3>AI Prediction</h3>
                <p id="latest-prediction">
                    <span class="badge badge-empty">BELUM DIPREDIKSI</span>
                </p>
                <small>
                    Score:
                    <span id="latest-score">-</span>
                </small>
            </div>

            <div class="card reason-card">
                <h3>Prediction Reason</h3>
                <p id="latest-reason" style="font-size: 16px; font-weight: normal;">
                    Belum ada alasan prediksi.
                </p>
            </div>

        </div>

    <?php endif; ?>

    <h2>Riwayat Data Sensor</h2>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Device</th>
                    <th>Temp</th>
                    <th>Humidity</th>
                    <th>CO</th>
                    <th>LPG</th>
                    <th>Smoke</th>
                    <th>Light</th>
                    <th>Motion</th>
                    <th>Prediction</th>
                    <th>Score</th>
                    <th>Reason</th>
                    <th>Created At</th>
                </tr>
            </thead>

            <tbody id="sensor-history-body">
                <?php if ($historyResult && $historyResult->num_rows > 0): ?>
                    <?php while ($row = $historyResult->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row["id"]); ?></td>
                            <td><?= htmlspecialchars($row["device_id"]); ?></td>
                            <td><?= htmlspecialchars($row["temperature"]); ?> °C</td>
                            <td><?= htmlspecialchars($row["humidity"]); ?> %</td>
                            <td><?= htmlspecialchars($row["co"]); ?></td>
                            <td><?= htmlspecialchars($row["lpg"]); ?></td>
                            <td><?= htmlspecialchars($row["smoke"]); ?></td>
                            <td><?= htmlspecialchars($row["light_intensity"]); ?></td>
                            <td><?= $row["motion_status"] == 1 ? "DETECTED" : "NOT DETECTED"; ?></td>
                            <td><?= predictionBadge($row["prediction_label"]); ?></td>
                            <td><?= htmlspecialchars($row["prediction_score"] ?? "-"); ?></td>
                            <td><?= htmlspecialchars($row["prediction_reason"] ?? "-"); ?></td>
                            <td><?= htmlspecialchars($row["created_at"]); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="13">Belum ada data sensor.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<script>
let realtimeInterval = null;
let countdownInterval = null;
let countdownValue = 30;

function generateSensorData() {
    fetch("simulation/generate_sensor.php")
        .then(response => response.json())
        .then(data => {
            console.log(data);

            if (data.status === "success") {
                alert("Data sensor berhasil dibuat");
                window.location.reload();
            } else {
                alert("Gagal membuat data sensor: " + data.message);
            }
        })
        .catch(error => {
            console.error(error);
            alert("Terjadi error saat membuat data sensor");
        });
}

function predictSensorData() {
    fetch("api/predict_sensor.php")
        .then(response => response.json())
        .then(data => {
            console.log(data);

            if (data.status === "success") {
                alert(
                    "Hasil prediksi: " +
                    data.prediction_label +
                    "\nScore: " +
                    data.prediction_score +
                    "\nAlasan: " +
                    data.prediction_reason
                );

                window.location.reload();
            } else {
                alert("Prediksi gagal: " + data.message);
            }
        })
        .catch(error => {
            console.error(error);
            alert("Terjadi error saat memproses prediksi");
        });
}

function generateAndPredictNow() {
    fetch("api/generate_and_predict.php")
        .then(response => response.json())
        .then(data => {
            console.log(data);

            if (data.status === "success") {
                updateDashboard(data.data);
                loadSensorHistory();
            } else {
                alert("Generate dan prediksi gagal: " + data.message);
            }
        })
        .catch(error => {
            console.error(error);
            alert("Terjadi error saat generate dan prediksi");
        });
}

function startRealtime() {
    if (realtimeInterval !== null) {
        alert("Realtime sudah berjalan");
        return;
    }

    generateAndPredictNow();

    countdownValue = 30;
    updateRealtimeStatus("Realtime aktif. Data baru dibuat dan diprediksi setiap 30 detik.");
    updateCountdown(countdownValue);

    realtimeInterval = setInterval(function() {
        generateAndPredictNow();
        countdownValue = 30;
        updateCountdown(countdownValue);
    }, 30000);

    countdownInterval = setInterval(function() {
        countdownValue--;

        if (countdownValue <= 0) {
            countdownValue = 30;
        }

        updateCountdown(countdownValue);
    }, 1000);
}

function stopRealtime() {
    if (realtimeInterval !== null) {
        clearInterval(realtimeInterval);
        realtimeInterval = null;
    }

    if (countdownInterval !== null) {
        clearInterval(countdownInterval);
        countdownInterval = null;
    }

    updateRealtimeStatus("Realtime berhenti.");
    updateCountdown("-");
}

function updateRealtimeStatus(text) {
    const realtimeStatus = document.getElementById("realtime-status");

    if (realtimeStatus) {
        realtimeStatus.innerText = text;
    }
}

function updateCountdown(value) {
    const countdownElement = document.getElementById("countdown");

    if (countdownElement) {
        countdownElement.innerText = value;
    }
}

function updateDashboard(sensor) {
    const fields = {
        "latest-temperature": sensor.temperature + " °C",
        "latest-humidity": sensor.humidity + " %",
        "latest-co": sensor.co,
        "latest-lpg": sensor.lpg,
        "latest-smoke": sensor.smoke,
        "latest-light": sensor.light_intensity,
        "latest-motion": sensor.motion_status == 1 ? "DETECTED" : "NOT DETECTED",
        "latest-score": sensor.prediction_score ?? "-"
    };

    Object.keys(fields).forEach(function(id) {
        const element = document.getElementById(id);

        if (element) {
            element.innerText = fields[id];
        }
    });

    const predictionElement = document.getElementById("latest-prediction");

    if (predictionElement) {
        predictionElement.innerHTML = getPredictionBadge(sensor.prediction_label);
    }

    const reasonElement = document.getElementById("latest-reason");

    if (reasonElement) {
        reasonElement.innerText = sensor.prediction_reason ?? "Belum ada alasan prediksi.";
    }
}

function loadSensorHistory() {
    fetch("api/get_sensor_history.php?limit=20")
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                renderSensorHistory(data.data);
            }
        })
        .catch(error => {
            console.error(error);
        });
}

function renderSensorHistory(rows) {
    const tbody = document.getElementById("sensor-history-body");

    if (!tbody) {
        return;
    }

    tbody.innerHTML = "";

    if (!rows || rows.length === 0) {
        tbody.innerHTML = "<tr><td colspan='13'>Belum ada data sensor.</td></tr>";
        return;
    }

    rows.forEach(function(row) {
        const motionText = row.motion_status == 1 ? "DETECTED" : "NOT DETECTED";

        const tr = document.createElement("tr");

        tr.innerHTML = `
            <td>${escapeHtml(row.id)}</td>
            <td>${escapeHtml(row.device_id)}</td>
            <td>${escapeHtml(row.temperature)} °C</td>
            <td>${escapeHtml(row.humidity)} %</td>
            <td>${escapeHtml(row.co)}</td>
            <td>${escapeHtml(row.lpg)}</td>
            <td>${escapeHtml(row.smoke)}</td>
            <td>${escapeHtml(row.light_intensity)}</td>
            <td>${motionText}</td>
            <td>${getPredictionBadge(row.prediction_label)}</td>
            <td>${escapeHtml(row.prediction_score ?? "-")}</td>
            <td>${escapeHtml(row.prediction_reason ?? "-")}</td>
            <td>${escapeHtml(row.created_at)}</td>
        `;

        tbody.appendChild(tr);
    });
}

function getPredictionBadge(label) {
    if (label === "NORMAL") {
        return "<span class='badge badge-normal'>NORMAL</span>";
    }

    if (label === "WARNING") {
        return "<span class='badge badge-warning'>WARNING</span>";
    }

    if (label === "DANGER") {
        return "<span class='badge badge-danger'>DANGER</span>";
    }

    return "<span class='badge badge-empty'>BELUM DIPREDIKSI</span>";
}

function escapeHtml(value) {
    if (value === null || value === undefined) {
        return "";
    }

    return String(value)
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}
</script>

</body>
</html>

<?php
$conn->close();
?>