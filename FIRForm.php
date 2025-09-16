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
$username = $_SESSION['username'];
$default_profile_pic = "https://www.w3schools.com/howto/img_avatar.png";

// Initialize variables
$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    $station_id = filter_input(INPUT_POST, 'station_id', FILTER_SANITIZE_STRING);
    $crime_type = filter_input(INPUT_POST, 'crime_type', FILTER_SANITIZE_STRING);
    $accused = filter_input(INPUT_POST, 'accused', FILTER_SANITIZE_STRING);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT);
    $number = filter_input(INPUT_POST, 'number', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $relation = filter_input(INPUT_POST, 'relation', FILTER_SANITIZE_STRING);
    $purpose = filter_input(INPUT_POST, 'purpose', FILTER_SANITIZE_STRING);

    // Validate inputs
    if (empty($station_id) || empty($crime_type) || empty($accused) || empty($name) || 
        empty($age) || empty($number) || empty($address) || empty($relation) || empty($purpose)) {
        $error_message = "All fields are required!";
    } elseif ($age < 1 || $age > 120) {
        $error_message = "Please enter a valid age (1-120).";
    } elseif (!preg_match('/^[0-9]{10,15}$/', $number)) {
        $error_message = "Invalid phone number format";
    } elseif (!isset($_FILES['file']) || $_FILES['file']['error'] == UPLOAD_ERR_NO_FILE) {
        $error_message = "Evidence file is required";
    } else {
        // Handle file upload
        $fileTmpPath = $_FILES['file']['tmp_name'];
        $fileName = uniqid() . '_' . basename($_FILES['file']['name']);
        $uploadDirectory = 'uploads/';
        
        if (!file_exists($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true);
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
        $fileType = mime_content_type($fileTmpPath);
        
        if (!in_array($fileType, $allowedTypes)) {
            $error_message = "Only JPG, PNG, GIF, and PDF files are allowed";
        } else {
            $dest_path = $uploadDirectory . $fileName;
            
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                // Verify the selected station handles this crime type
                $checkStationSql = "SELECT station_name FROM police WHERE station_id = ? AND crime_type = ?";
                $checkStmt = $conn->prepare($checkStationSql);
                $checkStmt->bind_param("ss", $station_id, $crime_type);
                $checkStmt->execute();
                $result = $checkStmt->get_result();

                if ($result->num_rows === 0) {
                    $error_message = "The selected police station doesn't handle this type of crime.";
                } else {
                    $station = $result->fetch_assoc();
                    $station_name = $station['station_name'];

                    // Insert data into the FIR table with user_id
                    $sql = "INSERT INTO FIR (user_id, station_name, station_id, crime_type, accused, name, age, number, address, relation, purpose, file, status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Sent')";
                    $stmt = $conn->prepare($sql);

                    if (!$stmt) {
                        $error_message = "Database error: " . $conn->error;
                    } else {
                        $stmt->bind_param("issssissssss", $user_id, $station_name, $station_id, $crime_type, $accused, $name, $age, $number, $address, $relation, $purpose, $dest_path);

                        if ($stmt->execute()) {
                            // Insert a notification for the police station
                            $message = "New FIR filed: Crime Type - $crime_type";
                            $notificationSql = "INSERT INTO notifications (station_id, message) VALUES (?, ?)";
                            $notificationStmt = $conn->prepare($notificationSql);
                            $notificationStmt->bind_param("ss", $station_id, $message);
                            $notificationStmt->execute();
                            $notificationStmt->close();

                            $success_message = "FIR submitted successfully to $station_name!";
                        } else {
                            $error_message = "Error submitting FIR: " . $stmt->error;
                        }
                        $stmt->close();
                    }
                    $checkStmt->close();
                }
            } else {
                $error_message = "Failed to upload file";
            }
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FIR Form</title>
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
        
        .preview-container {
            margin-top: 10px;
        }
        
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            display: none;
        }
        
        .preview-pdf {
            display: none;
            width: 100%;
            height: 500px;
            border: 1px solid #ddd;
            margin-top: 10px;
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
            <a href="userhomepg.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <h4><i class="bi bi-shield-lock me-2"></i>User Portal</h4>
            </a>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item"><a href="userhomepg.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
                <li class="nav-item"><a href="FIRForm.php" class="nav-link active"><i class="bi bi-file-earmark-plus me-2"></i>FIR Form</a></li>
                <li class="nav-item"><a href="userFIR.php" class="nav-link"><i class="bi bi-clock-history me-2"></i>FIR History</a></li>
            </ul>
            <hr>
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                    <img src="<?php echo htmlspecialchars($default_profile_pic); ?>" alt="Profile" width="32" height="32" class="rounded-circle me-2">
                    <span><?php echo htmlspecialchars($username); ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark">
                    <li><a class="dropdown-item" href="myprofileuser.php"><i class="bi bi-person-circle me-2"></i>My Profile</a></li>
                    <li><a class="dropdown-item" href="changepassword.php"><i class="bi bi-key me-2"></i>Change Password</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Log out</a></li>
                </ul>
            </div>
        </div>

        <!-- Navbar -->
        <nav class="navbar navbar-dark bg-dark shadow-sm">
            <div class="container-fluid">
                <a href="FIRForm.php" class="navbar-brand"><i class="bi bi-file-earmark-plus me-2"></i><strong>FIR Form</strong></a>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <div class="container mt-4">
                <?php if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i><?php echo htmlspecialchars($success_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error_message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="bi bi-file-earmark-plus me-2"></i>FIR Registration Form</h5>
                    </div>
                    <div class="card-body">
                        <form id="firForm" action="FIRForm.php" method="POST" enctype="multipart/form-data" novalidate>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="crime_type" class="form-label">Crime Type</label>
                                    <select class="form-select" name="crime_type" id="crime_type" required>
                                        <option value="" selected disabled>Select Crime Type</option>
                                        <option value="Sexual Harassment">Sexual Harassment</option>
                                        <option value="Kidnapping & Abduction">Kidnapping & Abduction</option>
                                        <option value="Phishing & Online Fraud">Phishing & Online Fraud</option>
                                        <option value="Rape & Sexual Assault">Rape & Sexual Assault</option>
                                        <option value="Drunk Driving (DUI/DWI)">Drunk Driving (DUI/DWI)</option>
                                        <option value="Lottery & Fake Prize Scams">Lottery & Fake Prize Scams</option>
                                        <option value="Fake Currency Circulation">Fake Currency Circulation</option>
                                    </select>
                                    <div class="invalid-feedback">Please select a crime type</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="station_id" class="form-label">Police Station</label>
                                    <select class="form-select" name="station_id" id="station_id" required>
                                        <option value="" selected disabled>Select Police Station</option>
                                        <?php
                                        // Fetch stations with their crime types
                                        $stationsSql = "SELECT station_id, station_name, crime_type FROM police";
                                        $stationsResult = $conn->query($stationsSql);
                                        
                                        if ($stationsResult->num_rows > 0) {
                                            while($station = $stationsResult->fetch_assoc()) {
                                                echo "<option value='{$station['station_id']}' data-crime-type='{$station['crime_type']}'>
                                                    {$station['station_name']} - {$station['crime_type']}
                                                </option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                    <div class="invalid-feedback">Please select a police station</div>
                                    <small class="text-muted">Only stations that handle the selected crime type will be available</small>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="accused" class="form-label">Name of Accused</label>
                                    <input type="text" name="accused" class="form-control" id="accused" required>
                                    <div class="invalid-feedback">Please provide accused name</div>
                                </div>
                                
                                <div class="col-12">
                                    <hr>
                                    <h5 class="mb-3"><i class="bi bi-person-lines-fill me-2"></i>Applicant Details</h5>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control" id="name" required>
                                    <div class="invalid-feedback">Please provide your name</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="age" class="form-label">Age</label>
                                    <input type="number" name="age" class="form-control" id="age" min="1" max="120" required>
                                    <div class="invalid-feedback">Please provide a valid age (1-120)</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="number" class="form-label">Contact Number</label>
                                    <input type="tel" name="number" class="form-control" id="number" required>
                                    <div class="invalid-feedback">Please provide a valid contact number</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="relation" class="form-label">Relation with Accused</label>
                                    <input type="text" name="relation" class="form-control" id="relation" required>
                                    <div class="invalid-feedback">Please provide relation with accused</div>
                                </div>
                                
                                <div class="col-12">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea class="form-control" name="address" id="address" rows="3" required></textarea>
                                    <div class="invalid-feedback">Please provide your address</div>
                                </div>
                                
                                <div class="col-12">
                                    <label for="purpose" class="form-label">Purpose of FIR</label>
                                    <textarea class="form-control" name="purpose" id="purpose" rows="3" required></textarea>
                                    <div class="invalid-feedback">Please provide purpose of FIR</div>
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="file" class="form-label">Evidence (Photo/PDF)</label>
                                    <input class="form-control" type="file" name="file" id="file" accept="image/*,.pdf" required>
                                    <div class="invalid-feedback">Please upload evidence</div>
                                    
                                    <div class="preview-container">
                                        <img id="previewImage" class="preview-image img-thumbnail" alt="Preview">
                                        <iframe id="previewPdf" class="preview-pdf" frameborder="0"></iframe>
                                    </div>
                                </div>
                                
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-dark px-4">
                                        <i class="bi bi-send-fill me-2"></i>Submit FIR
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
            
            const form = document.getElementById('firForm');
            
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                
                form.classList.add('was-validated');
            }, false);
            
            // Phone number validation
            const phoneInput = document.getElementById('number');
            phoneInput.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
                if (this.value.length > 10) {
                    this.value = this.value.slice(0, 10);
                }
            });
            
            // Dynamic station filtering based on crime type
            const crimeTypeSelect = document.getElementById('crime_type');
            const stationSelect = document.getElementById('station_id');
            const originalStations = Array.from(stationSelect.options);
            
            crimeTypeSelect.addEventListener('change', function() {
                const selectedCrimeType = this.value;
                
                // Clear and restore original options
                stationSelect.innerHTML = '';
                stationSelect.appendChild(new Option('Select Police Station', '', true, true));
                
                // Filter stations based on crime type
                originalStations.forEach(option => {
                    if (option.value === '') return;
                    
                    const crimeType = option.getAttribute('data-crime-type');
                    if (crimeType === selectedCrimeType) {
                        stationSelect.appendChild(option.cloneNode(true));
                    }
                });
                
                if (stationSelect.options.length === 1) {
                    stationSelect.disabled = true;
                    stationSelect.nextElementSibling.textContent = 'No stations available for this crime type';
                } else {
                    stationSelect.disabled = false;
                    stationSelect.nextElementSibling.textContent = 'Please select a police station';
                }
            });
            
            // File preview
            const fileInput = document.getElementById('file');
            const previewImage = document.getElementById('previewImage');
            const previewPdf = document.getElementById('previewPdf');
            
            fileInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewImage.src = e.target.result;
                            previewImage.style.display = 'block';
                            previewPdf.style.display = 'none';
                        }
                        reader.readAsDataURL(file);
                    } else if (file.type === 'application/pdf') {
                        previewPdf.src = URL.createObjectURL(file);
                        previewPdf.style.display = 'block';
                        previewImage.style.display = 'none';
                    }
                } else {
                    previewImage.style.display = 'none';
                    previewPdf.style.display = 'none';
                }
            });
            
            // Auto-scroll to top for messages
            if (document.querySelector('.alert')) {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        })();
    </script>
</body>
</html>