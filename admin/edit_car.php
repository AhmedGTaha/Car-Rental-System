<?php
session_start();
include ('../db_con.php');
include ('nav_bar.php');

if (!isset($_GET['id'])) {
    header("Location: cars.php");
    exit();
}

$plateNo = $_GET['id'];

// Fetch existing car details
$sql = "SELECT * FROM Car WHERE plate_No = :plateNo";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':plateNo', $plateNo);
$stmt->execute();
$car = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$car) {
    header("Location: cars.php");
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $plate_No = $plateNo;
    $model_name = trim($_POST['model-name']);
    $year = trim($_POST['model-year']);
    $type = trim($_POST['type']);
    $transmission = trim($_POST['transmission']);
    $price_day = trim($_POST['price']);
    $status = trim($_POST['status']);
    $color = trim($_POST['color']);
    $car_image = $car['car_image']; // Default to existing image

    try {
        // Check if plate number exists excluding the current car
        $sql_check = "SELECT COUNT(*) FROM Car WHERE plate_No = :plate_No AND plate_No != :current_plate";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->bindParam(':plate_No', $plate_No);
        $stmt_check->bindParam(':current_plate', $plateNo);
        $stmt_check->execute();
        if ($stmt_check->fetchColumn() > 0) {
            throw new Exception("A car with this plate number already exists.");
        }

        // Validation
        if (!preg_match('/^[0-9]+$/', $plate_No)) throw new Exception("Invalid plate number.");
        if (!preg_match('/^[a-zA-Z0-9 -]+$/', $model_name)) throw new Exception("Invalid model name.");
        if (!preg_match('/^(19|20)\d{2}$/', $year)) throw new Exception("Invalid model year.");
        if ($price_day < 0) throw new Exception("Invalid price per day.");
        if (!preg_match('/^[a-zA-Z ]+$/', $color)) throw new Exception("Invalid color.");

        // Image Upload Handling
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            if ($_FILES['image']['size'] > 5 * 1024 * 1024) throw new Exception("File size exceeds 5MB limit.");

            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_mime = mime_content_type($_FILES['image']['tmp_name']);
            if (!in_array($file_mime, $allowed_types)) throw new Exception("Invalid file type.");

            // Generate unique file name
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $unique_name = uniqid() . '.' . strtolower($file_extension);
            $target_directory = '../uploads/';
            $target_file = $target_directory . $unique_name;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                throw new Exception("Failed to save uploaded file.");
            }

            chmod($target_file, 0644);
            $car_image = $target_file; // Update image path
        }

        // Update query
        $sql = "UPDATE Car 
                SET model_name = :model_name, model_year = :model_year, type = :type, 
                    transmission = :transmission, price_day = :price_day, status = :status, 
                    color = :color, car_image = :car_image 
                WHERE plate_No = :plateNo";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':model_name', $model_name);
        $stmt->bindParam(':model_year', $year);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':transmission', $transmission);
        $stmt->bindParam(':price_day', $price_day);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':color', $color);
        $stmt->bindParam(':car_image', $car_image);
        $stmt->bindParam(':plateNo', $plateNo);

        if ($stmt->execute()) {
            header("Location: cars.php");
            exit();
        } else {
            throw new Exception("Error updating car details.");
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
    <title>Edit Car</title>
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
                <h3 class="mb-3">Edit Car Details</h3>
                <form method="POST" enctype="multipart/form-data" action="edit_car.php?id=<?php echo $car['plate_No']; ?>" id="edit-car">
            <div class="mb-3">
                <label for="model_name" class="form-label">Model Name</label>
                <input type="text" class="form-control" id="model-name" name="model-name" value="<?php echo htmlspecialchars($car['model_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="model_year" class="form-label">Model Year</label>
                <input type="number" class="form-control" id="model-year" name="model-year" value="<?php echo htmlspecialchars($car['model_year']); ?>" required>
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
                <label for="price_day" class="form-label">Price per Day</label>
                <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($car['price_day']); ?>" required>
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
                <input type="text" class="form-control" id="color" name="color" value="<?php echo htmlspecialchars($car['color']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="car_image" class="form-label">Car Image</label>
                <input type="text" class="form-control" id="car_image" name="car_image" value="<?php echo htmlspecialchars($car['car_image']); ?>" required disabled>
            </div>
            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to edit this car details?');">Update Car</button>
            <a href="cars.php" class="btn btn-outline-dark">Cancel</a>
        </form>
            </div>
        </div>   
    </div>
    <script>
        document.getElementById('edit-car').addEventListener('submit', function(event) {
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