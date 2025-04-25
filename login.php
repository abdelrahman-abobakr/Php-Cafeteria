<?php
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
                // Password is correct → start session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];

                header("Location: home.php"); // غيرها حسب صفحتك بعد تسجيل الدخول
                exit();
            } else {
                $errors['login'] = "Incorrect password.";
            }
        } else {
            $errors['login'] = "No user found with this email.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
        }
        .login-container {
            max-width: 400px;
            margin: 80px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }
        h2 {
            color: #0d6efd;
        }
    </style>
</head>
<body>
<div class="login-container">
    <h2 class="text-center mb-4">Login</h2>
    <?php if (isset($errors['login'])): ?>
        <div class="alert alert-danger"><?= $errors['login'] ?></div>
    <?php endif; ?>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($email) ?>">
            <div class="invalid-feedback"><?= $errors['email'] ?? '' ?></div>
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>">
            <div class="invalid-feedback"><?= $errors['password'] ?? '' ?></div>
        </div>

        <button type="submit" class="btn btn-primary w-100">Login</button>
        <p class="mt-3 text-center">
        <a href="reset_password.php">Forgot your password?</a>
</p>
    </form>
</div>
</body>
</html>
