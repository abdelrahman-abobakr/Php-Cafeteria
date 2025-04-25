<?php
include_once "connect.php";
$errors = [];
$values = ['name' => '', 'email' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $profile_picture = $_FILES['profile_picture'] ?? null;

    $values['name'] = $name;
    $values['email'] = $email;

    // Validation
    if ($name === '') $errors['name'] = "Name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Invalid email.";
    if (strlen($password) < 6) $errors['password'] = "Password must be at least 6 characters.";
    if ($password !== $confirm_password) $errors['confirm_password'] = "Passwords do not match.";

    if (!$profile_picture || $profile_picture['error'] != 0) {
        $errors['profile_picture'] = "Profile picture is required.";
    } else {
        $fileName = $profile_picture["name"];
        $filePath = $profile_picture["tmp_name"];
        $fileArray = explode(".", $fileName);
        $ext = strtolower(end($fileArray));
        $allowed = ["png", "jpg", "jpeg", "gif", "svg"];

        if (!in_array($ext, $allowed)) {
            $errors['profile_picture'] = "Only image files (png, jpg, jpeg, gif, svg) are allowed.";
        }
    }

    if (empty($errors)) {
        // Save image
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $img_name = time() . "_" . basename($fileName);
        $target_path = $upload_dir . $img_name;
        move_uploaded_file($filePath, $target_path);

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

     
        if ($myconnection) {
            $sql = "INSERT INTO users (name, email, password, profile_image) 
                    VALUES ('$name', '$email', '$hashed_password', '$img_name')";

            if (mysqli_query($myconnection, $sql)) {
                header("Location: login.php");
                exit();
            } else {
                echo "Database error: " . mysqli_error($myconnection);
            }
        } else {
            echo "Database connection failed!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: #fff;
            border-radius: 15px;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #0d6efd;
        }
    </style>
</head>
<body>
<div class="container form-container">
    <h2 class="mb-4 text-center">Register</h2>
    <form method="post" enctype="multipart/form-data" novalidate>
        <!-- Name -->
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($values['name']) ?>">
            <div class="invalid-feedback"><?= $errors['name'] ?? '' ?></div>
        </div>

        <!-- Email -->
        <div class="mb-3">
            <label class="form-label">Email address</label>
            <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($values['email']) ?>">
            <div class="invalid-feedback"><?= $errors['email'] ?? '' ?></div>
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>">
            <div class="invalid-feedback"><?= $errors['password'] ?? '' ?></div>
        </div>

        <!-- Confirm Password -->
        <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>">
            <div class="invalid-feedback"><?= $errors['confirm_password'] ?? '' ?></div>
        </div>

        <!-- Profile Picture -->
        <div class="mb-3">
            <label class="form-label">Profile Picture</label>
            <input type="file" name="profile_picture" class="form-control <?= isset($errors['profile_picture']) ? 'is-invalid' : '' ?>">
            <div class="invalid-feedback"><?= $errors['profile_picture'] ?? '' ?></div>
        </div>
        
        <button type="submit" class="btn btn-primary">Submit</button>
        <button type="reset" class="btn btn-secondary">Reset</button>
    </form>
</div>
</body>
</html>
