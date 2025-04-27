<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
    $_SESSION['room'] = '';
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];

require_once 'connect.php';

if (!isset($user_id) || empty($user_id)) {
    die('No user ID found in session');
}

$query = "SELECT * FROM users WHERE user_id = '$user_id' LIMIT 1";
$result = mysqli_query($connection, $query);

if (!$result) {
    die('Query Error: ' . mysqli_error($connection));
}

$user = mysqli_fetch_assoc($result);


$categories=[];
    $products=[];
    
    // pagination variables
    $items_per_page = 8; 
    $current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $offset = ($current_page - 1) * $items_per_page;
    
    
    // retrieving all categories
    $categories_query = "SELECT name, category_id FROM categories";
    $result = mysqli_query($connection, $categories_query);
    while($cat = mysqli_fetch_assoc($result)) {
        $categories[] = $cat;
    }

    $products_query = "SELECT * FROM products WHERE deleted_at IS NULL LIMIT $items_per_page OFFSET $offset";
    $result = mysqli_query($connection, $products_query);
    while($prod = mysqli_fetch_assoc($result)) {
        $products[] = $prod;
    }

    // counting total_items and total_pages for pagination
    $count_query = "SELECT COUNT(*) as total FROM products WHERE deleted_at IS NULL";
    $count_result = mysqli_query($connection, $count_query);
    $total_items = mysqli_fetch_assoc($count_result)['total'];  // Total number of products
    $total_pages = ceil($total_items / $items_per_page);




    // Handle adding to cart
if (isset($_POST['add_to_cart'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity'] ?? 1);
    
    // Get product details
    $product_query = "SELECT * FROM products WHERE product_id = $product_id";
    $product_result = mysqli_query($connection, $product_query);
    $product = mysqli_fetch_assoc($product_result);
    
    if ($product) {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = [
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $quantity,
                'image' => $product['image_path']
            ];
        }
    }
}

// Handle removing from cart
if (isset($_GET['remove_from_cart'])) {
    $product_id = intval($_GET['remove_from_cart']);
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
}

// Handle quantity updates
if (isset($_POST['update_quantity'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    
    if ($quantity > 0 && isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] = $quantity;
    }
}

// Handle room selection
if (isset($_POST['set_room'])) {
    $_SESSION['room'] = mysqli_real_escape_string($connection, $_POST['room']);
}

// Handle order submission
if (isset($_POST['place_order'])) {
    if (!empty($_SESSION['cart']) && !empty($_SESSION['room'])) {
        // Calculate total
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        // Create order
        $order_query = "INSERT INTO orders (user_id, room, total_amount) 
                       VALUES ($user_id, '{$_SESSION['room']}', $total)";
        mysqli_query($connection, $order_query);
        $order_id = mysqli_insert_id($connection);
        
        // Add order items
        foreach ($_SESSION['cart'] as $product_id => $item) {
            $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price)
                          VALUES ($order_id, $product_id, {$item['quantity']}, {$item['price']})";
            mysqli_query($connection, $item_query);
        }
        
        // Clear cart
        $_SESSION['cart'] = [];
        $_SESSION['order_success'] = "Order placed successfully!";
        header("Location: home.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .user-box {
            position: absolute;
            top: 30px;
            right: 20px;
            text-align: center;
        }
        .user-box img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border: 2px solid #0d6efd;
        }
        .user-box p {
            margin-top: 10px;
            font-weight: bold;
            color: #333;
        }
        body {
            background-image: url('./resources/backgrounds/pexels-conojeghuo-175711.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            min-height: 100vh;
            margin: 0;
        }
        .container-wrapper {
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 15px;
            margin: 20px auto;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <a class="navbar-brand" href="home.php">Coffe Drink</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="home.php">Drinks</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">Orders</a>
                    </li>                   
                </ul>
                <form class="d-flex" role="search">
                    <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                    <button class="btn btn-outline-success" type="submit">Search</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="user-box">
        <img src="uploads/<?= htmlspecialchars($user['profile_image'] ?? 'default.jpg') ?>" alt="User Photo">
        <p><?= htmlspecialchars($user_name) ?></p>
    </div>
    <div class="container p-4">
        
        <div class="container-wrapper p-4">

            <?php if (isset($_SESSION['order_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $_SESSION['order_success'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['order_success']); ?>
            <?php endif; ?>
            <div class="row">
                <!-- Cart Sidebar -->
                <div class="col-md-3">
                    <div class="card sticky-top" style="top: 20px;">
                        <div class="card-header text-white" style="background-color: #944639">
                            <h5 class="mb-0">Your Order</h5>
                        </div>
                        <div class="card-body">
                            <!-- Room Selection -->
                            <form method="POST" class="mb-3">
                                <div class="mb-3">
                                    <label for="room" class="form-label">Room Number</label>
                                    <input type="text" name="room" id="room" class="form-control" 
                                        value="<?= htmlspecialchars($_SESSION['room']) ?>" required>
                                </div>
                                <button type="submit" name="set_room" class="btn btn-sm btn-primary w-100">
                                    Set Room
                                </button>
                            </form>
                            
                            <!-- Cart Items -->
                            <div class="cart-items">
                                <?php if (!empty($_SESSION['cart'])): ?>
                                    <?php foreach ($_SESSION['cart'] as $product_id => $item): ?>
                                        <div class="d-flex align-items-center mb-3 border-bottom pb-2">
                                            <img src="<?= str_replace('../', './', $item['image']) ?>" 
                                                class="rounded me-2" width="60" height="60" style="object-fit: cover;">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                                <div class="d-flex align-items-center">
                                                    <form method="POST" class="d-flex me-2">
                                                        <input type="hidden" name="product_id" value="<?= $product_id ?>">
                                                        <input type="number" name="quantity" 
                                                            value="<?= $item['quantity'] ?>" min="1" 
                                                            class="form-control form-control-sm" style="width: 60px;">
                                                        <button type="submit" name="update_quantity" 
                                                                class="btn btn-sm btn-outline-secondary ms-1">
                                                            <i class="bi bi-arrow-clockwise"></i>
                                                        </button>
                                                    </form>
                                                    <span class="text-muted">$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                                                </div>
                                            </div>
                                            <a href="?remove_from_cart=<?= $product_id ?>" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <!-- Order Summary -->
                                    <div class="border-top pt-2 mt-2">
                                        <div class="d-flex justify-content-between fw-bold">
                                            <span>Total:</span>
                                            <span>$<?= number_format(array_reduce($_SESSION['cart'], function($carry, $item) {
                                                return $carry + ($item['price'] * $item['quantity']);
                                            }, 0), 2) ?></span>
                                        </div>
                                        <form method="POST">
                                            <button type="submit" name="place_order" class="btn btn-success w-100 mt-2"
                                                    <?= empty($_SESSION['room']) ? 'disabled' : '' ?>>
                                                Confirm Order
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center text-muted py-3">
                                        Your cart is empty
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Products Grid -->
                <div class="col-md-9">
                    <!-- Your existing products grid goes here -->
                    <?php if(count($products)>0): ?>
                        <div class="row g-3">
                            <?php foreach($products as $product): ?>
                                <div class="col-12 col-sm-6 col-md-4 col-lg-4 mb-4">
                                    <div class="card h-100 p-1 " style="background-color:rgb(251, 216, 210)">
                                        <img src="<?= str_replace('../', './', $product['image_path']) ?>" 
                                            class="card-img-top object-fit-cover" 
                                            alt="<?= $product['name']?>"
                                            style="height: 180px;"
                                        >
                                        <div class="card-body d-flex flex-column">
                                            <!-- Text Content (flexible) -->
                                            <div class="flex-grow-1">
                                                <h5 class="card-title"><?= $product['name']?></h5>
                                                <p class="card-text text-truncate-3" 
                                                style="display: -webkit-box;
                                                        -webkit-line-clamp: 3;
                                                        -webkit-box-orient: vertical;
                                                        overflow: hidden;">
                                                    <?= $product['description']?>
                                                </p>
                                                <span class="badge text-bg-warning"><?= "$".$product['price']?></span>
                                            </div>
                                           
                                        </div>
                                        <div class="card-footer bg-transparent">
                                            <form method="POST">
                                                <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
                                                <div class="input-group">
                                                    <input type="number" name="quantity" value="1" min="1" class="form-control" style="background-color:rgb(226, 185, 177)">
                                                    <button type="submit" name="add_to_cart" class="btn btn-warning rounded rounded-1" style="background-color: #944639">
                                                        Add to Cart
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                             <!-- Pagination -->
                        <?php if($total_pages > 1): ?>
                            <nav aria-label="Page navigation" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <!-- Previous Button -->
                                    <li class="page-item <?= $current_page == 1 ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $current_page - 1 ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    
                                    <!-- Page Numbers -->
                                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <!-- Next Button -->
                                    <li class="page-item <?= $current_page == $total_pages ? 'disabled' : '' ?>">
                                        <a class="page-link" href="?page=<?= $current_page + 1 ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>


        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</body>
</html>
