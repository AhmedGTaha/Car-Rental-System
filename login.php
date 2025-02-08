<?php
session_start();
if (isset($_SESSION['user_email'])) {
    header("Location: customer/home.php");
    exit();
}

$errorMessage = isset($_SESSION['error']) ? $_SESSION['error'] : null;
unset($_SESSION['error']); // Remove error after storing it
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>Login Page</title>
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
                    <h3 class="card-title mb-3 text-center">Login</h3>
                    <form action="login_process.php" method="post" id="login-form">
                        
                        <?php if ($errorMessage): ?>
                            <p class="error-message"><?php echo $errorMessage; ?></p>
                        <?php endif; ?>
                        
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="email" placeholder="name@example.com" name="email" required>
                            <label for="email">Email address</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="password" placeholder="Password" name="password" required>
                            <label for="password">Password</label>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-outline-dark">Login</button>
                        </div>
                    </form>
                    <p class="text-body-secondary text-center">Don't have an account? <a href="sign_up.php" class="text-decoration-none">Sign Up</a></p>

                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($errorMessage): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function validateField(id) {
            const field = document.getElementById(id);
            if (field) {
                field.classList.add('error');
            }
        }
        validateField('email');
        validateField('password');
    });
</script>
<?php endif; ?>
</body>
</html>