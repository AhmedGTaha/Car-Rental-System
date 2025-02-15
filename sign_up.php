<?php
session_start();
include('../db_con.php');
include('../nav.php');
include('../cleanup_bookings.php');

if (isset($_SESSION['user_email'])) {
    $user_email = $_SESSION['user_email'];

    try {
        $sql = "SELECT * FROM user WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $user_email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $_SESSION['errors'] = ["Error fetching user details."];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $errors = [];

        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $password = trim($_POST['password']);
        $profile_image = $_FILES['profile-image']['name'] ? $_FILES['profile-image']['name'] : $user['profile_image'];

        if (!preg_match("/^[a-zA-Z-' ]+$/", $name)) {
            $errors[] = "Invalid name format.";
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match("/@gmail\.com$/", $email)) {
            $errors[] = "Only Gmail addresses are allowed.";
        }

        if (!preg_match("/^(3|6|9|1|7|8)[0-9]{7}$/", $phone)) {
            $errors[] = "Invalid Bahrain phone number.";
        }

        if ($errors) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }

        try {
            // Check for existing email (excluding the current user)
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM user WHERE email = :email AND email != :current_email");
            $stmt_check->execute(['email' => $email, 'current_email' => $user_email]);

            if ($stmt_check->fetchColumn() > 0) {
                $_SESSION['errors'] = ["Email already exists. Use a different email address."];
                $_SESSION['old_input'] = $_POST;
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }

            // Handle active bookings
            $stmt_check_bookings = $pdo->prepare("SELECT COUNT(*) FROM booking WHERE user_email = :user_email");
            $stmt_check_bookings->bindParam(':user_email', $user_email);
            $stmt_check_bookings->execute();
            $active_rentals = $stmt_check_bookings->fetchColumn();

            if ($active_rentals > 0) {
                $update_active_SQL = "UPDATE booking SET user_email = :new_email WHERE user_email = :user_email";
                $stmt_update_active = $pdo->prepare($update_active_SQL);
                $stmt_update_active->bindParam(':new_email', $email);
                $stmt_update_active->bindParam(':user_email', $user_email);
                $stmt_update_active->execute();
            }

            // Handle profile image upload
            if (!empty($_FILES['profile-image']['name'])) {
                $target_dir = "../uploads/";
                $target_file = $target_dir . basename($_FILES['profile-image']['name']);
                move_uploaded_file($_FILES['profile-image']['tmp_name'], $target_file);
            }

            // Update user details
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $update_SQL = "UPDATE user 
                               SET username = :name, email = :email, phone_No = :phone, password = :password, profile_image = :profile_image 
                               WHERE email = :user_email";
                $stmt_update = $pdo->prepare($update_SQL);
                $stmt_update->bindParam(':password', $hashedPassword);
            } else {
                $update_SQL = "UPDATE user 
                               SET username = :name, email = :email, phone_No = :phone, profile_image = :profile_image 
                               WHERE email = :user_email";
                $stmt_update = $pdo->prepare($update_SQL);
            }

            $stmt_update->bindParam(':name', $name);
            $stmt_update->bindParam(':email', $email);
            $stmt_update->bindParam(':phone', $phone);
            $stmt_update->bindParam(':profile_image', $profile_image);
            $stmt_update->bindParam(':user_email', $user_email);
            $stmt_update->execute();

            $_SESSION['success'] = "Profile updated successfully.";
            $_SESSION['user_email'] = $email;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();

        } catch (PDOException $e) {
            $_SESSION['errors'] = ["Error updating profile. Please try again later."];
            $_SESSION['old_input'] = $_POST;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Sign Up Page</title>
    <style>
        .error {
            border: 2px solid #dc3545 !important;
        }
        .error-message {
            color: #dc3545 !important;
        }
    </style>
</head>
<body class="bg-light">
<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-11 col-sm-8 col-md-6 col-lg-5 col-xl-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">

                    <h3 class="card-title mb-3 text-center">Create New Account</h3>
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" id="signupForm">
                    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                    <script>
                        $(document).ready(function () {
                            $("#email").on("blur", function () {
                                let email = $(this).val().trim();
                                let emailField = $("#email");
                                if (email !== "") {
                                    $.ajax({
                                        type: "POST",
                                        url: "check_email.php",
                                        data: { email: email },
                                        success: function (response) {
                                            if (response.trim() === "exists") {
                                                emailField.addClass("error");
                                                alert("Email already exists. Use a different email address.");
                                            } else {
                                                emailField.removeClass("error");
                                            }
                                        }
                                    });
                                }
                            });
                        });
                        </script>

                        <div class="input-group mb-3">
                            <span class="input-group-text">@</span>
                            <div class="form-floating">
                                <input type="text" class="form-control" id="username" placeholder="Username" name="name" required>
                                <label for="username">Username</label>
                            </div>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="email" placeholder="name@example.com" name="email" required>
                            <label for="email">Email address</label>
                        </div>
                        
                        <div class="form-floating mb-3">
                            <input type="number" class="form-control" id="phone" placeholder="17001700" name="phone" required>
                            <label for="phone">Phone Number</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="password" placeholder="Password" name="password" required>
                            <label for="password">Password</label>
                        </div>

                        <div class="form-floating mb-4">
                            <input type="password" class="form-control" id="confirmPassword" placeholder="Password" name="confirmPassword" required>
                            <label for="confirmPassword">Confirm Password</label>
                        </div>

                        <div class="form-floating mb-4">
                            <select class="form-select" id="userRole" name="role">
                                <option selected value="customer">Customer</option>
                                <option value="admin">Admin</option>
                            </select>
                            <label for="userRole">User Role</label>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-outline-dark">Create Account</button>
                        </div>
                    </form>
                    <p class="text-body-secondary" style="text-align: center;">Already have an account? <a href="login.php" class="text-decoration-none" style="color:#006aff;">Login</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    document.getElementById('signupForm').addEventListener('submit', function(event) {
        let valid = true;
        
        function validateField(id, regex) {
            const field = document.getElementById(id);
            if (!regex.test(field.value.trim())) {
                field.classList.add('error');
                valid = false;
            } else {
                field.classList.remove('error');
            }
        }
        
        validateField('username', /^[a-zA-Z-' ]*$/);
        validateField('email', /^[a-zA-Z0-9._%+-]+@gmail\.com$/);
        validateField('phone', /^(3|6|9|1|7|8)[0-9]{7}$/);
        
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirmPassword');
        if (confirmPassword.value !== password) {
            confirmPassword.classList.add('error');
            valid = false;
        } else {
            confirmPassword.classList.remove('error');
        }
        
        if (!valid) {
            event.preventDefault();
        }
    });
</script>
</body>
</html>