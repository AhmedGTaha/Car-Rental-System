<?php
include ('../db_con.php');
include ('nav_bar.php');

// Fetch all cars from the database
$sql = "SELECT * FROM Car";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Car Management</title>
</head>
<body>
    <div class="container mt-4">    
        <h1 class="text-center mb-4">Car Management</h1>
        <h6 class="text-center mb-4">Need to add a car? <a href="add_car.php">Add Car</a></h6>
        <div class="row mt-4">
            <?php foreach ($cars as $car): ?>
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm">
                        <img src="<?php echo htmlspecialchars($car['car_image']); ?>" class="card-img-top" alt="Car Image">
                        <div class="card-body">
                            <h5 class="card-title">Model: <?php echo htmlspecialchars($car['model_name']); ?></h5>
                            <p class="card-text">
                                <strong>Plate No:</strong> <?php echo htmlspecialchars($car['plate_No']); ?><br>
                                <strong>Year:</strong> <?php echo htmlspecialchars($car['model_year']); ?><br>
                                <strong>Type:</strong> <?php echo htmlspecialchars($car['type']); ?><br>
                                <strong>Transmission:</strong> <?php echo htmlspecialchars($car['transmission']); ?><br>
                                <strong>Price/Day:</strong> $<?php echo htmlspecialchars($car['price_day']); ?><br>
                                <strong>Status:</strong> <?php echo htmlspecialchars($car['status']); ?><br>
                                <strong>Color:</strong> <?php echo htmlspecialchars($car['color']); ?>
                            </p>
                            <a href="edit_car.php?id=<?php echo $car['plate_No']; ?>" class="btn btn-primary">Edit</a>
                            <a href="delete_car.php?id=<?php echo $car['plate_No']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to remove this car?');">Remove</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>