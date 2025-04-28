<?php
    session_start();

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
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


    $orders=[];
    $products=[];
    
    
    $order_id=$_GET['id'];
    // retrieving all categories
    $orders_query = "SELECT oi.* , p.name FROM order_items oi join products p  on oi.product_id = p.product_id where order_id=$order_id";
    $result = mysqli_query($connection, $orders_query);
    while($order = mysqli_fetch_assoc($result)) {
        $orders[] = $order;
    }

    $products_query = "SELECT * FROM products ";
    $result = mysqli_query($connection, $products_query);
    while($prod = mysqli_fetch_assoc($result)) {
        $products[] = $prod;
    }


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* .user-box {
            position: absolute;
            top: 30px;
            right: 20px;
            text-align: center;
        } */
        .user-box img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            /* border: 2px solid #0d6efd; */
        }
        .user-box p {
            margin-top: 10px;
            font-weight: bold;
            color: #333;
        }
        body {
            background-image: url('./resources/backgrounds/pexels-brigitte-tohm-36757-143640.jpg');
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
            <a class="navbar-brand" href="home.php">Coffee Drink</a>
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
                <form class="d-flex me-3" role="search">
                    <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                    <button class="btn btn-outline-success" type="submit">Search</button>
                </form>
                <div class="user-box d-flex align-items-center">
                    <img src="uploads/<?= htmlspecialchars($user['profile_image'] ?? 'default.jpg') ?>" 
                        class="rounded-circle border border-secondary" 
                        style="width: 40px; height: 40px; object-fit: cover;" 
                        alt="User Photo">
                    <span class="ms-2 fw-bold"><?= htmlspecialchars($user_name) ?></span>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-wrapper p-4">
           

            <div class="row col-lg-11 offset-lg-1 col-md-8 offset-md-2 border border-3 rounded rounded-4 p-4 custom-border" style="background-color: rgba(255, 255, 255, 0.85)">    
            <?php if(count($orders)>0):?>
                <table class="table table-striped table-bordered table-hover text-center m-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Item_ID</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($orders as $order):?>
                            <tr>
                                <td><?= $order['item_id'] ?></a></td>
                                <td><?= $order['name']?> </td>
                                <td><?= $order['quantity']?></td>
                                <td><?= "$".$order['price']?></td>
                            </tr>
                            
                        
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <h2 class='alert alert-info m-3'>No Categories Found!<h2>
            <?php endif; ?>
                        
            </div>


        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</body>
</html>
