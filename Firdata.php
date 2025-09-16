<?php
session_start();
include 'conn.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['station_id']) && isset($_POST['status'])) {
    $station_id = $_POST['station_id'];
    $status = $_POST['status'];

    // Validate status
    if (!in_array($status, ['Sent', 'Approved', 'Rejected'])) {
        echo "error: Invalid status";
        exit();
    }

    // Use prepared statements to prevent SQL injection
    $query = "UPDATE fir SET status = ? WHERE station_id = ?";
    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        echo "error: " . mysqli_error($conn);
        exit();
    }

    mysqli_stmt_bind_param($stmt, "ss", $status, $station_id);

    if (mysqli_stmt_execute($stmt)) {
        // Get FIR details for the activity log
        $fir_query = "SELECT station_name, crime_type FROM fir WHERE station_id = ?";
        $fir_stmt = mysqli_prepare($conn, $fir_query);
        mysqli_stmt_bind_param($fir_stmt, "s", $station_id);
        mysqli_stmt_execute($fir_stmt);
        $fir_result = mysqli_stmt_get_result($fir_stmt);
        $fir_data = mysqli_fetch_assoc($fir_result);

        // Log the activity for the dashboard
        $activity_type = "fir";
        $description = "<span class=\"font-weight-bold\">FIR</span> from {$fir_data['station_name']} for {$fir_data['crime_type']} has been <span class=\"font-weight-bold\">{$status}</span>";
        $current_user = isset($_SESSION['username']) ? $_SESSION['username'] : 'System';

        // Create activity_logs table if it doesn't exist
        $create_table_sql = "CREATE TABLE IF NOT EXISTS activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            activity_type VARCHAR(50) NOT NULL,
            description TEXT NOT NULL,
            user VARCHAR(100) NOT NULL,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        mysqli_query($conn, $create_table_sql);

        // Insert the activity log
        $log_sql = "INSERT INTO activity_logs (activity_type, description, user) VALUES (?, ?, ?)";
        $log_stmt = mysqli_prepare($conn, $log_sql);
        if ($log_stmt) {
            mysqli_stmt_bind_param($log_stmt, "sss", $activity_type, $description, $current_user);
            mysqli_stmt_execute($log_stmt);
            mysqli_stmt_close($log_stmt);
        }

        echo "success";
    } else {
        echo "error: " . mysqli_stmt_error($stmt);
    }

    mysqli_stmt_close($stmt);
    exit();
}

// Fetch all FIR records without filtering by station ID
$sql = "SELECT * FROM FIR";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All FIR Records</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --accent-color: #2e59d9;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --dark-bg: #1a1a2e;
            --card-bg: #2d3748;
            --text-primary: #e2e8f0;
            --text-secondary: #a0aec0;
        }

        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .header-gradient {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-weight: 700;
        }

        .table-container {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
            overflow: hidden;
        }

        .table thead {
            background-color: var(--primary-color);
            color: white;
        }

        .table th {
            padding: 15px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .table td {
            padding: 12px 15px;
            vertical-align: middle;
            border-color: rgba(0, 0, 0, 0.05);
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.05);
        }

        .search-box {
            background-color: white;
            border: 1px solid #d1d3e2;
            border-radius: 30px;
            padding: 10px 20px;
            margin-bottom: 20px;
            transition: all 0.3s;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .search-box:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }

        .btn-add-fir {
            background: linear-gradient(135deg, var(--success-color), #17a076);
            border: none;
            border-radius: 30px;
            padding: 10px 25px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(28, 200, 138, 0.3);
        }

        .btn-add-fir:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(28, 200, 138, 0.4);
        }

        .badge {
            font-weight: 500;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            display: inline-block;
            min-width: 80px;
            text-align: center;
        }

        .action-btn {
            padding: 6px 12px;
            font-size: 0.85rem;
            font-weight: 500;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .btn-view {
            background-color: var(--info-color);
            color: white;
        }

        .btn-approve {
            background-color: var(--success-color);
            color: white;
        }

        .btn-reject {
            background-color: var(--danger-color);
            color: white;
        }

        .no-records {
            background-color: rgba(0, 0, 0, 0.02);
            font-style: italic;
            color: #6c757d;
        }

        @media (max-width: 992px) {
            .table-responsive {
                border-radius: 8px;
            }

            .table th,
            .table td {
                padding: 10px 8px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h1 class="header-gradient">
                <i class="fas fa-file-alt"></i> All FIR Records
            </h1>
        </div>

        <div class="mb-4 position-relative">
            <input type="text" id="searchBox" class="form-control search-box" placeholder="Search by name, ID, or crime type...">
            <i class="fas fa-search position-absolute" style="right: 20px; top: 12px; opacity: 0.5;"></i>
        </div>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Station</th>
                            <th>Crime Type</th>
                            <th>Accused</th>
                            <th>Applicant</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="FirTable">
                        <?php
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                // Determine badge color based on status
                                $badgeClass = 'bg-secondary text-white';
                                if ($row['status'] == 'Approved') $badgeClass = 'bg-success text-white';
                                if ($row['status'] == 'Rejected') $badgeClass = 'bg-danger text-white';
                                if ($row['status'] == 'Sent') $badgeClass = 'bg-warning text-dark';

                                echo "<tr class='data-row' id='row-{$row['srNo']}'>
                                        <td>{$row['srNo']}</td>
                                        <td>{$row['station_name']}</td>
                                        <td>{$row['crime_type']}</td>
                                        <td>{$row['accused']}</td>
                                        <td>{$row['name']}</td>
                                        <td>{$row['number']}</td>
                                        <td><span class='badge rounded-pill {$badgeClass}'>{$row['status']}</span></td>
                                        <td>
                                            <div class='d-flex gap-2'>
                                                <a href='{$row['file']}' target='_blank' class='btn btn-sm btn-view action-btn' title='View Evidence'>
                                                    <i class='fas fa-eye'></i>
                                                </a>
                                                <button onclick='updateStatus(\"{$row['station_id']}\", \"Approved\")'
                                                    class='btn btn-sm btn-approve action-btn' title='Approve'>
                                                    <i class='fas fa-check'></i>
                                                </button>
                                                <button onclick='updateStatus(\"{$row['station_id']}\", \"Rejected\")'
                                                    class='btn btn-sm btn-reject action-btn' title='Reject'>
                                                    <i class='fas fa-times'></i>
                                                </button>
                                            </div>
                                        </td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr id='noRecord' class='no-records'><td colspan='8' class='text-center py-4'>No FIR records found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function updateStatus(station_id, status) {
        if (confirm(`Are you sure you want to ${status.toLowerCase()} this FIR?`)) {
            $.ajax({
                url: "Firdata.php",
                type: "POST",
                data: { station_id: station_id, status: status },
                success: function(response) {
                    if (response.trim() === "success") {
                        // Remove the row from the table
                        $(`[id^='row-']`).filter(function() {
                            return $(this).find(`button[onclick*=\"${station_id}\"]`).length > 0;
                        }).fadeOut(300, function() {
                            $(this).remove();
                            // Check if no records left
                            if ($(".data-row").length === 0) {
                                $("#FirTable").append("<tr id='noRecord' class='no-records'><td colspan='8' class='text-center py-4'>No FIR records found</td></tr>");
                            }
                        });

                        // Show success notification
                        showAlert(`FIR ${status.toLowerCase()} successfully`, 'success');
                    } else {
                        showAlert("Error updating status: " + response, 'danger');
                    }
                },
                error: function(xhr, status, error) {
                    showAlert("Failed to update FIR status: " + error, 'danger');
                }
            });
        }
    }

    function showAlert(message, type) {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} position-fixed`;
        alert.style.top = '20px';
        alert.style.right = '20px';
        alert.style.zIndex = '9999';
        alert.style.minWidth = '300px';
        alert.innerHTML = `
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            ${message}
        `;

        document.body.appendChild(alert);

        // Auto-dismiss after 3 seconds
        setTimeout(() => {
            $(alert).fadeOut(300, () => alert.remove());
        }, 3000);
    }

    // Search Function
    $("#searchBox").on("input", function() {
        let input = this.value.toLowerCase();
        let rows = $("#FirTable .data-row");
        let found = false;

        rows.each(function() {
            let match = $(this).text().toLowerCase().includes(input);
            $(this).toggle(match);
            if (match) found = true;
        });

        if (!found) {
            if ($("#noRecord").length === 0) {
                $("#FirTable").append("<tr id='noRecord' class='no-records'><td colspan='8' class='text-center py-4'>No matching records found</td></tr>");
            }
        } else {
            $("#noRecord").remove();
        }
    });
    </script>
</body>
</html>