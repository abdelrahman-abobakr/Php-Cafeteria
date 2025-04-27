<?php
session_start();
include_once("../Connection.php");

// Make sure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch users (all users)
$users_query = "SELECT * FROM users";
$users_result = mysqli_query($connection, $users_query);

// Fetch products
$products_query = "SELECT * FROM products WHERE deleted_at IS NULL";
$products_result = mysqli_query($connection, $products_query);

// Fetch all orders with user information, sorted by latest first
$orders_query = "SELECT o.*, u.name as user_name 
                FROM orders o
                JOIN users u ON o.user_id = u.user_id
                ORDER BY o.created_at DESC";
$orders_result = mysqli_query($connection, $orders_query);

// Static Rooms
$rooms = ["Room 101", "Room 102", "Room 103", "Room 104"];

// Handle order confirmation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_order'])) {
    $user_id = intval($_POST['user']);
    $room = mysqli_real_escape_string($connection, $_POST['room']);
    $notes = mysqli_real_escape_string($connection, $_POST['notes']);
    $total_amount = floatval($_POST['total_amount']);

    // Insert into orders
    $order_query = "INSERT INTO orders (user_id, room, total_amount, notes, created_at) 
                    VALUES ('$user_id', '$room', '$total_amount', '$notes', NOW())";
    mysqli_query($connection, $order_query);
    $order_id = mysqli_insert_id($connection);

    // Insert order items
    foreach ($_POST['products'] as $product_id => $details) {
        $qty = intval($details['quantity']);
        $price = floatval($details['price']);
        if ($qty > 0) {
            $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price) 
                           VALUES ('$order_id', '$product_id', '$qty', '$price')";
            mysqli_query($connection, $item_query);
        }
    }

    header("Location: orders.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Orders Management</title>
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
        .order-table {
            background-color: white;
        }
        .order-table th {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>

<div class="container-wrapper p-4">
    <div class="container">
        <h1 class="text-center mt-2" style="color:#944639">Orders Management</h1>

        <!-- Create New Order Section -->
        <div class="row col-lg-11 offset-lg-1 col-md-8 offset-md-2 border border-3 rounded rounded-4 p-4 bg-white custom-border mb-4">
            <h2 class="text-center mb-4">Create New Order</h2>
            <form method="POST">
                <!-- User & Room -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="user" class="form-label">Select User</label>
                        <select class="form-select" id="user" name="user" required>
                            <option value="" disabled selected>Choose a user</option>
                            <?php while($user = mysqli_fetch_assoc($users_result)): ?>
                                <option value="<?= $user['user_id'] ?>"><?= htmlspecialchars($user['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="room" class="form-label">Select Room</label>
                        <select class="form-select" id="room" name="room" required>
                            <option value="" disabled selected>Choose a room</option>
                            <?php foreach($rooms as $room): ?>
                                <option value="<?= htmlspecialchars($room) ?>"><?= htmlspecialchars($room) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Any special instructions..."></textarea>
                </div>

                <!-- Products Table -->
                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Product</th>
                                <th>Price (EGP)</th>
                                <th>Quantity</th>
                                <th>Subtotal (EGP)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Reset products result pointer
                            mysqli_data_seek($products_result, 0);
                            while($product = mysqli_fetch_assoc($products_result)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($product['name']) ?></td>
                                    <td><?= $product['price'] ?></td>
                                    <td>
                                        <div class="input-group">
                                            <button type="button" class="btn btn-outline-secondary" onclick="decrementQuantity(<?= $product['product_id'] ?>)">-</button>
                                            <input type="text" name="products[<?= $product['product_id'] ?>][quantity]" id="quantity_<?= $product['product_id'] ?>" value="0" class="form-control text-center" readonly>
                                            <button type="button" class="btn btn-outline-secondary" onclick="incrementQuantity(<?= $product['product_id'] ?>)">+</button>
                                        </div>
                                        <input type="hidden" name="products[<?= $product['product_id'] ?>][price]" value="<?= $product['price'] ?>">
                                    </td>
                                    <td><span id="subtotal_<?= $product['product_id'] ?>">0.00</span></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Total Amount -->
                <div class="mb-3 text-end">
                    <h4>Total: EGP <span id="totalAmount">0.00</span></h4>
                    <input type="hidden" name="total_amount" id="total_amount_input" value="0">
                </div>

                <!-- Submit -->
                <div class="text-center">
                    <button type="submit" name="confirm_order" class="btn btn-primary w-50">Confirm Order</button>
                </div>
            </form>
        </div>

        <!-- All Orders Table Section -->
        <div class="row col-lg-11 offset-lg-1 col-md-8 offset-md-2 border border-3 rounded rounded-4 p-4 bg-white custom-border">
            <h2 class="text-center mb-4">All Orders</h2>
            <div class="table-responsive">
                <table class="table order-table table-hover">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>User</th>
                            <th>Room</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Notes</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($order = mysqli_fetch_assoc($orders_result)): ?>
                            <tr>
                                <td><?= $order['order_id'] ?></td>
                                <td><?= htmlspecialchars($order['user_name']) ?></td>
                                <td><?= htmlspecialchars($order['room']) ?></td>
                                <td>EGP <?= number_format($order['total_amount'], 2) ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $order['status'] == 'processing' ? 'warning' : 
                                        ($order['status'] == 'out_for_delivery' ? 'primary' : 
                                        ($order['status'] == 'done' ? 'success' : 'danger'))
                                    ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                                <td><?= !empty($order['notes']) ? htmlspecialchars($order['notes']) : '—' ?></td>
                                <td><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></td>
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
function incrementQuantity(id) {
    let qtyInput = document.getElementById('quantity_' + id);
    qtyInput.value = parseInt(qtyInput.value) + 1;
    updateSubtotal(id);
}

function decrementQuantity(id) {
    let qtyInput = document.getElementById('quantity_' + id);
    if (parseInt(qtyInput.value) > 0) {
        qtyInput.value = parseInt(qtyInput.value) - 1;
        updateSubtotal(id);
    }
}

function updateSubtotal(id) {
    let qty = parseInt(document.getElementById('quantity_' + id).value);
    let price = parseFloat(document.querySelector(`input[name="products[${id}][price]"]`).value);
    let subtotal = qty * price;
    document.getElementById('subtotal_' + id).innerText = subtotal.toFixed(2);
    calculateTotal();
}

function calculateTotal() {
    let total = 0;
    document.querySelectorAll("input[name^='products']").forEach(input => {
        if (input.name.includes('quantity')) {
            let quantity = parseInt(input.value);
            let priceInputName = input.name.replace('quantity', 'price');
            let priceInput = document.querySelector(`input[name="${priceInputName}"]`);
            let price = parseFloat(priceInput.value);
            total += quantity * price;
        }
    });
    document.getElementById('totalAmount').innerText = total.toFixed(2);
    document.getElementById('total_amount_input').value = total.toFixed(2);
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>