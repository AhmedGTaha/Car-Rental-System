<?php
session_start();
include('db_con.php'); // Database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate email and password
    if ($stmt = $pdo->prepare("SELECT * FROM User WHERE email = :email")) {
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if user exists and verify password
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['ID'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['username'];
            $_SESSION['user_pic'] = $user['profile_image'];

            // Redirect based on user role
            if ($user['role'] == 'admin') {
                header('Location: admin/admin_dashboard.php');
                exit();
            } elseif ($user['role'] == 'customer') {
                header('Location: customer/home.php');
                exit();
            }
        } else {
            $_SESSION['error'] = "Invalid email or password.";
            header('Location: login.php');
            exit();
        }
    } else {
        $_SESSION['error'] = "Database query failed.";
        header('Location: login.php');
        exit();
    }
} else {
    header('Location: login.php');
    exit();
}
?>