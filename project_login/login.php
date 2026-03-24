<?php
session_start();

$login_error = "";
$signup_success = "";
$forgot_password_message = "";
$show_forgot_password_form = false;

// Database connection
try {
    $conn = new mysqli("localhost", "root", "", "online_login");
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}

// Function to generate random token
function generateToken() {
    return bin2hex(random_bytes(32));
}

// Login processing
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['name'] = $user['name'];
            header("Location: index.html");
            exit();
        } else {
            $login_error = "Invalid email or password.";
        }
    } else {
        $login_error = "Invalid email or password.";
    }
    $stmt->close();
}

// Forgot Password processing
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['forgot_password'])) {
    $show_forgot_password_form = true;
    $email = $_POST['email'];
    
    $stmt = $conn->prepare("SELECT id, email FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $token = generateToken();
        $expiry = date("Y-m-d H:i:s", time() + 3600); // 1 hour expiry
        
        $update_stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $token, $expiry, $user['id']);
        
        if ($update_stmt->execute()) {
            $reset_link = "http://yourdomain.com/reset_password.php?token=$token";
            $forgot_password_message = "Password reset link has been sent to your email. Please check your inbox (and spam folder).";
        } else {
            $forgot_password_message = "Error generating reset link. Please try again.";
        }
        $update_stmt->close();
    } else {
        $forgot_password_message = "No account found with that email address.";
    }
    $stmt->close();
}

// Signup processing
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = $_POST['phone'] ?? '';

    // Validate phone number
    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        $login_error = "Please enter a valid 10-digit phone number.";
    } else {
        try {
            // Check if email or phone already exists
            $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
            $check_stmt->bind_param("ss", $email, $phone);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $existing_user = $check_result->fetch_assoc();
                $login_error = "Email or phone number already registered.";
            } else {
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $name, $email, $password, $phone);
                
                if ($stmt->execute()) {
                    $signup_success = "Registration successful! You can now login.";
                    // Clear form after successful registration
                    $_POST = array();
                } else {
                    $login_error = "Registration failed: " . $stmt->error;
                }
                $stmt->close();
            }
            $check_stmt->close();
        } catch (Exception $e) {
            $login_error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Portal - Login/Signup</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-image: url('loginpage.jpg');
            background-size: cover;
            background-position: center;
            background-color: lightblue;
            color: #fff;
        }

        .container {
            display: flex;
            border-radius: 10px;
            width: 900px;
            max-width: 95%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }

        .left-column {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .left-column h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
        }

        .left-column p {
            font-size: 1.1rem;
            line-height: 1.6;
        }

        .right-column {
            flex: 1;
            padding: 30px;
            display: flex;
            flex-direction: column;
        }

        .form-card {
            background-color: rgba(0, 0, 0, 0.7);
            border-radius: 10px;
            padding: 30px;
            border: 2px solid #4CAF50;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
            margin-bottom: 20px;
        }

        .form-card h2 {
            text-align: center;
            margin-bottom: 25px;
            font-size: 1.8rem;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #ddd;
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-size: 1rem;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4CAF50;
            background-color: rgba(255, 255, 255, 0.2);
        }

        .auth-button {
            width: 100%;
            padding: 14px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            cursor: pointer;
            margin-top: 15px;
            transition: background-color 0.3s;
        }

        .auth-button:hover {
            background-color: #45a049;
        }

        .secondary-button {
            background-color: #555;
        }

        .toggle-form {
            text-align: center;
            margin-top: 15px;
        }

        .toggle-form a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: 600;
        }

        .toggle-form a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }

        .alert-error {
            background-color: rgba(255, 235, 238, 0.8);
            color: #c62828;
        }

        .alert-success {
            background-color: rgba(232, 245, 233, 0.8);
            color: #2e7d32;
        }

        .alert-info {
            background-color: rgba(227, 242, 253, 0.8);
            color: #1565c0;
        }

        .hidden {
            display: none;
        }

        .forgot-password-link {
            text-align: right;
            margin-top: -15px;
            margin-bottom: 15px;
        }

        .forgot-password-link a {
            color: #4CAF50;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .forgot-password-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            .left-column, .right-column {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-column">
            <h1>Toys for kids</h1>
            <p>Manual record-keeping of farmer and land details. This project aims to develop software that digitizes farmer and land details along with beneficiary schemes information, streamlining agricultural data management for more accurate records and easier access to government schemes.</p>
        </div>
        
        <div class="right-column">
            <!-- Login Form -->
            <div class="form-card <?php echo (isset($_POST['login']) || (!isset($_POST['signup']) && !isset($_POST['forgot_password']) && !$show_forgot_password_form)) ? '' : 'hidden'; ?>" id="login-form">
                <h2>Login</h2>
                <?php if ($login_error && !isset($_POST['signup'])): ?>
                    <div class="alert alert-error"><?php echo $login_error; ?></div>
                <?php endif; ?>
                <?php if ($signup_success): ?>
                    <div class="alert alert-success"><?php echo $signup_success; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <input type="hidden" name="login" value="1">
                    <div class="form-group">
                        <label for="login-email">Email</label>
                        <input type="email" id="login-email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="login-password">Password</label>
                        <input type="password" id="login-password" name="password" required>
                    </div>
                    <div class="forgot-password-link">
                        <a href="#" onclick="showForgotPassword(); return false;">Forgot Password?</a>
                    </div>
                    <button type="submit" class="auth-button">Login</button>
                </form>
                <div class="toggle-form">
                    Don't have an account? <a href="#" onclick="showSignup(); return false;">Sign up</a>
                </div>
            </div>
            
            <!-- Forgot Password Form -->
            <div class="form-card <?php echo (isset($_POST['forgot_password']) || $show_forgot_password_form) ? '' : 'hidden'; ?>" id="forgot-password-form">
                <h2>Forgot Password</h2>
                <?php if ($forgot_password_message): ?>
                    <div class="alert <?php echo strpos($forgot_password_message, 'sent') !== false ? 'alert-success' : 'alert-error'; ?>">
                        <?php echo $forgot_password_message; ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="">
                    <input type="hidden" name="forgot_password" value="1">
                    <div class="form-group">
                        <label for="forgot-email">Email</label>
                        <input type="email" id="forgot-email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>
                    <button type="submit" class="auth-button">Send Reset Link</button>
                </form>
                <div class="toggle-form">
                    Remember your password? <a href="#" onclick="showLogin(); return false;">Login</a>
                </div>
            </div>
            
            <!-- Signup Form -->
            <div class="form-card <?php echo isset($_POST['signup']) ? '' : 'hidden'; ?>" id="signup-form">
                <h2>Sign Up</h2>
                <?php if ($login_error && isset($_POST['signup'])): ?>
                    <div class="alert alert-error"><?php echo $login_error; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <input type="hidden" name="signup" value="1">
                    <div class="form-group">
                        <label for="signup-name">Full Name</label>
                        <input type="text" id="signup-name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="signup-email">Email</label>
                        <input type="email" id="signup-email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="signup-password">Password</label>
                        <input type="password" id="signup-password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="signup-phone">Phone Number (10 digits)</label>
                        <input type="tel" id="signup-phone" name="phone" pattern="[0-9]{10}" title="10 digit phone number" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" required>
                    </div>
                    <button type="submit" class="auth-button">Register</button>
                </form>
                <div class="toggle-form">
                    Already have an account? <a href="#" onclick="showLogin(); return false;">Login</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showSignup() {
            document.getElementById('login-form').classList.add('hidden');
            document.getElementById('forgot-password-form').classList.add('hidden');
            document.getElementById('signup-form').classList.remove('hidden');
            document.querySelectorAll('.alert').forEach(el => el.style.display = 'none');
        }
        
        function showLogin() {
            document.getElementById('signup-form').classList.add('hidden');
            document.getElementById('forgot-password-form').classList.add('hidden');
            document.getElementById('login-form').classList.remove('hidden');
            document.querySelectorAll('.alert').forEach(el => el.style.display = 'none');
        }
        
        function showForgotPassword() {
            document.getElementById('login-form').classList.add('hidden');
            document.getElementById('signup-form').classList.add('hidden');
            document.getElementById('forgot-password-form').classList.remove('hidden');
            document.querySelectorAll('.alert').forEach(el => el.style.display = 'none');
        }
    </script>
</body>
</html>