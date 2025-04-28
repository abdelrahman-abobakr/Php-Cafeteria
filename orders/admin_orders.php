<?php
session_start();
if($_SESSION['user_role']!="admin")
{
    header("Location: ../unauth.php"); 
    exit();
}
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

// Create an array to store order items for each order
$order_items = [];
$items_query = "SELECT oi.*, p.name as product_name, p.image_path 
                FROM order_items oi
                JOIN products p ON oi.product_id = p.product_id
                ORDER BY oi.order_id";
$items_result = mysqli_query($connection, $items_query);
while($item = mysqli_fetch_assoc($items_result)) {
    $order_items[$item['order_id']][] = $item;
}

// Static Rooms
$rooms = ["Room 101", "Room 102", "Room 103", "Room 104"];

// Handle order confirmation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_order'])) {
    $user_id = intval($_POST['user']);
    $room = mysqli_real_escape_string($connection, $_POST['room']);
    $notes = mysqli_real_escape_string($connection, $_POST['notes']);
    $total_amount = floatval($_POST['total_amount']);

    // Insert into orders
    $order_query = "INSERT INTO orders (user_id, room, total_amount, notes, created_at, status) 
                    VALUES ('$user_id', '$room', '$total_amount', '$notes', NOW(), 'processing')";
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
    <title>Orders Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
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
            padding: 20px;
        }
        .custom-border {
            border-color: #944639 !important;
        }
        
        /* Products Table Specific Styles */
        .products-table {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .products-table thead {
            background-color: #944639;
            color: white;
        }
        
        .products-table th {
            padding: 12px 15px;
            text-align: center;
            font-weight: 600;
        }
        
        .products-table td {
            padding: 10px 15px;
            vertical-align: middle;
            text-align: center;
        }
        
        .products-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .products-table tbody tr:hover {
            background-color: #f1f1f1;
        }
        
        .products-table .input-group {
            justify-content: center;
            width: 140px;
            margin: 0 auto;
        }
        
        .products-table .input-group .form-control {
            max-width: 50px;
            text-align: center;
        }
        
        .products-table img {
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        
          
        /* Form Styles */
        .form-section {
            background-color: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }
        
        h1, h2 {
            color: #944639;
            margin-bottom: 20px;
        }
        
        .btn-primary {
            background-color: #944639;
            border-color: #944639;
        }
        
        .btn-primary:hover {
            background-color: #7a3a2f;
            border-color: #7a3a2f;
        }
        
        /* Collapsible Row Styles */
        .collapse.show {
            background-color: #f8f9fa;
        }

        .collapse .order-items-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 10px;
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
    </style>
</head>
<body>

<div class="container-wrapper">
    <div class="container">
        <h1 class="text-center mt-2">Orders Management</h1>

        <!-- Create New Order Section -->
        <div class="row form-section">
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

                <!-- All Products Table Section -->
                <div class="row form-section">
                    <h2 class="text-center mb-4">All Products</h2>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover order-table">
                            <thead>
                                <tr>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Price (EGP)</th>
                                    <th>Quantity</th>
                                    <th>Subtotal (EGP)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php mysqli_data_seek($products_result, 0); ?>
                                <?php while($product = mysqli_fetch_assoc($products_result)): ?>
                                    <tr>
                                        <td>
                                            <?php if (!empty($product['image_path'])): ?>
                                                <img src="../Uploads/<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width: 60px; height: 60px; object-fit: cover;">
                                            <?php else: ?>
                                                <img src="../Uploads/default.jpg" alt="Default Image" style="width: 60px; height: 60px; object-fit: cover;">
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($product['name']) ?></td>
                                        <td><?= number_format($product['price'], 2) ?></td>
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
                </div>

                <!-- Total Amount -->
                <div class="mb-3 text-end">
                    <h4>Total: EGP <span id="totalAmount">0.00</span></h4>
                    <input type="hidden" name="total_amount" id="total_amount_input" value="0">
                </div>

                <!-- Submit -->
                <div class="text-center">
                    <button type="submit" name="confirm_order" class="btn btn-primary w-50 py-2">Confirm Order</button>
                </div>
            </form>
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