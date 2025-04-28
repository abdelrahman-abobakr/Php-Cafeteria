<?php 
session_start();
if($_SESSION['user_role']!="admin")
{
    header("Location: ../unauth.php"); 
    exit();
}
include_once "../connect.php"; 

$user_id = $_SESSION['user_id'];
$query = "SELECT profile_image FROM users WHERE user_id = '$user_id' LIMIT 1";
$result = mysqli_query($connection, $query);

if (!$result) {
    die('Query Error: ' . mysqli_error($connection));
}

$user = mysqli_fetch_assoc($result);


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
    <nav class="navbar navbar-expand-lg bg-body-tertiary mb-5">
        <div class="container-fluid">
            <a class="navbar-brand" href="../home.php">Coffee Drink</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <div class="dropdown pt-2 ms-2">
                        <p class=" dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Products
                        </p>
                        <ul class="dropdown-menu">
                            <li class="nav-item">
                                <a class="nav-link" aria-current="page" href="../products/products.php">Products</a>
                            </li>   
                            <li class="nav-item">
                                <a class="nav-link" href="../products/addProduct.php">Add Products</a>
                            </li>  
                            <li class="nav-item">
                                <a class="nav-link" href="../products/deletedProducts.php">Deleted Products</a>
                            </li>  

                        </ul>
                    </div>                  
                    <div class="dropdown pt-2 ms-3">
                        <p class=" dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Categories
                        </p>
                        <ul class="dropdown-menu">
                            <li class="nav-item">
                                <a class="nav-link" aria-current="page" href="../categories/addCategory.php">Categories</a>
                            </li>    

                        </ul>
                    </div>                  
                    <div class="dropdown pt-2 ms-3">
                        <p class=" dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Orders
                        </p>
                        <ul class="dropdown-menu">
                            <li class="nav-item">
                                <a class="nav-link" aria-current="page" href="../orders/orders.php">Orders</a>
                            </li>    
                            <li class="nav-item">
                                <a class="nav-link" aria-current="page" href="../orders/admin_orders.php">Add Order</a>
                            </li>    
                            <li class="nav-item">
                                <a class="nav-link" aria-current="page" href="../orders/checks.php">Checks</a>
                            </li>    

                        </ul>
                    </div>                                   
                    <div class="dropdown pt-2 ms-3">
                        <p class=" dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Users
                        </p>
                        <ul class="dropdown-menu">
                            <li class="nav-item">
                                <a class="nav-link" aria-current="page" href="users.php">Users</a>
                            </li>    
                            <li class="nav-item">
                                <a class="nav-link" aria-current="page" href="add_user.php">Add User</a>
                            </li>       

                        </ul>
                    </div>                                   
                                     
                </ul>
                <div class="user-box d-flex align-items-center">
                    <img src="../resources/uploads/<?= htmlspecialchars($user['profile_image']) ?>"
                        class="rounded-circle border border-secondary" 
                        style="width: 40px; height: 40px; object-fit: cover;" 
                        alt="User Photo">
                    <span class="ms-2 fw-bold"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                </div>
                <a href="../logout.php" class="btn btn-danger mx-3">Log out</a>

            </div>
        </div>
    </nav>
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
                                src="../resources/uploads/<?= htmlspecialchars($users['profile_image']) ?>" 
                                width="100" height="100" 
                                class="user-img"
                                alt="User Image"
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