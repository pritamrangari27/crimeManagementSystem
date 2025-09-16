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

$username = $_SESSION['username'];
$default_profile_pic = !empty($user['profile_pic']) ? $user['profile_pic'] : "https://www.w3schools.com/howto/img_avatar.png";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    $station_name = filter_input(INPUT_POST, 'station_name', FILTER_SANITIZE_STRING);
    $station_id = filter_input(INPUT_POST, 'station_id', FILTER_SANITIZE_STRING);
    $crime_type = filter_input(INPUT_POST, 'crime_type', FILTER_SANITIZE_STRING);
    $crime_date = filter_input(INPUT_POST, 'crime_date', FILTER_SANITIZE_STRING);
    $crime_time = filter_input(INPUT_POST, 'crime_time', FILTER_SANITIZE_STRING);
    $Prison_name = filter_input(INPUT_POST, 'Prison_name', FILTER_SANITIZE_STRING);
    $Court_name = filter_input(INPUT_POST, 'Court_name', FILTER_SANITIZE_STRING);
    $Criminal_name = filter_input(INPUT_POST, 'Criminal_name', FILTER_SANITIZE_STRING);
    $contact = filter_input(INPUT_POST, 'contact', FILTER_SANITIZE_STRING);
    $DateOfBirth = filter_input(INPUT_POST, 'DateOfBirth', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $state = filter_input(INPUT_POST, 'state', FILTER_SANITIZE_STRING);
    $city = filter_input(INPUT_POST, 'city', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Invalid email format";
        header("Location: addCriminal.php");
        exit();
    }

    // Validate phone number (basic validation)
    if (!preg_match('/^[0-9]{10,15}$/', $contact)) {
        $_SESSION['error_message'] = "Invalid phone number format";
        header("Location: addCriminal.php");
        exit();
    }

    // Handle file upload
    $file = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['photo']['tmp_name'];
        $fileName = uniqid() . '_' . basename($_FILES['photo']['name']);
        $uploadDirectory = 'uploads/';
        
        // Ensure the uploads directory exists
        if (!file_exists($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true);
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($fileTmpPath);
        
        if (!in_array($fileType, $allowedTypes)) {
            $_SESSION['error_message'] = "Only JPG, PNG, and GIF files are allowed";
            header("Location: addCriminal.php");
            exit();
        }
        
        $dest_path = $uploadDirectory . $fileName;
        
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $file = $dest_path;
        } else {
            $_SESSION['error_message'] = "Failed to upload photo";
            header("Location: addCriminal.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Photo is required";
        header("Location: addCriminal.php");
        exit();
    }

    // Insert data into the criminals table
    $sql = "INSERT INTO criminals (station_name, station_id, crime_type, crime_date, crime_time, Prison_name, 
            Court_name, Criminal_name, contact, DateOfBirth, email, state, city, address, photo) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        $_SESSION['error_message'] = "Database error: " . $conn->error;
        header("Location: addCriminal.php");
        exit();
    }

    $stmt->bind_param("sssssssssssssss", $station_name, $station_id, $crime_type, $crime_date, $crime_time, 
                      $Prison_name, $Court_name, $Criminal_name, $contact, $DateOfBirth, $email, 
                      $state, $city, $address, $file);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Criminal record added successfully!";
        header("Location: addCriminal.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error: " . $stmt->error;
        header("Location: addCriminal.php");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Criminal</title>
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
        
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            margin-top: 10px;
            display: none;
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
            <a href="policehomepg.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <h4><i class="bi bi-shield-lock me-2"></i>Police Portal</h4>
            </a>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item"><a href="policehomepg.php" class="nav-link text-white"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                <li class="mb-1">
                    <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0 collapsed text-white w-100 active"
                        data-bs-toggle="collapse" data-bs-target="#criminals-collapse" aria-expanded="true">
                        <i class="bi bi-person-badge me-2"></i>Criminals
                    </button>
                    <div class="collapse show" id="criminals-collapse">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                            <li><a href="addCriminal.php" class="nav-link text-white active"><i class="bi bi-person-plus me-2"></i>Add Criminal</a></li>
                            <li><a href="manageCriminal.php" class="nav-link text-white"><i class="bi bi-person-gear me-2"></i>Manage Criminals</a></li>
                        </ul>
                    </div>
                </li>
                <li class="mb-1">
                    <button class="btn btn-toggle d-inline-flex align-items-center rounded border-0 collapsed text-white w-100"
                        data-bs-toggle="collapse" data-bs-target="#fir-collapse" aria-expanded="false">
                        <i class="bi bi-file-earmark-text me-2"></i>FIR
                    </button>
                    <div class="collapse" id="fir-collapse">
                        <ul class="btn-toggle-nav list-unstyled fw-normal pb-1 small">
                            <li><a href="sentFir.php" class="nav-link text-white"><i class="bi bi-file-earmark-plus me-2"></i>New FIR</a></li>
                            <li><a href="approvedFir.php" class="nav-link text-white"><i class="bi bi-file-earmark-check me-2"></i>Approved FIR</a></li>
                            <li><a href="rejectedFir.php" class="nav-link text-white"><i class="bi bi-file-earmark-x me-2"></i>Rejected FIR</a></li>
                            <li><a href="Firdata.php" class="nav-link text-white"><i class="bi bi-files me-2"></i>All FIR</a></li>
                        </ul>
                    </div>
                </li>
            </ul>
            <hr>
            <div class="dropdown">
                <a href="policehomepg.php" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
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
                <a href="addCriminal.php" class="navbar-brand"><i class="bi bi-person-plus me-2"></i><strong>Add Criminal Record</strong></a>
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
                        <h5 class="mb-0"><i class="bi bi-person-plus me-2"></i>Criminal Details</h5>
                    </div>
                    <div class="card-body">
                        <form id="criminalForm" action="addCriminal.php" method="POST" enctype="multipart/form-data" novalidate>
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
                                    <label for="crime_type" class="form-label">Crime Type</label>
                                    <select class="form-select" name="crime_type" id="crime_type" required>
                                        <option value="" selected disabled>Select Crime Type</option>
                                        <option value="Personal Crime">Personal Crime</option>
                                        <option value="Property Crime">Property Crime</option>
                                        <option value="Sexual Assault Crime">Sexual Assault Crime</option>
                                        <option value="Financial Crime">Financial Crime</option>
                                        <option value="Cyber Crime">Cyber Crime</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a crime type</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="Criminal_name" class="form-label">Criminal Name</label>
                                    <input type="text" name="Criminal_name" class="form-control" id="Criminal_name" required>
                                    <div class="invalid-feedback">Please provide criminal name</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="crime_date" class="form-label">Crime Date</label>
                                    <input type="date" name="crime_date" class="form-control" id="crime_date" required>
                                    <div class="invalid-feedback">Please select crime date</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="crime_time" class="form-label">Crime Time</label>
                                    <input type="time" name="crime_time" class="form-control" id="crime_time" required>
                                    <div class="invalid-feedback">Please select crime time</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="Prison_name" class="form-label">Prison Name</label>
                                    <input type="text" name="Prison_name" class="form-control" id="Prison_name" required>
                                    <div class="invalid-feedback">Please provide prison name</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="Court_name" class="form-label">Court Name</label>
                                    <input type="text" name="Court_name" class="form-control" id="Court_name" required>
                                    <div class="invalid-feedback">Please provide court name</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="contact" class="form-label">Contact Number</label>
                                    <input type="tel" name="contact" class="form-control" id="contact" required>
                                    <div class="invalid-feedback">Please provide valid contact number</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="DateOfBirth" class="form-label">Date of Birth</label>
                                    <input type="date" name="DateOfBirth" class="form-control" id="DateOfBirth" required>
                                    <div class="invalid-feedback">Please select date of birth</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control" id="email" required>
                                    <div class="invalid-feedback">Please provide valid email address</div>
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
                                
                                <div class="col-md-6">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" name="city" class="form-control" id="city" required>
                                    <div class="invalid-feedback">Please provide city</div>
                                </div>
                                
                                <div class="col-12">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" name="address" id="address" rows="3" required></textarea>
                                    <div class="invalid-feedback">Please provide address</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="photo" class="form-label">Photo</label>
                                    <input class="form-control" type="file" name="photo" id="photo" accept="image/*" required>
                                    <div class="invalid-feedback">Please upload a photo</div>
                                    <img id="previewImage" class="preview-image img-thumbnail" alt="Preview">
                                </div>
                                
                                <div class="col-12">
                                    <button type="submit" class="btn btn-dark px-4">
                                        <i class="bi bi-person-plus me-2"></i>Add Criminal Record
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
            const form = document.getElementById('criminalForm');
            
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                form.classList.add('was-validated');
            }, false);
            
            // Phone number validation
            const phoneInput = document.getElementById('contact');
            phoneInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length > 10) {
                    this.value = this.value.slice(0, 10);
                }
            });
            
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
            
            // Image preview
            const photoInput = document.getElementById('photo');
            const previewImage = document.getElementById('previewImage');
            
            photoInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result;
                        previewImage.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                } else {
                    previewImage.style.display = 'none';
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