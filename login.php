<?php
ob_start(); 
session_start();
include_once "connect.php";

$errors = [];
$email = '';
$password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    }
    if ($password === '') {
        $errors['password'] = "Password is required.";
    }

    if (empty($errors)) {
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($myconnection, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                header("Location: home.php"); 
                exit();
            } else {
                $errors['login'] = "Incorrect password.";
            }
        } else {
            $errors['login'] = "No user found with this email.";
        }
    }
}
ob_end_flush(); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-image: url('../resources/backgrounds/pexels-marta-dzedyshko-1042863-2067431.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            margin: 0;
            padding: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-container {
            background-color: rgba(255, 249, 240, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            border: 1px solid #e0d6c2;
        }
        h2 {
            font-weight: 600;
            color: #8b6b4a;
            text-align: center;
            margin-bottom: 30px;
        }
        .form-label {
            font-weight: 500;
            color: #6b5a4a;
            margin-bottom: 8px;
        }
        .form-control {
            background-color: #fff;
            border: 1px solid #d3c8b8;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 5px;
        }
        .form-control:focus {
            border-color: #a78b6f;
            box-shadow: 0 0 0 0.25rem rgba(167, 139, 111, 0.25);
        }
        .is-invalid {
            border-color: #d4a59a;
        }
        .is-invalid:focus {
            box-shadow: 0 0 0 0.25rem rgba(212, 165, 154, 0.25);
        }
        .invalid-feedback {
            color: #c08b7e;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .btn-login {
            background-color: #a78b6f;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 500;
            width: 100%;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background-color: #8b6b4a;
        }
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
            border-radius: 8px;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
            color: #8d6e63;
        }
        .register-link a {
            color: #6d4c41;
            text-decoration: none;
            font-weight: 500;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }
        .forgot-password a {
            color: #8d6e63;
            text-decoration: none;
            font-size: 14px;
        }
        .forgot-password a:hover {
            text-decoration: underline;
            color: #6d4c41;
        }
        @media (max-width: 768px) {
            body {
                padding: 20px;
            }
            .login-container {
                padding: 30px;
            }
        }
    </style>
</head>
<body>
<div class="login-container">
    <h2>Welcome Back</h2>
    
    <?php if (isset($errors['login'])): ?>
        <div class="alert alert-danger mb-4"><?= $errors['login'] ?></div>
    <?php endif; ?>
    
    <form method="post">
        <!-- Email -->
        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                   value="<?= htmlspecialchars($email) ?>" oninput="clearError('email')">
            <div class="invalid-feedback"><?= $errors['email'] ?? '' ?></div>
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                   oninput="clearError('password')">
            <div class="invalid-feedback"><?= $errors['password'] ?? '' ?></div>
        </div>

        <button type="submit" class="btn btn-login">Login</button>
        
        <div class="forgot-password">
            <a href="reset_password.php">Forgot your password?</a>
        </div>
    </form>
    
    <div class="register-link">
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</div>

<script>
    function clearError(field) {
        const inputField = document.querySelector(`[name="${field}"]`);
        const errorMessage = inputField.nextElementSibling;
        if (inputField.classList.contains('is-invalid')) {
            inputField.classList.remove('is-invalid');
            errorMessage.textContent = '';
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>