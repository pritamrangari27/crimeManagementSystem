<?php
session_start();
include "conn.php";

// Check if the user is NOT logged in
if (!isset($_SESSION['username'])) {
    header("Location: userlogin.php");
    exit();
}

$username = $_SESSION['username'];
$sql = "SELECT username, email, password FROM user_1 WHERE username = ?";
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
    <title>User Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --accent-color: #2e59d9;
            --success-color: #1cc88a;
        }

        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f8f9fc;
        }

        .header-gradient {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-weight: 700;
        }

        .profile-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
        }

        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 20px;
            display: block;
            border: 3px solid var(--primary-color);
        }

        .profile-label {
            font-weight: 600;
            color: var(--primary-color);
        }

        .profile-value {
            color: #212529;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .btn-back {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border: none;
            border-radius: 30px;
            padding: 10px 25px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(78, 115, 223, 0.3);
            color: white;
            margin-top: 20px;
        }

        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(78, 115, 223, 0.4);
            color: white;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            font-weight: 700;
        }

        .profile-detail {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="profile-container">
            <h2>
                <i class="fas fa-user"></i> USER PROFILE
            </h2>
            <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture" class="profile-pic">
            
            <div class="profile-detail">
                <div class="profile-label">Username:</div>
                <div class="profile-value"><?php echo htmlspecialchars($user['username'] ?? 'N/A'); ?></div>
            </div>
            
            <div class="profile-detail">
                <div class="profile-label">Email:</div>
                <div class="profile-value"><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?></div>
            </div>
            
            <div class="profile-detail">
                <div class="profile-label">Password:</div>
                <div class="profile-value"><?php echo str_repeat('*', strlen($user['password'] ?? '')); ?></div>
            </div>
            
            <div class="text-center">
                <a href="userhomepg.php" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> BACK TO DASHBOARD
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>