<?php
include 'conn.php';

// Handle status update request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['station_id']) && isset($_POST['status'])) {
    $station_id = $_POST['station_id'];
    $status = $_POST['status'];

    // Validate status
    $validStatuses = ['Sent', 'Approved', 'Rejected'];
    if (!in_array($status, $validStatuses)) {
        echo "error: Invalid status";
        exit();
    }

    // Update FIR status in the database based on srNo
    $query = "UPDATE fir SET status = ? WHERE station_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $status, $station_id);

    if (mysqli_stmt_execute($stmt)) {
        echo "success";
    } else {
        echo "error: " . mysqli_error($conn);
    }

    mysqli_stmt_close($stmt);
    exit();
}

// Fetch updated FIR records (for auto-refresh)
if (isset($_GET['fetchFIR']) && $_GET['fetchFIR'] == 1) {
    $sql = "SELECT * FROM FIR";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Determine badge color based on status
            $badgeClass = 'bg-secondary';
            if ($row['status'] == 'Approved') $badgeClass = 'bg-success';
            if ($row['status'] == 'Rejected') $badgeClass = 'bg-danger';
            if ($row['status'] == 'Sent') $badgeClass = 'bg-warning text-dark';
            
            echo "<tr class='data-row' id='row-{$row['station_id']}'>
                    <td>{$row['station_id']}</td>
                    <td>{$row['station_name']}</td>
                    <td><span class='badge bg-primary'>{$row['crime_type']}</span></td>
                    <td>{$row['accused']}</td>
                    <td class='fw-bold'>{$row['name']}</td>
                    <td>{$row['age']}</td>
                    <td>{$row['number']}</td>
                    <td>{$row['address']}</td>
                    <td>{$row['relation']}</td>
                    <td>{$row['purpose']}</td>
                    <td><a href='{$row['file']}' target='_blank' class='btn btn-sm btn-outline-info'><i class='bi bi-file-earmark-text'></i> View</a></td>
                    <td><span class='badge {$badgeClass}'>{$row['status']}</span></td>
                  </tr>";
        }
    } else {
        echo "<tr id='noRecord' class='no-records'><td colspan='12' class='text-center py-4'>No records found</td></tr>";
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FIR STATUS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --accent-color: #2e59d9;
            --dark-color: #212529;
            --light-color: #f8f9fa;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
        }

        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f8f9fc;
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
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border: none;
            border-radius: 30px;
            padding: 10px 25px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(78, 115, 223, 0.3);
            color: white;
        }

        .btn-add-fir:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(78, 115, 223, 0.4);
            color: white;
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
                <i class="bi bi-clipboard-data"></i> YOUR FIR STATUS
            </h1>
            <a href="FIRForm.php" class="btn btn-add-fir">
                <i class="bi bi-plus-circle"></i> FILE NEW FIR
            </a>
        </div>
        
        <div class="mb-4 position-relative">
            <input type="text" id="searchBox" class="form-control search-box" placeholder="Search by name, ID, or crime type...">
            <i class="bi bi-search position-absolute" style="right: 20px; top: 12px; opacity: 0.5;"></i>
        </div>
        
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
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
                        </tr>
                    </thead>
                    <tbody id="FirTable">
                        <?php
                        $sql = "SELECT * FROM FIR";
                        $result = mysqli_query($conn, $sql);
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                // Determine badge color based on status
                                $badgeClass = 'bg-secondary';
                                if ($row['status'] == 'Approved') $badgeClass = 'bg-success';
                                if ($row['status'] == 'Rejected') $badgeClass = 'bg-danger';
                                if ($row['status'] == 'Sent') $badgeClass = 'bg-warning text-dark';
                                
                                echo "<tr class='data-row' id='row-{$row['station_id']}'>
                                        <td>{$row['station_id']}</td>
                                        <td>{$row['station_name']}</td>
                                        <td><span class='badge bg-primary'>{$row['crime_type']}</span></td>
                                        <td>{$row['accused']}</td>
                                        <td class='fw-bold'>{$row['name']}</td>
                                        <td>{$row['age']}</td>
                                        <td>{$row['number']}</td>
                                        <td>{$row['address']}</td>
                                        <td>{$row['relation']}</td>
                                        <td>{$row['purpose']}</td>
                                        <td><a href='{$row['file']}' target='_blank' class='btn btn-sm btn-outline-info'><i class='bi bi-file-earmark-text'></i> View</a></td>
                                        <td><span class='badge {$badgeClass}'>{$row['status']}</span></td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr id='noRecord' class='no-records'><td colspan='12' class='text-center py-4'>No records found</td></tr>";
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
                url: "userFIR.php?fetchFIR=1",
                type: "GET",
                success: function(response) {
                    $("#FirTable").html(response);
                },
                error: function(xhr, status, error) {
                    showNotification("Failed to refresh data. Please check your connection.", 'error');
                }
            });
        }

        setInterval(loadFIRData, 5000); // Auto-refresh every 5 seconds

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
                    $("#FirTable").append("<tr id='noRecord' class='no-records'><td colspan='12' class='text-center py-4'>No matching records found</td></tr>");
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
    
    function showNotification(message, type) {
        // Remove any existing notifications
        $(".notification").remove();
        
        // Create notification element
        const notification = $("<div>").addClass(`notification ${type}`)
            .html(`<i class="bi ${type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle'}"></i> ${message}`);
        
        // Add to body and animate
        $("body").append(notification);
        notification.hide().fadeIn(300);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            notification.fadeOut(300, () => notification.remove());
        }, 3000);
    }
    </script>
</body>
</html>