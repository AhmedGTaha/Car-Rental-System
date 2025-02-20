<?php
include('../db_con.php');

if (!isset($_GET['id'])) {
    header("Location: cars.php");
    exit();
}

$plateNo = $_GET['id'];

try {
    // Fetch car details before deletion
    $sql = "SELECT car_image, status FROM Car WHERE plate_No = :plateNo";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':plateNo', $plateNo);
    $stmt->execute();
    $car = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$car) {
        header("Location: cars.php");
        exit();
    }

    // Prevent deletion if the car is rented
    if ($car['status'] === "rented") {
        echo "<script>alert('Cannot delete a rented car.'); window.location.href='cars.php';</script>";
        exit();
    }

    $car_image = $car['car_image']; // Get image path

    // Delete the car from the database
    $sql = "DELETE FROM Car WHERE plate_No = :plateNo";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':plateNo', $plateNo);

    if ($stmt->execute()) {
        if (!empty($car_image) && file_exists($car_image) && $car_image !== '../pic/car.jpg') {
            unlink($car_image);
        }
        echo "<script>alert('Car deleted successfully'); window.location.href='cars.php';</script>";
        exit();
    } else {
        throw new Exception("Failed to delete car.");
    }
} catch (Exception $e) {
    echo "<script>alert('" . $e->getMessage() . "'); window.location.href='cars.php';</script>";
    exit();
}
?>