<?php
session_start();
include "conn.php";

// Set default username if not logged in
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = "Guest";
}

$user = $_SESSION['username'];
$default_profile_pic = !empty($user['profile_pic']) ? $user['profile_pic'] : "https://www.w3schools.com/howto/img_avatar.png";

// Fetch FIR count for the logged-in police station
function fetchFirCount($conn, $station_id) {
    $query = "SELECT COUNT(*) as sent_firs FROM FIR WHERE status = 'Sent' AND station_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $station_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        $row = $result->fetch_assoc();
        return $row['sent_firs'];
    } else {
        return 0; // Fallback if query fails
    }
}

// Check if the request is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($isAjax) {
    // Return JSON for AJAX requests
    $station_id = $_SESSION['station_id']; // Get station_id from session
    $count = fetchFirCount($conn, $station_id);
    echo json_encode(['status' => 'success', 'count' => $count]);
    exit();
}

// If it's not an AJAX request, render the full HTML page
$station_id = $_SESSION['station_id']; // Get station_id from session
$count = fetchFirCount($conn, $station_id); // Fetch FIR count for the page
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Police Officer</title>
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
            overflow-y: auto; /* Added scroll for sidebar if content is long */
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
            padding: 0.5rem 1rem;
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

        /* Notification bell styling */
        .notification-bell {
            color: var(--primary-color);
            font-size: 1.25rem;
            margin-left: 1rem;
            position: relative;
            transition: all 0.3s;
        }

        .notification-bell:hover {
            color: var(--accent-color);
            transform: scale(1.1);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 0.7rem;
            min-width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--danger-color);
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
            <a href="policehomepg.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <span class="fs-4">Police Dashboard</span>
            </a>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="policehomepg.php" class="nav-link text-white active">
                        <i class="fas fa-fw fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="mb-1">
                    <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0 collapsed text-white"
                        data-bs-toggle="collapse" data-bs-target="#criminals-collapse" aria-expanded="false">
                        <i class="fas fa-fw fa-user-ninja"></i> Criminals
                    </button>
                    <div class="collapse" id="criminals-collapse">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                            <li><a href="addCriminal.php" class="nav-link text-white"><i class="fas fa-fw fa-plus"></i> Add criminal</a></li>
                            <li><a href="manageCriminal.php" class="nav-link text-white"><i class="fas fa-fw fa-edit"></i> Manage criminal</a></li>
                        </ul>
                    </div>
                </li>
                <li class="mb-1">
                    <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0 collapsed text-white"
                        data-bs-toggle="collapse" data-bs-target="#fir-collapse" aria-expanded="false">
                        <i class="fas fa-fw fa-file-alt"></i> FIR
                    </button>
                    <div class="collapse" id="fir-collapse">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                            <li><a href="approvedFir.php" class="nav-link text-white"><i class="fas fa-fw fa-check-circle"></i> Approved FIR</a></li>
                            <li><a href="rejectedFir.php" class="nav-link text-white"><i class="fas fa-fw fa-times-circle"></i> Rejected FIR</a></li>
                        </ul>
                    </div>
                </li>
            </ul>
            <hr>
            <div class="dropdown mt-auto">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                    <img src="<?php echo $default_profile_pic; ?>" alt="Profile" width="32" height="32" class="rounded-circle me-2">
                    <span><?php echo htmlspecialchars($_SESSION['username'] ?? 'Guest'); ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark">
                    <li><a class="dropdown-item" href="myprofilepolice.php"><i class="fas fa-fw fa-user me-2"></i>My Profile</a></li>
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
                <a class="navbar-brand ms-2"><strong>Police Officer Dashboard</strong></a>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a href="sentFir.php" class="nav-link notification-bell position-relative">
                            <i class="fas fa-bell"></i>
                            <span id="firCount" class="notification-badge badge rounded-pill"><?php echo $count; ?></span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <div class="container-fluid">
                <h1 class="h3 mb-4 text-gray-800">Dashboard</h1>
                <!-- Simple welcome message -->
                <div class="card shadow">
                    <div class="card-body">
                        <h5 class="card-title">Welcome, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Officer'); ?></h5>
                        <p class="card-text">You have <span id="firCountText" class="fw-bold"><?php echo $count; ?></span> new FIR(s).</p>
                        <a href="sentFir.php" class="btn btn-primary">View New FIRs</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Toggle sidebar
            $('#sidebarToggle').click(function() {
                $('.sidebar').toggleClass('sidebar-collapsed');
                $('.navbar').toggleClass('navbar-collapsed');
                $('.main-content').toggleClass('main-content-collapsed');
            });

            // Mobile sidebar toggle
            if (window.innerWidth < 768) {
                $('.sidebar').addClass('sidebar-collapsed');
                $('.navbar').addClass('navbar-collapsed');
                $('.main-content').addClass('main-content-collapsed');
            }

            $(window).resize(function() {
                if (window.innerWidth >= 768) {
                    $('.sidebar').removeClass('sidebar-collapsed');
                    $('.navbar').removeClass('navbar-collapsed');
                    $('.main-content').removeClass('main-content-collapsed');
                }
            });

            // FIR count update
            function fetchFIRCount() {
                $.ajax({
                    url: 'policehomepg.php',
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#firCount').text(response.count).show();
                            $('#firCountText').text(response.count);
                        } else {
                            $('#firCount').hide();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching FIR count: " + error);
                        $('#firCount').text('0').show(); // Fallback
                        $('#firCountText').text('0');
                    }
                });
            }

            setInterval(fetchFIRCount, 5000); // Refresh every 5 seconds
        });
    </script>
</body>
</html>