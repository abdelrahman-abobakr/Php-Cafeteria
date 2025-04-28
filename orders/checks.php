<?php
include_once("../Connection.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checks Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
                .btn-primary {
            background-color: #944639;
            border-color: #944639;
        }
        
        .btn-primary:hover {
            background-color: #7a3a2f;
            border-color: #7a3a2f;
        }
    </style>
</head>
<body class="p-4">

    <h1 class="mb-4">Checks Page</h1>

    <!-- 2. Filter Form -->
    <form method="GET" class="d-flex flex-wrap gap-3 align-items-end mb-5">
        <div>
            <label>User</label>
            <select name="user_id" class="form-select">
                <option value="">-- All Users --</option>
                <?php
                $users = mysqli_query($connection, "SELECT user_id, name FROM users");
                while ($user = mysqli_fetch_assoc($users)):
                ?>
                <option value="<?= $user['user_id'] ?>" <?= (isset($_GET['user_id']) && $_GET['user_id'] == $user['user_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($user['name']) ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div>
            <label>From Date</label>
            <input type="date" name="from_date" class="form-control" value="<?= $_GET['from_date'] ?? '' ?>">
        </div>

        <div>
            <label>To Date</label>
            <input type="date" name="to_date" class="form-control" value="<?= $_GET['to_date'] ?? '' ?>">
        </div>

        <div>
            <button type="submit" class="btn btn-primary w-100 py-2">Filter</button>
        </div>
    </form>

    <!-- 3. Fetch and Display Data -->
    <?php
    // Build WHERE conditions
    $where = "WHERE 1";
    if (!empty($_GET['user_id'])) {
        $user_id = (int) $_GET['user_id'];
        $where .= " AND orders.user_id = $user_id";
    }
    if (!empty($_GET['from_date'])) {
        $from_date = $_GET['from_date'];
        $where .= " AND orders.created_at >= '$from_date'";
    }
    if (!empty($_GET['to_date'])) {
        $to_date = $_GET['to_date'];
        $where .= " AND orders.created_at <= '$to_date 23:59:59'";
    }

    // Query Orders
    $query = "SELECT orders.*, users.name AS user_name 
              FROM orders 
              JOIN users ON users.user_id = orders.user_id 
              $where
              ORDER BY users.name ASC, orders.created_at DESC";

    $result = mysqli_query($connection, $query);

    if (mysqli_num_rows($result) > 0):
        $current_user = null;
        while ($order = mysqli_fetch_assoc($result)):
            if ($current_user !== $order['user_name']) {
                if ($current_user !== null) echo "</div>"; // close previous user
                $current_user = $order['user_name'];
                ?>
                <div class="card mb-4">
                    <div class="card-header bg-dark text-white">
                        <?= htmlspecialchars($current_user) ?> - Total Amount: <?= number_format($order['total_amount'], 2) ?> EGP
                    </div>
                    <div class="card-body">
                <?php
            }
            ?>
            <div class="card mb-3">
                <div class="card-header">
                    <button class="btn btn-link" data-bs-toggle="collapse" data-bs-target="#order<?= $order['order_id'] ?>">
                        <?= date('Y/m/d h:i A', strtotime($order['created_at'])) ?> - Amount: <?= number_format($order['total_amount'], 2) ?> EGP
                    </button>
                </div>
                <div id="order<?= $order['order_id'] ?>" class="collapse">
                    <div class="card-body d-flex flex-wrap">
                        <?php
                        $order_id = $order['order_id'];
                        $products_query = "SELECT p.name, p.price, p.image_path, oi.quantity 
                                           FROM order_items oi
                                           JOIN products p ON oi.product_id = p.product_id
                                           WHERE oi.order_id = $order_id";
                        $products_result = mysqli_query($connection, $products_query);
                        while ($product = mysqli_fetch_assoc($products_result)):
                        ?>
                            <div class="text-center m-2 border p-2" style="width: 100px;">
                            <img src="<?= htmlspecialchars($product['image_path']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="img-fluid mb-2" style="height: 60px; object-fit: cover;">
                            <div style="font-size: small;"><?= htmlspecialchars($product['name']) ?></div>
                                <div style="font-size: small;"><?= number_format($product['price'], 2) ?> EGP</div>
                                <div style="font-size: small;">Qty: <?= $product['quantity'] ?></div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
        <?php if ($current_user !== null) echo "</div>"; // close last user ?>
    <?php else: ?>
        <div class="alert alert-warning">No results found.</div>
    <?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
