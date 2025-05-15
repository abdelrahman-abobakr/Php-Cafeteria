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

    // Check if email already exists in the database
    if (empty($errors)) {
        $sql = "SELECT * FROM users WHERE email = ?";
        if ($stmt = mysqli_prepare($connection, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            if (mysqli_num_rows($result) > 0) {
                $errors['email'] = "This email is already registered.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // If no errors, proceed to save user
    if (empty($errors)) {
        // Save image
        $upload_dir = 'resources/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $img_name = time() . "_" . basename($fileName);
        $target_path = $upload_dir . $img_name;
        move_uploaded_file($filePath, $target_path);

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert into database
        if ($connection) {
            $sql = "INSERT INTO users (name, email, password, profile_image) 
                    VALUES ('$name', '$email', '$hashed_password', '$img_name')";

            if (mysqli_query($connection, $sql)) {
                header("Location: login.php");
                exit();
            } else {
                echo "Database error: " . mysqli_error($connection);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Form</title>
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
        .form-container {
            background-color: rgba(255, 249, 240, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 600px;
            border: 1px solid #e0d6c2;
        }
        h2,h1 {
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
        .btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 12px 25px;
            border: none;
            transition: all 0.3s;
        }
        .btn-primary {
            background-color: #a78b6f;
            color: white;
        }
        .btn-primary:hover {
            background-color: #8b6b4a;
        }
        .btn-secondary {
            background-color: #b8a99a;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #9c8c7d;
        }
        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 25px;
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
            .form-container {
                padding: 25px;
            }
        }
    </style>
</head>
<body>
<div class="form-container">
    <h1>Coffee Drink</h1>
    <h2>Create Your Account</h2>
    <form method="post" enctype="multipart/form-data" novalidate>
        <!-- Name -->
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" id="name" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                   value="<?= htmlspecialchars($values['name']) ?>" oninput="clearError('name')">
            <div class="invalid-feedback" id="name-error"><?= $errors['name'] ?? '' ?></div>
        </div>

        <!-- Email -->
        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" id="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                   value="<?= htmlspecialchars($values['email']) ?>" oninput="clearError('email')">
            <div class="invalid-feedback" id="email-error"><?= $errors['email'] ?? '' ?></div>
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                   oninput="clearError('password')">
            <div class="invalid-feedback" id="password-error"><?= $errors['password'] ?? '' ?></div>
        </div>

        <!-- Confirm Password -->
        <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                   oninput="clearError('confirm_password')">
            <div class="invalid-feedback" id="confirm_password-error"><?= $errors['confirm_password'] ?? '' ?></div>
        </div>

        <!-- Profile Picture -->
        <div class="mb-4">
            <label class="form-label">Profile Picture</label>
            <input type="file" id="profile_picture" name="profile_picture" class="form-control <?= isset($errors['profile_picture']) ? 'is-invalid' : '' ?>" 
                   oninput="clearError('profile_picture')">
            <div class="invalid-feedback" id="profile_picture-error"><?= $errors['profile_picture'] ?? '' ?></div>
        </div>
        
        <div class="btn-group">
            <button type="submit" class="btn btn-primary">Register</button>
            <button type="reset" class="btn btn-secondary">Reset</button>
        </div>
        
        <div class="login-link">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </form>
</div>

<script>
    function clearError(field) {
        const inputField = document.getElementById(field);
        const errorMessage = document.getElementById(field + "-error");
        inputField.classList.remove('is-invalid');
        errorMessage.textContent = '';
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>