<?php
session_start();
include('../db_con.php');
include('nav_bar.php');
include('../cleanup_bookings.php');
try {
    // Fetch all confirmed bookings with user and car details in a single query
    $sql = "SELECT * FROM Booking WHERE status = 'confirmed'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<script>alert('Error fetching bookings');</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        header {
            margin-bottom: 15px;
        }
        main {
            padding-bottom: 15px;
        }
        .card {
            border: 1px solid #ddd;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .card:hover {
            border: 1px solid #006aff;
            transform: scale(1.05);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <main class="flex-grow-1">

        <div class="container mt-4">
            <header class="text-center mb-4">
                <h1 class="display-4">Bookings Management</h1>
            </header>
            <div class="row row-cols-1 row-cols-md-3 g-4">
                <?php foreach ($bookings as $booking): ?>
                <div class="col">
                    <div class="card user-card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Booking ID: <?php echo htmlspecialchars($booking['booking_id']); ?>
                            </h5>
                            <p class="card-text">Car: <?php echo htmlspecialchars($booking['plate_No']); ?></p>
                            <p class="card-text">Customer: <?php echo htmlspecialchars($booking['user_email']); ?></p>
                            <p class="card-text">Dates: from <?php echo htmlspecialchars($booking['start_date']); ?>
                                until <?php echo htmlspecialchars($booking['end_date']); ?></p>
                            <p class="card-text">Fees: <?php echo htmlspecialchars($booking['total_price']); ?></p>
                            <p class="card-text">Status: <?php echo htmlspecialchars($booking['status']); ?></p>
                            <a href="delete_booking.php?id=<?php echo $booking['booking_id']; ?>"
                                class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Are you sure you want to cancel?');">
                                Cancel Booking
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
    <?php include('../footer.php'); ?>
</body>

</html>