<?php
session_start();
if($_SESSION['user_role']!="admin")
{
    header("Location: ../unauth.php"); 
    exit();
}

include_once("../Connection.php");

// Get user_id from URL
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id <= 0) {
    header("Location: orders.php");
    exit();
}

// Fetch user details
$user_query = "SELECT * FROM users WHERE user_id = $user_id";
$user_result = mysqli_query($connection, $user_query);
$user = mysqli_fetch_assoc($user_result);

if (!$user) {
    header("Location: orders.php");
    exit();
}

// Fetch all orders for this user
$orders_query = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC";
$orders_result = mysqli_query($connection, $orders_query);

// Fetch order items for all orders at once (more efficient)
$order_ids = [];
while ($order = mysqli_fetch_assoc($orders_result)) {
    $order_ids[] = $order['order_id'];
}
mysqli_data_seek($orders_result, 0); // Reset pointer

$order_items = [];
if (!empty($order_ids)) {
    $order_ids_str = implode(',', $order_ids);
    $items_query = "SELECT oi.*, p.name as product_name 
                   FROM order_items oi
                   JOIN products p ON oi.product_id = p.product_id
                   WHERE oi.order_id IN ($order_ids_str)";
    $items_result = mysqli_query($connection, $items_query);
    while ($item = mysqli_fetch_assoc($items_result)) {
        $order_items[$item['order_id']][] = $item;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Orders - <?= htmlspecialchars($user['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <style>
        body {
            background-image: url('../resources/backgrounds/pexels-marta-dzedyshko-1042863-2067431.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            min-height: 100vh;
            margin: 0;
        }
        .container-wrapper {
            background-color: rgba(255, 255, 255, 0.85);
            border-radius: 15px;
            margin: 20px auto;
        }
        .custom-border {
            border-color: #944639 !important;
        }
        .order-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .badge-processing {
            background-color: #ffc107;
            color: #000;
        }
        .badge-out_for_delivery {
            background-color: #0d6efd;
            color: #fff;
        }
        .badge-done {
            background-color: #198754;
            color: #fff;
        }
        .badge-cancelled {
            background-color: #dc3545;
            color: #fff;
        }
    </style>
</head>
<body>

<div class="container-wrapper p-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 style="color:#944639">Orders for <?= htmlspecialchars($user['name']) ?></h1>
            <a href="orders.php" class="btn btn-secondary">Back to All Orders</a>
        </div>

        <?php if (mysqli_num_rows($orders_result) > 0): ?>
            <?php while($order = mysqli_fetch_assoc($orders_result)): ?>
                <div class="order-card p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h3>Order #<?= $order['order_id'] ?></h3>
                            <div>
                                <span class="badge badge-<?= str_replace('_', '-', $order['status']) ?>">
                                    <?= ucfirst(str_replace('_', ' ', $order['status'])) ?>
                                </span>
                                <span class="ms-2"><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></span>
                            </div>
                        </div>
                        <div class="text-end">
                            <div><strong>Room:</strong> <?= htmlspecialchars($order['room']) ?></div>
                            <div><strong>Total:</strong> EGP <?= number_format($order['total_amount'], 2) ?></div>
                        </div>
                    </div>

                    <?php if (!empty($order['notes'])): ?>
                        <div class="alert alert-info mb-3">
                            <strong>Notes:</strong> <?= htmlspecialchars($order['notes']) ?>
                        </div>
                    <?php endif; ?>

                    <h4>Order Items</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (isset($order_items[$order['order_id']])): ?>
                                    <?php foreach($order_items[$order['order_id']] as $item): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item['product_name']) ?></td>
                                            <td>EGP <?= number_format($item['price'], 2) ?></td>
                                            <td><?= $item['quantity'] ?></td>
                                            <td>EGP <?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No items found for this order</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <form method="POST" action="orders.php" class="d-flex align-items-center">
                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                            <select name="status" class="form-select form-select-sm me-2" style="width: 150px;">
                                <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                                <option value="out_for_delivery" <?= $order['status'] == 'out_for_delivery' ? 'selected' : '' ?>>Out for Delivery</option>
                                <option value="done" <?= $order['status'] == 'done' ? 'selected' : '' ?>>Done</option>
                                <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-sm btn-success">Update Status</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="alert alert-info">
                No orders found for this user.
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>