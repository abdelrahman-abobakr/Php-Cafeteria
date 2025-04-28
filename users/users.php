<?php 
session_start();
if($_SESSION['user_role']!="admin")
{
    header("Location: ../unauth.php"); 
    exit();
}
include_once "../connect.php"; 

// التحقق هل تم طلب عرض الـ Admins فقط؟
if (isset($_GET['filter']) && $_GET['filter'] === 'admins') {
    $sql = "SELECT * FROM users WHERE role = 'admin'"; 
} else {
    $sql = "SELECT * FROM users"; 
}

$myusers = mysqli_query($connection, $sql); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f1ea;
            padding: 40px;
            color: #4a3f35;
        }
        .container {
            max-width: 1200px;
            background: #fff9f0;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 30px;
            border: 1px solid #e0d6c2;
        }
        h2 {
            font-weight: 600;
            color: #8b6b4a;
        }
        .btn-primary, .btn-secondary {
            border: none;
            padding: 10px 20px;
            font-weight: 500;
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
            color: #fff;
        }
        .btn-secondary:hover {
            background-color: #9c8c7d;
        }
        .btn-danger {
            background-color: #d4a59a;
            border: none;
            padding: 8px 16px;
            font-size: 14px;
            color: white;
        }
        .btn-danger:hover {
            background-color: #c08b7e;
        }
        .btn-warning {
            background-color: #d1b48c;
            border: none;
            color: #4a3f35;
            padding: 8px 16px;
            font-size: 14px;
        }
        .btn-warning:hover {
            background-color: #b89b6f;
        }
        .table {
            border-radius: 10px;
            overflow: hidden;
            background-color: #fff;
        }
        .table thead {
            background-color: #8b6b4a;
            color: #fff;
        }
        .table th, .table td {
            vertical-align: middle;
            text-align: center;
            padding: 15px;
        }
        .table tbody tr {
            background-color: #fffbf5;
        }
        .table tbody tr:nth-child(even) {
            background-color: #f9f5ed;
        }
        .table tbody tr:hover {
            background-color: #f3e9db;
        }
        .user-img {
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #e0d6c2;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .table-bordered {
            border: none;
        }
        .table-bordered th, .table-bordered td {
            border: 1px solid #e0d6c2;
        }
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }
        .action-btn i {
            font-size: 14px;
        }
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            .table th, .table td {
                font-size: 14px;
                padding: 10px;
            }
            .user-img {
                width: 80px !important;
                height: 80px !important;
            }
            .action-btn span {
                display: none;
            }
            .action-btn i {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><?= isset($_GET['filter']) && $_GET['filter'] === 'admins' ? 'All Admins' : 'All Users' ?></h2>
            <div class="d-flex gap-2">
                <a class="btn btn-primary" href="add_user.php">
                    <i class="fas fa-user-plus"></i> Add New User
                </a>
            </div>
        </div>

        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Image</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($users = mysqli_fetch_assoc($myusers)) : ?>
                    <tr>
                        <td><?= htmlspecialchars($users['name']) ?></td>
                        <td>
                            <img 
                                src="../Uploads/<?= htmlspecialchars($users['profile_image']) ?>" 
                                width="100" height="100" 
                                class="user-img"
                                alt="User Image"
                                onerror="this.src='https://via.placeholder.com/100?text=No+Image'"
                            >
                        </td>
                        <td>
                            <span class="badge rounded-pill 
                                <?= $users['role'] === 'admin' ? 'bg-primary' : 'bg-secondary' ?>">
                                <?= htmlspecialchars($users['role']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="edit_user.php?userid=<?= $users['user_id'] ?>" class="btn btn-warning action-btn">
                                    <i class="fas fa-edit"></i> <span>Edit</span>
                                </a>
                                <a href="delete_user.php?userid=<?= $users['user_id'] ?>" class="btn btn-danger action-btn">
                                    <i class="fas fa-trash-alt"></i> <span>Delete</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>