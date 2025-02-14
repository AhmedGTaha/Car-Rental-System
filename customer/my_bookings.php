<?php
include('../db_con.php');
include('../cleanup_bookings.php');

$user_email = $_SESSION['user_email'] ?? null;

if (!$user_email) {
    echo "<p>You must be logged in to view your bookings.</p>";
    exit();
}

try {
    $sql = "SELECT B.booking_id, B.plate_No, B.start_date, B.end_date, B.total_price, B.status, 
                   C.model_name, C.model_year, C.type, C.transmission, C.color, C.car_image, 
                   U.username, U.email
            FROM Booking B
            JOIN Car C ON B.plate_No = C.plate_No
            JOIN User U ON B.user_id = U.ID
            WHERE U.email = :user_email
            ORDER BY B.start_date DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_email', $user_email);
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "<p>Error fetching bookings.</p>";
    error_log("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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

        .btn-cancel {
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="container mt-4">
    <h2 class="text-center">My Bookings</h2>

    <?php if ($bookings): ?>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($bookings as $booking): ?>
                <div class="col">
                    <div class="card user-card">
                        <img src="<?= htmlspecialchars($booking['car_image']) ?>" class="card-img-top" alt="Car Image">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?= htmlspecialchars($booking['model_name']) ?> (<?= htmlspecialchars($booking['model_year']) ?>)</h5>
                            <p class="card-text">Plate No: <?= htmlspecialchars($booking['plate_No']) ?></p>
                            <p class="card-text">Customer: <?= htmlspecialchars($booking['username']) ?></p>
                            <p class="card-text">Rental: <?= htmlspecialchars($booking['start_date']) ?> to <?= htmlspecialchars($booking['end_date']) ?></p>
                            <p class="card-text">Total: BD<?= number_format($booking['total_price'], 2) ?></p>
                            <p class="card-text">Status: <strong><?= ucfirst(htmlspecialchars($booking['status'])) ?></strong></p>

                            <?php if ($booking['status'] === 'confirmed' && $booking['start_date'] >= date('Y-m-d')): ?>
                                <form action="cancel_booking.php" method="POST">
                                    <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-cancel" 
                                        onclick="return confirm('Are you sure you want to cancel?');">
                                        Cancel Booking
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">Not Cancellable</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-center">No bookings found.</p>
    <?php endif; ?>
</div>
</body>
</html>