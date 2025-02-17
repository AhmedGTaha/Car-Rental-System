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

$admin_name = htmlspecialchars($_SESSION['user_name']);
$admin_image = htmlspecialchars($_SESSION['user_pic']);


// Get current hour for greeting
$hour = date('H');
if ($hour >= 5 && $hour < 12) {
    $greeting = 'Good Morning';
} elseif ($hour >= 12 && $hour < 17) {
    $greeting = 'Good Afternoon';
} else {
    $greeting = 'Good Evening';
}

$users_SQL = "SELECT count(email) FROM user WHERE role = 'customer'";
$users_stmt = $pdo->prepare($users_SQL);
$users_stmt->execute();
$total_users = $users_stmt->fetchColumn();

$rentals_SQL = "SELECT count(user_id) FROM booking";
$rentals_stmt = $pdo->prepare($rentals_SQL);
$rentals_stmt->execute();
$total_rentals = $rentals_stmt->fetchColumn();

$cars_SQL = "SELECT count(plate_No) FROM car";
$cars_stmt = $pdo->prepare($cars_SQL);
$cars_stmt->execute();
$total_cars = $cars_stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        header {
            margin-bottom: 20px;
        }

        main {
            padding-bottom: 20px;
        }

        .user-card {
            border: 1px solid #ddd;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
            border-radius: 10px;
            padding: 15px;
        }

        .user-card:hover {
            border: 1px solid #006aff;
            transform: scale(1.05);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .greeting {
            font-weight: bold;
            font-size: 1.5rem;
        }

        .profile-card {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .profile-card img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
        }

        .profile-card h5 {
            margin-bottom: 10px;
            color: #333;
        }

        .profile-card p {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 15px;
        }

        .profile-card .btn {
            font-size: 0.85rem;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <main class="flex-grow-1">
        <div class="container mt-5">
            <header class="text-center mb-4">
                <div class="text-center">
                    <h1 class="display-4">Welcome to our Car Rental System</h1>
                </div>
                <p class="lead">Manage all orations through the dashboard!</p>
            </header>
            <!-- User Card -->
            <div class="container my-5">
                <div class="profile-card">
                    <img
                        src="<?php echo $admin_image ?? 'pic/user.png'; ?>"
                        alt="User Avatar" />
                    <h5><?php echo $greeting . ', ' . $admin_name; ?></h5>
                    <p>Email: <?php echo $_SESSION['user_email'] ?></p>
                    <hr />
                    <div class="d-flex gap-2 justify-content-center mt-3">
                        <a href="edit_profile.php" class="btn btn-outline-primary btn-sm">Edit Profile</a>
                        <a href="../logout_process.php" class="btn btn-outline-danger btn-sm">Logout</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Numbers Section -->
        <div class="container my-5">
            <div class="row justify-content-center" style="padding:10px;">
                <div class="col-md-4">
                    <div class="card user-card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Total Customers: <?php echo htmlspecialchars($total_users); ?></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card user-card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Total Cars: <?php echo htmlspecialchars($total_cars); ?></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card user-card text-center">
                        <div class="card-body">
                            <h5 class="card-title">Active Rentals: <?php echo htmlspecialchars($total_rentals); ?></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section id="contact" class="mt-5">
            <h3 class="text-center display-4">Need Any Assistance?</h3>
            <p class="text-center">Contact me for any queries or suggestions on <a href="https://github.com/AhmedGTaha">GitHub</a></p>
        </section>
    </main>

    <?php include('../footer.php'); ?>
</body>

</html>