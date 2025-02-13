<?php
session_start();
include('../db_con.php');
include('../nav.php');

// Check if user is logged in
if (!isset($_SESSION['user_email'])) {
    header("Location: ../index.php");
    exit();
}

// Fetch car details based on plate number
if (isset($_GET['id'])) {
    $plateNo = $_GET['id'];

    try {
        $sql = "SELECT * FROM car WHERE plate_No = :plateNo";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':plateNo', $plateNo, PDO::PARAM_STR);
        $stmt->execute();
        $car = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$car) {
            header("Location: browse.php");
            exit();
        }
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
} else {
    header("Location: browse.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental - Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function updatePrice() {
            let startDate = document.getElementById('start-date').value;
            let endDate = document.getElementById('end-date').value;
            let pricePerDay = parseFloat(document.getElementById('price-day').value);

            let today = new Date().toISOString().split('T')[0]; // Get today's date in YYYY-MM-DD format

            if (startDate && endDate) {
                let start = new Date(startDate);
                let end = new Date(endDate);

                // Check if the start date is in the past
                if (start < new Date(today)) {
                    alert("Start date cannot be in the past.");
                    document.getElementById('start-date').value = ''; // Reset the input
                    document.getElementById('rental-days').innerText = "Invalid dates";
                    document.getElementById('total-price').innerText = "BD 0.00";
                    return;
                }

                // Check if the start date is after the end date
                if (end <= start) {
                    alert("Return date must be after the pick-up date.");
                    document.getElementById('end-date').value = ''; // Reset the input
                    document.getElementById('rental-days').innerText = "Invalid dates";
                    document.getElementById('total-price').innerText = "BD 0.00";
                    return;
                }

                // Calculate rental days and total price
                let days = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
                document.getElementById('rental-days').innerText = days + " days";
                document.getElementById('total-price').innerText = "BD " + (days * pricePerDay).toFixed(2);
            }
        }
    </script>
</head>

<body>
    <div class="container py-5">
        <h1 class="text-center">Complete Your Booking</h1>
        <div class="row">
            <div class="col-md-8">
                <img src="<?php echo htmlspecialchars($car['car_image']) ?>" class="img-fluid">
                <h2><?php echo htmlspecialchars($car['model_year']) . ' ' . htmlspecialchars($car['model_name']) ?></h2>
                <p>Plate No: <?php echo htmlspecialchars($car['plate_No']) ?></p>
                <p>Type: <?php echo htmlspecialchars($car['type']) ?></p>
                <p>Color: <?php echo htmlspecialchars($car['color']) ?></p>
                <p>Transmission: <?php echo htmlspecialchars($car['transmission']) ?></p>
            </div>
            <div class="col-md-4">
                <form action="book_car.php" method="POST">
                    <input type="hidden" id="price-day" value="<?php echo htmlspecialchars($car['price_day']) ?>">
                    <input type="hidden" name="plate_No" value="<?php echo htmlspecialchars($car['plate_No']) ?>"> <!-- Add this line -->
                    <div>
                        <label>Pick-up Date</label>
                        <input type="date" id="start-date" name="start-date" class="form-control" required onchange="updatePrice()">
                    </div>
                    <div>
                        <label>Return Date</label>
                        <input type="date" id="end-date" name="end-date" class="form-control" required onchange="updatePrice()">
                    </div>
                    <p>Rental Period: <span id="rental-days">0 days</span></p>
                    <p>Total: <span id="total-price">BD 0.00</span></p>
                    <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want this car?');">Rent Car</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>