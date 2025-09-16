<?php
include 'conn.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM police WHERE id = $id";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
}

if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $station_name = $_POST['station_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $sql = "UPDATE police SET name='$name', station_name='$station_name', email='$email', phone='$phone', address='$address' WHERE id=$id";
    if (mysqli_query($conn, $sql)) {
        header("Location: managePolice.php");
        exit();
    } else {
        echo "Error updating record: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Police Record</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --accent-color: #2e59d9;
            --dark-bg: #1a1a2e;
            --card-bg: #2d3748;
            --text-primary: #e2e8f0;
            --text-secondary: #a0aec0;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
        }

        body {
            background-color: #f8f9fc;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .form-container {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
            padding: 2rem;
            width: 100%;
            max-width: 800px;
            margin: 2rem auto;
        }

        .form-header {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-weight: 700;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }

        .form-header i {
            font-size: 1.5rem;
            margin-right: 0.75rem;
        }

        .form-label {
            font-weight: 600;
            color: #5a5c69;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }

        .form-label i {
            margin-right: 8px;
            font-size: 0.9rem;
            color: var(--primary-color);
        }

        .form-control {
            border-radius: 0.35rem;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d3e2;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.25);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 0.35rem;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background-color: var(--accent-color);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: #6c757d;
            border: none;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 0.35rem;
            transition: all 0.3s;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-1px);
        }

        .form-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 1.5rem;
                margin: 1rem;
            }
            
            .form-section {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="form-container">
        <div class="form-header">
            <i class="fas fa-user-edit"></i>
            <h2 class="mb-0">Edit Police Record</h2>
        </div>
        
        <form method="POST">
            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">

            <div class="form-section">
                <div>
                    <label class="form-label">
                        <i class="fas fa-user"></i> Full Name
                    </label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($row['name']); ?>" required>
                </div>
                
                <div>
                    <label class="form-label">
                        <i class="fas fa-building"></i> Station Name
                    </label>
                    <input type="text" name="station_name" class="form-control" value="<?php echo htmlspecialchars($row['station_name']); ?>" required>
                </div>
            </div>

            <div class="form-section">
                <div>
                    <label class="form-label">
                        <i class="fas fa-envelope"></i> Email Address
                    </label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($row['email']); ?>" required>
                </div>
                
                <div>
                    <label class="form-label">
                        <i class="fas fa-phone"></i> Phone Number
                    </label>
                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($row['phone']); ?>" required>
                </div>
            </div>

            <div>
                <label class="form-label">
                    <i class="fas fa-map-marker-alt"></i> Address
                </label>
                <textarea name="address" class="form-control" rows="3" required><?php echo htmlspecialchars($row['address']); ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" name="update" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update Record
                </button>
                <a href="managePolice.php" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>