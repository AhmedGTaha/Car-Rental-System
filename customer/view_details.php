<?php
    session_start();
    include('../db_con.php');
    include('../nav.php');

    // Check if the user is logged in and has a valid session
    if (!isset($_SESSION['user_email'])) {
        header("Location: ../index.php");
        exit();
    }
    
    // Get the car details based on the plate number
    if (isset($_GET['id'])) {
        $plateNo = $_GET['id'];
        $sql = "SELECT * FROM Car WHERE plate_No = :plateNo";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':plateNo', $plateNo);
        $stmt->execute();
        $car = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        header("Location: browse.php");
        exit();
    }


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rental - Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
        }

        body {
            background-color: white;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        .car-image {
            height: 300px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .booking-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
        }

        .price-badge {
            font-size: 1.1rem;
            padding: 0.5rem 1rem;
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
            border-color: var(--primary-color);
        }

        .feature-list i {
            color: var(--primary-color);
            margin-right: 0.5rem;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="text-center mb-5">
            <h1 class="display-5 fw-bold text-dark mb-3">Complete Your Booking</h1>
            <p class="lead text-muted">Review your selection and finalize reservation</p>
        </div>

        <div class="row g-5">
            <!-- Car Details Section -->
            <div class="col-lg-8">
                <div class="booking-card p-4 mb-4">
                    <div class="d-flex align-items-center mb-4">
                        <img src="../pic/car.jpg" alt="2023 Tesla Model S" class="car-image w-100">
                    </div>

                    <h2 class="mb-3 fw-bold"><?php echo htmlspecialchars($car['model_year']) ?>
                        <?php echo htmlspecialchars($car['model_name'])?></h2>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="feature-list">
                                <p class="mb-2"><i class="fas fa-bolt"></i> 3.1s 0-60 mph</p>
                                <p class="mb-2"><i class="fas fa-bolt"></i> 3.1s 0-60 mph</p>
                                <p class="mb-2"><i class="fas fa-chair"></i> Leather interior</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-list">
                                <p class="mb-2"><i class="fas fa-sliders-h"></i> Automatic transmission</p>
                                <p class="mb-2"><i class="fas fa-snowflake"></i> Climate control</p>
                                <p class="mb-2"><i class="fas fa-shield-alt"></i> Premium safety features</p>
                            </div>
                        </div>
                    </div>

                    <form class="needs-validation" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="start-date" class="form-label fw-medium">Pick-up Date</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="date" class="form-control" id="start-date" required>
                                </div>
                                <div class="invalid-feedback">Please select a valid pick-up date</div>
                            </div>

                            <div class="col-md-6">
                                <label for="end-date" class="form-label fw-medium">Return Date</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="date" class="form-control" id="end-date" required>
                                </div>
                                <div class="invalid-feedback">Please select a valid return date</div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Booking Summary -->
            <div class="col-lg-4">
                <div class="booking-card p-4 sticky-top" style="top: 20px;">
                    <h4 class="mb-4 fw-semibold text-dark">Booking Summary</h4>

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Daily Rate</span>
                        <span class="fw-medium">$120/day</span>
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Rental Period</span>
                        <span class="fw-medium">3 days</span>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-medium">$360.00</span>
                    </div>

                    <div class="d-flex justify-content-between mb-4">
                        <span class="text-muted">Taxes & Fees</span>
                        <span class="fw-medium">$45.00</span>
                    </div>

                    <div class="d-flex justify-content-between fw-bold fs-5 mb-4">
                        <span>Total</span>
                        <span class="text-primary">$405.00</span>
                    </div>

                    <button class="btn btn-primary w-100 py-3 fw-semibold">
                        <i class="fas fa-lock me-2"></i>Secure Checkout
                    </button>

                    <p class="text-center text-muted mt-3 small">
                        <i class="fas fa-shield-alt me-2"></i>Secure SSL encryption
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Form validation and date handling
        (function() {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
            // Date validation
            const startDate = document.getElementById('start-date')
            const endDate = document.getElementById('end-date')
            startDate.min = new Date().toISOString().split('T')[0]
            startDate.addEventListener('change', () => {
                endDate.min = startDate.value
                endDate.disabled = false
            })
        })()
    </script>
</body>

</html>