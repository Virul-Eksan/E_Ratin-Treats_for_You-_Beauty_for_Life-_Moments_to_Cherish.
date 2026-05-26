<?php
session_start();
require_once '../db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login_submit'])) {
        $email = trim($_POST['login_email']);
        $password = $_POST['login_password'];

        $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        $customer = $stmt->fetch();

        if ($customer && password_verify($password, $customer['password'])) {
            $_SESSION['customer_id'] = $customer['id'];
            $_SESSION['customer_name'] = $customer['name'];
            $_SESSION['profile_image'] = $customer['profile_image'] ?? null;
            header("Location: ../index.php");
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    } elseif (isset($_POST['register_submit'])) {
        $name = trim($_POST['reg_name']);
        $email = trim($_POST['reg_email']);
        $password = $_POST['reg_password'];

        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Email is already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO customers (name, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$name, $email, $hashedPassword])) {
                $_SESSION['customer_id'] = $pdo->lastInsertId();
                $_SESSION['customer_name'] = $name;
                $_SESSION['profile_image'] = null;
                header("Location: ../index.php");
                exit;
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}

$showAlert = ($_SERVER['REQUEST_METHOD'] !== 'POST' && empty($error));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Authentication - ඒ රtin (E RATIN)</title>
    <link rel="stylesheet" href="auth.css">
    <style>
        .error-message {
            color: #ef4444;
            background: #fee2e2;
            border: 1px solid #f87171;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<?php if ($showAlert): ?>
<style>
    @keyframes fadeInZoom {
        0% { opacity: 0; transform: scale(1); }
        28% { opacity: 1; } /* 2 seconds (28% of 7s) to fully fade in */
        100% { opacity: 1; transform: scale(1.03); }
    }
</style>
<div id="imageAlertOverlay" style="position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: #fffbf5; z-index: 9999; display: flex; justify-content: center; align-items: center; transition: opacity 2s ease; overflow: hidden;">
    <img src="alertpage.png" alt="Welcome Alert" style="width: 100vw; height: 100vh; object-fit: cover; animation: fadeInZoom 7s linear forwards;">
</div>
<?php endif; ?>

<div class="auth-container">
    <div class="auth-card" id="authCard">
        <a href="../index.php"><img src="../logo.jpg" alt="Logo" class="auth-logo"></a>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Login Form -->
        <div id="loginFormContainer" <?php echo (isset($_POST['register_submit']) && !empty($error)) ? 'class="hidden-form" style="display:none;"' : ''; ?>>
            <h2 class="auth-title">Welcome Back</h2>
            <p class="auth-subtitle">Login to access your account</p>
            
            <form action="" method="POST">
                <div class="form-group">
                    <input type="email" name="login_email" class="form-control" placeholder="Email Address" required value="<?php echo isset($_POST['login_email']) ? htmlspecialchars($_POST['login_email']) : ''; ?>">
                </div>
                <div class="form-group">
                    <input type="password" name="login_password" class="form-control" placeholder="Password" required>
                </div>
                <button type="submit" name="login_submit" class="btn-primary">Login</button>
            </form>
            
            <p class="toggle-text">Don't have an account? <span class="toggle-link" onclick="toggleForms()">Register Here</span></p>
        </div>

        <!-- Registration Form -->
        <div id="registerFormContainer" <?php echo (isset($_POST['register_submit']) && !empty($error)) ? '' : 'class="hidden-form" style="display:none;"'; ?>>
            <h2 class="auth-title">Create Account</h2>
            <p class="auth-subtitle">Join us to start shopping</p>
            
            <form action="" method="POST">
                <div class="form-group">
                    <input type="text" name="reg_name" class="form-control" placeholder="Full Name" required value="<?php echo isset($_POST['reg_name']) ? htmlspecialchars($_POST['reg_name']) : ''; ?>">
                </div>
                <div class="form-group">
                    <input type="email" name="reg_email" class="form-control" placeholder="Email Address" required value="<?php echo isset($_POST['reg_email']) ? htmlspecialchars($_POST['reg_email']) : ''; ?>">
                </div>
                <div class="form-group">
                    <input type="password" name="reg_password" class="form-control" placeholder="Password" required>
                </div>
                <button type="submit" name="register_submit" class="btn-primary">Create Account</button>
            </form>
            
            <p class="toggle-text">Already have an account? <span class="toggle-link" onclick="toggleForms()">Login Here</span></p>
        </div>
    </div>
</div>

<script>
    function toggleForms() {
        const loginForm = document.getElementById('loginFormContainer');
        const registerForm = document.getElementById('registerFormContainer');
        const authCard = document.getElementById('authCard');
        const errorMsg = document.querySelector('.error-message');

        if (errorMsg) errorMsg.style.display = 'none';

        // Add a small bounce animation to the card when toggling
        authCard.style.transform = 'scale(0.95)';
        setTimeout(() => {
            authCard.style.transform = 'scale(1)';
        }, 150);

        if (loginForm.classList.contains('hidden-form')) {
            loginForm.style.display = 'block';
            setTimeout(() => { loginForm.classList.remove('hidden-form'); }, 10);
            
            registerForm.classList.add('hidden-form');
            setTimeout(() => { registerForm.style.display = 'none'; }, 400);
        } else {
            registerForm.style.display = 'block';
            setTimeout(() => { registerForm.classList.remove('hidden-form'); }, 10);
            
            loginForm.classList.add('hidden-form');
            setTimeout(() => { loginForm.style.display = 'none'; }, 400);
        }
    }

    <?php if ($showAlert): ?>
    document.addEventListener("DOMContentLoaded", () => {
        const authCard = document.getElementById('authCard');
        authCard.style.opacity = '0'; // Hide login form initially
        
        setTimeout(() => {
            const alertBox = document.getElementById('imageAlertOverlay');
            if(alertBox) {
                alertBox.style.opacity = '0';
                setTimeout(() => alertBox.remove(), 2000);
            }
            authCard.style.transition = 'opacity 2s ease';
            authCard.style.opacity = '1';
        }, 7000);
    });
    <?php endif; ?>
</script>

</body>
</html>
