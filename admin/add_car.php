<?php
session_start();
include('../db_con.php');
include('nav_bar.php');

// Clear previous errors and input on initial load
if (empty($_POST) && isset($_SESSION['errors'])) {
    unset($_SESSION['errors'], $_SESSION['old_input']);
}

$errors = [];
$old_input = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $plate_No = trim($_POST['plate-number']);
    $model_name = trim($_POST['model-name']);
    $year = trim($_POST['model-year']);
    $type = trim($_POST['type']);
    $transmission = trim($_POST['transmission']);
    $price_day = trim($_POST['price']);
    $status = "available";
    $color = trim($_POST['color']);

    // Validate plate number
    if (empty($plate_No)) {
        $errors['plate-number'] = "Plate number is required";
    } elseif (!preg_match('/^[0-9]+$/', $plate_No)) {
        $errors['plate-number'] = "Invalid plate number (numbers only)";
    }

    // Validate model name
    if (empty($model_name)) {
        $errors['model-name'] = "Model name is required";
    } elseif (!preg_match('/^[a-zA-Z0-9 -]+$/', $model_name)) {
        $errors['model-name'] = "Invalid model name (alphanumeric characters and hyphens only)";
    }

    // Validate year
    if (empty($year)) {
        $errors['model-year'] = "Model year is required";
    } elseif (!preg_match('/^(19|20)\d{2}$/', $year)) {
        $errors['model-year'] = "Invalid model year (1900-2099)";
    }

    // Validate price
    if (empty($price_day)) {
        $errors['price'] = "Price is required";
    } elseif ($price_day <= 0) {
        $errors['price'] = "Price must be greater than 0";
    }

    // Validate color
    if (empty($color)) {
        $errors['color'] = "Color is required";
    } elseif (!preg_match('/^[a-zA-Z ]+$/', $color)) {
        $errors['color'] = "Invalid color (letters and spaces only)";
    }

    // Validate image
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $errors['image'] = "Car image is required";
    } else {
        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $errors['image'] = "File size exceeds 5MB limit";
        }

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_mime = mime_content_type($_FILES['image']['tmp_name']);
        if (!in_array($file_mime, $allowed_types)) {
            $errors['image'] = "Invalid file type (only JPG, PNG, GIF allowed)";
        }
    }

    // Check plate number uniqueness
    if (empty($errors)) {
        try {
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM Car WHERE plate_No = :plate_No");
            $stmt_check->bindParam(':plate_No', $plate_No);
            $stmt_check->execute();
            
            if ($stmt_check->fetchColumn() > 0) {
                $errors['plate-number'] = "A car with this plate number already exists";
            }
        } catch (PDOException $e) {
            $errors['database'] = "Database error: " . $e->getMessage();
        }
    }

    if (empty($errors)) {
        try {
            // Process image upload
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $unique_name = uniqid() . '.' . strtolower($file_extension);
            $target_directory = '../uploads/';
            $target_file = $target_directory . $unique_name;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                throw new Exception('Failed to save uploaded file');
            }

            chmod($target_file, 0644);

            // Insert into database
            $sql = "INSERT INTO Car (plate_No, model_name, model_year, type, transmission, price_day, status, color, car_image) 
                    VALUES (:plate_No, :model_name, :model_year, :type, :transmission, :price_day, :status, :color, :car_image)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'plate_No' => $plate_No,
                'model_name' => $model_name,
                'model_year' => $year,
                'type' => $type,
                'transmission' => $transmission,
                'price_day' => $price_day,
                'status' => $status,
                'color' => $color,
                'car_image' => $target_file
            ]);

            header('Location: cars.php');
            exit();
        } catch (Exception $e) {
            $errors['database'] = "Error: " . $e->getMessage();
        }
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Add Car</title>
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
                    <h3 class="mb-3">Add a Car</h3>

                    <?php if (isset($_SESSION['errors']['database'])): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($_SESSION['errors']['database']); ?>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($errors['image'])): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($errors['image']); ?>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($errors['plate-number'])):?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($errors['plate-number']); ?>
                    </div>
                    <?php endif; ?>

                    <form method="post" enctype="multipart/form-data"
                        action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" id="add-car">
                        <div class="mb-3">
                            <label for="plate-number" class="form-label">Plate Number</label>
                            <input type="number"
                                class="form-control <?= isset($_SESSION['errors']['plate-number']) ? 'error-border' : '' ?>"
                                id="plate-number" name="plate-number"
                                value="<?= htmlspecialchars($_SESSION['old_input']['plate-number'] ?? '') ?>" required>
                            <?php if (isset($_SESSION['errors']['plate-number'])): ?>
                            <div class="error-message"><?= $_SESSION['errors']['plate-number'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="model-name" class="form-label">Model Name</label>
                            <input type="text"
                                class="form-control <?= isset($_SESSION['errors']['model-name']) ? 'error-border' : '' ?>"
                                id="model-name" name="model-name"
                                value="<?= htmlspecialchars($_SESSION['old_input']['model-name'] ?? '') ?>" required>
                            <?php if (isset($_SESSION['errors']['model-name'])): ?>
                            <div class="error-message"><?= $_SESSION['errors']['model-name'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="model-year" class="form-label">Model Year</label>
                            <input type="number"
                                class="form-control <?= isset($_SESSION['errors']['model-year']) ? 'error-border' : '' ?>"
                                id="model-year" name="model-year"
                                value="<?= htmlspecialchars($_SESSION['old_input']['model-year'] ?? '') ?>" required>
                            <?php if (isset($_SESSION['errors']['model-year'])): ?>
                            <div class="error-message"><?= $_SESSION['errors']['model-year'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Type</label>
                            <select class="form-select" id="type" name="type" required>
                                <?php $oldType = $_SESSION['old_input']['type'] ?? 'sedan'; ?>
                                <option value="sedan" <?= $oldType === 'sedan' ? 'selected' : '' ?>>Sedan</option>
                                <option value="SUV" <?= $oldType === 'SUV' ? 'selected' : '' ?>>SUV</option>
                                <option value="sport" <?= $oldType === 'sport' ? 'selected' : '' ?>>Sport</option>
                                <option value="pickup" <?= $oldType === 'pickup' ? 'selected' : '' ?>>Pickup</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="transmission" class="form-label">Transmission</label>
                            <select class="form-select" id="transmission" name="transmission" required>
                                <?php $oldTrans = $_SESSION['old_input']['transmission'] ?? 'automatic'; ?>
                                <option value="automatic" <?= $oldTrans === 'automatic' ? 'selected' : '' ?>>Automatic
                                </option>
                                <option value="manual" <?= $oldTrans === 'manual' ? 'selected' : '' ?>>Manual</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="price" class="form-label">Price/Day</label>
                            <input type="number" step="0.01"
                                class="form-control <?= isset($_SESSION['errors']['price']) ? 'error-border' : '' ?>"
                                id="price" name="price"
                                value="<?= htmlspecialchars($_SESSION['old_input']['price'] ?? '') ?>" required>
                            <?php if (isset($_SESSION['errors']['price'])): ?>
                            <div class="error-message"><?= $_SESSION['errors']['price'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="color" class="form-label">Color</label>
                            <input type="text"
                                class="form-control <?= isset($_SESSION['errors']['color']) ? 'error-border' : '' ?>"
                                id="color" name="color"
                                value="<?= htmlspecialchars($_SESSION['old_input']['color'] ?? '') ?>" required>
                            <?php if (isset($_SESSION['errors']['color'])): ?>
                            <div class="error-message"><?= $_SESSION['errors']['color'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Car Image</label>
                            <input type="file"
                                class="form-control <?= isset($_SESSION['errors']['image']) ? 'error-border' : '' ?>"
                                id="image" name="image" required>
                            <?php if (isset($_SESSION['errors']['image'])): ?>
                            <div class="error-message"><?= $_SESSION['errors']['image'] ?></div>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn-outline-dark">Add Car</button>
                        <a href="cars.php" class="btn btn-outline-danger">Cancel</a>
                    </form>
                </div>
            </div>
        </div>

        <script>
            document.getElementById('add-car').addEventListener('submit', function(e) {
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
                    document.getElementById('plate-number'),
                    /^[0-9]+$/,
                    'Invalid plate number (numbers only)'
                );
                validateField(
                    document.getElementById('model-name'),
                    /^[a-zA-Z0-9 -]+$/,
                    'Invalid model name (alphanumeric and hyphens only)'
                );
                validateField(
                    document.getElementById('model-year'),
                    /^(19|20)\d{2}$/,
                    'Invalid model year (1900-2099)'
                );
                validateField(
                    document.getElementById('color'),
                    /^[a-zA-Z ]+$/,
                    'Invalid color (letters and spaces only)'
                );
                const priceField = document.getElementById('price');
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