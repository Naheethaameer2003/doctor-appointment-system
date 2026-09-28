<?php
/* ============================================================
   get_slots.php — AJAX endpoint: taken time slots for a date
   Called by appointment.php via fetch() when date changes.
   Returns JSON array of taken slot strings.
   e.g. ["05:00 PM","06:30 PM"]
   ============================================================ */
require_once 'db.php';

header('Content-Type: application/json');

$date = trim($_GET['date'] ?? '');

// Validate date format (YYYY-MM-DD)
if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare(
    "SELECT appt_time FROM appointments WHERE appt_date = ? AND status != 'cancelled'"
);
$stmt->execute([$date]);
$taken = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode($taken);
