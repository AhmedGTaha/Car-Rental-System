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
    $id = $_SESSION['user_id'];
    $plateNo = $_POST['plate_No'];
    $startDate = $_POST['start-date'];
    $endDate = $_POST['end-date'];

    // Check if car is available for rent
    try {
        $sql = "SELECT status FROM booking WHERE plate_No = :plateNo AND (start_date <= :startDate AND end_date >= :endDate)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':plateNo', $plateNo, PDO::PARAM_STR);
        $stmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
        $stmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);
        $stmt->execute();
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($booking) {
            die("Error: Car is already booked for this period.");
        }
    } catch (PDOException $e) {
        die("Database error: ". $e->getMessage());
    }

    // Validate date selection
    if (strtotime($endDate) <= strtotime($startDate)) {
        die("Error: Return date must be after the pick-up date.");
    }

    // Fetch car price per day
    try {
        $sql = "SELECT price_day, status FROM car WHERE plate_No = :plateNo";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':plateNo', $plateNo, PDO::PARAM_STR);
        $stmt->execute();
        $car = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$car) {
            die("Error: Car not found.");
        }

        if ($car['status'] === "available") {
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

            // change booking status to 'confirmed'
            $sql = "UPDATE booking SET status = 'confirmed' WHERE plate_No = :plateNo AND start_date = :startDate AND end_date = :endDate";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':plateNo', $plateNo, PDO::PARAM_STR);
            $stmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
            $stmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);
            $stmt->execute();

            // Add user id to booking table
            $sql = "UPDATE booking SET user_id = :id WHERE plate_No = :plateNo AND start_date = :startDate AND end_date = :endDate";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':plateNo', $plateNo, PDO::PARAM_STR);
            $stmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
            $stmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);
            $stmt->execute();

            // Update car status to 'rented'
            $sql = "UPDATE car SET status ='rented' WHERE plate_No = :plateNo";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':plateNo', $plateNo, PDO::PARAM_STR);
            $stmt->execute();

            header("Location: browse.php?success=1");

            exit();
        }
        else {
            echo "<script>alert('Car is not available for rent.')</script>";
            header("Location: browse.php");
            exit();
        }
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }
}
?>