<?php
session_start();
include('../db_con.php');
include('../nav.php');
include('../cleanup_bookings.php');

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

        // Initialize rental intervals
        $rental_intervals = [];

        // Fetch booked intervals if the car is rented
        if ($car['status'] === 'rented') {
            $sql_booking = "SELECT start_date, end_date FROM booking WHERE plate_No = :plateNo";
            $stmt_booking = $pdo->prepare($sql_booking);
            $stmt_booking->bindParam(':plateNo', $plateNo, PDO::PARAM_STR);
            $stmt_booking->execute();
            $rental_intervals = $stmt_booking->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        echo "<p class='text-danger'>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
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
    <style>
        header {
            margin-bottom: 15px;
        }

        main {
            padding-bottom: 30px;
        }

        .card {
            border: 1px solid #ddd;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .car-image {
            max-height: 350px;
            object-fit: cover;
            width: 100%;
        }

        .car-card {
            border: 1px solid #ddd;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
            border-radius: 10px;
            overflow: hidden;
        }

        .form-section {
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
    </style>
    <script>
        function updatePrice() {
            let startDate = document.getElementById('start-date').value;
            let endDate = document.getElementById('end-date').value;
            let pricePerDay = parseFloat(document.getElementById('price-day').value);
            let today = new Date().toISOString().split('T')[0];

            if (startDate && endDate) {
                let start = new Date(startDate);
                let end = new Date(endDate);

                if (start < new Date(today)) {
                    alert("Start date cannot be in the past.");
                    document.getElementById('start-date').value = '';
                    return;
                }

                if (end <= start) {
                    alert("Return date must be after the pick-up date.");
                    document.getElementById('end-date').value = '';
                    return;
                }

                let days = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
                document.getElementById('rental-days').innerText = days + " days";
                document.getElementById('total-price').innerText = "BD " + (days * pricePerDay).toFixed(2);
            }
        }
    </script>
</head>

<body class="d-flex flex-column min-vh-100">
    <main class="flex-grow-1">
        <div class="container py-5">
            <header class="text-center mb-4">
                <div class="text-center">
                    <h1 class="display-4">Complete Your Booking</h1>
                </div>
            </header>

            <div class="row g-4">
                <div class="col-md-8">
                    <div class="card car-card">
                        <img src="<?php echo htmlspecialchars($car['car_image']) ?>" class="car-image">
                        <div class="card-body">
                            <h2 class="card-title"><?php echo htmlspecialchars($car['model_year']) . ' ' . htmlspecialchars($car['model_name']) ?></h2>
                            <p class="text-muted">Plate No: <?php echo htmlspecialchars($car['plate_No']) ?></p>
                            <p class="card-text">Type: <strong><?php echo htmlspecialchars($car['type']) ?></strong></p>
                            <p class="card-text">Color: <strong><?php echo htmlspecialchars($car['color']) ?></strong></p>
                            <p class="card-text">Transmission: <strong><?php echo htmlspecialchars($car['transmission']) ?></strong></p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-section">
                        <form action="book_car.php" method="POST">
                            <input type="hidden" id="price-day" value="<?php echo htmlspecialchars($car['price_day']) ?>">
                            <input type="hidden" name="plate_No" value="<?php echo htmlspecialchars($car['plate_No']) ?>">

                            <div class="mb-3">
                                <label class="form-label">Pick-up Date</label>
                                <input type="date" id="start-date" name="start-date" class="form-control" required onchange="updatePrice()">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Return Date</label>
                                <input type="date" id="end-date" name="end-date" class="form-control" required onchange="updatePrice()">
                            </div>

                            <?php if ($car['status'] === 'rented' && !empty($rental_intervals)) { ?>
                                <div class="alert alert-warning mt-3">
                                    Car is already rented during the following periods:
                                    <ul>
                                        <?php foreach ($rental_intervals as $interval) { ?>
                                            <li class="card-text"><strong><?php echo htmlspecialchars($interval['start_date']) . " to " . htmlspecialchars($interval['end_date']); ?></strong></li>
                                        <?php } ?>
                                    </ul>
                                    Please choose suitable dates before booking.
                                </div>
                            <?php } ?>

                            <p class="fw-bold">Rental Period: <span id="rental-days">0 days</span></p>
                            <p class="fw-bold">Total: <span id="total-price">BD 0.00</span></p>

                            <button type="submit" class="btn btn-outline-success w-100" onclick="return confirm('Are you sure you want this car?');">Rent Car</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include('../footer.php'); ?>
</body>

</html>