<?php
    $categories = [];
    include_once("../Connection.php");

    // Always load existing categories first
    $get_categories = "SELECT * FROM categories";
    $result = mysqli_query($connection, $get_categories);
    while($cat = mysqli_fetch_assoc($result)) {
        $categories[] = $cat;
    }
    // if(count($categories)<1){
    //     echo "";
    // }
    if(isset($_POST['categoryBtn']) && isset($_POST['cName'])) {
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
            $stmt = $connection->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->bind_param("s", $categoryName);
            
            if($stmt->execute()) {
                header("Location: addCategory.php");
                exit();
            } else {
                $error = "Error adding category: " . $connection->error;
            }
        } else {
            $error = "Category '$categoryName' already exists!";
        }
    }

    if(isset($_GET['id'])){
        $delete_id = $_GET['id'];
        
        // Case-insensitive check
        foreach($categories as $cat) {
            if($delete_id == $cat['category_id']) {
                $delete_query = "DELETE FROM categories WHERE category_id = $delete_id";
                mysqli_query($connection,$delete_query);
                header("Location: addCategory.php");
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
    <a href="../Products/addProduct.php" class="text-decoration-none">Products</a>

        <h1 class="text-center mt-2" style="color:#9b4220;">Categories</h1>

        
        <button class="btn btn-primary p-2 mt-4 mb-3 w-25 ms-5 " data-bs-toggle="modal" data-bs-target="#addCategory">add</button>
        <div class="container rounded-3 shadow p-0 overflow-hidden bg-white">
            <?php if(count($categories)>0):?>
                <table class="table table-striped table-bordered table-hover text-center m-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Id</th>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            foreach($categories as $category){
                                echo "<tr>";
                                echo "<td>".$category['category_id']."</td>";
                                echo "<td>".$category['name']."</td>";
                                echo "<td>";
                                echo "<a href='updateCategory.php?id=$category[category_id]'><button class='btn btn-sm btn-primary'>Edit</button></a>";
                                echo " | ";
                                echo "<a href='addCategory.php?id=$category[category_id]'><button class='btn btn-sm btn-danger'>Delete</button></a>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        
                        ?>
                    </tbody>
                </table>
            <?php else: ?>
                <h2 class='alert alert-info m-3'>No Categories Found!<h2>
            <?php endif; ?>
            
            <?php if(isset($error)): ?>
                <div class="alert alert-warning alert-dismissible fade show mt-3">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="modal fade" tabindex="-1" id="addCategory">
            <div class="modal-dialog">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">add category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <label for="cName" class="form-label">Category Name</label>
                        <input id="cName" type="text" class="form-control" name="cName"/>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="categoryBtn" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
                </div>
            </div>
        </div>



    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</body>
</html>

