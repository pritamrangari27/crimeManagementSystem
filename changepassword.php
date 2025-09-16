<?php
session_start();
include "conn.php";

// Handle password change request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['changePassword'])) {
    // Check if the user is logged in
    if (!isset($_SESSION['username'])) {
        $message = "You must be logged in to change your password.";
        $messageClass = "alert-danger";
    } else {
        // Get form data
        $currentPassword = $_POST['currentPassword'];
        $newPassword = $_POST['newPassword'];
        $confirmPassword = $_POST['confirmPassword'];

        // Validate inputs
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $message = "All fields are required.";
            $messageClass = "alert-danger";
        } elseif ($newPassword !== $confirmPassword) {
            $message = "New password and confirm password do not match.";
            $messageClass = "alert-danger";
        } else {
            // Fetch the current password from the database
            $username = $_SESSION['username'];
            $sql = "SELECT password FROM users WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            // Verify the current password (plain text comparison)
            if ($currentPassword !== $user['password']) {
                $message = "Current password is incorrect.";
                $messageClass = "alert-danger";
            } else {
                // Update the password in the database (plain text)
                $updateSql = "UPDATE users SET password = ? WHERE username = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("ss", $newPassword, $username);

                if ($updateStmt->execute()) {
                    $message = "Password changed successfully!";
                    $messageClass = "alert-success";
                } else {
                    $message = "Failed to update password. Please try again.";
                    $messageClass = "alert-danger";
                }

                $updateStmt->close();
            }

            $stmt->close();
        }
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Change Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        .password-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
        }

        h2 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 25px;
            font-weight: 700;
        }

        .form-label {
            font-weight: 600;
            color: #5a5c69;
        }

        .form-control {
            border-radius: 0.35rem;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d3e2;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            width: 100%;
            border-radius: 0.35rem;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background-color: var(--accent-color);
            transform: translateY(-1px);
        }

        .alert {
            margin-top: 20px;
            border-radius: 0.35rem;
            padding: 1rem;
            border-left: 0.25rem solid;
        }

        .alert-success {
            background-color: rgba(28, 200, 138, 0.1);
            border-left-color: var(--success-color);
            color: var(--success-color);
        }

        .alert-danger {
            background-color: rgba(231, 74, 59, 0.1);
            border-left-color: var(--danger-color);
            color: var(--danger-color);
        }

        .form-text {
            color: #858796;
            font-size: 0.85rem;
        }

        @media (max-width: 576px) {
            .password-container {
                padding: 20px;
                margin: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="password-container">
            <h2><i class="fas fa-key"></i> Change Password</h2>

            <?php if (isset($message)): ?>
                <div id="message" class="alert <?php echo $messageClass; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="currentPassword" name="currentPassword" placeholder="Current Password" required>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                        <input type="password" class="form-control" id="newPassword" name="newPassword" placeholder="New Password" required>
                    </div>
                    <div class="form-text">Enter your new password</div>
                </div>

                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm New Password" required>
                    </div>
                    <div class="form-text">Re-enter your new password</div>
                </div>

                <button type="submit" name="changePassword" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update Password
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Hide message after 5 seconds
        setTimeout(function() {
            const message = document.getElementById('message');
            if (message) {
                message.style.transition = 'opacity 1s ease';
                message.style.opacity = '0';
                setTimeout(() => message.remove(), 1000);
            }
        }, 5000);
    </script>
</body>
</html>