<?php
session_start();
include('../db_con.php');
include('../cleanup_bookings.php');

if (!isset($_SESSION['user_email'])) {
    header("Location: ../index.php");
    exit();
}

$user_email = $_SESSION['user_email'];

function redirectWithError($message)
{
    echo "<script>alert('$message'); window.location.href='home.php';</script>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];
    $plateNo = $_POST['plate_No'];
    $startDate = $_POST['start-date'];
    $endDate = $_POST['end-date'];

    // Validate dates
    if (strtotime($endDate) <= strtotime($startDate)) {
        redirectWithError("Return date must be after the pick-up date.");
    }

    // Check if the booking exists and belongs to the user
    try {
        $sql = "SELECT * FROM booking WHERE booking_id = :booking_id AND user_email = :user_email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_email', $user_email, PDO::PARAM_STR);
        $stmt->execute();
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            redirectWithError("Booking not found.");
        }
    } catch (PDOException $e) {
        redirectWithError("Database error: " . $e->getMessage());
    }

    // Check for overlapping bookings
    try {
        // Enhance overlapping bookings check
        $sql = "SELECT * FROM booking 
WHERE plate_No = :plateNo 
AND booking_id != :booking_id
AND status = 'confirmed'
AND (start_date <= :endDate AND end_date >= :startDate)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':plateNo', $plateNo, PDO::PARAM_STR);
        $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
        $stmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
        $stmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);
        $stmt->execute();
        $overlappingBooking = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($overlappingBooking) {
            redirectWithError("Car is already booked for the selected period.");
        }
    } catch (PDOException $e) {
        redirectWithError("Database error: " . $e->getMessage());
    }

    // Get car price per day
    try {
        $sql = "SELECT price_day FROM car WHERE plate_No = :plateNo";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':plateNo', $plateNo, PDO::PARAM_STR);
        $stmt->execute();
        $car = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$car) {
            redirectWithError("Car not found.");
        }

        $pricePerDay = $car['price_day'];
        // Fix the total price calculation
        $days = ceil((strtotime($endDate) - strtotime($startDate)) / (60 * 60 * 24));
        $totalCost = $days * $pricePerDay;
    } catch (PDOException $e) {
        redirectWithError("Database error: " . $e->getMessage());
    }

    // Update the booking
    try {
        // Fix the SQL query to use correct column name
        $sql = "UPDATE booking 
SET start_date = :startDate, 
    end_date = :endDate, 
    total_price = :totalCost 
WHERE booking_id = :booking_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':startDate', $startDate, PDO::PARAM_STR);
        $stmt->bindParam(':endDate', $endDate, PDO::PARAM_STR);
        $stmt->bindParam(':totalCost', $totalCost, PDO::PARAM_INT);
        $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo "<script>alert('Booking rescheduled successfully.'); window.location.href='home.php';</script>";
        } else {
            redirectWithError("Failed to reschedule booking.");
        }
    } catch (PDOException $e) {
        redirectWithError("Database error: " . $e->getMessage());
    }
}
