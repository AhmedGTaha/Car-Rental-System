<?php
session_start();
include('../db_con.php');
include('nav_bar.php');

try {
    // Fetch all bookings from the database
    $sql = "SELECT * FROM Booking";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<script>alert('Error fetching bookings')<script>";
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
<body>
    <div class="container mt-4">    
        <h1 class="text-center mb-4">Bookings Management</h1>      
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <!-- Fetch and display bookings data -->
            <?php foreach ($bookings as $booking):?>
                <?php if ($booking['status'] == 'confirmed'):?>
                    <div class="col">
                        <div class="card user-card">
                            <div class="card-body text-center">
                                <h5 class="card-title"><?php echo htmlspecialchars($booking['booking_id'])?></h5>
                                <p class="card-text">Car: <?php echo htmlspecialchars($booking['plate_No'])?></p>
                                <p class="card-text">Customer: <?php echo htmlspecialchars($booking['user_id'])?></p>
                                <p class="card-text">Dates: <?php echo htmlspecialchars($booking['start_date'])?> - <?php echo htmlspecialchars($booking['end_date'])?></p>
                                <p class="card-text">Fees: <?php echo htmlspecialchars($booking['total_price'])?></p>
                                <p class="card-text">Status: <?php echo htmlspecialchars($booking['status'])?></p>
                                <a href="delete_booking.php?id=" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to cancel?');">Cancel Booking</a>
                            </div>
                        </div>
                    </div>
                <?php endif;?>
            <?php endforeach;?>
        </div>
    </div>
</body>
</html>