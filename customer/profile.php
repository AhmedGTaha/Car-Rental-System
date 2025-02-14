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
    <title>Profile Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <header>
                <div class="row justify-content-center">
                    <div class="col-md-8 text-center">
                        <h1 class="display-4">Your Profile</h1>
                        <p class="lead">Edit and customize your profile!</p>
                        <a href="../logout_process.php" class="btn btn-danger btn-lg mt-3">Log Out</a>
                    </div>
                </div>
            </header>
        </div>
    </main>
    <?php include('../footer.php'); ?>

</body>

</html>