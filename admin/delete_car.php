<?php
include('../db_con.php');

// Check if the car ID (plate number) is provided in the URL
if (isset($_GET['id'])) {
    $plateNo = $_GET['id'];

    // Prepare the SQL query to delete the car
    $sql = "DELETE FROM Car WHERE plate_No = :plateNo";
    $stmt = $pdo->prepare($sql);
    
    // Bind the plate number to the SQL statement
    $stmt->bindParam(':plateNo', $plateNo);

    // Execute the query
    if ($stmt->execute()) {
        // Redirect back to the car management page after successful deletion
        header("Location: cars.php");
        exit();
    } else {
        // If something goes wrong, show an error message
        echo "<script>alert('Error: Unable to delete the car.')</script>";
        exit();
    }
} else {
    // If no ID is provided, redirect to the car management page
    header("Location: cars.php");
    exit();
}
?>