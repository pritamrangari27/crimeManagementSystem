<?php
session_start();
include "conn.php";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: userlogin.php");
    exit();
}

// Get user data from session
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'Guest';
$default_profile_pic = "https://www.w3schools.com/howto/img_avatar.png";

// Initialize FIR counts
$total_firs = 0;
$approved_firs = 0;
$pending_firs = 0;
$rejected_firs = 0;

try {
    // Get FIR counts for this user - using both uppercase and lowercase table name to handle case sensitivity
    $fir_query = "SELECT
                    COUNT(*) as total,
                    COUNT(CASE WHEN status = 'Approved' THEN 1 END) as approved,
                    COUNT(CASE WHEN status = 'Sent' THEN 1 END) as pending,
                    COUNT(CASE WHEN status = 'Rejected' THEN 1 END) as rejected
                  FROM FIR
                  WHERE user_id = ?";

    $fir_stmt = $conn->prepare($fir_query);
    if (!$fir_stmt) {
        // Try with lowercase table name if uppercase fails
        $fir_query = "SELECT
                        COUNT(*) as total,
                        COUNT(CASE WHEN status = 'Approved' THEN 1 END) as approved,
                        COUNT(CASE WHEN status = 'Sent' THEN 1 END) as pending,
                        COUNT(CASE WHEN status = 'Rejected' THEN 1 END) as rejected
                      FROM fir
                      WHERE user_id = ?";
        $fir_stmt = $conn->prepare($fir_query);

        if (!$fir_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
    }

    $fir_stmt->bind_param("i", $user_id);
    if (!$fir_stmt->execute()) {
        throw new Exception("Execute failed: " . $fir_stmt->error);
    }

    $fir_result = $fir_stmt->get_result();
    if ($fir_result && $fir_result->num_rows > 0) {
        $row = $fir_result->fetch_assoc();
        $total_firs = (int)$row['total'];
        $approved_firs = (int)$row['approved'];
        $pending_firs = (int)$row['pending'];
        $rejected_firs = (int)$row['rejected'];
    } else {
        // If no FIRs found for this user, set counts to 0
        $total_firs = 0;
        $approved_firs = 0;
        $pending_firs = 0;
        $rejected_firs = 0;
    }

    if ($fir_stmt) {
        $fir_stmt->close();
    }

} catch (Exception $e) {
    error_log("Error in userhomepg.php: " . $e->getMessage());
    // Display user-friendly error message
    $error_message = "System error. Please try again later.";
    if ($_SESSION['role'] === 'Admin') {
        $error_message .= " (Debug: " . $e->getMessage() . ")";
    }
    echo "<div class='alert alert-danger'>$error_message</div>";
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 280px;
            --primary: #4e73df;
            --success: #1cc88a;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --dark: #212529;
            --light: #f8f9fc;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: var(--light);
        }

        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: linear-gradient(180deg, var(--dark) 0%, #1a1a1a 100%);
            padding: 20px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
            z-index: 1001;
        }

        .navbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            z-index: 1000;
            background-color: white;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
            padding: 0.5rem 1rem;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: 56px;
            padding: 20px;
            width: calc(100% - var(--sidebar-width));
        }

        .stat-card {
            border-left: 0.25rem solid;
            color: white;
        }

        .stat-card.primary {
            border-left-color: var(--primary);
            background-color: var(--primary);
        }

        .stat-card.success {
            border-left-color: var(--success);
            background-color: var(--success);
        }

        .stat-card.warning {
            border-left-color: var(--warning);
            background-color: var(--warning);
        }

        .stat-card.danger {
            border-left-color: var(--danger);
            background-color: var(--danger);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 0.7rem;
            min-width: 20px;
            height: 20px;
            background-color: var(--danger);
        }

        @media (max-width: 768px) {
            .sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            .navbar {
                left: 0;
                width: 100%;
            }
            .main-content {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar text-bg-dark d-flex flex-column flex-shrink-0 p-3">
            <a href="userhomepg.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <h4><i class="fas fa-shield-alt me-2"></i> User Portal</h4>
            </a>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="userhomepg.php" class="nav-link active">
                        <i class="fas fa-fw fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="FIRForm.php" class="nav-link text-white">
                        <i class="fas fa-fw fa-file-alt"></i> FIR Form
                    </a>
                </li>
                <li class="nav-item">
                    <a href="userFIR.php" class="nav-link text-white">
                        <i class="fas fa-fw fa-history"></i> FIR History
                    </a>
                </li>
            </ul>
            <hr>
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                    data-bs-toggle="dropdown">
                    <img src="<?php echo $default_profile_pic; ?>" alt="Profile" width="32" height="32" class="rounded-circle me-2">
                    <span><?php echo htmlspecialchars($username); ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark">
                    <li><a class="dropdown-item" href="myprofileuser.php"><i class="fas fa-user me-2"></i> My Profile</a></li>
                    <li><a class="dropdown-item" href="changepassword.php"><i class="fas fa-key me-2"></i> Change Password</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Log out</a></li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="container mt-4">
                <h2 class="mb-4">Welcome, <?php echo htmlspecialchars($username); ?></h2>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-4">
                        <div class="card stat-card primary h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                                            Total FIRs</div>
                                        <div class="h5 mb-0 font-weight-bold text-white"><?php echo $total_firs; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-file-alt fa-2x text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card stat-card success h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                                            Approved</div>
                                        <div class="h5 mb-0 font-weight-bold text-white"><?php echo $approved_firs; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check-circle fa-2x text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card stat-card warning h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                                            Pending</div>
                                        <div class="h5 mb-0 font-weight-bold text-white"><?php echo $pending_firs; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-hourglass-half fa-2x text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card stat-card danger h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-white text-uppercase mb-1">
                                            Rejected</div>
                                        <div class="h5 mb-0 font-weight-bold text-white"><?php echo $rejected_firs; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-times-circle fa-2x text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card shadow">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <a href="FIRForm.php" class="btn btn-primary btn-block mb-3">
                                    <i class="fas fa-plus me-2"></i> File New FIR
                                </a>
                                <a href="userFIR.php" class="btn btn-info btn-block">
                                    <i class="fas fa-history me-2"></i> View FIR History
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card shadow">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Recent Activity</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($total_firs == 0): ?>
                                    <p>You haven't filed any FIRs yet. Get started by filing your first FIR.</p>
                                <?php else: ?>
                                    <p>Your recent FIR activity:</p>
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Pending Review
                                            <span class="badge bg-warning rounded-pill"><?php echo $pending_firs; ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Approved FIRs
                                            <span class="badge bg-success rounded-pill"><?php echo $approved_firs; ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Rejected FIRs
                                            <span class="badge bg-danger rounded-pill"><?php echo $rejected_firs; ?></span>
                                        </li>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
