<?php
session_start();
include('../db_con.php');
include('../nav.php');
include('../cleanup_bookings.php');

if (isset($_SESSION['user_email'])) {
    $user_email = $_SESSION['user_email'];
    // Fetch the user with the email address
    try {
        $sql = "SELECT * FROM User WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $user_email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "<script>alert('Error fetching user details');</script>";
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
    <title>Edit Profile</title>
    <style>
        header {
            margin-bottom: 15px;
        }

        main {
            padding-bottom: 15px;
        }

        .profile-container {
            max-width: 500px;
            margin: auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 2px 2px 2px 2px rgba(0, 0, 0, 0.1);
        }

        .profile-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
        }
    </style>
</head>

<body class="d-flex flex-column min-vh-100">
    <main class="flex-grow-1 d-flex align-items-center justify-content-center">
        <div class="profile-container p-4">
            <h3 class="text-center">Edit Profile</h3>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" id="edit-profile">
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
                <div class="text-center mb-3">
                    <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Image" class="profile-image" id="profilePreview">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="profile-image">Profile Image</label>
                    <input type="file" class="form-control" id="profile-image" name="profile-image" accept="image/*" value="<?php echo htmlspecialchars($user['profile_image']); ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="username">Username</label>
                    <input type="text" class="form-control" id="username" placeholder="Username" name="name" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" class="form-control" id="email" placeholder="name@example.com" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input type="number" class="form-control" id="phone" placeholder="17001700" name="phone" value="<?php echo htmlspecialchars($user['phone_No']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="password">New Password</label>
                    <input type="password" class="form-control" id="password" placeholder="Password" name="password" required>
                    </div>
                <button type="submit" class="btn btn-outline-primary w-100">Save Changes</button>
            </form>
        </div>
    </main>
</body>

</html>