<?php
session_start();
include('../db_con.php');

// Check if user is logged in
if (!isset($_SESSION['user_email'])) {
    header("Location: ../index.php");
    exit();
}

// Validate form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userEmail = $_SESSION['user_email'];
    $plateNo = $_POST['plate_No'];
    $startDate = $_POST['start-date'];
    $endDate = $_POST['end-date'];

    // Validate date selection
    if (strtotime($endDate) <= strtotime($startDate)) {
        die("Error: Return date must be after the pick-up date.");
    }

    // Fetch car price per day
    try {
        $sql = "SELECT price_day FROM car WHERE plate_No = :plateNo";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':plateNo', $plateNo, PDO::PARAM_STR);
        $stmt->execute();
        $car = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$car) {
            die("Error: Car not found.");
        }

        $pricePerDay = $car['price_day'];
        $days = (strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24);
        $totalCost = $days * $pricePerDay;

        // Insert booking into the database
        $sql = "INSERT INTO booking (user_email, plate_No, start_date, end_date, total_price) 
                VALUES (:userEmail, :plateNo, :startDate, :endDate, :totalCost)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':userEmail', $userEmail, PDO::PARAM_STR);
        $stmt->bindParam(':plateNo', $plateNo, PDO::PARAM_STR);
        $stmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
        $stmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);
        $stmt->bindParam(':totalCost', $totalCost, PDO::PARAM_STR);
        $stmt->execute();

        header("Location: browse.php?success=1");
        exit();
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
} else {
    header("Location: browse.php");
    exit();
}
?>
