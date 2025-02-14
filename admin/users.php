<?php
session_start();
include ('../db_con.php');
include ('nav_bar.php');
include('../cleanup_bookings.php');

try {
// Fetch all users from the database
$sql ="SELECT * FROM User";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  echo "<script>alert('Error fetching users')<script>";
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>User Management</title>
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
        .user-card img {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 15px;
      }

      .user-card h5 {
        margin-bottom: 10px;
        color: #333;
      }

      .user-card p {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 15px;
      }

      .user-card .btn {
        font-size: 0.85rem;
      }
    </style>
</head>
<body>
    <div class="container mt-4">    
        <h1 class="text-center mb-4">User Management</h1>      
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <!-- Fetch and display user data -->
            <?php foreach ($users as $user):?>
                <?php if ($user['role'] == 'customer'):?>
                    <div class="col">
                        <div class="card user-card">
                            <div class="card-body text-center">
                                <img
                                src="<?php echo htmlspecialchars($user['profile_image']) ?? '..\pic\user.png'; ?>"
                                class="rounded-circle mb-3"
                                alt="User Avatar"
                                />
                                <h5 class="card-title"><?php echo htmlentities($user['username'])?></h5>
                                <p class="card-text">Email: <?php echo htmlspecialchars($user['email'])?></p>
                                <p class="card-text">Phone: <?php echo htmlspecialchars($user['phone_No'])?></p>
                                <a href="delete_user.php?id=<?php echo $user['email'];?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                            </div>
                        </div>
                    </div>
                <?php endif;?>
            <?php endforeach;?>
        </div>
    </div>
</body>
</html>