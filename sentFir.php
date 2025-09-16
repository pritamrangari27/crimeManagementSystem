<?php
session_start();
include 'conn.php';

// Check if station_id is set in the session
if (!isset($_SESSION['station_id'])) {
    die("Station ID not found in session. Please log in again.");
}

$station_id = $_SESSION['station_id']; // Retrieve station_id from the session

// Handle status update request (Approve/Reject)
if (isset($_POST['updateStatus'])) {
    $srNo = $_POST['srNo'];
    $newStatus = $_POST['status'];

    // Sanitize inputs to prevent SQL injection
    $srNo = mysqli_real_escape_string($conn, $srNo);
    $newStatus = mysqli_real_escape_string($conn, $newStatus);

    // Update the status in the database
    $updateQuery = "UPDATE FIR SET status = '$newStatus' WHERE srNo = '$srNo' AND station_id = '$station_id'";
    if (mysqli_query($conn, $updateQuery)) {
        echo "Success"; // Return success message
    } else {
        echo "Error updating status: " . mysqli_error($conn); // Return error message
    }
    exit();
}

// Fetch FIR records with status "Sent"
if (isset($_GET['fetchFIR']) && $_GET['fetchFIR'] == 1) {
    $query = "SELECT * FROM FIR WHERE status = 'Sent' AND station_id = '$station_id'";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr class='data-row' id='row-{$row['srNo']}'>
                    <td>{$row['srNo']}</td>
                    <td>{$row['station_id']}</td>
                    <td>{$row['station_name']}</td>
                    <td><span class='badge bg-warning text-dark'>{$row['crime_type']}</span></td>
                    <td>{$row['accused']}</td>
                    <td class='fw-bold'>{$row['name']}</td>
                    <td>{$row['age']}</td>
                    <td>{$row['number']}</td>
                    <td>{$row['address']}</td>
                    <td>{$row['relation']}</td>
                    <td>{$row['purpose']}</td>
                    <td><a href='{$row['file']}' target='_blank' class='btn btn-sm btn-outline-info'><i class='fas fa-file-alt'></i> View</a></td>
                    <td><span class='badge bg-warning text-dark'>{$row['status']}</span></td>
                    <td class='action-buttons'>
                        <button class='btn btn-success btn-sm' onclick='updateStatus({$row['srNo']}, \"Approved\")'><i class='fas fa-check-circle'></i> Approve</button>
                        <button class='btn btn-danger btn-sm' onclick='updateStatus({$row['srNo']}, \"Rejected\")'><i class='fas fa-times-circle'></i> Reject</button>
                    </td>
                  </tr>";
        }
    } else {
        echo "<tr id='noRecord' class='no-records'><td colspan='14' class='text-center py-4'>No pending FIRs found</td></tr>";
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PENDING FIR APPROVAL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 280px;
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --accent-color: #2e59d9;
            --dark-color: #212529;
            --light-color: #f8f9fa;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --chart-bg: #1a1a2e;
            --chart-text: #e6f7ff;
            --chart-grid: rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f8f9fc;
        }

        .header-gradient {
            background: linear-gradient(135deg, var(--warning-color), #f8c347);
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
            background-color: var(--warning-color);
            color: var(--dark-color);
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
            background-color: rgba(246, 194, 62, 0.05);
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
            border-color: var(--warning-color);
            box-shadow: 0 0 0 0.25rem rgba(246, 194, 62, 0.25);
        }

        .btn-add-fir {
            background: linear-gradient(135deg, var(--warning-color), #f8c347);
            border: none;
            border-radius: 30px;
            padding: 10px 25px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(246, 194, 62, 0.3);
            color: var(--dark-color);
        }

        .btn-add-fir:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(246, 194, 62, 0.4);
            color: var(--dark-color);
        }

        .badge {
            font-weight: 500;
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
        }

        .no-records {
            background-color: rgba(0, 0, 0, 0.02);
            font-style: italic;
            color: #6c757d;
        }

        .action-buttons {
            white-space: nowrap;
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
            
            .action-buttons {
                white-space: normal;
            }
            
            .action-buttons .btn {
                margin-bottom: 5px;
                display: block;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h1 class="header-gradient">
                <i class="fas fa-hourglass-half"></i> PENDING FIR APPROVAL
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
                            <th>Station ID</th>
                            <th>Station Name</th>
                            <th>Crime Type</th>
                            <th>Accused</th>
                            <th>Applicant</th>
                            <th>Age</th>
                            <th>Contact</th>
                            <th>Address</th>
                            <th>Relation</th>
                            <th>Purpose</th>
                            <th>Evidence</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="FirTable">
                        <?php
                        $sql = "SELECT * FROM FIR WHERE status = 'Sent' AND station_id = '$station_id'";
                        $result = mysqli_query($conn, $sql);
                        if ($result && mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr class='data-row' id='row-{$row['srNo']}'>
                                        <td>{$row['srNo']}</td>
                                        <td>{$row['station_id']}</td>
                                        <td>{$row['station_name']}</td>
                                        <td><span class='badge bg-warning text-dark'>{$row['crime_type']}</span></td>
                                        <td>{$row['accused']}</td>
                                        <td class='fw-bold'>{$row['name']}</td>
                                        <td>{$row['age']}</td>
                                        <td>{$row['number']}</td>
                                        <td>{$row['address']}</td>
                                        <td>{$row['relation']}</td>
                                        <td>{$row['purpose']}</td>
                                        <td><a href='{$row['file']}' target='_blank' class='btn btn-sm btn-outline-info'><i class='fas fa-file-alt'></i> View</a></td>
                                        <td><span class='badge bg-warning text-dark'>{$row['status']}</span></td>
                                        <td class='action-buttons'>
                                            <button class='btn btn-success btn-sm' onclick='updateStatus({$row['srNo']}, \"Approved\")'><i class='fas fa-check-circle'></i> Approve</button>
                                            <button class='btn btn-danger btn-sm' onclick='updateStatus({$row['srNo']}, \"Rejected\")'><i class='fas fa-times-circle'></i> Reject</button>
                                        </td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr id='noRecord' class='no-records'><td colspan='14' class='text-center py-4'>No pending FIRs found</td></tr>";
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
    $(document).ready(function() {
        // Auto-refresh FIR table every 5 seconds
        function loadFIRData() {
            $.ajax({
                url: "sentFIR.php?fetchFIR=1",
                type: "GET",
                success: function(response) {
                    $("#FirTable").html(response);
                }
            });
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
                    $("#FirTable").append("<tr id='noRecord' class='no-records'><td colspan='14' class='text-center py-4'>No matching records found</td></tr>");
                }
            } else {
                $("#noRecord").remove();
            }
        });
        
        // Add animation to table rows
        $(".data-row").hover(
            function() {
                $(this).css('transform', 'translateY(-2px)');
            },
            function() {
                $(this).css('transform', 'translateY(0)');
            }
        );
    });
    
    function updateStatus(srNo, newStatus) {
        if (confirm(`Are you sure you want to ${newStatus.toLowerCase()} this FIR?`)) {
            $.ajax({
                url: "sentFIR.php",
                type: "POST",
                data: { 
                    updateStatus: 1,
                    srNo: srNo,
                    status: newStatus
                },
                success: function(response) {
                    if (response.trim() === "Success") {
                        // Remove the row after successful update
                        $("#row-" + srNo).fadeOut(300, function() {
                            $(this).remove();
                            // If no rows left, show "No records" message
                            if ($("#FirTable .data-row").length === 0) {
                                $("#FirTable").append("<tr id='noRecord' class='no-records'><td colspan='14' class='text-center py-4'>No pending FIRs found</td></tr>");
                            }
                        });
                        
                        // Show notification
                        showNotification(`FIR ${newStatus.toLowerCase()} successfully!`, 'success');
                    } else {
                        showNotification("Error: " + response, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    showNotification("Failed to update status. Please try again.", 'error');
                }
            });
        }
    }
    
    function showNotification(message, type) {
        // Remove any existing notifications
        $(".notification").remove();
        
        // Create notification element
        const notification = $("<div>").addClass(`notification ${type}`)
            .html(`<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'}"></i> ${message}`);
        
        // Add to body and animate
        $("body").append(notification);
        notification.hide().fadeIn(300);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            notification.fadeOut(300, () => notification.remove());
        }, 3000);
    }
    
    // Add CSS for notifications
    $("head").append(`
        <style>
            .notification {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 25px;
                border-radius: 5px;
                color: white;
                font-weight: 600;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
                z-index: 1000;
                display: flex;
                align-items: center;
                gap: 10px;
                animation: slideIn 0.3s ease-out;
            }
            
            .notification.success {
                background-color: var(--success-color);
                border-left: 5px solid #55efc4;
            }
            
            .notification.error {
                background-color: var(--danger-color);
                border-left: 5px solid #ff7675;
            }
            
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        </style>
    `);
    </script>
</body>
</html>