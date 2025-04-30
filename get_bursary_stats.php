<?php
include 'db_connect.php';

$totalFunds = 100000000; // Change this to match your real fund

$sql = "SELECT SUM(amount_granted) AS total_disbursed FROM bursary_applications WHERE status = 'approved'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

$disbursed = $row['total_disbursed'] ?? 0;
$remaining = $totalFunds - $disbursed;

echo json_encode([
    'disbursed' => $disbursed,
    'remaining' => $remaining
]);
?>
