<?php
include('db_con.php'); 
try {
    // Get the current date
    $current_date = date('y-m-d');

    $sql = "SELECT DISTINCT plate_No FROM booking WHERE end_date < :current_date";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':current_date', $current_date);
    $stmt->execute();
    $expired_cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Delete the expired bookings
    $deleteSQL = "DELETE FROM booking WHERE end_date > :current_date";
    $deleteStmt = $pdo->prepare($deleteSQL);
    $deleteStmt->bindParam(':current_date', $current_date);
    $deleteStmt->execute();

    if ($expired_cars) {
        foreach ($expired_cars as $car) {
            // Update the car status to 'available'
            $updateSQL = "UPDATE Car SET status = 'available' WHERE plate_No = :plate_No";
            $updateStmt = $pdo->prepare($updateSQL);
            $updateStmt->bindParam(':plate_No', $car['plate_No']);
            $updateStmt->execute();
        }
    }
} catch (PDOException $e) {
    echo "Error updating car statuses: ". $e->getMessage();
    exit();
} catch (Exception $e) {
    echo "Error deleting expired bookings: ". $e->getMessage();
    exit();
}
?>