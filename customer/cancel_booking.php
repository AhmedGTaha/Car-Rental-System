<?php
session_start();
include('../db_con.php');
include('../nav.php');
include('../cleanup_bookings.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];

    try {
        // Fetch booking details
        $sql = "SELECT plate_No, start_date FROM Booking WHERE booking_id = :booking_id AND status = 'confirmed'";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':booking_id', $booking_id);
        $stmt->execute();
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            throw new Exception("Booking cannot be canceled.");
        }

        // Delete the booking
        $sql = "DELETE FROM Booking WHERE booking_id = :booking_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':booking_id', $booking_id);
        $stmt->execute();

        // Update car status if there are no other bookings
        $plate_No = $booking['plate_No'];
        $sql = "SELECT COUNT(*) FROM Booking WHERE plate_No = :plate_No";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':plate_No', $plate_No);
        $stmt->execute();
        $activeBookings = $stmt->fetchColumn();

        if ($activeBookings == 0) {
            $sql = "UPDATE Car SET status = 'available' WHERE plate_No = :plate_No";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':plate_No', $plate_No);
            $stmt->execute();
        }

        echo "<script>alert('Booking canceled successfully.'); window.location = 'home.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Error: " . $e->getMessage() . "'); window.location = 'home.php';</script>";
    }
}
?>