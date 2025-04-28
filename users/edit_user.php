<?php 
$id = $_GET["userid"]; 
include_once "../connect.php"; 

$sql = "SELECT * FROM users WHERE user_id = $id";
$user = mysqli_query($myconnection, $sql); 
$myuser = mysqli_fetch_assoc($user);

$errors = [];

if (isset($_POST["btn"])) {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"] ?? '';
    $confirm_password = $_POST["confirm_password"] ?? '';

    if ($name === '') $errors['name'] = "Name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Invalid email address.";
    if (!empty($password) && strlen($password) < 6) $errors['password'] = "Password must be at least 6 characters.";
    if (!empty($password) && $password !== $confirm_password) $errors['confirm_password'] = "Passwords do not match.";

    if (empty($errors)) {
        if ($_FILES["image"]["name"] != "") {
            $filename = $_FILES["image"]["name"];
            $tempname = $_FILES["image"]["tmp_name"];
            $folder = "../uploads/" . time() . "_" . $filename;
            move_uploaded_file($tempname, $folder);
            $image_name = basename($folder);
        } else {
            $image_name = $myuser["profile_image"];
        }

        $update_password = !empty($password) ? ", password='" . password_hash($password, PASSWORD_DEFAULT) . "'" : "";

        $sql = "UPDATE users SET name='$name', email='$email', profile_image='$image_name' $update_password WHERE user_id=$id";
        mysqli_query($myconnection, $sql); 
        header("location: users.php"); 
        exit;
    }
}
?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
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
        .form-control, .form-select {
            background-color: #fff;
            border: 1px solid #d3c8b8;
            border-radius: 8px;
            padding: 12px 15px;
        }
        .form-control:focus, .form-select:focus {
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
        .user-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #e0d6c2;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 15px;
        }
        .image-container {
            text-align: center;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            body {
                padding: 20px;
            }
            .form-container {
                padding: 25px;
            }
            .user-image {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>
<body>
<div class="form-container">
    <h2>Edit User Profile</h2>
    <form method="POST" enctype="multipart/form-data" novalidate>
        <!-- Email -->
        <div class="mb-3">
            <label class="form-label">Email address</label>
            <input type="text" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                   value="<?= htmlspecialchars($myuser["email"]) ?>" oninput="clearError('email')">
            <div class="invalid-feedback"><?= $errors['email'] ?? '' ?></div>
        </div>

        <!-- Name -->
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                   value="<?= htmlspecialchars($myuser["name"]) ?>" oninput="clearError('name')">
            <div class="invalid-feedback"><?= $errors['name'] ?? '' ?></div>
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label class="form-label">New Password (optional)</label>
            <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                   oninput="clearError('password')">
            <div class="invalid-feedback"><?= $errors['password'] ?? '' ?></div>
        </div>

        <!-- Confirm Password -->
        <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                   oninput="clearError('confirm_password')">
            <div class="invalid-feedback"><?= $errors['confirm_password'] ?? '' ?></div>
        </div>

        <!-- Image -->
        <div class="mb-4">
            <div class="image-container">
                <img src='../uploads/<?= htmlspecialchars($myuser["profile_image"]) ?>' class="user-image" 
                     onerror="this.src='https://via.placeholder.com/120?text=No+Image'"><br>
                <label class="form-label">Change Profile Picture</label>
            </div>
            <input type="file" name="image" class="form-control">
        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="submit" name="btn" class="btn btn-primary">Update Profile</button>
        </div>
    </form>
</div>

<script>
    function clearError(field) {
        const fieldElement = document.querySelector(`[name=${field}]`);
        if (fieldElement.classList.contains('is-invalid')) {
            fieldElement.classList.remove('is-invalid');
        }
        const feedback = fieldElement.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.textContent = '';
        }
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>