<?php
    session_start();
    if($_SESSION['user_role']!="admin")
    {
        header("Location: ../unauth.php"); 
        exit();
    }
    include_once("../Connection.php");
    $categories=[];
    $products=[];
    
    $user_id = $_SESSION['user_id'];
    $query = "SELECT profile_image FROM users WHERE user_id = '$user_id' LIMIT 1";
    $result = mysqli_query($connection, $query);

    if (!$result) {
        die('Query Error: ' . mysqli_error($connection));
    }

    $user = mysqli_fetch_assoc($result);


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


    if(isset($_GET['delete_id'])){
        $delete_id = $_GET['delete_id'];
        
        // Case-insensitive check
        foreach($products as $product) {
            if($delete_id == $product['product_id']) {
                $delete_query = "UPDATE products SET deleted_at = NOW() WHERE product_id = $delete_id";
                mysqli_query($connection,$delete_query);
                header("Location: products.php");
                exit();
            }else{
                $error = "ID doesn't exist!";
            }
        }
    }


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
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
        .object-fit-cover {
            object-fit: cover;
            width: 100%;
        }
    </style>
    <title>Add Product</title>
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
                                <a class="nav-link" aria-current="page" href="products.php">Products</a>
                            </li>   
                            <li class="nav-item">
                                <a class="nav-link" href="addProduct.php">Add Products</a>
                            </li>  
                            <li class="nav-item">
                                <a class="nav-link" href="deletedProducts.php">Deleted Products</a>
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
    
    <div class="container-wrapper p-4">
        <div class="container">
            <h1 class="text-center mt-2 " style="color:#944639">Products</h1>           

            <div class="row col-lg-11 offset-lg-1 col-md-8 offset-md-2 border border-3 rounded rounded-4 p-4 bg-white custom-border mt-3">    
                <?php if(count($products)>0):?>
                    <div class="container">
                        <div class="row g-3"> <!-- Added gutter spacing -->
                            <?php foreach($products as $product): ?>
                                <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4"> <!-- Responsive columns -->
                                    <div class="card h-100 d-flex flex-column"> <!-- Full height flex container -->
                                        <!-- Product Image (fixed height) -->
                                        <img src="<?= $product['image_path']?>" 
                                            class="card-img-top object-fit-cover" 
                                            alt="<?= $product['name']?>"
                                            style="height: 180px;">
                                        
                                        <!-- Card Content (flexible space) -->
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
                                                <span class="badge bg-warning text-dark"  style="-color:rgb(223, 214, 213)"><?= "$".$product['price']?></span><br>
                                                <span class="badge bg-<?= $product['is_active']? 'success':'danger'?>"  style="background-color:rgb(120, 114, 113)"><?= $product['is_active']? 'Available':'Not Available'?></span>
                                            </div>
                                            
                                            <!-- Button (fixed at bottom) -->
                                            <div class="d-flex gap-1 pt-3">
                                                <a href="./updateProduct.php?id=<?= $product['product_id']?>" class="btn btn-warning w-50">Update</a>
                                                <a href="./products.php?delete_id=<?= $product['product_id']?>" class="btn btn-danger w-50">Delete</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
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
                <?php else: ?>
                    <h2 class='alert alert-info m-3'>No Products Found!<h2>
                <?php endif; ?>
                        
            </div>


        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</body>
</html>