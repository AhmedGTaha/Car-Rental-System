<?php
session_start();
include('../db_con.php');
include('../nav.php');

// Fetch all types and transmissions and statuses and model years and prices and colors from the database
$types_sql = "SELECT DISTINCT type FROM Car";
$transmissions_sql = "SELECT DISTINCT transmission FROM Car";
$statuses_sql = "SELECT DISTINCT status FROM Car";
$year_sql = "SELECT DISTINCT model_year FROM Car";
$price_sql = "SELECT DISTINCT price_day FROM Car";

$types_stmt = $pdo->prepare($types_sql);
$transmissions_stmt = $pdo->prepare($transmissions_sql);
$statuses_stmt = $pdo->prepare($statuses_sql);
$year_stmt = $pdo->prepare($year_sql);
$price_stmt = $pdo->prepare($price_sql);

$types_stmt->execute();
$transmissions_stmt->execute();
$statuses_stmt->execute();
$year_stmt->execute();
$price_stmt->execute();

$types = $types_stmt->fetchAll(PDO::FETCH_COLUMN);
$transmissions = $transmissions_stmt->fetchAll(PDO::FETCH_COLUMN);
$statuses = $statuses_stmt->fetchAll(PDO::FETCH_COLUMN);
$years = $year_stmt->fetchAll(PDO::FETCH_COLUMN);
$prices = $price_stmt->fetchAll(PDO::FETCH_COLUMN);

// Apply filters to the query
$conditions = array();
$prapms = array();

if (!empty($_GET['type'])) {
    $conditions[] = "type = :type";
    $params['type'] = $_GET['type'];
}
if (!empty($_GET['transmission'])) {
    $conditions[] = "transmission = :transmission";
    $params['transmission'] = $_GET['transmission'];
}
if (!empty($_GET['status'])) {
    $conditions[] = "status = :status";
    $params['status'] = $_GET['status'];
}
if (!empty($_GET['year'])) {
    $conditions[] = "model_year = :year";
    $params['year'] = $_GET['year'];
}
if (!empty($_GET['price'])) {
    $conditions[] = "price_day = :price";
    $params['price'] = $_GET['price'];
}

$sql = "SELECT * FROM Car";
    if ($conditions) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Cars</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <header class="text-center mb-4">
            <h1 class="display-4">Browse Cars</h1>
            <p class="lead">Explore our selection of available rental cars and make your booking today!</p>
        </header>

        <!-- Filter Section -->
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">Type</label>
                <select class="form-select" name="type">
                    <option value="">All</option>
                    <?php foreach ($types as $type): ?>
                        <option value="<?= htmlspecialchars($type) ?>" <?= (isset($_GET['type']) && $_GET['type'] == $type) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucfirst($type)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Transmission</label>
                <select class="form-select" name="transmission">
                    <option value="">All</option>
                    <?php foreach ($transmissions as $transmission): ?>
                        <option value="<?= htmlspecialchars($transmission) ?>" <?= (isset($_GET['transmission']) && $_GET['transmission'] == $transmission) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucfirst($transmission)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">All</option>
                    <?php foreach ($statuses as $status): ?>
                        <option value="<?= htmlspecialchars($status) ?>" <?= (isset($_GET['status']) && $_GET['status'] == $status) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(ucfirst($status)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 align-self-end">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>

        <!-- Car Listings -->
        <div class="row mt-4">
            <!-- Display cars -->
            <?php foreach ($cars as $car): ?>
                <div class="col-md-4 mb-4">
                    <div class="card shadow-sm">
                        <img src="<?php echo htmlspecialchars($car['car_image']); ?>" class="card-img-top" alt="Car Image">
                        <div class="card-body">
                            <h5 class="card-title"> <?php echo htmlspecialchars($car['model_name']); ?> </h5>
                            <p class="card-text">Plate No: <?php echo htmlspecialchars($car['plate_No']) ?></p>
                            <p class="card-text">Year: <?php echo htmlspecialchars($car['model_year'])?></p>
                            <p class="card-text">Type: <?php echo htmlspecialchars($car['type'])?></p>
                            <p class="card-text">Transmission: <?php echo htmlspecialchars($car['transmission'])?></p>
                            <p class="card-text">Price/Day: BD<?php echo htmlspecialchars($car['price_day'])?></p>
                            <p class="card-text">Color: <?php echo htmlspecialchars($car['color'])?></p>
                            <p class="card-text">Status: 
                                <large class="card-text" style="color:<?php echo ($car['status'] === 'rented') ? '#dc3545' : '#198754'; ?>;">
                                    <?php echo htmlspecialchars($car['status']); ?>
                                </large>
                            </p>
                            <a href="edit_car.php?id=<?php echo $car['plate_No']; ?>" 
                               class="btn btn-outline-dark <?php echo ($car['status'] === 'rented') ? 'disabled' : ''; ?>">
                                Edit
                            </a>
                            <a href="delete_car.php?id=<?php echo $car['plate_No']; ?>" 
                               class="btn btn-outline-danger <?php echo ($car['status'] === 'rented') ? 'disabled' : ''; ?>" 
                               onclick="return confirm('Are you sure you want to remove this car?');">
                                Delete
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>