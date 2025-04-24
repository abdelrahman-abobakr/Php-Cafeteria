<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">

    <title>Add Product</title>
</head>
<body>

    <h1 class="text-center mt-2 text-primary">Add Product</h1>

    <div class="container">
        <div class="row col-6 offset-3 border border-primary border-3 rounded rounded-4 p-4">


            <form action="./landing.php" method="POST" enctype="multipart/form-data">
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
                    <label for="confirmPass" class="form-label">Confirm Password</label>
                    <input type="password" name="confirmPass" id="confirmPass" class="form-control" required>
                </div>
                <div class="my-3">
                    <label for="city" class="form-label">Room No.</label>
                    <select class="form-select" aria-label="Default select example" name="room" id="room">
                        <option value="application1">Application 1</option>
                        <option value="application2">Application 2</option>
                        <option value="cloud">Cloud</option>
                    </select>
                    
                </div>

                <div class="my-3">
                    <label for="profilePicture" class="form-label"></label>
                    <input type="file" name="pic" id="profilePicture" class="form-control" required>
                </div>
                <button type="reset" class="btn btn-primary">Reset </button>
                <button type="submit" class="btn btn-primary">submit </button>
            </form>

        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</body>
</html>