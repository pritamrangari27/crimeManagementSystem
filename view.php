<?php
include 'conn.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM police WHERE id = $id";
    $result = mysqli_query($conn, $sql);
} else {
    $sql = "SELECT * FROM police";
    $result = mysqli_query($conn, $sql);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Police Records</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --dark-bg: #2a2a3a;
            --darker-bg: #1e1e2e;
            --card-bg: #1a2a4a;
        }
        
        body {
            background-color: var(--dark-bg);
            color: white !important; /* Force white text everywhere */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
        }
        
        /* Ensure all text elements are white */
        h1, h2, h3, h4, h5, h6,
        p, span, div, td, th, li,
        .card-body, .table, .table-dark,
        .btn, .form-control, .badge {
            color: white !important;
        }
        
        .container {
            max-width: 95%;
            padding: 20px;
        }
        
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--info-color));
            padding: 15px 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        
        .table-container {
            background-color: var(--darker-bg);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
        }
        
        .table-dark {
            background-color: transparent;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .table-dark thead th {
            background-color: var(--primary-color);
            border: none;
            padding: 15px;
            font-weight: 600;
            font-size: 1rem;
        }
        
        .table-dark tbody tr {
            background-color: rgba(255, 255, 255, 0.08);
            transition: all 0.3s ease;
        }
        
        .table-dark tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }
        
        .table-dark td, .table-dark th {
            border: none;
            padding: 14px 15px;
            vertical-align: middle;
            font-size: 0.95rem;
        }
        
        .card {
            background-color: var(--darker-bg);
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            margin: 0 auto;
            max-width: 600px;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color), var(--info-color));
            border-radius: 10px 10px 0 0 !important;
            padding: 18px 25px;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .card-body {
            padding: 25px;
        }
        
        .card-body p {
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
            font-size: 1rem;
        }
        
        .btn-view {
            background-color: var(--info-color);
        }
        
        .btn-edit {
            background-color: var(--warning-color);
            color: #1a1a1a !important; /* Dark text for contrast */
        }
        
        .btn-delete {
            background-color: var(--danger-color);
        }
        
        .btn-back {
            background-color: var(--secondary-color);
        }
        
        .btn {
            border: none;
            border-radius: 6px;
            padding: 9px 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-right: 8px;
            font-size: 0.9rem;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            opacity: 0.9;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .table-dark td:before {
                color: var(--info-color);
            }
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h2 class="page-header">
        <i class="bi bi-shield-lock"></i> Police Records Management
    </h2>

    <?php if (isset($_GET['id'])) { ?>
        <?php $row = mysqli_fetch_assoc($result); ?>
        <div class="card">
            <div class="card-header">
                <h4><i class="bi bi-person-badge"></i> Officer Details: <?php echo $row['name']; ?></h4>
            </div>
            <div class="card-body">
                <p><strong><i class="bi bi-person"></i> Name:</strong> <?php echo $row['name']; ?></p>
                <p><strong><i class="bi bi-building"></i> Station Name:</strong> <?php echo $row['station_name']; ?></p>
                <p><strong><i class="bi bi-envelope"></i> Email:</strong> <?php echo $row['email']; ?></p>
                <p><strong><i class="bi bi-telephone"></i> Phone:</strong> <?php echo $row['phone']; ?></p>
                <p><strong><i class="bi bi-geo-alt"></i> Address:</strong> <?php echo $row['address']; ?></p>
                <a href="managePolice.php" class="btn btn-back"><i class="bi bi-arrow-left"></i> Back to List</a>
            </div>
        </div>
    <?php } else { ?>
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-dark table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Station Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                            <tr>
                                <td data-label="ID"><?php echo $row['id']; ?></td>
                                <td data-label="Name"><?php echo $row['name']; ?></td>
                                <td data-label="Station"><?php echo $row['station_name']; ?></td>
                                <td data-label="Email"><?php echo $row['email']; ?></td>
                                <td data-label="Phone"><?php echo $row['phone']; ?></td>
                                <td data-label="Address"><?php echo $row['address']; ?></td>
                                <td data-label="Actions" class="action-buttons">
                                    <a href="view.php?id=<?php echo $row['id']; ?>" class="btn btn-view btn-sm"><i class="bi bi-eye"></i> View</a>
                                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-edit btn-sm"><i class="bi bi-pencil"></i> Edit</a>
                                    <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-delete btn-sm" onclick="return confirmDelete();"><i class="bi bi-trash"></i> Delete</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php } ?>
</div>

<script>
    function confirmDelete() {
        return confirm("Are you sure you want to delete this record?");
    }
</script>
</body>
</html>