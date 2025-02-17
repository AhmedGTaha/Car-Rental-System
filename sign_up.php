<?php
session_start();
include('db_con.php'); // Database connection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);
    $role = trim($_POST['role']);
    $errors = [];

    if (!preg_match("/^[a-zA-Z-' ]+$/", $name)) {
        $errors[] = "Invalid name format.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match("/@gmail\.com$/", $email)) {
        $errors[] = "Only Gmail addresses are allowed.";
    }

    if (!preg_match("/^(3|6|9|1|7|8)[0-9]{7}$/", $phone)) {
        $errors[] = "Invalid Bahrain phone number.";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    if ($errors) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old_input'] = $_POST;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    try {
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM User WHERE email = :email");
        $stmt_check->execute(['email' => $email]);
        if ($stmt_check->fetchColumn() > 0) {
            $_SESSION['errors'] = ["Email already exists. Use a different email address."];
            $_SESSION['old_input'] = $_POST;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }

        $stmt = $pdo->prepare("INSERT INTO User (username, email, phone_No, password, role, profile_image) VALUES (:name, :email, :phone, :password, :role, :image)");
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $hashedPassword,
            'role' => $role,
            'image' => "..\pic\user.png"
        ]);

        $_SESSION['registration_success'] = "Account created successfully!";
        header("Location: login.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['errors'] = ["Error: Unable to register. Please try again later."];
        $_SESSION['old_input'] = $_POST;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
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
                                $(document).ready(function() {
                                    $("#email").on("blur", function() {
                                        let email = $(this).val().trim();
                                        let emailField = $("#email");
                                        if (email !== "") {
                                            $.ajax({
                                                type: "POST",
                                                url: "check_email.php",
                                                data: {
                                                    email: email
                                                },
                                                success: function(response) {
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
                        <p class="text-body-secondary" style="text-align: center; margin-top: 10px;">Already have an account? <a href="login.php" class="text-decoration-none" style="color:#006aff;">Login</a></p>
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