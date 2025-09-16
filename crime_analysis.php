<?php
session_start();
include "conn.php";

if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = "Guest";
}
$user = $_SESSION['username'];
$default_profile_pic = !empty($user['profile_pic']) ? $user['profile_pic'] : "https://www.w3schools.com/howto/img_avatar.png";

// Fetch crime data for the graph
$query = "SELECT crime_type, COUNT(*) as count FROM FIR GROUP BY crime_type";
$result = $conn->query($query);

$crime_data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $crime_data[] = $row;
    }
}

// Convert data to JSON for Chart.js
$crime_labels = [];
$crime_counts = [];

foreach ($crime_data as $crime) {
    $crime_labels[] = $crime["crime_type"];
    $crime_counts[] = $crime["count"];
}

// Return JSON response if requested via AJAX
if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'labels' => $crime_labels,
        'counts' => $crime_counts
    ]);
    exit();
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crime Analysis</title>
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
            --chart-bg: #1a1a2e;
            --chart-text: #e6f7ff;
            --chart-grid: rgba(255, 255, 255, 0.1);
        }

        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--secondary-color);
        }

        /* Sidebar Styling */
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

        .sidebar a {
            color: white;
            text-decoration: none;
        }

        .sidebar a:hover {
            color: var(--primary-color);
        }

        .sidebar .nav-link {
            padding: 10px 15px;
            border-radius: 5px;
            transition: background-color 0.2s ease-in-out;
        }

        .sidebar .nav-link:hover {
            background-color: var(--primary-color) !important;
        }

        .sidebar .nav-link.active {
            background-color: var(--accent-color) !important;
        }

        /* Navbar Styling */
        .navbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            z-index: 1000;
            background-color: white;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
        }

        /* Main Content Styling */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: 56px;
            padding: 30px;
            width: calc(100% - var(--sidebar-width));
            min-height: calc(100vh - 56px);
        }

        /* Chart Container */
        .chart-container {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
            padding: 2rem;
            margin-bottom: 2rem;
            height: 500px;
            position: relative;
            max-width: 800px; /* Reduced width */
            margin: 0 auto; /* Center the container */
        }

        .chart-header {
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-weight: 700;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chart-header i {
            font-size: 1.5rem;
            margin-right: 0.5rem;
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
            .chart-container {
                padding: 1rem;
                height: 400px;
                max-width: 100%;
            }
        }

        /* Toggle button for sidebar */
        #sidebarToggle {
            cursor: pointer;
            color: #d1d3e2;
        }

        #sidebarToggle:hover {
            color: var(--primary-color);
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar text-bg-dark d-flex flex-column flex-shrink-0 p-3">
            <a href="homepage.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <h4>Crime Analysis</h4>
            </a>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item"><a href="homepage.php" class="nav-link text-white">Dashboard</a></li>
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
                <li>
                    <a href="crime_analysis.php" class="nav-link text-white active"><i class="fas fa-fw fa-chart-bar"></i> Crime Analysis</a>
                </li>
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
                <a class="navbar-brand ms-2"><strong>Crime Analysis Dashboard</strong></a>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a href="#" class="nav-link" onclick="fetchCrimeData()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <div class="container-fluid">
                <div class="chart-container">
                    <div class="chart-header">
                        <span>
                            <i class="fas fa-chart-bar"></i> Crime Distribution by Type
                        </span>
                        <button class="btn btn-sm btn-primary" onclick="fetchCrimeData()">
                            <i class="fas fa-sync-alt"></i> Refresh Data
                        </button>
                    </div>
                    <canvas id="crimeChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize the chart
        const ctx = document.getElementById("crimeChart").getContext("2d");
        let crimeChart;

        // Function to fetch crime data via AJAX
        function fetchCrimeData() {
            fetch('crime_analysis.php?ajax=1')
                .then(response => response.json())
                .then(data => {
                    // Update the chart data
                    crimeChart.data.labels = data.labels;
                    crimeChart.data.datasets[0].data = data.counts;
                    crimeChart.update();
                    
                    // Show success toast
                    showToast('Data refreshed successfully', 'success');
                })
                .catch(error => {
                    console.error('Error fetching crime data:', error);
                    showToast('Error refreshing data', 'danger');
                });
        }

        // Create the chart
        function createChart(labels, counts) {
            crimeChart = new Chart(ctx, {
                type: "bar",
                data: {
                    labels: labels,
                    datasets: [{
                        label: "Number of Crimes",
                        data: counts,
                        backgroundColor: [
                            'rgba(78, 115, 223, 0.8)',
                            'rgba(28, 200, 138, 0.8)',
                            'rgba(246, 194, 62, 0.8)',
                            'rgba(231, 74, 59, 0.8)',
                            'rgba(54, 185, 204, 0.8)',
                            'rgba(142, 68, 173, 0.8)'
                        ],
                        borderColor: [
                            'rgba(78, 115, 223, 1)',
                            'rgba(28, 200, 138, 1)',
                            'rgba(246, 194, 62, 1)',
                            'rgba(231, 74, 59, 1)',
                            'rgba(54, 185, 204, 1)',
                            'rgba(142, 68, 173, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 12
                            },
                            callbacks: {
                                label: function(context) {
                                    return `${context.dataset.label}: ${context.raw}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: "Number of Crimes",
                                font: {
                                    size: 14,
                                    weight: "bold"
                                },
                                color: '#5a5c69'
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            ticks: {
                                color: '#5a5c69',
                                stepSize: 1
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: "Crime Type",
                                font: {
                                    size: 14,
                                    weight: "bold"
                                },
                                color: '#5a5c69'
                            },
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#5a5c69',
                                autoSkip: false,
                                maxRotation: 0, // Horizontal labels
                                minRotation: 0  // Horizontal labels
                            }
                        }
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutQuart'
                    },
                    // Adjust bar width and spacing
                    barPercentage: 0.6,
                    categoryPercentage: 0.8
                }
            });
        }

        // Show toast notification
        function showToast(message, type) {
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type} border-0`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            toast.style.position = 'fixed';
            toast.style.bottom = '20px';
            toast.style.right = '20px';
            toast.style.zIndex = '9999';
            
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            `;
            
            document.body.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            // Remove toast after it hides
            toast.addEventListener('hidden.bs.toast', function() {
                toast.remove();
            });
        }

        // Initial chart creation
        createChart(<?php echo json_encode($crime_labels); ?>, <?php echo json_encode($crime_counts); ?>);

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
                document.querySelector('.sidebar').removeClass('sidebar-collapsed');
                document.querySelector('.navbar').removeClass('navbar-collapsed');
                document.querySelector('.main-content').removeClass('main-content-collapsed');
            }
        });
    </script>
</body>
</html>