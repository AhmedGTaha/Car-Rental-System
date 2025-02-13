CREATE DATABASE CarRentalSystem;
USE CarRentalSystem;

-- User Table
CREATE TABLE user (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone_No VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') NOT NULL,
    profile_image VARCHAR(255) DEFAULT '..\pic\user.png' -- Corrected file path
);

-- Car Table
CREATE TABLE car (
    plate_No VARCHAR(20) PRIMARY KEY,
    model_name VARCHAR(50) NOT NULL,
    model_year VARCHAR(20) NOT NULL,
    type ENUM('pickup', 'sedan', 'sport', 'SUV') NOT NULL,
    transmission ENUM('manual', 'automatic') NOT NULL,
    price_day DECIMAL(10,2) NOT NULL CHECK (price_Day >= 0),
    status ENUM('available', 'rented') DEFAULT 'available',
    color VARCHAR(50) NOT NULL,
    car_image VARCHAR(255) DEFAULT '..\pic\car.jpg'
);

-- Booking Table
CREATE TABLE booking (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    user_email VARCHAR(255) NOT NULL,
    plate_No VARCHAR(20),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_price DECIMAL(10,2) CHECK (total_price >= 0),
    status ENUM('confirmed', 'canceled') DEFAULT 'confirmed',
    FOREIGN KEY (user_id) REFERENCES User(ID) ON DELETE CASCADE,
    FOREIGN KEY (user_email) REFERENCES User(email) ON DELETE CASCADE,
    FOREIGN KEY (plate_No) REFERENCES Car(plate_No) ON DELETE CASCADE,
    CHECK (start_date < end_date)
);