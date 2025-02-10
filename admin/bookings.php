<?php
include('../db_con.php');
include('nav_bar.php');
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
            <?php foreach ():?>
                <?php if ():?>
                    <div class="col">
                        <div class="card user-card">
                            <div class="card-body text-center">
                                <h5 class="card-title"></h5>
                                <p class="card-text">Customer: - </p>
                                <p class="card-text">Car: - </p>
                                <p class="card-text">Dates: - </p>
                                <p class="card-text">Fees: </p>
                                <p class="card-text">Status: </p>
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