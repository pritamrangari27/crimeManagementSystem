<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect to login if not authenticated
if (!isset($_SESSION['username']) || $_SESSION['username'] === "Guest") {
    header("Location: userlogin.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "db_crime");
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    $station_name = filter_input(INPUT_POST, 'station_name', FILTER_SANITIZE_STRING);
    $station_id = filter_input(INPUT_POST, 'station_id', FILTER_SANITIZE_STRING);
    $state = filter_input(INPUT_POST, 'state', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);

    // Check if station ID already exists
    $check_sql = "SELECT station_id FROM police_station WHERE station_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $station_id);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        $_SESSION['error_message'] = "Police Station ID already exists";
        $check_stmt->close();
        header("Location: addpolicestation.php");
        exit();
    }
    $check_stmt->close();

    // Use prepared statements to prevent SQL injection
    $sql = "INSERT INTO police_station (station_name, station_id, state, address)
            VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $station_name, $station_id, $state, $address);

    if ($stmt->execute()) {
        // Log the activity for the dashboard
        $activity_type = "station";
        $description = "<span class=\"font-weight-bold\">New police station</span> {$station_name} ({$station_id}) added in {$state}";
        $current_user = $_SESSION['username'];

        // Create activity_logs table if it doesn't exist
        $create_table_sql = "CREATE TABLE IF NOT EXISTS activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            activity_type VARCHAR(50) NOT NULL,
            description TEXT NOT NULL,
            user VARCHAR(100) NOT NULL,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        $conn->query($create_table_sql);

        // Insert the activity log
        $log_sql = "INSERT INTO activity_logs (activity_type, description, user) VALUES (?, ?, ?)";
        $log_stmt = $conn->prepare($log_sql);
        if ($log_stmt) {
            $log_stmt->bind_param("sss", $activity_type, $description, $current_user);
            $log_stmt->execute();
            $log_stmt->close();
        }

        $_SESSION['success_message'] = "Police station added successfully!";
        header("Location: addpolicestation.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error: " . $stmt->error;
        header("Location: addpolicestation.php");
        exit();
    }

    $stmt->close();
    $conn->close();
    exit();
}

$username = $_SESSION['username'];
$default_profile_pic = !empty($user['profile_pic']) ? $user['profile_pic'] : "https://www.w3schools.com/howto/img_avatar.png";
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Police Station</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --sidebar-width: 280px;
            --navbar-height: 56px;
        }

        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #212529;
            padding: 20px;
            transition: all 0.3s;
            z-index: 1000;
        }

        .navbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            height: var(--navbar-height);
            z-index: 999;
            transition: all 0.3s;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--navbar-height);
            padding: 20px;
            width: calc(100% - var(--sidebar-width));
            transition: all 0.3s;
        }

        .nav-link {
            color: white;
            transition: all 0.2s;
        }

        .nav-link:hover, .nav-link:focus {
            background-color: rgba(42, 42, 232, 0.8) !important;
            color: white !important;
            transform: translateX(5px);
        }

        .nav-link.active {
            background-color: rgba(0, 0, 255, 0.9) !important;
            color: white !important;
            font-weight: 500;
        }

        .btn-toggle::before {
            width: 1.25em;
            line-height: 0;
            content: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='rgba(255,255,255,.8)' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 14l6-6-6-6'/%3e%3c/svg%3e");
            transition: transform 0.35s ease;
            transform-origin: 0.5em 50%;
        }

        .btn-toggle[aria-expanded="true"]::before {
            transform: rotate(90deg);
        }

        @media (max-width: 992px) {
            .sidebar {
                left: -280px;
            }

            .sidebar.active {
                left: 0;
            }

            .navbar, .main-content {
                left: 0;
                width: 100%;
            }

            .main-content {
                margin-left: 0;
            }
        }

        .form-control:focus, .form-select:focus {
            border-color: #212529;
            box-shadow: 0 0 0 0.25rem rgba(33, 37, 41, 0.25);
        }

        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 1100;
        }

        @media (max-width: 992px) {
            .sidebar-toggle {
                display: block;
            }
        }
    </style>
</head>

<body>
    <button class="btn btn-dark sidebar-toggle d-lg-none" type="button">
        <i class="bi bi-list"></i>
    </button>

    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar text-bg-dark d-flex flex-column flex-shrink-0 p-3">
            <a href="addpolicestation.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <h4><i class="bi bi-shield-lock me-2"></i>Police Portal</h4>
            </a>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item"><a href="addpolicestation.php" class="nav-link text-white"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                <li class="mb-1">
                    <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0 collapsed text-white w-100 active"
                        data-bs-toggle="collapse" data-bs-target="#police-station-collapse" aria-expanded="true">
                        <i class="bi bi-building me-2"></i>Police station
                    </button>
                    <div class="collapse show" id="police-station-collapse">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                            <li><a href="addpolicestation.php" class="nav-link text-white active"><i class="bi bi-plus-circle me-2"></i>Add Station</a></li>
                            <li><a href="managePoliceStation.php" class="nav-link text-white"><i class="bi bi-gear me-2"></i>Manage Stations</a></li>
                        </ul>
                    </div>
                </li>
                <li class="mb-1">
                    <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0 collapsed text-white w-100"
                        data-bs-toggle="collapse" data-bs-target="#police-collapse" aria-expanded="false">
                        <i class="bi bi-people me-2"></i>Police
                    </button>
                    <div class="collapse" id="police-collapse">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                            <li><a href="addpolice.php" class="nav-link text-white"><i class="bi bi-person-plus me-2"></i>Add Police</a></li>
                            <li><a href="managePolice.php" class="nav-link text-white"><i class="bi bi-person-gear me-2"></i>Manage Police</a></li>
                        </ul>
                    </div>
                </li>
                <li><a href="manageCriminal.php" class="nav-link text-white"><i class="bi bi-person-badge me-2"></i>View Criminals</a></li>
                <li><a href="Firdata.php" class="nav-link text-white"><i class="bi bi-file-earmark-text me-2"></i>View FIR</a></li>
            </ul>
            <hr>
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                    <img src="<?php echo htmlspecialchars($default_profile_pic); ?>" alt="Profile" width="32" height="32" class="rounded-circle me-2">
                    <span><?php echo htmlspecialchars($username); ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark">
                    <li><a class="dropdown-item" href="myprofilepolice.php"><i class="bi bi-person-circle me-2"></i>My Profile</a></li>
                    <li><a class="dropdown-item" href="changepassword.php"><i class="bi bi-key me-2"></i>Change Password</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Sign out</a></li>
                </ul>
            </div>
        </div>

        <!-- Navbar -->
        <nav class="navbar navbar-dark bg-dark shadow-sm">
            <div class="container-fluid">
                <a href="addpolicestation.php" class="navbar-brand"><i class="bi bi-building-add me-2"></i><strong>Add Police Station</strong></a>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <div class="container mt-4">
                <?php
                // Display messages
                if (isset($_SESSION['success_message'])) {
                    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i>' . htmlspecialchars($_SESSION['success_message']) . '
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                          </div>';
                    unset($_SESSION['success_message']);
                }

                if (isset($_SESSION['error_message'])) {
                    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>' . htmlspecialchars($_SESSION['error_message']) . '
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                          </div>';
                    unset($_SESSION['error_message']);
                }
                ?>

                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="bi bi-building-add me-2"></i>Add Police Station Details</h5>
                    </div>
                    <div class="card-body">
                        <form id="managePoliceForm" action="addpolicestation.php" method="POST" novalidate>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="station_name" class="form-label">Station Name</label>
                                    <select class="form-select" name="station_name" id="station_name" required>
                                        <option value="" selected disabled>Select Station Name</option>
                                        <option value="Wakad Police Station">Wakad Police Station (SY101)</option>
                                        <option value="PCMC Police Station">PCMC Police Station (SY102)</option>
                                        <option value="Khadki Police Station">Khadki Police Station (SY103)</option>
                                        <option value="Kharadi Police Station">Kharadi Police Station (SY104)</option>
                                        <option value="Katraj Police Station">Katraj Police Station (SY105)</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a station name</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="station_id" class="form-label">Station ID</label>
                                    <select class="form-select" name="station_id" id="station_id" required>
                                        <option value="" selected disabled>Select Station ID</option>
                                        <option value="SY101">SY101</option>
                                        <option value="SY102">SY102</option>
                                        <option value="SY103">SY103</option>
                                        <option value="SY104">SY104</option>
                                        <option value="SY105">SY105</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a station ID</div>
                                </div>

                                <div class="col-md-6">
                                    <label for="state" class="form-label">State</label>
                                    <select class="form-select" name="state" id="state" required>
                                        <option value="" selected disabled>Select State</option>
                                        <option value="Maharashtra">Maharashtra</option>
                                        <option value="Madhya Pradesh">Madhya Pradesh</option>
                                        <option value="Goa">Goa</option>
                                        <option value="Uttar Pradesh">Uttar Pradesh</option>
                                        <option value="Bihar">Bihar</option>
                                        <option value="Rajasthan">Rajasthan</option>
                                        <option value="Kerala">Kerala</option>
                                        <option value="Karnataka">Karnataka</option>
                                        <option value="Haryana">Haryana</option>
                                        <option value="Punjab">Punjab</option>
                                        <option value="Telangana">Telangana</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a state</div>
                                </div>

                                <div class="col-12">
                                    <label for="address" class="form-label">Detailed Address</label>
                                    <textarea class="form-control" name="address" id="address" rows="3" required></textarea>
                                    <div class="invalid-feedback">Please provide the station's address</div>
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-dark px-4">
                                        <i class="bi bi-building-add me-2"></i>Add Police Station
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery, Bootstrap JS, and custom scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle sidebar on mobile
        document.querySelector('.sidebar-toggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Form validation
        (function() {
            'use strict';

            // Fetch the form we want to apply custom Bootstrap validation styles to
            const form = document.getElementById('managePoliceForm');

            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                form.classList.add('was-validated');
            }, false);

            // Link station name and ID selects
            const stationNameSelect = document.getElementById('station_name');
            const stationIdSelect = document.getElementById('station_id');

            stationNameSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const stationId = selectedOption.textContent.match(/\(([^)]+)\)/)[1];

                for (let option of stationIdSelect.options) {
                    if (option.value === stationId) {
                        option.selected = true;
                        break;
                    }
                }
            });

            stationIdSelect.addEventListener('change', function() {
                const selectedId = this.value;
                const stationNameRegex = new RegExp(selectedId + "\\)$");

                for (let option of stationNameSelect.options) {
                    if (stationNameRegex.test(option.textContent)) {
                        option.selected = true;
                        break;
                    }
                }
            });

            // Auto-scroll to top for messages
            if (window.location.hash === "#success" || document.querySelector('.alert')) {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        })();
    </script>
</body>
</html>