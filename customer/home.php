<?php
session_start();
include('../db_con.php');
include('../nav.php');
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
    <title>Home Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        header {
            margin-bottom: 20px;
        }

        main {
            padding-bottom: 20px;
        }

        .greeting {
            font-weight: bold;
            font-size: 1.5rem;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <main class="flex-grow-1">
        <div class="container mt-5">
            <header>
                <div class="row justify-content-center">
                    <div class="col-md-8 text-center">
                        <h1 class="display-4"><?php echo $greeting . ', ' . htmlspecialchars($_SESSION['user_name']); ?></h1>
                        <p class="lead">Explore our selection of available rental cars and make your booking today!</p>
                        <a href="../logout_process.php" class="btn btn-danger btn-lg mt-3">Log Out</a>
                    </div>
                </div>
            </header>
        </div>
        <?php include('my_bookings.php'); ?>
        <section id="contact" class="mt-5">
            <h3 class="text-center display-4">Need Any Assistance?</h3>
            <p class="text-center">Contact me for any queries or suggestions on <a href="https://github.com/AhmedGTaha">GitHub</a></p>
            </div>
        </section>
    </main>
    <?php include('../footer.php'); ?>
</body>

</html>