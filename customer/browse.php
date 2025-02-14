<?php
session_start();
include('../db_con.php');
include('../nav.php');
include('../cleanup_bookings.php');

// Fetch all types and transmissions and statuses and model years and prices and colors from the database
$types_sql = "SELECT DISTINCT type FROM Car";
$transmissions_sql = "SELECT DISTINCT transmission FROM Car";
$statuses_sql = "SELECT DISTINCT status FROM Car";
$year_sql = "SELECT DISTINCT model_year FROM Car";
$price_sql = "SELECT DISTINCT price_day FROM Car";
$color_sql = "SELECT DISTINCT color FROM Car";

$types_stmt = $pdo->prepare($types_sql);
$transmissions_stmt = $pdo->prepare($transmissions_sql);
$statuses_stmt = $pdo->prepare($statuses_sql);
$year_stmt = $pdo->prepare($year_sql);
$price_stmt = $pdo->prepare($price_sql);
$color_stmt = $pdo->prepare($color_sql);

$types_stmt->execute();
$transmissions_stmt->execute();
$statuses_stmt->execute();
$year_stmt->execute();
$price_stmt->execute();
$color_stmt->execute();

$types = $types_stmt->fetchAll(PDO::FETCH_COLUMN);
$transmissions = $transmissions_stmt->fetchAll(PDO::FETCH_COLUMN);
$statuses = $statuses_stmt->fetchAll(PDO::FETCH_COLUMN);
$years = $year_stmt->fetchAll(PDO::FETCH_COLUMN);
$prices = $price_stmt->fetchAll(PDO::FETCH_COLUMN);
$colors = $color_stmt->fetchAll(PDO::FETCH_COLUMN);

// Apply filters to the query
$conditions = array();
$params = array();

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
if (!empty($_GET['color'])) {
    $conditions[] = "color = :color";
    $params['color'] = $_GET['color'];
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
    <style>
        .card {
            border: 1px solid #ddd;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .card:hover {
            border: 1px solid #006aff;
            transform: scale(1.05);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .filter-container {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <header class="text-center mb-4">
            <h1 class="display-4">Browse Cars</h1>
            <p class="lead">Explore our selection of available rental cars and make your booking today!</p>
        </header>

        <div class="container filter-container mb-4">
            <h3 class="text-center mb-3">Narrow Your Research</h3>
            <form method="GET" class="d-flex flex-wrap gap-3 align-items-end justify-content-center">
                <div class="col-auto">
                    <label class="form-label">Type</label>
                    <select class="form-select" name="type">
                        <option value="">All</option>
                        <?php foreach ($types as $type): ?>
                            <option value="<?= htmlspecialchars($type) ?>"
                                <?= (isset($_GET['type']) && $_GET['type'] == $type) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($type)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label">Transmission</label>
                    <select class="form-select" name="transmission">
                        <option value="">All</option>
                        <?php foreach ($transmissions as $transmission): ?>
                            <option value="<?= htmlspecialchars($transmission) ?>"
                                <?= (isset($_GET['transmission']) && $_GET['transmission'] == $transmission) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($transmission)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">All</option>
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?= htmlspecialchars($status) ?>"
                                <?= (isset($_GET['status']) && $_GET['status'] == $status) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($status)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label">Year</label>
                    <select class="form-select" name="year">
                        <option value="">All</option>
                        <?php foreach ($years as $year): ?>
                            <option value="<?= htmlspecialchars($year) ?>"
                                <?= (isset($_GET['year']) && $_GET['year'] == $year) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($year) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label">Price/Day</label>
                    <select class="form-select" name="price">
                        <option value="">All</option>
                        <?php foreach ($prices as $price): ?>
                            <option value="<?= htmlspecialchars($price) ?>"
                                <?= (isset($_GET['price']) && $_GET['price'] == $price) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($price) ?> BD
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label">Color</label>
                    <select class="form-select" name="color">
                        <option value="">All</option>
                        <?php foreach ($colors as $color): ?>
                            <option value="<?= htmlspecialchars($color) ?>"
                                <?= (isset($_GET['color']) && $_GET['color'] == $color) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(ucfirst($color)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-outline-dark">Filter</button>
                </div>
            </form>

        </div>

        <!-- Car Listings -->
        <div class="row mt-4">
            <!-- Display cars -->
            <?php if ($cars): ?>
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php foreach ($cars as $car): ?>
                        <div class="col">
                            <div class="card user-card">
                                <img src="<?= htmlspecialchars($car['car_image']) ?>" class="card-img-top" alt="Car Image">
                                <div class="card-body text-center">
                                    <h5 class="card-title"><?= htmlspecialchars($car['model_name']) ?> (<?= htmlspecialchars($car['model_year']) ?>)</h5>
                                    <p class="card-text">Plate No: <?= htmlspecialchars($car['plate_No']) ?></p>
                                    <p class="card-text">Type: <?= htmlspecialchars($car['type']) ?></p>
                                    <p class="card-text">Transmission: <?= htmlspecialchars($car['transmission']) ?></p>
                                    <p class="card-text">Price Per Day: BD<?= number_format($car['price_day'], 2) ?></p>
                                    <p class="card-text">Color: <?php echo htmlspecialchars($car['color']) ?></p>
                                    <p class="card-text">Status: <strong><?= ucfirst(htmlspecialchars($car['status'])) ?></strong></p>
                                    <a href="view_details.php?id=<?php echo $car['plate_No']; ?>" class="btn btn-sm btn-outline-dark btn-cancel">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center">No Cars found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>