<?php
session_start();
include('../db_con.php');
include('../nav.php');
include('../cleanup_bookings.php');

if (!isset($_SESSION['user_email'])) {
    header("Location: ../index.php");
    exit();
}

function redirectWithError($message)
{
    echo "<script>alert('$message'); window.location.href='edit_booking.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];

    try {
        // Get the booking details
        $sql = "SELECT * FROM booking WHERE booking_id = :booking_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
        $stmt->execute();
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            redirectWithError("Booking not found.");
        }

        // Get car details
        $sql = "SELECT * FROM car WHERE plate_No = :plate_No";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':plate_No', $booking['plate_No'], PDO::PARAM_STR);
        $stmt->execute();
        $car = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$car) {
            redirectWithError("Car details not found.");
        }

        // Initialize rental intervals
        $rental_intervals = [];

        // Fetch booked intervals if the car is rented
        if ($car['status'] === 'rented') {
            $sql_booking = "SELECT start_date, end_date FROM booking WHERE plate_No = :plate_No";
            $stmt_booking = $pdo->prepare($sql_booking);
            $stmt_booking->bindParam(':plate_No', $car['plate_No'], PDO::PARAM_STR);
            $stmt_booking->execute();
            $rental_intervals = $stmt_booking->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        redirectWithError("Error fetching bookings: " . $e->getMessage());
    }
} else {
    redirectWithError("Invalid request.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental Editing</title>
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
                <h1 class="display-4">Reschedule Your Booking</h1>
            </header>

            <div class="row g-4">
                <div class="col-md-8">
                    <div class="card car-card">
                        <img src="<?php echo htmlentities($car['car_image']) ?>" class="car-image">
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
                        <!-- In the form section, modify the form and inputs -->
                        <form action="update_booking.php" method="POST">
                            <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                            <input type="hidden" id="price-day" value="<?php echo htmlspecialchars($car['price_day']) ?>">
                            <input type="hidden" name="plate_No" value="<?php echo htmlspecialchars($car['plate_No']) ?>">

                            <div class="mb-3">
                                <label class="form-label">Pick-up Date</label>
                                <input type="date" id="start-date" name="start-date" class="form-control"
                                    value="<?php echo htmlspecialchars($booking['start_date']); ?>" required onchange="updatePrice()">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Return Date</label>
                                <input type="date" id="end-date" name="end-date" class="form-control"
                                    value="<?php echo htmlspecialchars($booking['end_date']); ?>" required onchange="updatePrice()">
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

                            <!-- Add price display -->
                            <p class="fw-bold">Rental Period: <span id="rental-days">
                                    <?php
                                    $days = ceil((strtotime($booking['end_date']) - strtotime($booking['start_date'])) / (60 * 60 * 24));
                                    echo $days . " days";
                                    ?>
                                </span></p>
                            <p class="fw-bold">Total: <span id="total-price">
                                    BD <?php echo number_format($booking['total_price'], 2); ?>
                                </span></p>

                            <button type="submit" class="btn btn-outline-success w-100"
                                onclick="return confirm('Are you sure you want to update this booking?');">
                                Reschedule
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include('../footer.php'); ?>
</body>

</html>