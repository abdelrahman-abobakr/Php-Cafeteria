<?php
    include_once("../Connection.php");
    session_start();
    if($_SESSION['user_role']!="admin")
    {
        header("Location: ../unauth.php"); 
        exit();
    }

    $user_id = $_SESSION["user_id"];
    $query = "SELECT profile_image FROM users WHERE user_id = '$user_id' LIMIT 1";
    $result = mysqli_query($connection, $query);

    if (!$result) {
        die('Query Error: ' . mysqli_error($connection));
    }

    
    $user = mysqli_fetch_assoc($result);
    $categories=[];
    $products=[];

    
    // retrieving all categories
    $categories_query = "SELECT name, category_id FROM categories";
    $result = mysqli_query($connection, $categories_query);
    while($cat = mysqli_fetch_assoc($result)) {
        $categories[] = $cat;
    }

    $products_query = "SELECT * FROM products WHERE deleted_at IS NOT NULL;";
    $result = mysqli_query($connection, $products_query);
    while($prod = mysqli_fetch_assoc($result)) {
        $products[] = $prod;
    }

    if(isset($_GET['delete_id'])){
        $delete_id = $_GET['delete_id'];
        
        // Case-insensitive check
        foreach($products as $product) {
            if($delete_id == $product['product_id']) {
                $delete_query = "DELETE FROM products WHERE product_id = $delete_id";
                mysqli_query($connection,$delete_query);
                header("Location: products.php");
                exit();
            }else{
                $error = "ID doesn't exist!";
            }
        }
    }
    
    if(isset($_GET['restore_id'])){
        $restore_id = $_GET['restore_id'];
        
        // Case-insensitive check
        foreach($products as $product) {
            if($restore_id == $product['product_id']) {
                $restore_query = "UPDATE products SET deleted_at = NULL WHERE product_id = $restore_id";
                mysqli_query($connection,$restore_query);
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
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <a class="navbar-brand" href="../home.php">Coffee Drink</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="../categories/addCategory.php">Categories</a>
                    </li>                   
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="products.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="addProduct.php">Add Product</a>
                    </li>                   
                    <li class="nav-item">
                        <a class="nav-link" href="deletedProducts.php">Deleted Products</a>
                    </li>                   
                </ul>
                <form method="POST" class="d-flex me-3" role="search">
                    <input class="form-control me-2" type="text" placeholder="product" name="searchName">
                    <button class="btn btn-outline-success" name="searchBtn" type="submit">Search</button>
                </form>
                <form method="POST" class="d-flex me-3" role="search">
                    <input class="form-control me-2" type="text" placeholder="category" name="categorySearch">
                    <button class="btn btn-outline-success" name="searchCategoryBtn" type="submit">Search</button>
                </form>
                <div class="user-box d-flex align-items-center">
                    <img src="../resources/uploads/<?= htmlspecialchars($user['profile_image'] ?? 'default.jpg') ?>" 
                        class="rounded-circle border border-secondary" 
                        style="width: 40px; height: 40px; object-fit: cover;" 
                        alt="User Photo">
                    <span class="ms-2 fw-bold"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="container-wrapper p-4">
        <div class="container">
            <h1 class="text-center mt-2 " style="color:#944639">Deleted Products</h1>           

            <div class="row col-lg-11 offset-lg-1 col-md-8 offset-md-2 border border-3 rounded rounded-4 p-4 bg-white custom-border">    
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
                                            </div>
                                            
                                            <!-- Button (fixed at bottom) -->
                                            <div class="d-flex gap-1 pt-3">
                                                <a href="./updateProduct.php?id=<?= $product['product_id']?>" class="btn btn-warning ">Update</a>
                                                <a href="./deletedProducts.php?delete_id=<?= $product['product_id']?>" class="btn btn-danger">Delete</a>
                                                <a href="./deletedProducts.php?restore_id=<?= $product['product_id']?>" class="btn btn-info ">Restore</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
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