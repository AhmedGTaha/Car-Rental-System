<?php
session_start();
include('db_con.php'); // Database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    try {
        $stmt = $pdo->prepare("SELECT * FROM user WHERE email = :email");
        if (!$stmt) {
            throw new Exception("Database query preparation failed.");
        }

        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['ID'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['user_pic'] = $user['profile_image'];
            $_SESSION['user_role'] = $user['role'];
            include('../cleanup_bookings.php');

            $redirect_url = ($user['role'] == 'admin') ? 'admin/admin_dashboard.php' : 'customer/home.php';
            header("Location: $redirect_url");
            exit();
        } else {
            $_SESSION['error'] = "Invalid email or password.";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }

    // Redirect if an error occurs
    header('Location: login.php');
    exit();
}

// Redirect if accessed directly
header('Location: login.php');
exit();
?>