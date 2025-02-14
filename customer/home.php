<?php
session_start();
include('../db_con.php');
include('../nav.php');
include('../cleanup_bookings.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <header>
            <div class="row justify-content-center">
                <div class="col-md-8 text-center">
                    <h1 class="display-4">Welcome, <?php echo htmlspecialchars( $_SESSION['user_name']);?></h1>
                    <p class="lead">Explore our selection of available rental cars and make your booking today!</p>
                    <a href="../logout_process.php" class="btn btn-danger btn-lg mt-3">Log Out</a>
                </div>
            </div>
        </header>
    </div>
    <?php include('my_bookings.php');?>
</body>
</html>