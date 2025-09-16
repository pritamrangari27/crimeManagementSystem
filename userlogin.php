<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$conn = new mysqli("localhost", "root", "", "db_crime");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to fetch police stations
function fetchPoliceStations($conn) {
    $query = "SELECT station_id, station_name FROM police_station";
    $result = mysqli_query($conn, $query);
    if (!$result) {
        die("Error fetching police stations: " . mysqli_error($conn));
    }
    $stations = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $stations[] = $row;
    }
    return $stations;
}

$policeStations = fetchPoliceStations($conn);

// Initialize messages
$login_error = '';
$register_error = '';
$register_success = '';


// Handle User Login - Modified Section
if (isset($_POST['userLogin'])) {
    $username = trim($_POST['login_username']);
    $password = trim($_POST['login_password']);

    // Modified query to select all user data including id
    $query = "SELECT id, username, role FROM user_1 WHERE username = ? AND password = ? AND role='User'";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Set all necessary session variables
        $_SESSION['user_id'] = $user['id'];  // THIS IS THE CRUCIAL ADDITION
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Log the login activity
        $activity_type = "login";
        $description = "<span class=\"font-weight-bold\">{$user['username']}</span> logged in as User";

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
            $log_stmt->bind_param("sss", $activity_type, $description, $user['username']);
            $log_stmt->execute();
            $log_stmt->close();
        }

        header("Location: userhomepg.php");
        exit();
    } else {
        $login_error = "Invalid user credentials!";
    }
}

// Handle Admin Login - Modified Section
if (isset($_POST['adminLogin'])) {
    $username = trim($_POST['login_username']);
    $password = trim($_POST['login_password']);

    // Modified query to select all admin data including id
    $query = "SELECT id, username, role FROM user_1 WHERE username = ? AND password = ? AND role='Admin'";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Set all necessary session variables
        $_SESSION['user_id'] = $user['id'];  // THIS IS THE CRUCIAL ADDITION
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Log the login activity
        $activity_type = "login";
        $description = "<span class=\"font-weight-bold\">{$user['username']}</span> logged in as Admin";

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
            $log_stmt->bind_param("sss", $activity_type, $description, $user['username']);
            $log_stmt->execute();
            $log_stmt->close();
        }

        header("Location: homepage.php");
        exit();
    } else {
        $login_error = "Invalid admin credentials!";
    }
}

// Handle Police Login - Modified Section
if (isset($_POST['policeLogin'])) {
    $username = trim($_POST['login_username']);
    $password = trim($_POST['login_password']);

    // Modified query to select all police data including id
    $query = "SELECT u.id, u.username, u.role, u.station_id FROM users u
              JOIN police_station p ON u.station_id = p.station_id
              JOIN police pa ON u.id = pa.id
              WHERE u.username = ? AND u.password = ? AND u.role = 'Police'";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Set all necessary session variables
        $_SESSION['user_id'] = $user['id'];  // Consistent naming
        $_SESSION['username'] = $user['username'];
        $_SESSION['station_id'] = $user['station_id'];
        $_SESSION['role'] = $user['role'];

        // Log the login activity
        $activity_type = "login";
        $description = "<span class=\"font-weight-bold\">{$user['username']}</span> logged in as Police Officer";

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
            $log_stmt->bind_param("sss", $activity_type, $description, $user['username']);
            $log_stmt->execute();
            $log_stmt->close();
        }

        header("Location: policehomepg.php");
        exit();
    } else {
        $login_error = "Invalid police credentials!";
    }
}



// Handle Admin Registration
if (isset($_POST['Aregister'])) {
    $username = trim($_POST['reg_username']);
    $password = trim($_POST['reg_password']);
    $email = trim($_POST['reg_email']);
    $phone = trim($_POST['reg_phone']);
    $role = 'Admin';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $register_error = "Invalid email format!";
    } else {
        $query = "INSERT INTO user_1 (username, password, email, phone, role) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("sssss", $username, $password, $email, $phone, $role);

        if ($stmt->execute()) {
            $register_success = "Admin registration successful!";
        } else {
            $register_error = "Error: " . $stmt->error;
        }
    }
}

// Handle User Registration
if (isset($_POST['Uregister'])) {
    $username = trim($_POST['reg_username']);
    $password = trim($_POST['reg_password']);
    $email = trim($_POST['reg_email']);
    $phone = trim($_POST['reg_phone']);
    $role = 'User';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $register_error = "Invalid email format!";
    } else {
        $query = "INSERT INTO user_1 (username, password, email, phone, role) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("sssss", $username, $password, $email, $phone, $role);

        if ($stmt->execute()) {
            $register_success = "User registration successful!";
        } else {
            $register_error = "Error: " . $stmt->error;
        }
    }
}

// Handle Police Registration
if (isset($_POST['Pregister'])) {
    $username = trim($_POST['reg_username']);
    $password = trim($_POST['reg_password']);
    $email = trim($_POST['reg_email']);
    $phone = trim($_POST['reg_phone']);
    $station_id = $_POST['station_id'];
    $police_id = trim($_POST['police_id']);
    $role = 'Police';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $register_error = "Invalid email format!";
    } else {
        $check_query = "SELECT id FROM police WHERE id = ?";
        $check_stmt = $conn->prepare($check_query);
        if (!$check_stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $check_stmt->bind_param("s", $police_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $query = "INSERT INTO users (username, password, email, phone, role, station_id, id) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                die("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("sssssss", $username, $password, $email, $phone, $role, $station_id, $police_id);

            if ($stmt->execute()) {
            $register_success = "Police registration successful!";

            } else {
                $register_error = "Error: " . $stmt->error;
            }
        } else {
            $register_error = "No police found with the provided Police ID!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Signup</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --light: #ecf0f1;
            --dark: #2c3e50;
            --danger: #e74c3c;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh; /* Allow body to grow beyond viewport */
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-container {
            width: 100%;
            max-width: 450px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow-y: auto; /* Enable scrolling if content overflows */
            max-height: 90vh; /* Limit height to 90% of viewport */
        }

        .auth-header {
            background-color: var(--primary);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .auth-header h2 {
            margin: 0;
            font-weight: 600;
        }

        .auth-tabs {
            display: flex;
            border-bottom: 1px solid #dee2e6;
        }

        .auth-tab {
            flex: 1;
            text-align: center;
            padding: 12px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
            color: var(--dark);
            border-bottom: 3px solid transparent;
        }

        .auth-tab.active {
            color: var(--secondary);
            border-bottom: 3px solid var(--secondary);
            background-color: rgba(52, 152, 219, 0.1);
        }

        .auth-content {
            padding: 25px;
        }

        .form-title {
            text-align: center;
            margin-bottom: 25px;
            color: var(--dark);
            font-weight: 600;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .form-control {
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #ced4da;
        }

        .form-control:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .btn-auth {
            background-color: var(--secondary);
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            width: 100%;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-auth:hover {
            background-color: #2980b9;
        }

        .form-footer {
            text-align: center;
            margin-top: 20px;
            color: #6c757d;
        }

        .form-link {
            color: var(--secondary);
            cursor: pointer;
            font-weight: 500;
        }

        .form-link:hover {
            text-decoration: underline;
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%232c3e50' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px 12px;
            padding-right: 35px;
        }

        .alert {
            margin: 15px;
        }

        /* Toggle styles */
        .form-container {
            position: relative;
            min-height: 400px;
        }

        .auth-form {
            position: absolute;
            width: 100%;
            transition: opacity 0.3s ease;
        }

        .auth-form.d-none {
            opacity: 0;
            pointer-events: none;
        }

        .auth-form:not(.d-none) {
            opacity: 1;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h2><i class="bi bi-shield-lock"></i> Crime Management System</h2>
        </div>

        <?php if ($login_error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($login_error); ?></div>
        <?php endif; ?>

        <?php if ($register_error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($register_error); ?></div>
        <?php endif; ?>

        <?php if ($register_success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($register_success); ?></div>
        <?php endif; ?>

        <div class="auth-tabs">
            <div class="auth-tab active" onclick="switchTab('admin')"><i class="bi bi-person-gear"></i> Admin</div>
            <div class="auth-tab" onclick="switchTab('user')"><i class="bi bi-person"></i> User</div>
            <div class="auth-tab" onclick="switchTab('police')"><i class="bi bi-shield"></i> Police</div>
        </div>

        <div class="auth-content">
            <!-- Admin Section -->
            <div id="admin-section" class="form-container active">
                <div id="admin-login" class="auth-form">
                    <h3 class="form-title">Admin Login</h3>
                    <form method="post">
                        <div class="input-group">
                            <input type="text" class="form-control" name="login_username" placeholder="Username" required>
                        </div>
                        <div class="input-group">
                            <input type="password" class="form-control" name="login_password" placeholder="Password" required>
                        </div>
                        <button type="submit" name="adminLogin" class="btn-auth">Login</button>
                    </form>
                    <div class="form-footer">
                        Don't have an account? <span class="form-link" onclick="showForm('admin-signup', 'admin-login')">Register</span>
                    </div>
                </div>

                <div id="admin-signup" class="auth-form d-none">
                    <h3 class="form-title">Admin Registration</h3>
                    <form method="post">
                        <div class="input-group">
                            <input type="text" class="form-control" name="reg_username" placeholder="Username" required>
                        </div>
                        <div class="input-group">
                            <input type="email" class="form-control" name="reg_email" placeholder="Email" required>
                        </div>
                        <div class="input-group">
                            <input type="password" class="form-control" name="reg_password" placeholder="Password" required>
                        </div>
                        <div class="input-group">
                            <input type="text" class="form-control" name="reg_phone" placeholder="Phone Number" required>
                        </div>
                        <button type="submit" name="Aregister" class="btn-auth">Register</button>
                    </form>
                    <div class="form-footer">
                        Already have an account? <span class="form-link" onclick="showForm('admin-login', 'admin-signup')">Login</span>
                    </div>
                </div>
            </div>

            <!-- User Section -->
            <div id="user-section" class="form-container d-none">
                <div id="user-login" class="auth-form">
                    <h3 class="form-title">User Login</h3>
                    <form method="post">
                        <div class="input-group">
                            <input type="text" class="form-control" name="login_username" placeholder="Username" required>
                        </div>
                        <div class="input-group">
                            <input type="password" class="form-control" name="login_password" placeholder="Password" required>
                        </div>
                        <button type="submit" name="userLogin" class="btn-auth">Login</button>
                    </form>
                    <div class="form-footer">
                        Don't have an account? <span class="form-link" onclick="showForm('user-signup', 'user-login')">Register</span>
                    </div>
                </div>

                <div id="user-signup" class="auth-form d-none">
                    <h3 class="form-title">User Registration</h3>
                    <form method="post">
                        <div class="input-group">
                            <input type="text" class="form-control" name="reg_username" placeholder="Username" required>
                        </div>
                        <div class="input-group">
                            <input type="email" class="form-control" name="reg_email" placeholder="Email" required>
                        </div>
                        <div class="input-group">
                            <input type="password" class="form-control" name="reg_password" placeholder="Password" required>
                        </div>
                        <div class="input-group">
                            <input type="text" class="form-control" name="reg_phone" placeholder="Phone Number" required>
                        </div>
                        <button type="submit" name="Uregister" class="btn-auth">Register</button>
                    </form>
                    <div class="form-footer">
                        Already have an account? <span class="form-link" onclick="showForm('user-login', 'user-signup')">Login</span>
                    </div>
                </div>
            </div>

            <!-- Police Section -->
            <div id="police-section" class="form-container d-none">
                <div id="police-login" class="auth-form">
                    <h3 class="form-title">Police Login</h3>
                    <form method="post">
                        <div class="input-group">
                            <input type="text" class="form-control" name="login_username" placeholder="Username" required>
                        </div>
                        <div class="input-group">
                            <input type="password" class="form-control" name="login_password" placeholder="Password" required>
                        </div>
                        <button type="submit" name="policeLogin" class="btn-auth">Login</button>
                    </form>
                    <div class="form-footer">
                        Don't have an account? <span class="form-link" onclick="showForm('police-signup', 'police-login')">Register</span>
                    </div>
                </div>

                <div id="police-signup" class="auth-form d-none">
                    <h3 class="form-title">Police Registration</h3>
                    <form method="post">
                        <div class="input-group">
                            <input type="text" class="form-control" name="reg_username" placeholder="Username" required>
                        </div>
                        <div class="input-group">
                            <input type="email" class="form-control" name="reg_email" placeholder="Email" required>
                        </div>
                        <div class="input-group">
                            <input type="password" class="form-control" name="reg_password" placeholder="Password" required>
                        </div>
                        <div class="input-group">
                            <input type="text" class="form-control" name="reg_phone" placeholder="Phone Number" required>
                        </div>
                        <div class="input-group">
                            <input type="text" class="form-control" name="police_id" placeholder="Police ID" required>
                        </div>
                        <div class="input-group">
                            <select class="form-control" name="station_id" required>
                                <option value="">Select Police Station</option>
                                <?php foreach ($policeStations as $station): ?>
                                    <option value="<?php echo htmlspecialchars($station['station_id']); ?>">
                                        <?php echo htmlspecialchars($station['station_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" name="Pregister" class="btn-auth">Register</button>
                    </form>
                    <div class="form-footer">
                        Already have an account? <span class="form-link" onclick="showForm('police-login', 'police-signup')">Login</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function showForm(showId, hideId) {
        document.getElementById(hideId).classList.add('d-none');
        document.getElementById(showId).classList.remove('d-none');
    }
    

    function switchTab(tabName) {
        // Update active tab
        document.querySelectorAll('.auth-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        event.currentTarget.classList.add('active');

        // Hide all sections
        document.querySelectorAll('.form-container').forEach(section => {
            section.classList.add('d-none');
        });

        // Show selected section and reset to login form
        const section = document.getElementById(tabName + '-section');
        section.classList.remove('d-none');
        showForm(tabName + '-login', tabName + '-signup');
    }

    // Initialize by showing admin section
    document.getElementById('admin-login').classList.remove('d-none');
    </script>
</body>
</html>