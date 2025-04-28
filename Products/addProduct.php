<?php
    session_start();
    if($_SESSION['user_role']!="admin")
    {
        header("Location: ../unauth.php"); 
        exit();
    }
    include_once("../Connection.php");
    
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

    $products_query = "SELECT name FROM products";
    $result = mysqli_query($connection, $products_query);
    while($prod = mysqli_fetch_assoc($result)) {
        $products[] = $prod['name'];
    }

    if(isset($_POST['addBtn'])){

        // form data 
        $product_name = trim($_POST['pName']);
        $description = $_POST['description'];
        $price = $_POST['price'];
        if(!empty($_POST['isActive'])){
            $isActive = 1;
        }else{
            $isActive = 0;
        } 
        $category = $_POST['category'];

        // handling product image upload
        $temp = $_FILES['productImg']['tmp_name'];
        $file_name = $_FILES['productImg']['name'];
        $file_new_path = '../resources/products/'.time().$file_name;

        // for checking the extension of the image
        $ext = explode(".", $_FILES['productImg']['name']);
        $img_ext = strtolower(end($ext));
        $extentions = array('jpg','jpeg', 'png');

        if(empty($product_name) || empty($category) || empty($description || empty($price) )){
            $error = "Please fill All form data";
        }else{
            
            // check for duplicate product names
            if(in_array($product_name, $products)){
                $error = "Product Name Already Exists!";

            }

            // check product image extension
            if(in_array($img_ext, $extentions)===false){
                $error = "Invalid Picture Format";
            }
            
            // make sure that price is more than 0
            if($price < 1){
                $error = "Minimum Price Is $1";
            }

            if(empty($error)){
                
                // uploading product image
                move_uploaded_file($temp, $file_new_path);
                
                $insert_query = "INSERT INTO products (name, description, price, image_path, category_id, is_active) VALUES ('$product_name', '$description', $price, '$file_new_path', $category, $isActive)";
                mysqli_query($connection, $insert_query);

                header("Location: products.php");
                exit();
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
            padding: 20px;
            margin: 20px auto;
        }
        .custom-border {
            border-color: #944639 !important;
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
    <div class="container-wrapper">
        <div class="container">
            <h1 class="text-center mt-2 " style="color:#944639">Add Product</h1>

            <?php if(isset($error)): ?>
                <div class="alert alert-warning alert-dismissible fade show mt-3">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>


            <div class="row col-lg-6 offset-lg-3 col-md-8 offset-md-2 border border-3 rounded rounded-4 p-4 bg-white custom-border">    
                <form method="POST" enctype="multipart/form-data">
                    <div class="my-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" name="pName" id="name" class="form-control" required>
                    </div>
                    <label for="description" class="mb-1">Description</label>
                    <div class="form-floating">
                        <textarea class="form-control" placeholder="Leave a comment here" name="description" id="description" style="height: 100px"></textarea>
                        <label for="description">Description</label>
                    </div>
                    <div class="my-3">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" min="1" name="price" id="price" class="form-control" required>
                    </div>

                    <div class="my-3">
                        <label for="city" class="form-label">Category</label>
                        <select class="form-select" name="category" id="category">
                            <option disabled selected>Select Category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['category_id']?>"><?= $category['name']?></option>
                            <?php endforeach;?>
                        </select>
                        
                    </div>
    
                    <div class="my-3">
                        <label class="form-label" title="if product is available or not">Is Active</label><br>
                        <input type="checkbox" name="isActive" class="btn-check" id="isActive"  value='true' checked>
                        <label class="btn btn-outline-warning" for="isActive">Active</label>
                    </div>
                    <div class="my-3">
                        <label for="productImg" class="form-label">Product Image</label>
                        <input type="file" name="productImg" id="productImg" class="form-control" required>
                    </div>
                    <button type="reset" class="btn btn-info btn-sm mb-2">Reset </button><br>
                    <button type="submit" class="btn btn-info btn-sm" name="addBtn">submit </button>
                </form>
    
            </div>


        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</body>
</html>