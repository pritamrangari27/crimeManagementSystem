<?php
include 'conn.php';

// Check if station_id is provided
if (!isset($_GET['station_id'])) {
    header("Location: managePoliceStation.php");
    exit();
}

$station_id = $_GET['station_id'];

// Secure database query
$sql = "SELECT * FROM police_station WHERE station_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $station_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    die("Police station not found");
}

$station = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Police Station Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --info-color: #36b9cc;
            --dark-bg: #2a2a3a;
            --darker-bg: #1e1e2e;
        }
        
        body {
            background-color: #f8f9fa;
            color: #212529;
        }
        
        .station-card {
            max-width: 800px;
            margin: 30px auto;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        
        .station-header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            border-radius: 0.35rem 0.35rem 0 0;
        }
        
        .detail-item {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .btn-back {
            background-color: #6c757d;
            color: white;
            margin-top: 20px;
        }
        
        .station-image {
            max-width: 100%;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card station-card">
            <div class="card-header station-header text-center">
                <h3><i class="fas fa-building"></i> <?php echo htmlspecialchars($station['station_name']); ?></h3>
            </div>
            <div class="card-body">
                <?php if (!empty($station['image_path'])): ?>
                    <div class="text-center">
                        <img src="<?php echo htmlspecialchars($station['image_path']); ?>" 
                             class="station-image" 
                             alt="<?php echo htmlspecialchars($station['station_name']); ?>">
                    </div>
                <?php endif; ?>
                
                <div class="detail-item">
                    <div class="detail-label"><i class="fas fa-id-card"></i> Station ID</div>
                    <div><?php echo htmlspecialchars($station['station_id']); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label"><i class="fas fa-sign"></i> Station Name</div>
                    <div><?php echo htmlspecialchars($station['station_name']); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label"><i class="fas fa-map-marker-alt"></i> Address</div>
                    <div><?php echo htmlspecialchars($station['address']); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label"><i class="fas fa-globe"></i> State</div>
                    <div><?php echo htmlspecialchars($station['state']); ?></div>
                </div>
                
                <?php if (!empty($station['contact_number'])): ?>
                <div class="detail-item">
                    <div class="detail-label"><i class="fas fa-phone"></i> Contact Number</div>
                    <div><?php echo htmlspecialchars($station['contact_number']); ?></div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($station['email'])): ?>
                <div class="detail-item">
                    <div class="detail-label"><i class="fas fa-envelope"></i> Email</div>
                    <div><?php echo htmlspecialchars($station['email']); ?></div>
                </div>
                <?php endif; ?>
                
                <div class="text-center mt-4">
                    <a href="managePoliceStation.php" class="btn btn-back">
                        <i class="fas fa-arrow-left"></i> Back to Stations List
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>