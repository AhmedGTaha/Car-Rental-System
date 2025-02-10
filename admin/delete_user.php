<?php
include ('../db_con.php');

if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$email = $_GET['id'];

try {
    // Get the user profile image from the database
    $sql = "SELECT profile_image FROM User WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header("Location: users.php");
        exit();
    }
    
    $profile_image = $user['profile_image']; // Image path from database

    // Delete the user from the DB
    $sql = "DELETE FROM User WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $email);
    
    if ($stmt->execute()) {
        // Delete the profile image from the server
        if (!empty($profile_image) && file_exists($profile_image) && $profile_image != '..\pic\user.png') {
            unlink($profile_image); // Deletes the file
        }
        header("Location: users.php");
        exit();
    } else {
        throw new Exception("Failed to delete user.");
    }    
} catch (Exception $e) {
    echo "<script>alert('" . $e->getMessage() . "');</script>";
}
?>