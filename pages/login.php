<?php
session_start();

// Require PHPMailer files
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../PHPMailer-master/src/Exception.php';
require '../PHPMailer-master/src/PHPMailer.php';
require '../PHPMailer-master/src/SMTP.php';

// Database connection (PDO)
include_once  '../utility/connection.php';

// Default form to display
$form_to_display = 'login';

// Function to send verification email
function sendVerificationEmail($email, $code) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'slatetransportsystem@gmail.com';
        $mail->Password   = 'mfkkigrgxtoascov'; // Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Fix SSL issue
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            ]
        ];

        // Recipients
        $mail->setFrom('slatetransportsystem@gmail.com', 'Slate Account Management');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your SLATE Verification Code';
        $mail->Body    = "Your verification code is: <b>{$code}</b>. This code is valid for a single use.";
        $mail->AltBody = "Your verification code is: {$code}. This code is valid for a single use.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login'])) {
        $form_to_display = 'login';
        $input_username = $_POST['username'];
        $input_password = $_POST['password'];

        // Get user details
        $sql = "SELECT id, password, failed_attempts, lockout_until FROM users WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$input_username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Check lockout
            if ($user['lockout_until'] && strtotime($user['lockout_until']) > time()) {
                $time_remaining = strtotime($user['lockout_until']) - time();
                $minutes = ceil($time_remaining / 60);
                $error_message = "Your account is locked. Please try again in {$minutes} minute(s).";
            } else {
                // Verify password
                if (password_verify($input_password, $user['password'])) {
                    // Reset attempts
                    $sql_reset = "UPDATE users SET failed_attempts = 0, lockout_until = NULL WHERE id = ?";
                    $pdo->prepare($sql_reset)->execute([$user['id']]);

                    // Generate 2FA code
                    $verification_code = rand(100000, 999999);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $input_username;
                    $_SESSION['verification_code'] = $verification_code;

                    sendVerificationEmail($input_username, $verification_code);
                    $success_message = "A verification code has been sent to your email.";
                    $form_to_display = 'verification';
                } else {
                    // Wrong password
                    $failed_attempts = $user['failed_attempts'] + 1;
                    $lockout_until = null;

                    if ($failed_attempts >= 3) {
                        $lockout_until = date('Y-m-d H:i:s', strtotime('+1 minute'));
                        $error_message = "Incorrect password. You have reached the maximum login attempts. Your account is locked for 1 minute.";
                    } else {
                        $error_message = "Invalid username or password. You have " . (3 - $failed_attempts) . " attempts remaining.";
                    }

                    $sql_update = "UPDATE users SET failed_attempts = ?, lockout_until = ? WHERE id = ?";
                    $pdo->prepare($sql_update)->execute([$failed_attempts, $lockout_until, $user['id']]);
                }
            }
        } else {
            $error_message = "Invalid username or password.";
        }

    } elseif (isset($_POST['verify_code'])) {
        $form_to_display = 'verification';
        $input_code = $_POST['verification_code'];

        if (isset($_SESSION['verification_code']) && $input_code == $_SESSION['verification_code']) {
            unset($_SESSION['verification_code']);
            header("Location: dashboard/dashboard.php");
            exit();
        } else {
            $error_message = "Invalid verification code.";
        }

    } elseif (isset($_POST['signup'])) {
        $form_to_display = 'signup';
        $new_username = $_POST['new_username'];
        $new_email = $_POST['new_email'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Check duplicate
        $sql_check = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([$new_username, $new_email]);

        if ($stmt_check->fetch()) {
            $error_message = "Username or email already exists.";
        } elseif ($new_password !== $confirm_password) {
            $error_message = "Passwords do not match.";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $sql_insert = "INSERT INTO users (username, password, email) VALUES (?, ?, ?)";
            $stmt_insert = $pdo->prepare($sql_insert);

            if ($stmt_insert->execute([$new_username, $hashed_password, $new_email])) {
                $success_message = "Sign up successful! You can now log in.";
                $form_to_display = 'login';
            } else {
                $error_message = "Error: Could not create account.";
            }
        }
    }
}

// No $pdo->close(); in PDO (connection closes automatically)
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Signup - SLATE System</title>
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

        .auth-container {
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
            text-align: center;
        }

        .welcome-panel h1 {
            font-size: 2.25rem;
            font-weight: 700;
            color: #ffffff;
            text-shadow: 0.125rem 0.125rem 0.5rem rgba(0, 0, 0, 0.6);
        }

        .auth-panel {
            width: 25rem;
            padding: 3.75rem 2.5rem;
            background: rgba(22, 33, 49, 0.95);
        }

        .auth-box {
            width: 100%;
            text-align: center;
        }

        .auth-box img {
            width: 6.25rem;
            height: auto;
            margin-bottom: 1.25rem;
        }

        .auth-box h2 {
            margin-bottom: 1.5625rem;
            color: #ffffff;
            font-size: 1.75rem;
        }

        .auth-box form {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .auth-box input {
            width: 100%;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.375rem;
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .auth-box input:focus {
            outline: none;
            border-color: #00c6ff;
            box-shadow: 0 0 0 0.125rem rgba(0, 198, 255, 0.2);
        }

        .auth-box input::placeholder {
            color: rgba(160, 160, 160, 0.8);
        }

        .terms-container {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .terms-container input[type="checkbox"] {
            width: auto;
        }

        .terms-container a, .switch-link {
            color: #00c6ff;
            text-decoration: none;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .terms-container a:hover, .switch-link:hover {
            color: #009ee3;
            text-decoration: underline;
        }

        .auth-box button {
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

        .auth-box button:hover {
            background: linear-gradient(to right, #0052cc, #009ee3);
            transform: translateY(-0.125rem);
            box-shadow: 0 0.3125rem 0.9375rem rgba(0, 0, 0, 0.2);
        }
        
        .switch-link-container {
            margin-top: 1rem;
            font-size: 0.875rem;
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
            color: #44ff44;
            font-size: 0.9rem;
        }

        footer {
            text-align: center;
            padding: 1.25rem;
            background: rgba(0, 0, 0, 0.2);
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.875rem;
        }
        
        .form-container {
            display: none;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #2c3e50;
            margin: auto;
            border: 1px solid #888;
            width: 90%;
            max-width: 40rem;
            border-radius: 0.5rem;
            position: relative;
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.5);
            overflow-y: auto;
            height: 90vh;
            max-height: 90vh;
        }

        .modal-content::-webkit-scrollbar {
            width: 10px;
        }

        .modal-content::-webkit-scrollbar-track {
            background: #2c3e50;
        }

        .modal-content::-webkit-scrollbar-thumb {
            background-color: #4a5d6e;
            border-radius: 10px;
            border: 2px solid #2c3e50;
        }

        .modal-content::-webkit-scrollbar-thumb:hover {
            background-color: #5d7389;
        }

        .close-button {
            color: #aaa;
            float: right;
            font-size: 1.75rem;
            font-weight: bold;
        }

        .close-button:hover,
        .close-button:focus {
            color: white;
            text-decoration: none;
            cursor: pointer;
        }

        .modal-content h2 {
            margin-bottom: 1rem;
            color: #00c6ff;
        }

        @media (max-width: 48rem) {
            .auth-container {
                flex-direction: column;
            }

            .welcome-panel, 
            .auth-panel {
                width: 100%;
            }

            .welcome-panel {
                padding: 1.875rem 1.25rem;
            }

            .welcome-panel h1 {
                font-size: 1.75rem;
            }

            .auth-panel {
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

            .auth-box h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="auth-container">
            <div class="welcome-panel">
                <h1>FREIGHT MANAGEMENT SYSTEM</h1>
            </div>

            <div class="auth-panel">
                <div class="auth-box">
                    <img src="../image/logo.png" alt="SLATE Logo">
                    
                    <div id="login-form-container" class="form-container">
                        <h2>SLATE Login</h2>
                        <?php if (!empty($error_message) && $form_to_display == 'login'): ?>
                            <div class="error-message"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                        <?php if (!empty($success_message) && $form_to_display == 'login'): ?>
                            <div class="success-message"><?php echo $success_message; ?></div>
                        <?php endif; ?>
                        <form method="POST" >
                            <input type="email" name="username" placeholder="Username" required>
                            <input type="password" name="password" placeholder="Password" required>
                            <div class="terms-container">
                                <input type="checkbox" id="terms" name="terms" required>
                                <label for="terms">I agree to the <a href="#" id="terms-link">Terms & Conditions</a></label>
                            </div>
                            <button type="submit" name="login">Log In</button>
                        </form>
                        <div class="switch-link-container">
                            Don't have an account? <a href="#" id="show-signup">Sign up</a>
                        </div>
                    </div>
                    
                    <div id="signup-form-container" class="form-container">
                        <h2>SLATE Sign Up</h2>
                            <?php if (!empty($error_message) && $form_to_display == 'signup'): ?>
                                <div class="error-message"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                        <?php if (!empty($success_message) && $form_to_display == 'signup'): ?>
                                <div class="success-message"><?php echo $success_message; ?></div>
                        <?php endif; ?>
                        <form method="POST" action="login.php">
                            <input type="text" name="new_username" placeholder="Username" required>
                            <input type="email" name="new_email" placeholder="Email" required>
                            <input type="password" name="new_password" placeholder="Password" required>
                            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                            <div class="terms-container">
                                <input type="checkbox" id="terms-signup" name="terms" required>
                                <label for="terms-signup">I agree to the <a href="#" id="terms-link-signup">Terms & Conditions</a></label>
                            </div>
                            <button type="submit" name="signup">Sign Up</button>
                        </form>
                        <div class="switch-link-container">
                            Already have an account? <a href="#" id="show-login">Log in</a>
                        </div>
                    </div>

                    <div id="verification-form-container" class="form-container">
                        <h2>Verify Your Account</h2>
                        <?php if (!empty($error_message) && $form_to_display == 'verification'): ?>
                            <div class="error-message"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                        <?php if (!empty($success_message) && $form_to_display == 'verification'): ?>
                            <div class="success-message"><?php echo $success_message; ?></div>
                        <?php endif; ?>
                        <p style="margin-bottom: 1rem;">A verification code has been sent to your email.</p>
                        <form method="POST" action="login.php">
                            <input type="text" name="verification_code" placeholder="Enter 6-digit code" required>
                            <button type="submit" name="verify_code">Verify</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="terms-modal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <iframe id="terms-iframe" src="" style="width:100%; height:100%; border:none;"></iframe>
        </div>
    </div>

    <footer>
        &copy; <span id="currentYear"></span> SLATE Freight Management System. All rights reserved.
    </footer>

  <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('currentYear').textContent = new Date().getFullYear();

            var loginFormContainer = document.getElementById('login-form-container');
            var signupFormContainer = document.getElementById('signup-form-container');
            var verificationFormContainer = document.getElementById('verification-form-container');
            var showSignupLink = document.getElementById('show-signup');
            var showLoginLink = document.getElementById('show-login');

            // ✅ Safe default value
            var formToDisplay = <?php echo json_encode($form_to_display ?? 'login'); ?>;

            loginFormContainer.style.display = 'none';
            signupFormContainer.style.display = 'none';
            verificationFormContainer.style.display = 'none';

            if (formToDisplay === 'verification') {
                verificationFormContainer.style.display = 'block';
            } else if (formToDisplay === 'signup') {
                signupFormContainer.style.display = 'block';
            } else {
                loginFormContainer.style.display = 'block';
            }

            showSignupLink.onclick = function(event) {
                event.preventDefault();
                loginFormContainer.style.display = 'none';
                verificationFormContainer.style.display = 'none';
                signupFormContainer.style.display = 'block';
            }

            showLoginLink.onclick = function(event) {
                event.preventDefault();
                signupFormContainer.style.display = 'none';
                verificationFormContainer.style.display = 'none';
                loginFormContainer.style.display = 'block';
            }

            var modal = document.getElementById("terms-modal");
            var termsLinks = document.querySelectorAll("#terms-link, #terms-link-signup");
            var span = document.getElementsByClassName("close-button")[0];
            var termsIframe = document.getElementById("terms-iframe");

            termsLinks.forEach(function(link) {
                link.onclick = function(event) {
                    event.preventDefault();
                    termsIframe.src = "terms-and-conditions.html";
                    modal.style.display = "flex";
                }
            });

            span.onclick = function() {
                modal.style.display = "none";
            }

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
        });
    </script>
</body>
</html>