<?php
include 'conn.php';

// Fetch updated police records
if (isset($_GET['fetchPolice']) && $_GET['fetchPolice'] == 1) {
    $query = "SELECT * FROM police";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr class='data-row'>
                    <td>{$row['id']}</td>
                    <td class='fw-bold'>{$row['name']}</td>
                    <td>{$row['station_name']}</td>
                    <td>{$row['station_id']}</td>
                    <td>{$row['email']}</td>
                    <td>{$row['phone']}</td>
                    <td>{$row['address']}</td>
                    <td class='text-center'>
                        <div class='btn-group' role='group'>
                            <a href='view.php?id={$row['id']}' class='btn btn-sm btn-outline-info'><i class='fas fa-eye'></i></a>
                            <a href='edit.php?id={$row['id']}' class='btn btn-sm btn-outline-warning'><i class='fas fa-edit'></i></a>
                        </div>
                    </td>
                  </tr>";
        }
    } else {
        echo "<tr id='noRecord' class='no-records'><td colspan='8' class='text-center py-4'>No police records found</td></tr>";
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Police Records</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
            padding: 12px 15px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }

        .table td {
            padding: 10px 12px;
            vertical-align: middle;
            border-color: rgba(0, 0, 0, 0.05);
            font-size: 0.9rem;
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

        .btn-add-police {
            background: linear-gradient(135deg, var(--success-color), #17a076);
            border: none;
            border-radius: 30px;
            padding: 10px 25px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(28, 200, 138, 0.3);
            color: white;
        }

        .btn-add-police:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(28, 200, 138, 0.4);
            color: white;
        }

        .no-records {
            background-color: rgba(0, 0, 0, 0.02);
            font-style: italic;
            color: #6c757d;
        }

        .btn-outline-info {
            color: var(--info-color);
            border-color: var(--info-color);
        }

        .btn-outline-warning {
            color: var(--warning-color);
            border-color: var(--warning-color);
        }

        .btn-outline-danger {
            color: var(--danger-color);
            border-color: var(--danger-color);
        }

        @media (max-width: 1200px) {
            .table-responsive {
                display: block;
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <h1 class="header-gradient">
                <i class="fas fa-user-shield"></i> POLICE RECORDS
            </h1>
            <a href="addpolice.php" class="btn btn-add-police">
                <i class="fas fa-plus"></i> ADD POLICE
            </a>
        </div>
        
        <div class="mb-4 position-relative">
            <input type="text" id="searchBox" class="form-control search-box" placeholder="Search by name, station, or ID...">
            <i class="fas fa-search position-absolute" style="right: 20px; top: 12px; opacity: 0.5;"></i>
        </div>
        
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Station Name</th>
                            <th>Station ID</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="policeTable">
                        <?php
                        $sql = "SELECT * FROM police";
                        $result = mysqli_query($conn, $sql);
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr class='data-row'>
                                        <td>{$row['id']}</td>
                                        <td class='fw-bold'>{$row['name']}</td>
                                        <td>{$row['station_name']}</td>
                                        <td>{$row['station_id']}</td>
                                        <td>{$row['email']}</td>
                                        <td>{$row['phone']}</td>
                                        <td>{$row['address']}</td>
                                        <td class='text-center'>
                                            <div class='btn-group' role='group'>
                                                <a href='view.php?id={$row['id']}' class='btn btn-sm btn-outline-info'><i class='fas fa-eye'></i></a>
                                                <a href='edit.php?id={$row['id']}' class='btn btn-sm btn-outline-warning'><i class='fas fa-edit'></i></a>
                                            </div>
                                        </td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr id='noRecord' class='no-records'><td colspan='8' class='text-center py-4'>No police records found</td></tr>";
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
        // Auto-refresh police table every 5 seconds
        function loadPoliceData() {
            $.ajax({
                url: "managePolice.php?fetchPolice=1",
                type: "GET",
                success: function(response) {
                    $("#policeTable").html(response);
                }
            });
        }

        // Search Function
        $("#searchBox").on("input", function() {
            let input = this.value.toLowerCase();
            let rows = $("#policeTable .data-row");
            let found = false;

            rows.each(function() {
                let match = $(this).text().toLowerCase().includes(input);
                $(this).toggle(match);
                if (match) found = true;
            });

            if (!found) {
                if ($("#noRecord").length === 0) {
                    $("#policeTable").append("<tr id='noRecord' class='no-records'><td colspan='8' class='text-center py-4'>No matching records found</td></tr>");
                }
            } else {
                $("#noRecord").remove();
            }
        });

        // Set up auto-refresh
        setInterval(loadPoliceData, 5000);
        
        // Add hover animation
        $(".data-row").hover(
            function() {
                $(this).css('transform', 'translateY(-2px)');
            },
            function() {
                $(this).css('transform', 'translateY(0)');
            }
        );
    });
    </script>
</body>
</html>