<?php
session_start();
include('../db_con.php');
include('../nav.php');
include('../cleanup_bookings.php');

// Ensure user is logged in
if (!isset($_SESSION['user_email'])) {
    header('Location: ../login.php');
    exit();
}

// Fetch user details
$user_name = htmlspecialchars($_SESSION['user_name']);
$profile_image = isset($_SESSION['user_pic']) ? $_SESSION['user_pic'] : '../pic/user.png'; // Replace with actual image path logic

// Fetch rented cars count
$user_id = $_SESSION['user_id']; // Ensure user_id is stored in session
$query = "SELECT COUNT(*) AS rented_count FROM booking WHERE user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$rented_count = $row['rented_count'] ?? 0; // Default to 0 if no rentals found

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
            <header>
                <div class="row justify-content-center">
                    <div class="col-md-10 text-center">
                        <h1 class="display-4">Welcome to our Car Rental System</h1>
                    </div>
                </div>
            </header>
            <!-- User Card -->
            <div class="container my-5">
                <div class="profile-card">
                    <img
                        src="<?php echo $_SESSION['user_pic'] ?? 'pic/user.png'; ?>"
                        alt="User Avatar" />
                    <h5><?php echo $greeting . ', ' . $user_name; ?></h5>
                    <p>Email: <?php echo $_SESSION['user_email'] ?> - Your Rented Cars: <?php echo $rented_count?></p>
                    <hr />

                    <div class="d-flex gap-2 justify-content-center mt-3">
                        <a href="edit_profile.php" class="btn btn-outline-primary btn-sm">Edit Profile</a>
                        <a href="../logout_process.php" class="btn btn-outline-danger btn-sm">Logout</a>
                    </div>
                </div>
            </div>
        </div>

        <?php include('my_bookings.php'); ?>

        <section id="contact" class="mt-5">
            <h3 class="text-center display-4">Need Any Assistance?</h3>
            <p class="text-center">Contact me for any queries or suggestions on <a href="https://github.com/AhmedGTaha">GitHub</a></p>
        </section>
    </main>
    <?php include('../footer.php'); ?>
</body>

</html>