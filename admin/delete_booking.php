<?php
include('../db_con.php');

if (!isset($_GET['id'])) {
    header("Location: bookings.php");
    exit();
}

$booking_id = $_GET['id'];

try {
    // Fetch the booking details before deletion
    $sql = "SELECT plate_No FROM Booking WHERE booking_id = :booking_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    $stmt->execute();
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        header("Location: bookings.php");
        exit();
    }

    $plate_No = $booking['plate_No'];

    // Delete the booking
    $sql = "DELETE FROM Booking WHERE booking_id = :booking_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        // Update the car status to 'available'
        $sql = "UPDATE Car SET status = 'available' WHERE plate_No = :plate_No";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':plate_No', $plate_No, PDO::PARAM_STR);
        $stmt->execute();

        header("Location: bookings.php");
        exit();
    } else {
        throw new Exception("Failed to delete booking.");
    }
} catch (Exception $e) {
    echo "<script>alert('" . $e->getMessage() . "'); window.location.href='bookings.php';</script>";
    exit();
}
?>