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
        $errors['email'] = "Invalid email.";
    }
    if (strlen($new_password) < 6) {
        $errors['new_password'] = "Password must be at least 6 characters.";
    }
    if ($new_password !== $confirm_password) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($myconnection, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $update = "UPDATE users SET password = '$hashed' WHERE email = '$email'";
            if (mysqli_query($myconnection, $update)) {
                $success = "Password has been updated successfully. <a href='login.php'>Login</a>";
            } else {
                $errors['database'] = "Failed to update password.";
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
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .reset-container {
            max-width: 450px;
            margin: 80px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
<div class="reset-container">
    <h2 class="text-center mb-4">Reset Password</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($email) ?>">
            <div class="invalid-feedback"><?= $errors['email'] ?? '' ?></div>
        </div>

        <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>">
            <div class="invalid-feedback"><?= $errors['new_password'] ?? '' ?></div>
        </div>

        <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>">
            <div class="invalid-feedback"><?= $errors['confirm_password'] ?? '' ?></div>
        </div>

        <?php if (isset($errors['database'])): ?>
            <div class="alert alert-danger"><?= $errors['database'] ?></div>
        <?php endif; ?>

        <button type="submit" class="btn btn-primary w-100">Reset Password</button>
    </form>
</div>
</body>
</html>
