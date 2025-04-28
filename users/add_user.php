<?php
session_start();
if($_SESSION['user_role']!="admin")
{
    header("Location: ../unauth.php"); 
    exit();
}
include_once "../connect.php";
$errors = [];
$values = ['name' => '', 'email' => '', 'role' => 'user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // استلام القيم من النموذج
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $profile_picture = $_FILES['profile_picture'] ?? null;

    // تعيين القيم المدخلة لكي تبقى عند ظهور الأخطاء
    $values['name'] = $name;
    $values['email'] = $email;
    $values['role'] = $role;

    // التحقق من الأخطاء
    if ($name === '') $errors['name'] = "Name is required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Invalid email format.";
    if (strlen($password) < 6) $errors['password'] = "Password must be at least 6 characters.";
    if ($password !== $confirm_password) $errors['confirm_password'] = "Passwords do not match.";
    if ($role !== 'admin' && $role !== 'user') $errors['role'] = "Invalid role selected.";

    if (!$profile_picture || $profile_picture['error'] != 0) {
        $errors['profile_picture'] = "Profile picture is required.";
    } else {
        $fileName = $profile_picture["name"];
        $filePath = $profile_picture["tmp_name"];
        $fileArray = explode(".", $fileName);
        $ext = strtolower(end($fileArray));
        $allowed = ["png", "jpg", "jpeg", "gif", "svg", "jfif"];

        if (!in_array($ext, $allowed)) {
            $errors['profile_picture'] = "Only image files (png, jpg, jpeg, gif, svg, jfif) are allowed.";
        }
    }

    // التحقق من وجود الإيميل في قاعدة البيانات
    if (empty($errors)) {
        $stmt = mysqli_prepare($connection, "SELECT user_id FROM users WHERE email = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            if (mysqli_stmt_num_rows($stmt) > 0) {
                $errors['email'] = "Email already exists.";
            }
            mysqli_stmt_close($stmt);
        } else {
            $errors['database'] = "Database error during email check.";
        }
    }

    // إذا لا يوجد أخطاء، نقوم بإضافة المستخدم إلى قاعدة البيانات
    if (empty($errors)) {
        // حفظ الصورة
        $upload_dir = dirname(__DIR__) . '/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $img_name = time() . "_" . basename($fileName);
        $target_path = $upload_dir . $img_name;
        move_uploaded_file($filePath, $target_path);

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // إدخال المستخدم الجديد إلى قاعدة البيانات
        $stmt = mysqli_prepare($connection, "INSERT INTO users (name, email, password, role, profile_image) VALUES (?, ?, ?, ?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $hashed_password, $role, $img_name);
            if (mysqli_stmt_execute($stmt)) {
                // إعادة التوجيه بعد النجاح
                header("Location: users.php");
                exit();
            } else {
                $errors['database'] = "Database insert error: " . mysqli_error($connection);
            }
            mysqli_stmt_close($stmt);
        } else {
            $errors['database'] = "Database insert error: " . mysqli_error($connection);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User</title>
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
        h3 {
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
    <h3>Add New User</h3>
    <form method="post" enctype="multipart/form-data" novalidate>
        <!-- Name -->
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($values['name']) ?>" oninput="clearError('name')">
            <div class="invalid-feedback"><?= $errors['name'] ?? '' ?></div>
        </div>

        <!-- Email -->
        <div class="mb-3">
            <label class="form-label">Email address</label>
            <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($values['email']) ?>" oninput="clearError('email')">
            <div class="invalid-feedback"><?= $errors['email'] ?? '' ?></div>
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" oninput="clearError('password')">
            <div class="invalid-feedback"><?= $errors['password'] ?? '' ?></div>
        </div>

        <!-- Confirm Password -->
        <div class="mb-3">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" oninput="clearError('confirm_password')">
            <div class="invalid-feedback"><?= $errors['confirm_password'] ?? '' ?></div>
        </div>

        <!-- Role -->
        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select <?= isset($errors['role']) ? 'is-invalid' : '' ?>" oninput="clearError('role')">
                <option value="user" <?= $values['role'] === 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= $values['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
            <div class="invalid-feedback"><?= $errors['role'] ?? '' ?></div>
        </div>

        <!-- Profile Picture -->
        <div class="mb-4">
            <label class="form-label">Profile Picture</label>
            <input type="file" name="profile_picture" class="form-control <?= isset($errors['profile_picture']) ? 'is-invalid' : '' ?>" oninput="clearError('profile_picture')">
            <div class="invalid-feedback"><?= $errors['profile_picture'] ?? '' ?></div>
        </div>

        <!-- Buttons -->
        <div class="btn-group">
            <button type="submit" class="btn btn-primary">Submit</button>
            <button type="reset" class="btn btn-secondary">Reset</button>
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