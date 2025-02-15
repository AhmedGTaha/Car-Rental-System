<?php
session_start();
include('../db_con.php');
include('../cleanup_bookings.php');

if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

$user_email = $_SESSION['user_email'];

// Initialize errors and old input in session
$_SESSION['errors'] = $_SESSION['errors'] ?? [];
$_SESSION['old_input'] = $_SESSION['old_input'] ?? [];

try {
    $sql = "SELECT * FROM user WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $user_email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['errors']['database'] = "Error fetching user details: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = filter_var(trim($_POST['name']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $phone = filter_var(trim($_POST['phone']), FILTER_SANITIZE_STRING);
    $password = trim($_POST['password']);
    $profile_image = $user['profile_image'];

    // Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['errors']['email'] = "Invalid email format";
    } elseif (!preg_match("/@gmail\.com$/", $email)) {
        $_SESSION['errors']['email'] = "Only Gmail addresses allowed";
    }

    if (!preg_match("/^(3|6|9|1|7|8)[0-9]{7}$/", $phone)) {
        $_SESSION['errors']['phone'] = "Invalid Bahrain phone number";
    }

    // Check email existence
    if ($email !== $user_email) {
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM user WHERE email = :email");
        $stmt_check->execute(['email' => $email]);
        if ($stmt_check->fetchColumn() > 0) {
            $_SESSION['errors']['email'] = "Email already exists";
        }
    }

    // Handle file upload
    if (!empty($_FILES['profile-image']['name'])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($_FILES['profile-image']['type'], $allowed_types)) {
            $_SESSION['errors']['profile-image'] = "Invalid image type (only JPG, PNG, GIF allowed)";
        } elseif ($_FILES['profile-image']['size'] > $max_size) {
            $_SESSION['errors']['profile-image'] = "Image too large (max 2MB)";
        } else {
            $target_dir = "../uploads/";
            $target_file = $target_dir . uniqid() . '_' . basename($_FILES['profile-image']['name']);

            if (!move_uploaded_file($_FILES['profile-image']['tmp_name'], $target_file)) {
                $_SESSION['errors']['profile-image'] = "Failed to upload image";
            } else {
                $profile_image = $target_file;
            }
        }
    }

    if (empty($_SESSION['errors'])) {
        try {
            $pdo->beginTransaction();
            $update_data = [
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':profile_image' => $profile_image,
                ':user_email' => $user_email
            ];

            $update_SQL = "UPDATE user SET 
                username = :name, 
                email = :email, 
                phone_No = :phone, 
                profile_image = :profile_image";

            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $update_SQL .= ", password = :password";
                $update_data[':password'] = $hashedPassword;
            }

            $update_SQL .= " WHERE email = :user_email";
            $stmt_update = $pdo->prepare($update_SQL);
            $stmt_update->execute($update_data);

            $pdo->commit();
            $_SESSION['success'] = "Profile updated successfully";

            //update session variables
            $_SESSION['user_name'] = $name;
            $_SESSION['user_phone'] = $phone;
            $_SESSION['user_profile_image'] = $profile_image;
            $_SESSION['user_email'] = $email;
            header("Location: ". $_SERVER['PHP_SELF']);
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $_SESSION['errors']['database'] = "Error updating profile: " . $e->getMessage();
        }
    }

    $_SESSION['old_input'] = $_POST;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Edit Profile</title>
    <style>
        header {
            margin-bottom: 15px;
        }

        main {
            margin-top: 20px;
            padding-bottom: 20px;
        }

        .error-border {
            border: 2px solid #dc3545 !important;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.875em;
            margin-top: 0.25rem;
        }

        .profile-container {
            max-width: 500px;
            width: 100%;
        }

        #profilePreview {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <main class="flex-grow-1 d-flex align-items-center justify-content-center">
        <div class="container">
            <div class="row justify-content-center align-items-center min-vh-100">
                <div class="col-11 col-sm-8 col-md-6 col-lg-5 col-xl-4">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <header class="text-center mb-4">
                                <h1 class="display-4">Edit Profile</h1>
                            </header>
                            <?php if (isset($_SESSION['errors']['database'])): ?>
                                <div class="alert alert-danger">
                                    <?= htmlspecialchars($_SESSION['errors']['database']) ?>
                                </div>
                            <?php endif; ?>
                            <form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="post" enctype="multipart/form-data" id="edit-profile">
                                <div class="text-center mb-3">
                                    <img src="<?= htmlspecialchars($user['profile_image']) ?>" alt="Profile Image" class="profile-image" id="profilePreview">
                                </div>

                                <?php if (isset($_SESSION['success'])): ?>
                                    <div class="alert alert-success" style="margin-top: 10px;">
                                        <?= htmlspecialchars($_SESSION['success']) ?>
                                    </div>
                                <?php unset($_SESSION['success']);
                                endif; ?>

                                <div class="mb-3">
                                    <label class="form-label">Profile Image</label>
                                    <input type="file" class="form-control <?= isset($_SESSION['errors']['profile-image']) ? 'error-border' : '' ?>"
                                        name="profile-image" accept="image/*">
                                    <?php if (isset($_SESSION['errors']['profile-image'])): ?>
                                        <div class="error-message"><?= $_SESSION['errors']['profile-image'] ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Username</label>
                                    <input type="text" class="form-control <?= isset($_SESSION['errors']['name']) ? 'error-border' : '' ?>"
                                        name="name" value="<?= htmlspecialchars($_SESSION['old_input']['name'] ?? $user['username']) ?>" required>
                                    <?php if (isset($_SESSION['errors']['name'])): ?>
                                        <div class="error-message"><?= $_SESSION['errors']['name'] ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control <?= isset($_SESSION['errors']['email']) ? 'error-border' : '' ?>"
                                        id="email" name="email" value="<?= htmlspecialchars($_SESSION['old_input']['email'] ?? $user['email']) ?>" required>
                                    <?php if (isset($_SESSION['errors']['email'])): ?>
                                        <div class="error-message"><?= $_SESSION['errors']['email'] ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" class="form-control <?= isset($_SESSION['errors']['phone']) ? 'error-border' : '' ?>"
                                        name="phone" value="<?= htmlspecialchars($_SESSION['old_input']['phone'] ?? $user['phone_No']) ?>" required>
                                    <?php if (isset($_SESSION['errors']['phone'])): ?>
                                        <div class="error-message"><?= $_SESSION['errors']['phone'] ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">New Password (Optional)</label>
                                    <input type="password" class="form-control <?= isset($_SESSION['errors']['password']) ? 'error-border' : '' ?>"
                                        name="password">
                                    <?php if (isset($_SESSION['errors']['password'])): ?>
                                        <div class="error-message"><?= $_SESSION['errors']['password'] ?></div>
                                    <?php endif; ?>
                                </div>

                                <button type="submit" class="btn btn-outline-dark w-100" onclick="return confirm('Are you sure you want to update your information?');">Save Changes</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.getElementById('edit-profile').addEventListener('submit', function(e) {
            let isValid = true;

            function validateField(field, regex, errorMessage) {
                const errorDiv = field.nextElementSibling;
                if (!regex.test(field.value.trim())) {
                    field.classList.add('error-border');
                    if (!errorDiv || !errorDiv.classList.contains('error-message')) {
                        const newError = document.createElement('div');
                        newError.className = 'error-message';
                        newError.textContent = errorMessage;
                        field.parentNode.insertBefore(newError, field.nextSibling);
                    } else {
                        errorDiv.textContent = errorMessage;
                    }
                    isValid = false;
                } else {
                    field.classList.remove('error-border');
                    if (errorDiv && errorDiv.classList.contains('error-message')) {
                        errorDiv.remove();
                    }
                }
            }

            // Clear previous errors
            document.querySelectorAll('.error-border').forEach(el => el.classList.remove('error-border'));
            document.querySelectorAll('.error-message').forEach(el => el.remove());

            // Validate fields
            validateField(
                document.querySelector('[name="email"]'),
                /^[a-zA-Z0-9._%+-]+@gmail\.com$/,
                'Invalid Gmail address'
            );

            validateField(
                document.querySelector('[name="phone"]'),
                /^(3|6|9|1|7|8)[0-9]{7}$/,
                'Invalid Bahrain phone number'
            );

            if (!isValid) {
                e.preventDefault();
            }
        });

        // Image preview
        document.querySelector('[name="profile-image"]').addEventListener('change', function(e) {
            const [file] = e.target.files;
            if (file) {
                document.getElementById('profilePreview').src = URL.createObjectURL(file);
            }
        });
    </script>
    <?php include('../footer.php'); ?>

</body>

</html>
<?php
// Clear session errors after display
unset($_SESSION['errors']);
unset($_SESSION['old_input']);
?>