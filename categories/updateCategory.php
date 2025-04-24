<?php
    include_once("../Connection.php");
    $id = $_GET['id'];

    // Always load existing categories first
    $get_category = "SELECT * FROM categories WHERE category_id=$id";
    $result = mysqli_query($connection, $get_category);
    $category = mysqli_fetch_assoc($result);
    
    if(isset($_POST['updateBtn'])){
        $categories = [];
        $get_categories = "SELECT * FROM categories";
        $total_result = mysqli_query($connection, $get_categories);
        while($cat = mysqli_fetch_assoc($total_result)) {
            $categories[] = $cat;
        }


        $categoryName = trim($_POST['cName']);
        $categoryExists = false;
        
        // Case-insensitive check
        foreach($categories as $cat) {
            if(strcasecmp($cat['name'], $categoryName) === 0) {
                $categoryExists = true;
                break;
            }
        }
        
        if(!$categoryExists) {
            // Use prepared statement to prevent SQL injection
            $update_query = "UPDATE categories SET name = '$categoryName' WHERE category_id=$id";
            
            if(mysqli_query($connection, $update_query)) {
                header("Location: addCategory.php");
                exit();
            } else {
                $error = "Error adding category";
            }
        } else {
            $error = "Category '$categoryName' already exists!";
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
            background-image: url('../resources/backgrounds/pexels-tyler-nix-1259808-2396220.jpg');
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
    <title>Add Category</title>
</head>
<body>

    <div class="container-wrapper">
        <h1 class="text-center mt-2" style="color:#9b4220;">Categories</h1>

        
        <div class="container rounded-3 shadow p-0  bg-white">
            <div class="w-50 text-start">
                <form method="POST" class=" p-3">
                    <label for="cName" class="form-label"> Category Name</label>
                    <input type="text" class="form-control border border-2" name="cName" id="cName" value='<?php echo $category['name']?>'>
                    <button type="submit" class="btn btn-primary my-2" name="updateBtn">update</button>
                </form>
            </div>
            <?php if(isset($error)): ?>
                <div class="alert alert-warning alert-dismissible fade show mt-3">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        </div>
        
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</body>
</html>

