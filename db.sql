CREATE DATABASE CarRentalSystem;
USE CarRentalSystem;

-- User Table
CREATE TABLE User (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone_No VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') NOT NULL,
    profile_image VARCHAR(255) DEFAULT '..\pic\user.png' -- Corrected file path
);

-- Car Table
CREATE TABLE Car (
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
CREATE TABLE Booking (
    bookingID INT AUTO_INCREMENT PRIMARY KEY,
    userID INT,
    plate_No VARCHAR(20),
    startDate DATE NOT NULL,
    endDate DATE NOT NULL,
    totalPrice DECIMAL(10,2) CHECK (totalPrice >= 0),
    status ENUM('pending', 'confirmed', 'canceled') DEFAULT 'pending',
    FOREIGN KEY (userID) REFERENCES User(ID) ON DELETE CASCADE,
    FOREIGN KEY (plate_No) REFERENCES Car(plate_No) ON DELETE CASCADE,
    CHECK (startDate < endDate) -- Ensure valid date range
);