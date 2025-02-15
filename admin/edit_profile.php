<?php
session_start();
include('../db_con.php');
include('nav_bar.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Edit Profile</title>
    <style>
        .profile-container {
            max-width: 500px;
            margin: auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
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
        <div class="profile-container bg-light p-4">
            <h3 class="text-center">Edit Profile</h3>
            <form>
                <div class="text-center mb-3">
                    <img src="../pic/user.png" alt="Profile Image" class="profile-image" id="profilePreview">
                </div>
                <div class="mb-3">
                    <label class="form-label">Profile Image</label>
                    <input type="file" class="form-control" id="profileImage" accept="image/*">
                </div>
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" value="" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" value="" required disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phone" value="" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" class="form-control" id="password">
                </div>
                <button type="submit" class="btn btn-primary w-100">Save Changes</button>
            </form>
        </div>
    </main>
</body>

</html>