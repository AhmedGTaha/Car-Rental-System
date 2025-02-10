<?php
include('../db_con.php');

if (!isset($_GET['id'])) {
    header("Location: cars.php");
    exit();
}

$plateNo = $_GET['id'];

try {
    // Fetch the car image path before deletion
    $sql = "SELECT car_image FROM Car WHERE plate_No = :plateNo";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':plateNo', $plateNo);
    $stmt->execute();
    $car = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$car) {
        header("Location: cars.php");
        exit();
    }

    $car_image = $car['car_image']; // Image path from database

    // Delete the car from the database
    $sql = "DELETE FROM Car WHERE plate_No = :plateNo";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':plateNo', $plateNo);
    
    if ($stmt->execute()) {
        // Delete the image file from the uploads directory
        if (!empty($car_image) && file_exists($car_image) && $car_image != '..\pic\car.jpg') {
            unlink($car_image); // Deletes the file
        }
        header("Location: cars.php");
        exit();
    } else {
        throw new Exception("Failed to delete car.");
    }
} catch (Exception $e) {
    echo "<script>alert('" . $e->getMessage() . "');</script>";
}
?>