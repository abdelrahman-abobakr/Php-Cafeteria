<?php

session_start();
if($_SESSION['user_role']!="admin")
{
    header("Location: ../unauth.php"); 
    exit();
}

include_once("../Connection.php");

$user_id = $_SESSION['user_id'];
$query = "SELECT profile_image FROM users WHERE user_id = '$user_id' LIMIT 1";
$result = mysqli_query($connection, $query);

if (!$result) {
    die('Query Error: ' . mysqli_error($connection));
}

$user = mysqli_fetch_assoc($result);

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch all orders with user information
$orders_query = "SELECT o.*, u.name as user_name 
                FROM orders o
                JOIN users u ON o.user_id = u.user_id
                ORDER BY o.created_at DESC";
$orders_result = mysqli_query($connection, $orders_query);

// Create an array to store order items for each order
$order_items = [];
$items_query = "SELECT oi.*, p.name as product_name, p.image_path 
                FROM order_items oi
                JOIN products p ON oi.product_id = p.product_id
                ORDER BY oi.order_id";
$items_result = mysqli_query($connection, $items_query);
while ($item = mysqli_fetch_assoc($items_result)) {
    $order_items[$item['order_id']][] = $item;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = mysqli_real_escape_string($connection, $_POST['status']);
    
    $update_query = "UPDATE orders SET status = '$status' WHERE order_id = '$order_id'";
    mysqli_query($connection, $update_query);
    
    header("Location: orders.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .order-table {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .order-table th {
            background-color: #343a40;
            color: white;
        }
        .status-form {
            display: flex;
            gap: 5px;
            align-items: center;
        }
        .status-form select {
            width: 150px;
        }
        .btn-primary {
            background-color: #944639;
            border-color: #944639;
        }
        .btn-primary:hover {
            background-color: #7a3a2f;
            border-color: #7a3a2f;
        }
        /* Order Items Styles */
        .order-items-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 10px;
        }
        .order-item {
            text-align: center;
            background: #f8f9fa;
            border-radius: 5px;
            padding: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            min-width: 80px;
        }
        .order-item img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #dee2e6;
        }
        .order-item div {
            font-size: 0.8rem;
            margin-top: 3px;
        }
        /* Collapsible Row Styles */
        .collapse.show {
            background-color: #f8f9fa;
        }
        .btn-link i.bi-chevron-down {
            transition: transform 0.2s;
        }
        .btn-link[aria-expanded="true"] i.bi-chevron-down {
            transform: rotate(180deg);
        }
        .order-table .collapse td {
            padding: 0;
            border: none;
        }
        .order-table .collapse .p-3 {
            border-top: 1px solid #dee2e6;
        }
        /* Form Section */
        .form-section {
            background-color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .container-wrapper {
            background-color: rgba(255, 255, 255, 0.85);
            border-radius: 15px;
            margin: 20px auto;
            padding: 20px;
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
                                <a class="nav-link" aria-current="page" href="orders.php">Orders</a>
                            </li>    
                            <li class="nav-item">
                                <a class="nav-link" aria-current="page" href="admin_orders.php">Add Order</a>
                            </li>    
                            <li class="nav-item">
                                <a class="nav-link" aria-current="page" href="checks.php">Checks</a>
                            </li>    

                        </ul>
                    </div>                                   
                    <div class="dropdown pt-2 ms-3">
                        <p class=" dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Users
                        </p>
                        <ul class="dropdown-menu">
                            <li class="nav-item">
                                <a class="nav-link" aria-current="page" href="../users/users.php">Users</a>
                            </li>    
                            <li class="nav-item">
                                <a class="nav-link" aria-current="page" href="../users/add_user.php">Add User</a>
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
    <div class="container-wrapper">

        <div class="container">
            <h1 class="text-center mb-4">All Orders</h1>
            
            <!-- All Orders Table Section -->
            <div class="row form-section">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover order-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>User</th>
                                <th>Room</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Notes</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php mysqli_data_seek($orders_result, 0); ?>
                            <?php while($order = mysqli_fetch_assoc($orders_result)): ?>
                                <tr>
                                    <td>
                                        <button class="btn btn-link p-0 text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#items-<?= $order['order_id'] ?>" aria-expanded="false" aria-controls="items-<?= $order['order_id'] ?>">
                                            <?= $order['order_id'] ?> <i class="bi bi-chevron-down"></i>
                                        </button>
                                    </td>
                                    <td><?= htmlspecialchars($order['user_name']) ?></td>
                                    <td><?= htmlspecialchars($order['room']) ?></td>
                                    <td>EGP <?= number_format($order['total_amount'], 2) ?></td>
                                    <td>
                                        <form method="POST" class="status-form">
                                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                            <select name="status" class="form-select form-select-sm">
                                                <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                                                <option value="out_for_delivery" <?= $order['status'] == 'out_for_delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                                                <option value="done" <?= $order['status'] == 'done' ? 'selected' : '' ?>>Done</option>
                                                <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            </select>
                                            <button type="submit" name="update_status" class="btn btn-sm btn-success w-100 py-2">Save</button>
                                        </form>
                                    </td>
                                    <td><?= !empty($order['notes']) ? htmlspecialchars($order['notes']) : '—' ?></td>
                                    <td><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></td>
                                    <td>
                                        <a href="user_orders.php?user_id=<?= $order['user_id'] ?>" class="btn btn-primary w-100 py-2">View</a>
                                    </td>
                                </tr>
                                <!-- Collapsible Row for Order Items -->
                                <tr class="collapse" id="items-<?= $order['order_id'] ?>">
                                    <td colspan="8">
                                        <div class="p-3">
                                            <h6>Order Items</h6>
                                            <?php if(isset($order_items[$order['order_id']])): ?>
                                                <div class="order-items-container">
                                                    <?php foreach($order_items[$order['order_id']] as $item): ?>
                                                        <div class="order-item">
                                                            <?php if (!empty($item['image_path'])): ?>
                                                                <img src="../Uploads/<?= htmlspecialchars($item['image_path']) ?>" 
                                                                    alt="<?= htmlspecialchars($item['product_name']) ?>">
                                                            <?php else: ?>
                                                                <img src="../Uploads/default.jpg" 
                                                                    alt="Default Image">
                                                            <?php endif; ?>
                                                            <div>
                                                                <div><?= htmlspecialchars($item['product_name']) ?></div>
                                                                <div><?= $item['quantity'] ?> × <?= number_format($item['price'], 2) ?> EGP</div>
                                                                <div><strong><?= number_format($item['quantity'] * $item['price'], 2) ?> EGP</strong></div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <p>No items</p>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<!-- Scripts -->
<script>
    // Optional: Accordion-like behavior for collapsible sections
    document.querySelectorAll('.order-table .btn-link').forEach(button => {
        button.addEventListener('click', function () {
            // Close all other collapsible sections
            document.querySelectorAll('.order-table .collapse.show').forEach(collapse => {
                if (collapse.id !== this.getAttribute('data-bs-target').substring(1)) {
                    new bootstrap.Collapse(collapse, { toggle: false }).hide();
                }
            });
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>