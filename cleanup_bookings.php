<?php
include('db_con.php');

try {
    // Get the current date
    $current_date = date('Y-m-d');

    // Find all cars with expired bookings
    $sql = "SELECT DISTINCT plate_No FROM booking WHERE end_date < :current_date";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':current_date', $current_date);
    $stmt->execute();
    $expired_cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Delete expired bookings
    $deleteSql = "DELETE FROM booking WHERE end_date < :current_date";
    $deleteStmt = $pdo->prepare($deleteSql);
    $deleteStmt->bindParam(':current_date', $current_date);
    $deleteStmt->execute();

    // Update car status to 'available' only if there are no active bookings for that car
    if ($expired_cars) {
        foreach ($expired_cars as $car) {
            $plate_No = $car['plate_No'];

            // Check if the car still has active bookings
            $checkSql = "SELECT COUNT(*) FROM booking WHERE plate_No = :plate_No";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->bindParam(':plate_No', $plate_No);
            $checkStmt->execute();
            $activeBookings = $checkStmt->fetchColumn();

            // If no active bookings, update car status to available
            if ($activeBookings == 0) {
                $updateSql = "UPDATE car SET status = 'available' WHERE plate_No = :plate_No";
                $updateStmt = $pdo->prepare($updateSql);
                $updateStmt->bindParam(':plate_No', $plate_No);
                $updateStmt->execute();
            }
        }
    }
} catch (Exception $e) {
    error_log("Error cleaning up expired bookings: " . $e->getMessage());
}
?>