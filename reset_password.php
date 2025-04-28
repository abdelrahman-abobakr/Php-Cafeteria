<?php
include_once "connect.php";
$errors = [];
$success = '';
$email = '';
$new_password = '';
$confirm_password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    }
    if (strlen($new_password) < 6) {
        $errors['new_password'] = "Password must be at least 6 characters.";
    }
    if ($new_password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($connection, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update = "UPDATE users SET password = '$hashed' WHERE email = '$email'";
            if (mysqli_query($connection, $update)) {
                $success = "Password has been updated successfully. <a href='login.php' class='text-decoration-underline'>Login now</a>";
            } else {
                $errors['database'] = "Failed to update password. Please try again.";
            }
        } else {
            $errors['email'] = "No user found with this email.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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
        .reset-container {
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
        .btn-reset {
            background-color: #a78b6f;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 500;
            width: 100%;
            transition: all 0.3s;
        }
        .btn-reset:hover {
            background-color: #8b6b4a;
        }
        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #8d6e63;
        }
        .login-link a {
            color: #6d4c41;
            text-decoration: none;
            font-weight: 500;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            body {
                padding: 20px;
            }
            .reset-container {
                padding: 30px;
            }
        }
    </style>
</head>
<body>
<div class="reset-container">
    <h2>Reset Your Password</h2>
    
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    
    <?php if (isset($errors['database'])): ?>
        <div class="alert alert-danger"><?= $errors['database'] ?></div>
    <?php endif; ?>
    
    <form method="post">
        <!-- Email -->
        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                   value="<?= htmlspecialchars($email) ?>" oninput="clearError('email')">
            <div class="invalid-feedback"><?= $errors['email'] ?? '' ?></div>
        </div>

        <!-- New Password -->
        <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>" 
                   oninput="clearError('new_password')">
            <div class="invalid-feedback"><?= $errors['new_password'] ?? '' ?></div>
        </div>

        <!-- Confirm Password -->
        <div class="mb-4">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                   oninput="clearError('confirm_password')">
            <div class="invalid-feedback"><?= $errors['confirm_password'] ?? '' ?></div>
        </div>

        <button type="submit" class="btn btn-reset">Reset Password</button>
    </form>
    
    <div class="login-link">
        <p>Remember your password? <a href="login.php">Login here</a></p>
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