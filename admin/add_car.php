<?php
include ('../db_con.php');
include ('nav_bar.php');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $plate_No = trim($_POST['plate-number']);
    $model_name = trim($_POST['model-name']);
    $year = trim($_POST['model-year']);
    $type = trim($_POST['type']);
    $transmission = trim($_POST['transmission']);
    $price_day = trim($_POST['price']);
    $status = trim($_POST['status']);
    $color = trim($_POST['color']);

    try {
        // Check if plate number already exists
        $sql_check = "SELECT COUNT(*) FROM Car WHERE plate_No = :plate_No";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->bindParam(':plate_No', $plate_No);
        $stmt_check->execute();
        $plate_exists = $stmt_check->fetchColumn();
        if ($stmt_check->fetchColumn() > 0) {
            throw new Exception("A car with this plate number already exists.");
        }

        // Validation
        if (!preg_match('/^[0-9]+$/', $plate_No)) throw new Exception("Invalid plate number.");
        if (!preg_match('/^[a-zA-Z0-9 -]+$/', $model_name)) throw new Exception("Invalid model name.");
        if (!preg_match('/^(19|20)\d{2}$/', $year)) throw new Exception("Invalid model year.");
        if ($price_day < 0) throw new Exception("Invalid price per day.");
        if (!preg_match('/^[a-zA-Z ]+$/', $color)) throw new Exception("Invalid color.");

        // Image validation
        if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload failed';
        } else {
            if ($_FILES['image']['size'] > 5 * 1024 * 1024) throw new Exception("File size exceeds 5MB limit.");

            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_mime = mime_content_type($_FILES['image']['tmp_name']);
            if (!in_array($file_mime, $allowed_types)) throw new Exception("Invalid file type.");

            // Sanitize file name and set upload directory
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_extension = strtolower($file_extension);
            $unique_name = uniqid() . '.' . $file_extension;
            $target_directory = '../uploads/';
            $target_file = $target_directory . $unique_name;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                throw new Exception('Failed to save uploaded file');
            }

            chmod($target_file, 0644);

            // Insert into database
            $sql = "INSERT INTO Car (plate_No, model_name, model_year, type, transmission, price_day, status, color, car_image) 
                    VALUES (:plate_No, :model_name, :model_year, :type, :transmission, :price_day, :status, :color, :car_image)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':plate_No', $plate_No);
            $stmt->bindParam(':model_name', $model_name);
            $stmt->bindParam(':model_year', $year);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':transmission', $transmission);
            $stmt->bindParam(':price_day', $price_day);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':color', $color);
            $stmt->bindParam(':car_image', $target_file);

            if ($stmt->execute()) {
                header('Location: cars.php');
                exit();
            } else {
                throw new Exception("Failed to add car. Please try again.");
            }
        }
    } catch (Exception $e) {
        echo "<script>alert('" . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Add Car</title>
    <style>
        .error {
            border: 2px solid #dc3545 !important;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="text-center mb-4">Car Management</h1>
        <div class="card shadow-sm">
            <div class="card-body p-4">    
                <h3 class="mb-3">Add a Car</h3>
                <form method="post" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="add-car">
                <div class="mb-3">
                        <label for="plate-number" class="form-label">Plate Number</label>
                        <input type="number" class="form-control" id="plate-number" name="plate-number" placeholder="123123" required>
                    </div>
                    <div class="mb-3">
                        <label for="model-name" class="form-label">Model Name</label>
                        <input type="text" class="form-control" id="model-name" name="model-name" placeholder="xxxx" required>
                    </div>
                    <div class="mb-3">
                        <label for="model-year" class="form-label">Model Year</label>
                        <input type="number" class="form-control" id="model-year" name="model-year" placeholder="2025" required>
                    </div>
                    <div class="mb-3">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-select" id="type" name="type" required>
                            <option value="sedan" selected>Sedan</option>
                            <option value="SUV">SUV</option>
                            <option value="sport">Sport</option>
                            <option value="pickup">Pickup</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="transmission" class="form-label">Transmission</label>
                        <select class="form-select" id="transmission" name="transmission" required>
                            <option value="automatic" selected>Automatic</option>
                            <option value="manual">Manual</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price/Day</label>
                        <input type="number" step="0.01" class="form-control" id="price" name="price" placeholder="10" required>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="available" selected>Available</option>
                            <option value="rented">Rented</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="color" class="form-label">Color</label>
                        <input type="text" class="form-control" id="color" name="color" placeholder="Black" required>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Car Image</label>
                        <input type="file" class="form-control" id="image" name="image" required>
                    </div>
                    <button type="submit" class="btn btn-outline-dark">Add Car</button>
                    <a href="cars.php" class="btn btn-outline-danger">Cancel</a>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        document.getElementById('add-car').addEventListener('submit', function(event) {
            let valid = true;
            
            function validateField(id, regex) {
                const field = document.getElementById(id);
                if (!regex.test(field.value.trim())) {
                    field.classList.add('error');
                    valid = false;
                } else {
                    field.classList.remove('error');
                }
            }
            
            validateField('plate-number', /^[0-9]+$/);
            validateField('model-name', /^[a-zA-Z0-9 -]+$/);
            validateField('model-year', /^(19|20)\d{2}$/);
            validateField('color', /^[a-zA-Z ]+$/);

            const price = document.getElementById('price');
            if (price.value <= 0) {
                alert('Price must be greater than 0.');
                price.classList.add('error');
                valid = false;
            } else {
                price.classList.remove('error');
            }

            if (!valid) {
                event.preventDefault();
            }
        });
    </script>
</body>
</html>