<?php
include 'conn.php';

// Fetch updated criminal records
if (isset($_GET['fetchCriminals']) && $_GET['fetchCriminals'] == 1) {
    $query = "SELECT * FROM criminals";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr class='data-row'>
                    <td>{$row['station_id']}</td>
                    <td>{$row['station_name']}</td>
                    <td><span class='badge bg-primary'>{$row['crime_type']}</span></td>
                    <td>{$row['crime_date']}</td>
                    <td>{$row['crime_time']}</td>
                    <td>{$row['Prison_name']}</td>
                    <td>{$row['Court_name']}</td>
                    <td class='fw-bold'>{$row['Criminal_name']}</td>
                    <td>{$row['contact']}</td>
                    <td>{$row['DateOfBirth']}</td>
                    <td>{$row['email']}</td>
                    <td>{$row['state']}</td>
                    <td>{$row['city']}</td>
                    <td>{$row['address']}</td>
                    <td><a href='{$row['photo']}' target='_blank' class='btn btn-sm btn-outline-info'><i class='fas fa-image'></i> View</a></td>
                  </tr>";
        }
    } else {
        echo "<tr id='noRecord' class='no-records'><td colspan='15' class='text-center py-4'>No criminal records found</td></tr>";
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Criminal Records</title>
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

        .btn-add-criminal {
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

        .btn-add-criminal:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(28, 200, 138, 0.4);
            color: white;
        }

        .badge {
            font-weight: 500;
            padding: 5px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
        }

        .no-records {
            background-color: rgba(0, 0, 0, 0.02);
            font-style: italic;
            color: #6c757d;
        }

        .btn-outline-info {
            padding: 5px 10px;
            font-size: 0.8rem;
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
                <i class="fas fa-user-shield"></i> CRIMINAL RECORDS
            </h1>
            <a href="addCriminal.php" class="btn btn-add-criminal">
                <i class="fas fa-plus"></i> ADD CRIMINAL
            </a>
        </div>
        
        <div class="mb-4 position-relative">
            <input type="text" id="searchBox" class="form-control search-box" placeholder="Search by name, crime type, or prison...">
            <i class="fas fa-search position-absolute" style="right: 20px; top: 12px; opacity: 0.5;"></i>
        </div>
        
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Station ID</th>
                            <th>Station</th>
                            <th>Crime Type</th>
                            <th>Crime Date</th>
                            <th>Crime Time</th>
                            <th>Prison</th>
                            <th>Court</th>
                            <th>Criminal Name</th>
                            <th>Contact</th>
                            <th>DOB</th>
                            <th>Email</th>
                            <th>State</th>
                            <th>City</th>
                            <th>Address</th>
                            <th>Photo</th>
                        </tr>
                    </thead>
                    <tbody id="criminalTable">
                        <?php
                        $sql = "SELECT * FROM criminals";
                        $result = mysqli_query($conn, $sql);
                        if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr class='data-row'>
                                        <td>{$row['station_id']}</td>
                                        <td>{$row['station_name']}</td>
                                        <td><span class='badge bg-primary'>{$row['crime_type']}</span></td>
                                        <td>{$row['crime_date']}</td>
                                        <td>{$row['crime_time']}</td>
                                        <td>{$row['Prison_name']}</td>
                                        <td>{$row['Court_name']}</td>
                                        <td class='fw-bold'>{$row['Criminal_name']}</td>
                                        <td>{$row['contact']}</td>
                                        <td>{$row['DateOfBirth']}</td>
                                        <td>{$row['email']}</td>
                                        <td>{$row['state']}</td>
                                        <td>{$row['city']}</td>
                                        <td>{$row['address']}</td>
                                        <td><a href='{$row['photo']}' target='_blank' class='btn btn-sm btn-outline-info'><i class='fas fa-image'></i> View</a></td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr id='noRecord' class='no-records'><td colspan='15' class='text-center py-4'>No criminal records found</td></tr>";
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
        // Auto-refresh criminal table every 5 seconds
        function loadCriminalData() {
            $.ajax({
                url: "manageCriminal.php?fetchCriminals=1",
                type: "GET",
                success: function(response) {
                    $("#criminalTable").html(response);
                }
            });
        }

        // Search Function
        $("#searchBox").on("input", function() {
            let input = this.value.toLowerCase();
            let rows = $("#criminalTable .data-row");
            let found = false;

            rows.each(function() {
                let match = $(this).text().toLowerCase().includes(input);
                $(this).toggle(match);
                if (match) found = true;
            });

            if (!found) {
                if ($("#noRecord").length === 0) {
                    $("#criminalTable").append("<tr id='noRecord' class='no-records'><td colspan='15' class='text-center py-4'>No matching records found</td></tr>");
                }
            } else {
                $("#noRecord").remove();
            }
        });

        // Set up auto-refresh
        setInterval(loadCriminalData, 5000);
        
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