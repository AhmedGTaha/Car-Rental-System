<?php
include('../db_con.php');
include('nav_bar.php');
// Check if the car ID (plate number) is provided in the URL
if (isset($_GET['id'])) {
    $plateNo = $_GET['id'];

    // Fetch the car details from the database
    $sql = "SELECT * FROM Car WHERE plate_No = :plateNo";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':plateNo', $plateNo);
    $stmt->execute();
    $car = $stmt->fetch(PDO::FETCH_ASSOC);

    // If the car does not exist, redirect to car management page
    if (!$car) {
        header("Location: car_management.php");
        exit();
    }
} else {
    // If no ID is provided, redirect to car management page
    header("Location: car_management.php");
    exit();
}

// Handle form submission for updating the car
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $modelName = $_POST['model_name'];
    $modelYear = $_POST['model_year'];
    $type = $_POST['type'];
    $transmission = $_POST['transmission'];
    $priceDay = $_POST['price_day'];
    $status = $_POST['status'];
    $color = $_POST['color'];
    $carImage = $_POST['car_image']; // Assuming user may not change the image, so keep it as is

    // Update the car details in the database
    $sql = "UPDATE Car SET model_name = :modelName, model_year = :modelYear, type = :type, 
            transmission = :transmission, price_day = :priceDay, status = :status, color = :color, 
            car_image = :carImage WHERE plate_No = :plateNo";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':modelName', $modelName);
    $stmt->bindParam(':modelYear', $modelYear);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':transmission', $transmission);
    $stmt->bindParam(':priceDay', $priceDay);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':color', $color);
    $stmt->bindParam(':carImage', $carImage);
    $stmt->bindParam(':plateNo', $plateNo);

    if ($stmt->execute()) {
        // Redirect to car management page after successful update
        header("Location: car_management.php");
        exit();
    } else {
        echo "Error: Unable to update the car details.";
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
</head>
<body>
    <div class="container mt-4">
        <h1 class="text-center mb-4">Edit Car Details</h1>
        <form method="POST" action="edit_car.php?id=<?php echo $car['plate_No']; ?>">
            <div class="mb-3">
                <label for="model_name" class="form-label">Model Name</label>
                <input type="text" class="form-control" id="model_name" name="model_name" value="<?php echo htmlspecialchars($car['model_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="model_year" class="form-label">Model Year</label>
                <input type="number" class="form-control" id="model_year" name="model_year" value="<?php echo htmlspecialchars($car['model_year']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="type" class="form-label">Type</label>
                <input type="text" class="form-control" id="type" name="type" value="<?php echo htmlspecialchars($car['type']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="transmission" class="form-label">Transmission</label>
                <input type="text" class="form-control" id="transmission" name="transmission" value="<?php echo htmlspecialchars($car['transmission']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="price_day" class="form-label">Price per Day</label>
                <input type="number" step="0.01" class="form-control" id="price_day" name="price_day" value="<?php echo htmlspecialchars($car['price_day']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <input type="text" class="form-control" id="status" name="status" value="<?php echo htmlspecialchars($car['status']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="color" class="form-label">Color</label>
                <input type="text" class="form-control" id="color" name="color" value="<?php echo htmlspecialchars($car['color']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="car_image" class="form-label">Car Image URL</label>
                <input type="text" class="form-control" id="car_image" name="car_image" value="<?php echo htmlspecialchars($car['car_image']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Car</button>
            <a href="cars.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>