<?php
session_start();
include "conn.php";
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = "Guest";
}
$user = $_SESSION['username'];
$default_profile_pic = !empty($user['profile_pic']) ? $user['profile_pic'] : "https://www.w3schools.com/howto/img_avatar.png";

// Fetch dynamic data for dashboard cards
$total_police = 0;
$total_criminals = 0;
$total_police_stations = 0;
$total_firs = 0;

// Fetch total police count
$query = "SELECT COUNT(*) as total_police FROM police";
$result = $conn->query($query);
if ($result) {
    $total_police = $result->fetch_assoc()['total_police'];
}

// Fetch total criminals count
$query = "SELECT COUNT(*) as total_criminals FROM criminals";
$result = $conn->query($query);
if ($result) {
    $total_criminals = $result->fetch_assoc()['total_criminals'];
}

// Fetch total police stations count
$query = "SELECT COUNT(*) as total_police_stations FROM police_station";
$result = $conn->query($query);
if ($result) {
    $total_police_stations = $result->fetch_assoc()['total_police_stations'];
}

// Fetch total FIRs count
$query = "SELECT COUNT(*) as total_firs FROM FIR";
$result = $conn->query($query);
if ($result) {
    $total_firs = $result->fetch_assoc()['total_firs'];
}

// Create activity_logs table if it doesn't exist
$create_table_sql = "CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    activity_type VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    user VARCHAR(100) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($create_table_sql)) {
    // Silently handle error - don't disrupt the user experience
    error_log("Error creating activity_logs table: " . $conn->error);
}

// Function to log activity
function logActivity($conn, $activity_type, $description, $user) {
    $sql = "INSERT INTO activity_logs (activity_type, description, user) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sss", $activity_type, $description, $user);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch recent activities
$recent_activities = [];
$activity_sql = "SELECT * FROM activity_logs ORDER BY timestamp DESC LIMIT 5";
$activity_result = $conn->query($activity_sql);
if ($activity_result && $activity_result->num_rows > 0) {
    while ($row = $activity_result->fetch_assoc()) {
        $recent_activities[] = $row;
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
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
        }

        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f8f9fc;
        }

        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: linear-gradient(180deg, var(--dark-color) 0%, #1a1a1a 100%);
            padding: 20px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
            z-index: 1001;
            transition: all 0.3s;
        }

        .sidebar-collapsed {
            margin-left: calc(-1 * var(--sidebar-width));
        }

        .navbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            z-index: 1000;
            background-color: white;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
            transition: all 0.3s;
        }

        .navbar-collapsed {
            left: 0;
            width: 100%;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: 56px;
            padding: 20px;
            width: calc(100% - var(--sidebar-width));
            transition: all 0.3s;
        }

        .main-content-collapsed {
            margin-left: 0;
            width: 100%;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            border-radius: 0.35rem;
            padding: 0.75rem 1rem;
            margin-bottom: 0.2rem;
            transition: all 0.3s;
            display: flex;
            align-items: center;
        }

        .nav-link i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1) !important;
            color: white !important;
        }

        .nav-link.active {
            background-color: var(--primary-color) !important;
            color: white !important;
            font-weight: 600;
        }

        .btn-toggle {
            color: rgba(255, 255, 255, 0.8);
            border-radius: 0.35rem;
            padding: 0.75rem 1rem;
            margin-bottom: 0.2rem;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            width: 100%;
            text-align: left;
            background: none;
            border: none;
        }

        .btn-toggle:hover {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }

        .btn-toggle::before {
            width: 1.25em;
            line-height: 0;
            content: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='rgba(255,255,255,.8)' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 14l6-6-6-6'/%3e%3c/svg%3e");
            transition: transform 0.35s ease;
            transform-origin: 0.5em 50%;
            margin-right: 0.5rem;
        }

        .btn-toggle[aria-expanded="true"]::before {
            transform: rotate(90deg);
        }

        .btn-toggle-nav a {
            padding: 0.5rem 1rem 0.5rem 2.5rem;
            font-size: 0.9rem;
        }

        .dropdown-menu {
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
        }

        .dropdown-item {
            padding: 0.5rem 1.5rem;
        }

        /* Dashboard Cards */
        .dashboard-card {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1.75rem 0 rgba(33, 40, 50, 0.25);
        }

        .card-icon {
            font-size: 2rem;
            opacity: 0.3;
            position: absolute;
            right: 1.5rem;
            top: 1.5rem;
        }

        .card-title {
            font-size: 0.9rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #5a5c69;
            margin-bottom: 0.5rem;
        }

        .card-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-color);
        }

        .card-link {
            color: var(--primary-color);
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
        }

        .card-link:hover {
            text-decoration: underline;
        }

        /* Activity Styles */
        .activity-list {
            max-height: 400px;
            overflow-y: auto;
            padding-right: 5px;
        }

        .activity-list::-webkit-scrollbar {
            width: 6px;
        }

        .activity-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .activity-list::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }

        .activity-list::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        .activity-item:last-child {
            border-bottom: none !important;
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }

        .activity-icon {
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }

        .activity-item:hover .activity-icon {
            transform: scale(1.1);
        }

        .fw-medium {
            font-weight: 500;
        }

        /* Color variants for cards */
        .card-police {
            border-left: 0.25rem solid var(--primary-color);
        }

        .card-criminals {
            border-left: 0.25rem solid var(--danger-color);
        }

        .card-stations {
            border-left: 0.25rem solid var(--success-color);
        }

        .card-firs {
            border-left: 0.25rem solid var(--warning-color);
        }

        /* Toggle button for sidebar */
        #sidebarToggle {
            cursor: pointer;
            color: #d1d3e2;
        }

        #sidebarToggle:hover {
            color: var(--primary-color);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            .sidebar.show {
                margin-left: 0;
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
            <a href="homepage.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <span class="fs-4">Crime Management System</span>
            </a>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="homepage.php" class="nav-link text-white active">
                        <i class="fas fa-fw fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="mb-1">
                    <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0 collapsed text-white"
                        data-bs-toggle="collapse" data-bs-target="#police-station-collapse" aria-expanded="false">
                        <i class="fas fa-fw fa-building"></i> Police station
                    </button>
                    <div class="collapse" id="police-station-collapse">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                            <li><a href="addpolicestation.php" class="nav-link text-white"><i class="fas fa-fw fa-plus"></i> Add Police station</a></li>
                            <li><a href="managePoliceStation.php" class="nav-link text-white"><i class="fas fa-fw fa-edit"></i> Manage police station</a></li>
                        </ul>
                    </div>
                </li>
                <li class="mb-1">
                    <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0 collapsed text-white"
                        data-bs-toggle="collapse" data-bs-target="#police-collapse" aria-expanded="false">
                        <i class="fas fa-fw fa-user-shield"></i> Police
                    </button>
                    <div class="collapse" id="police-collapse">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                            <li><a href="addpolice.php" class="nav-link text-white"><i class="fas fa-fw fa-plus"></i> Add police</a></li>
                            <li><a href="managePolice.php" class="nav-link text-white"><i class="fas fa-fw fa-edit"></i> Manage police</a></li>
                        </ul>
                    </div>
                </li>
                <li><a href="manageCriminal.php" class="nav-link text-white"><i class="fas fa-fw fa-user-ninja"></i> View criminals</a></li>
                <li><a href="Firdata.php" class="nav-link text-white"><i class="fas fa-fw fa-file-alt"></i> View FIR</a></li>
                <li><a href="crime_analysis.php" class="nav-link text-white"><i class="fas fa-fw fa-chart-bar"></i> Crime Analysis</a></li>
            </ul>
            <hr>
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                    <img src="<?php echo $default_profile_pic; ?>" alt="Profile" width="32" height="32" class="rounded-circle me-2">
                    <span><?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark">
                    <li><a class="dropdown-item" href="myprofileadmin.php"><i class="fas fa-fw fa-user me-2"></i>My Profile</a></li>
                    <li><a class="dropdown-item" href="changepassword.php"><i class="fas fa-fw fa-key me-2"></i>Change Password</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="userlogin.php"><i class="fas fa-fw fa-sign-out-alt me-2"></i>Sign out</a></li>
                </ul>
            </div>
        </div>

        <!-- Navbar -->
        <nav class="navbar navbar-expand navbar-light bg-white shadow-sm w-100">
            <div class="container-fluid">
                <button class="btn" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <a href="homepage.php" class="navbar-brand ms-2"><strong>Dashboard</strong></a>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <div class="container-fluid">
                <!-- Page Heading -->
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Dashboard Overview</h1>
                </div>

                <!-- Content Row -->
                <div class="row">
                    <!-- Total Police Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card dashboard-card card-police h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="card-title">Total Police</div>
                                        <div class="card-value mb-1"><?php echo $total_police; ?></div>
                                        <a href="managePolice.php" class="card-link">
                                            View Details <i class="fas fa-arrow-right fa-sm ms-1"></i>
                                        </a>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-shield card-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Criminals Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card dashboard-card card-criminals h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="card-title">Total Criminals</div>
                                        <div class="card-value mb-1"><?php echo $total_criminals; ?></div>
                                        <a href="manageCriminal.php" class="card-link">
                                            View Details <i class="fas fa-arrow-right fa-sm ms-1"></i>
                                        </a>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-user-ninja card-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Police Stations Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card dashboard-card card-stations h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="card-title">Police Stations</div>
                                        <div class="card-value mb-1"><?php echo $total_police_stations; ?></div>
                                        <a href="managePoliceStation.php" class="card-link">
                                            View Details <i class="fas fa-arrow-right fa-sm ms-1"></i>
                                        </a>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-building card-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total FIRs Card -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card dashboard-card card-firs h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="card-title">Total FIRs</div>
                                        <div class="card-value mb-1"><?php echo $total_firs; ?></div>
                                        <a href="Firdata.php" class="card-link">
                                            View Details <i class="fas fa-arrow-right fa-sm ms-1"></i>
                                        </a>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-file-alt card-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Section -->
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between" style="background: linear-gradient(to right, var(--primary-color), #6889e0); color: white;">
                                <h6 class="m-0 font-weight-bold"><i class="fas fa-history me-2"></i>Recent Activities</h6>
                            </div>
                            <div class="card-body">
                                <div class="activity-list">
                                    <?php if (empty($recent_activities)): ?>
                                        <div class="text-center py-4 text-muted">
                                            <i class="fas fa-info-circle fa-2x mb-3"></i>
                                            <p class="mb-0">No recent activities found.</p>
                                            <p class="small">Activities will appear here as they occur.</p>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($recent_activities as $activity): ?>
                                            <div class="activity-item d-flex mb-3 pb-3" style="border-bottom: 1px solid rgba(0,0,0,0.05);">
                                                <?php
                                                // Set icon and color based on activity type
                                                $icon_class = 'fa-bell';
                                                $bg_class = 'bg-primary';

                                                switch ($activity['activity_type']) {
                                                    case 'police':
                                                        $icon_class = 'fa-user-shield';
                                                        $bg_class = 'bg-primary';
                                                        break;
                                                    case 'criminal':
                                                        $icon_class = 'fa-user-ninja';
                                                        $bg_class = 'bg-danger';
                                                        break;
                                                    case 'station':
                                                        $icon_class = 'fa-building';
                                                        $bg_class = 'bg-warning';
                                                        break;
                                                    case 'fir':
                                                        $icon_class = 'fa-file-alt';
                                                        $bg_class = 'bg-success';
                                                        break;
                                                    case 'login':
                                                        $icon_class = 'fa-sign-in-alt';
                                                        $bg_class = 'bg-info';
                                                        break;
                                                }
                                                ?>
                                                <div class="activity-icon <?php echo $bg_class; ?> text-white rounded-circle me-3 p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; flex-shrink: 0;">
                                                    <i class="fas <?php echo $icon_class; ?> fa-fw"></i>
                                                </div>
                                                <div style="flex-grow: 1;">
                                                    <div class="small text-gray-500 mb-1">
                                                        <i class="fas fa-clock me-1"></i> <?php echo date('F j, Y, g:i a', strtotime($activity['timestamp'])); ?>
                                                    </div>
                                                    <div class="mb-1" style="font-size: 0.95rem;"><?php echo $activity['description']; ?></div>
                                                    <div class="small text-muted">
                                                        <i class="fas fa-user me-1"></i> by <span class="fw-medium"><?php echo htmlspecialchars($activity['user']); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>


                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions Section -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <a href="addpolice.php" class="btn btn-primary w-100 py-3">
                                            <i class="fas fa-user-plus fa-2x mb-2"></i><br>
                                            Add Police Officer
                                        </a>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <a href="manageCriminal.php" class="btn btn-danger w-100 py-3">
                                            <i class="fas fa-user-ninja fa-2x mb-2"></i><br>
                                            Manage Criminals
                                        </a>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <a href="Firdata.php" class="btn btn-info w-100 py-3">
                                            <i class="fas fa-file-alt fa-2x mb-2"></i><br>
                                            View FIRs
                                        </a>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <a href="crime_analysis.php" class="btn btn-success w-100 py-3">
                                            <i class="fas fa-chart-bar fa-2x mb-2"></i><br>
                                            Crime Analysis
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('sidebar-collapsed');
            document.querySelector('.navbar').classList.toggle('navbar-collapsed');
            document.querySelector('.main-content').classList.toggle('main-content-collapsed');
        });

        // Mobile sidebar toggle
        if (window.innerWidth < 768) {
            document.querySelector('.sidebar').classList.add('sidebar-collapsed');
            document.querySelector('.navbar').classList.add('navbar-collapsed');
            document.querySelector('.main-content').classList.add('main-content-collapsed');
        }

        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                document.querySelector('.sidebar').classList.remove('sidebar-collapsed');
                document.querySelector('.navbar').classList.remove('navbar-collapsed');
                document.querySelector('.main-content').classList.remove('main-content-collapsed');
            }
        });
    </script>
</body>

</html>