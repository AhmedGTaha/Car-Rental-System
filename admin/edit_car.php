<?php
session_start();
include('../db_con.php');
include('nav_bar.php');

// Clear previous errors and input on initial load
if (empty($_POST) && isset($_SESSION['errors'])) {
    unset($_SESSION['errors'], $_SESSION['old_input']);
}

if (!isset($_GET['id'])) {
    header("Location: cars.php");
    exit();
}

$plateNo = $_GET['id'];

// Fetch existing car details
$sql = "SELECT * FROM car WHERE plate_No = :plateNo";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':plateNo', $plateNo);
$stmt->execute();
$car = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$car) {
    header("Location: cars.php");
    exit();
}

// Prevent editing if car is rented
if ($car['status'] === 'rented') {
    $_SESSION['errors']['general'] = 'Cannot edit a rented car';
    header("Location: cars.php");
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $model_name = trim($_POST['model-name']);
    $year = trim($_POST['model-year']);
    $type = trim($_POST['type']);
    $transmission = trim($_POST['transmission']);
    $price_day = trim($_POST['price']);
    $color = trim($_POST['color']);
    $car_image = $car['car_image'];

    // Validation
    if (empty($model_name)) {
        $errors['model-name'] = "Model name is required";
    } elseif (!preg_match('/^[a-zA-Z0-9 -]+$/', $model_name)) {
        $errors['model-name'] = "Invalid model name (alphanumeric and hyphens only)";
    }

    if (empty($year)) {
        $errors['model-year'] = "Model year is required";
    } elseif (!preg_match('/^(19|20)\d{2}$/', $year)) {
        $errors['model-year'] = "Invalid model year (1900-2099)";
    }

    if (empty($price_day)) {
        $errors['price'] = "Price is required";
    } elseif ($price_day <= 0) {
        $errors['price'] = "Price must be greater than 0";
    }

    if (empty($color)) {
        $errors['color'] = "Color is required";
    } elseif (!preg_match('/^[a-zA-Z ]+$/', $color)) {
        $errors['color'] = "Invalid color (letters and spaces only)";
    }

    // Image validation if new file uploaded
    if (!empty($_FILES['car_image']['name'])) {
        if ($_FILES['car_image']['error'] !== UPLOAD_ERR_OK) {
            $errors['car_image'] = "File upload failed";
        } else {
            if ($_FILES['car_image']['size'] > 5 * 1024 * 1024) {
                $errors['car_image'] = "File size exceeds 5MB limit";
            }

            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_mime = mime_content_type($_FILES['car_image']['tmp_name']);
            if (!in_array($file_mime, $allowed_types)) {
                $errors['car_image'] = "Invalid file type (only JPG, PNG, GIF allowed)";
            }
        }
    }

    if (empty($errors)) {
        try {
            // Process image upload if valid
            if (!empty($_FILES['car_image']['name']) && empty($errors['car_image'])) {
                $file_extension = pathinfo($_FILES['car_image']['name'], PATHINFO_EXTENSION);
                $unique_name = uniqid() . '.' . strtolower($file_extension);
                $target_directory = '../uploads/';
                $target_file = $target_directory . $unique_name;

                if (move_uploaded_file($_FILES['car_image']['tmp_name'], $target_file)) {
                    chmod($target_file, 0644);
                    $car_image = $target_file;
                } else {
                    throw new Exception("Failed to save uploaded file");
                }
            }

            // Update query
            $sql = "UPDATE car SET 
                    model_name = :model_name, 
                    model_year = :model_year, 
                    type = :type, 
                    transmission = :transmission, 
                    price_day = :price_day, 
                    color = :color, 
                    car_image = :car_image 
                WHERE plate_No = :plateNo";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'model_name' => $model_name,
                'model_year' => $year,
                'type' => $type,
                'transmission' => $transmission,
                'price_day' => $price_day,
                'color' => $color,
                'car_image' => $car_image,
                'plateNo' => $plateNo
            ]);
            
            echo "<script>alert('Car added successfully'); window.location.href='cars.php';</script>";
            exit();
        } catch (Exception $e) {
            $errors['database'] = "Error updating car: " . $e->getMessage();
        }
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['old_input'] = $_POST;
        header("Location: edit_car.php?id=" . $plateNo);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Edit Car</title>
    <style>
        header {
            margin-bottom: 15px;
        }

        main {
            padding-bottom: 15px;
        }

        .error-border {
            border: 2px solid #dc3545 !important;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.875em;
            margin-top: 0.25rem;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <main class="flex-grow-1">

        <div class="container mt-4">
            <header class="text-center mb-4">
                <h1 class="display-4">Car Management</h1>
            </header>
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="mb-3">Edit Car Details</h3>

                    <?php if (isset($_SESSION['errors']['database'])): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($_SESSION['errors']['database']) ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data" action="edit_car.php?id=<?= $plateNo ?>"
                        id="edit-car">
                        <div class="mb-3">
                            <label class="form-label">Model Name</label>
                            <input type="text"
                                class="form-control <?= isset($_SESSION['errors']['model-name']) ? 'error-border' : '' ?>"
                                name="model-name"
                                value="<?= htmlspecialchars($_SESSION['old_input']['model-name'] ?? $car['model_name']) ?>">
                            <?php if (isset($_SESSION['errors']['model-name'])): ?>
                            <div class="error-message"><?= $_SESSION['errors']['model-name'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Model Year</label>
                            <input type="number"
                                class="form-control <?= isset($_SESSION['errors']['model-year']) ? 'error-border' : '' ?>"
                                name="model-year"
                                value="<?= htmlspecialchars($_SESSION['old_input']['model-year'] ?? $car['model_year']) ?>">
                            <?php if (isset($_SESSION['errors']['model-year'])): ?>
                            <div class="error-message"><?= $_SESSION['errors']['model-year'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="type">
                                <?php $currentType = $_SESSION['old_input']['type'] ?? $car['type']; ?>
                                <option value="sedan" <?= $currentType === 'sedan' ? 'selected' : '' ?>>Sedan</option>
                                <option value="SUV" <?= $currentType === 'SUV' ? 'selected' : '' ?>>SUV</option>
                                <option value="sport" <?= $currentType === 'sport' ? 'selected' : '' ?>>Sport</option>
                                <option value="pickup" <?= $currentType === 'pickup' ? 'selected' : '' ?>>Pickup
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Transmission</label>
                            <select class="form-select" name="transmission">
                                <?php $currentTrans = $_SESSION['old_input']['transmission'] ?? $car['transmission']; ?>
                                <option value="automatic" <?= $currentTrans === 'automatic' ? 'selected' : '' ?>>
                                    Automatic</option>
                                <option value="manual" <?= $currentTrans === 'manual' ? 'selected' : '' ?>>Manual
                                </option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Price per Day</label>
                            <input type="number" step="0.01"
                                class="form-control <?= isset($_SESSION['errors']['price']) ? 'error-border' : '' ?>"
                                name="price"
                                value="<?= htmlspecialchars($_SESSION['old_input']['price'] ?? $car['price_day']) ?>">
                            <?php if (isset($_SESSION['errors']['price'])): ?>
                            <div class="error-message"><?= $_SESSION['errors']['price'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Color</label>
                            <input type="text"
                                class="form-control <?= isset($_SESSION['errors']['color']) ? 'error-border' : '' ?>"
                                name="color"
                                value="<?= htmlspecialchars($_SESSION['old_input']['color'] ?? $car['color']) ?>">
                            <?php if (isset($_SESSION['errors']['color'])): ?>
                            <div class="error-message"><?= $_SESSION['errors']['color'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Car Image (Optional)</label>
                            <input type="file"
                                class="form-control <?= isset($_SESSION['errors']['car_image']) ? 'error-border' : '' ?>"
                                name="car_image">
                            <?php if (isset($_SESSION['errors']['car_image'])): ?>
                            <div class="error-message"><?= $_SESSION['errors']['car_image'] ?></div>
                            <?php endif; ?>
                            <?php if (!empty($car['car_image'])): ?>
                            <div class="mt-2">
                                <p>Current Image:</p>
                                <img src="<?= $car['car_image'] ?>" alt="Car Image" width="100" class="img-thumbnail">
                            </div>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn-outline-success">Update Car</button>
                        <a href="cars.php" class="btn btn-outline-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>

        <script>
            document.getElementById('edit-car').addEventListener('submit', function(e) {
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
                    document.querySelector('[name="model-name"]'),
                    /^[a-zA-Z0-9 -]+$/,
                    'Invalid model name (alphanumeric and hyphens only)'
                );
                validateField(
                    document.querySelector('[name="model-year"]'),
                    /^(19|20)\d{2}$/,
                    'Invalid model year (1900-2099)'
                );
                validateField(
                    document.querySelector('[name="color"]'),
                    /^[a-zA-Z ]+$/,
                    'Invalid color (letters and spaces only)'
                );
                const priceField = document.querySelector('[name="price"]');
                if (priceField.value <= 0) {
                    priceField.classList.add('error-border');
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'error-message';
                    errorDiv.textContent = 'Price must be greater than 0';
                    priceField.parentNode.insertBefore(errorDiv, priceField.nextSibling);
                    isValid = false;
                }
                if (!isValid) {
                    e.preventDefault();
                }
            });
        </script>
    </main>
    <?php include('../footer.php'); ?>
</body>

</html>
<?php
// Clear session errors after display
if (isset($_SESSION['errors'])) {
    unset($_SESSION['errors']);
    unset($_SESSION['old_input']);
}
?>