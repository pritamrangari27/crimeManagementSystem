<?php
session_start();
include "conn.php";

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: userlogin.php");
    exit();
}

$username = $_SESSION['username'];
$sql = "SELECT username, email, password FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL Prepare Failed: " . $conn->error);
}

$stmt->bind_param("s", $username);
if (!$stmt->execute()) {
    die("SQL Execution Failed: " . $stmt->error);
}

$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

$profilePic = !empty($user['profile_pic']) ? $user['profile_pic'] : "https://www.w3schools.com/howto/img_avatar.png";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Police Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --accent-color: #2e59d9;
            --success-color: #1cc88a;
        }

        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8f9fc;
            padding-top: 20px;
        }

        .profile-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
            padding: 30px;
        }

        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 15px 20px;
            border-radius: 8px 8px 0 0;
            margin: -30px -30px 20px -30px;
            display: flex;
            align-items: center;
        }

        .profile-header h2 {
            margin: 0;
            font-weight: 700;
            font-size: 1.5rem;
        }

        .profile-header i {
            margin-right: 10px;
            font-size: 1.5rem;
        }

        .profile-pic {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-color);
            margin: 0 auto 20px;
            display: block;
        }

        .profile-detail {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .profile-label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 5px;
        }

        .profile-value {
            color: #333;
            font-size: 1rem;
        }

        .btn-back {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border: none;
            border-radius: 30px;
            padding: 10px 25px;
            font-weight: 600;
            color: white;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(78, 115, 223, 0.3);
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(78, 115, 223, 0.4);
            color: white;
        }

        @media (max-width: 576px) {
            .profile-container {
                padding: 20px;
            }
            .profile-header {
                padding: 12px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-container">
            <div class="profile-header">
                <i class="fas fa-user-shield"></i>
                <h2>POLICE PROFILE</h2>
            </div>
            
            <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture" class="profile-pic">
            
            <div class="profile-detail">
                <div class="profile-label">Username</div>
                <div class="profile-value"><?php echo htmlspecialchars($user['username'] ?? 'N/A'); ?></div>
            </div>
            
            <div class="profile-detail">
                <div class="profile-label">Email</div>
                <div class="profile-value"><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></div>
            </div>
            
            <div class="profile-detail">
                <div class="profile-label">Password</div>
                <div class="profile-value"><?php echo str_repeat('*', strlen($user['password'] ?? '')); ?></div>
            </div>
            
            <div class="text-center">
                <a href="policehomepg.php" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> BACK TO DASHBOARD
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>