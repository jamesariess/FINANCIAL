<?php
session_start();
include "../utility/connection.php";

// Prevent logged-in users from accessing register page
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$errors = [];
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    // Sanitize inputs
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = "Username must be between 3 and 50 characters";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username can only contain letters, numbers, and underscores";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Check if username already exists
    if (empty($errors)) {
        $sql = "SELECT id FROM user WHERE username = :username";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['username' => $username]);
        $result = $stmt->fetch();
        
        if ($result) {
            $errors[] = "Username already taken";
        }
    }

    // Proceed with registration if no errors
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO user (username, password) VALUES (:username, :password)";
        $stmt = $pdo->prepare($sql);
        
        try {
            $stmt->execute([
                'username' => $username,
                'password' => $hashed_password
            ]);
            $success_message = "Registration successful! You can now log in.";
            // Optionally redirect to login page after a delay
            // header("Refresh: 2; url=login.php");
        } catch (PDOException $e) {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SLATE System</title>
    <style>
        /* Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            color: white;
            line-height: 1.6;
        }

        .main-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .register-container {
            width: 100%;
            max-width: 75rem;
            display: flex;
            background: rgba(31, 42, 56, 0.8);
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 0.625rem 1.875rem rgba(0, 0, 0, 0.3);
        }

        .welcome-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem;
            background: linear-gradient(135deg, rgba(0, 114, 255, 0.2), rgba(0, 198, 255, 0.2));
        }

        .welcome-panel h1 {
            font-size: 2.25rem;
            font-weight: 700;
            color: #ffffff;
            text-shadow: 0.125rem 0.125rem 0.5rem rgba(0, 0, 0, 0.6);
            text-align: center;
        }

        .register-panel {
            width: 25rem;
            padding: 3.75rem 2.5rem;
            background: rgba(22, 33, 49, 0.95);
        }

        .register-box {
            width: 100%;
            text-align: center;
        }

        .register-box img {
            width: 6.25rem;
            height: auto;
            margin-bottom: 1.25rem;
        }

        .register-box h2 {
            margin-bottom: 1.5625rem;
            color: #ffffff;
            font-size: 1.75rem;
        }

        .register-box form {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .register-box input {
            width: 100%;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.375rem;
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .register-box input:focus {
            outline: none;
            border-color: #00c6ff;
            box-shadow: 0 0 0 0.125rem rgba(0, 198, 255, 0.2);
        }

        .register-box input::placeholder {
            color: rgba(160, 160, 160, 0.8);
        }

        .register-box button {
            padding: 0.75rem;
            background: linear-gradient(to right, #0072ff, #00c6ff);
            border: none;
            border-radius: 0.375rem;
            font-weight: 600;
            font-size: 1rem;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .register-box button:hover {
            background: linear-gradient(to right, #0052cc, #009ee3);
            transform: translateY(-0.125rem);
            box-shadow: 0 0.3125rem 0.9375rem rgba(0, 0, 0, 0.2);
        }

        .error-message {
            background: rgba(255, 0, 0, 0.2);
            padding: 0.75rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            color: #ff4444;
            font-size: 0.9rem;
        }

        .success-message {
            background: rgba(0, 255, 0, 0.2);
            padding: 0.75rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            color: #00ff00;
            font-size: 0.9rem;
        }

        .login-link {
            margin-top: 1rem;
            text-align: center;
            font-size: 0.9rem;
        }

        .login-link a {
            color: #00c6ff;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        footer {
            text-align: center;
            padding: 1.25rem;
            background: rgba(0, 0, 0, 0.2);
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.875rem;
        }

        @media (max-width: 48rem) {
            .register-container {
                flex-direction: column;
            }

            .welcome-panel, 
            .register-panel {
                width: 100%;
            }

            .welcome-panel {
                padding: 1.875rem 1.25rem;
            }

            .welcome-panel h1 {
                font-size: 1.75rem;
            }

            .register-panel {
                padding: 2.5rem 1.25rem;
            }
        }

        @media (max-width: 30rem) {
            .main-container {
                padding: 1rem;
            }

            .welcome-panel h1 {
                font-size: 1.5rem;
            }

            .register-box h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="register-container">
            <div class="welcome-panel">
                <h1>FREIGHT MANAGEMENT SYSTEM</h1>
            </div>

            <div class="register-panel">
                <div class="register-box">
                    <img src="../image/logo.png" alt="SLATE Logo">
                    <h2>SLATE Register</h2>
                    <?php if (!empty($errors)): ?>
                        <div class="error-message">
                            <?php foreach ($errors as $error): ?>
                                <p><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($success_message)): ?>
                        <div class="success-message">
                            <p><?php echo htmlspecialchars($success_message); ?></p>
                        </div>
                    <?php endif; ?>
                    <form method="POST">
                        <input type="text" name="username" placeholder="Username" required>
                        <input type="password" name="password" placeholder="Password" required>
                        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                        <button type="submit" name="register">Register</button>
                    </form>
                    <div class="login-link">
                        Already have an account? <a href="login.php">Log in here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        &copy; <span id="currentYear"></span> SLATE Freight Management System. All rights reserved.
    </footer>

    <script>
        document.getElementById('currentYear').textContent = new Date().getFullYear();
    </script>
</body>
</html>