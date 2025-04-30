<?php
include 'db_connect.php';
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["is_admin"] != 1) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $application_id = $_POST["application_id"];
    $action = $_POST["action"];

    if ($action == "approve" && isset($_POST["amount_granted"])) {
        $amount_granted = intval($_POST["amount_granted"]);

        $stmt = $conn->prepare("UPDATE bursary_applications SET status = 'approved', amount_granted = ? WHERE id = ?");
        $stmt->bind_param("ii", $amount_granted, $application_id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action == "reject") {
        $stmt = $conn->prepare("UPDATE bursary_applications SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $application_id);
        $stmt->execute();
        $stmt->close();
    }
}

$conn->close();
header("Location: admin_dashboard.php");
exit();
?>
