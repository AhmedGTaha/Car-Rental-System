<?php
include('db_con.php');

if (isset($_POST['email'])) {
    $email = trim($_POST['email']);

    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM user WHERE email = :email");
    $stmt_check->execute(['email' => $email]);

    if ($stmt_check->fetchColumn() > 0) {
        echo "exists";
    } else {
        echo "available";
    }
}
?>