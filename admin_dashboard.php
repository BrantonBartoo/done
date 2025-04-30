<?php
include 'db_connect.php';
session_start();

// Ensure only admin can access this page
if (!isset($_SESSION["user_id"]) || $_SESSION["is_admin"] != 1) {
    header("Location: login.php");
    exit();
}

// Fetch all bursary applications
$sql = "SELECT b.id, b.financial_situation, b.academic_goals, b.career_aspirations, b.status, b.amount_granted, u.fullname, u.email
        FROM bursary_applications b
        JOIN users u ON b.user_id = u.id";
$result = $conn->query($sql);

// Fetch approved applications
$approvedQuery = "SELECT u.fullname, b.amount_granted 
                  FROM bursary_applications b
                  JOIN users u ON b.user_id = u.id
                  WHERE b.status = 'approved'";
$approvedResult = $conn->query($approvedQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .admin-header {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #007BFF;
            color: white;
        }

        .btn {
            padding: 8px 12px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            color: white;
        }

        .btn.approve {
            background: green;
        }

        .btn.reject {
            background: red;
        }

        .logout-btn {
            display: block;
            width: fit-content;
            padding: 10px 20px;
            background-color: #ff4d4d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            margin: 20px auto;
        }

        .logout-btn:hover {
            background-color: #cc0000;
        }

        canvas {
            max-width: 400px;
            margin: 30px auto;
            display: block;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Admin Dashboard - Manage Applications</h2>

        <?php if ($result->num_rows > 0) : ?>
            <table>
                <tr>
                    <th>#</th>
                    <th>Applicant</th>
                    <th>Email</th>
                    <th>Financial Situation</th>
                    <th>Academic Goals</th>
                    <th>Career Aspirations</th>
                    <th>Status</th>
                    <th>Actions / Amount Granted</th>
                </tr>
                <?php $count = 1; ?>
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo $count++; ?></td>
                        <td><?php echo htmlspecialchars($row["fullname"]); ?></td>
                        <td><?php echo htmlspecialchars($row["email"]); ?></td>
                        <td><?php echo htmlspecialchars($row["financial_situation"]); ?></td>
                        <td><?php echo htmlspecialchars($row["academic_goals"]); ?></td>
                        <td><?php echo htmlspecialchars($row["career_aspirations"]); ?></td>
                        <td class="status <?php echo strtolower($row["status"]); ?>">
                            <?php echo htmlspecialchars($row["status"]); ?>
                        </td>
                        <td>
                            <form id="form-<?php echo $row['id']; ?>" action="update_application_status.php" method="POST" onsubmit="return handleApprove(this)">
                                <input type="hidden" name="application_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="amount_granted" value="">
                                <?php if (strtolower($row["status"]) !== 'approved') : ?>
                                    <button type="submit" name="action" value="approve" class="btn approve">Approve</button>
                                    <button type="submit" name="action" value="reject" class="btn reject">Reject</button>
                                <?php else : ?>
                                    Approved: KES <?php echo htmlspecialchars($row["amount_granted"]); ?>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else : ?>
            <p>No bursary applications found.</p>
        <?php endif; ?>

        <h3 style="text-align:center; margin-top:40px;">Approved Applications & Granted Amounts</h3>
        <table>
            <tr>
                <th>Applicant</th>
                <th>Amount Granted (KES)</th>
            </tr>
            <?php while ($row = $approvedResult->fetch_assoc()) : ?>
            <tr>
                <td><?php echo htmlspecialchars($row["fullname"]); ?></td>
                <td><?php echo htmlspecialchars($row["amount_granted"]); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>

        <canvas id="bursaryChart"></canvas>

        <div class="admin-header">
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>

    <script>
    function handleApprove(form) {
        const action = form.querySelector("button[name='action']:hover")?.value;
        if (action === "approve") {
            const amount = prompt("Enter amount to grant:");
            if (amount === null || isNaN(amount) || parseInt(amount) <= 0) {
                alert("Invalid amount.");
                return false;
            }
            form.amount_granted.value = parseInt(amount);
        }
        return true;
    }

    fetch('get_bursary_stats.php')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('bursaryChart').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Disbursed', 'Remaining'],
                    datasets: [{
                        label: 'Bursary Fund Status',
                        data: [data.disbursed, data.remaining],
                        backgroundColor: ['#28a745', '#ffc107'],
                    }]
                }
            });
        });
    </script>
</body>
</html>

<?php $conn->close(); ?>
