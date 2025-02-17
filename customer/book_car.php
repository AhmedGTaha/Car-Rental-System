<?php
session_start();
include('../db_con.php');

// Check if user is logged in
if (!isset($_SESSION['user_email'])) {
    header("Location: ../index.php");
    exit();
}

// Function to redirect with an error message
function redirectWithError($message)
{
    echo "<script>alert('$message'); window.location.href='view_details.php';</script>";
    exit();
}

// Validate form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userEmail = $_SESSION['user_email'];
    $id = $_SESSION['user_id'];
    $plateNo = $_POST['plate_No'];
    $startDate = $_POST['start-date'];
    $endDate = $_POST['end-date'];

    // Validate date selection
    if (strtotime($endDate) <= strtotime($startDate)) {
        redirectWithError("Return date must be after the pick-up date.");
    }

    // Check if car is available for rent
    try {
        // Check if there are any bookings that overlap with the selected dates
        $sql = "SELECT status, end_date FROM booking WHERE plate_No = :plateNo 
                AND (start_date <= :endDate AND end_date >= :startDate)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':plateNo', $plateNo, PDO::PARAM_STR);
        $stmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
        $stmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);
        $stmt->execute();
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        // If there is an overlapping booking, check if the start date is after the last booking's end date
        if ($booking) {
            // If the selected start date is later than the last booking's end date, allow booking
            if (strtotime($startDate) > strtotime($booking['end_date'])) {
                // Proceed with the booking
            } else {
                redirectWithError("Car is already booked for the selected period.");
            }
        }
    } catch (PDOException $e) {
        redirectWithError("Database error: " . $e->getMessage());
    }

    // Fetch car price per day and availability
    try {
        $sql = "SELECT price_day, status FROM car WHERE plate_No = :plateNo";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':plateNo', $plateNo, PDO::PARAM_STR);
        $stmt->execute();
        $car = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$car) {
            redirectWithError("Car not found.");
        }

        // Ignore the car's rented status if booking is not overlapping
        if ($car['status'] === "available" || $car['status'] === "rented") {
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

            // Change booking status to 'confirmed'
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
            $sql = "UPDATE car SET status = 'rented' WHERE plate_No = :plateNo";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':plateNo', $plateNo, PDO::PARAM_STR);
            $stmt->execute();

            header("Location: home.php?success=1");
            exit();
        } else {
            redirectWithError("Car is not available for rent.");
        }
    } catch (PDOException $e) {
        redirectWithError("Database error: " . $e->getMessage());
    }
}
