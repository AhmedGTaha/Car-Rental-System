<?php
session_start();
include('../db_con.php');
include('nav_bar.php');
include('../cleanup_bookings.php');

// Ensure user is logged in
if (!isset($_SESSION['user_name'])) {
    header('Location: ../login.php');
    exit();
}

// Get current hour for greeting
$hour = date('H');
if ($hour >= 5 && $hour < 12) {
    $greeting = 'Good Morning';
} elseif ($hour >= 12 && $hour < 17) {
    $greeting = 'Good Afternoon';
} else {
    $greeting = 'Good Evening';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        header {
            margin-bottom: 15px;
        }

        main {
            padding-bottom: 15px;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <main class="flex-grow-1">
        <div class="container mt-5">
            <header class="text-center mb-4">
                <h1 class="display-4"><?php echo htmlspecialchars($greeting)?>, <?php echo htmlspecialchars($_SESSION['user_name']);?>
                    <a href="../logout_process.php" class="lead">Log Out</a></h1>
                <p class="lead">Manage all orations through the dashboard!</p>
            </header>
        </div>
    </main>

    <?php include('../footer.php'); ?>
</body>

</html>