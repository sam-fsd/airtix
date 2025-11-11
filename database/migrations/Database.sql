-- Create Database
CREATE DATABASE IF NOT EXISTS airtix;
USE airtix;

-- Users Table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    date_of_birth DATE,
    profile_photo VARCHAR(255),
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
);

-- Destinations Table
CREATE TABLE destinations (
    destination_id INT PRIMARY KEY AUTO_INCREMENT,
    city VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    airport_code VARCHAR(3) UNIQUE NOT NULL,
    airport_name VARCHAR(200) NOT NULL,
    timezone VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Aircraft Table
CREATE TABLE aircraft (
    aircraft_id INT PRIMARY KEY AUTO_INCREMENT,
    model VARCHAR(100) NOT NULL,
    manufacturer VARCHAR(100),
    total_seats INT NOT NULL,
    economy_seats INT,
    business_seats INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Flights Table
CREATE TABLE flights (
    flight_id INT PRIMARY KEY AUTO_INCREMENT,
    flight_number VARCHAR(10) UNIQUE NOT NULL,
    aircraft_id INT,
    origin_id INT NOT NULL,
    destination_id INT NOT NULL,
    departure_time DATETIME NOT NULL,
    arrival_time DATETIME NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    available_seats INT NOT NULL,
    status ENUM('scheduled', 'boarding', 'departed', 'arrived', 'cancelled') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (aircraft_id) REFERENCES aircraft(aircraft_id),
    FOREIGN KEY (origin_id) REFERENCES destinations(destination_id),
    FOREIGN KEY (destination_id) REFERENCES destinations(destination_id),
    INDEX idx_flight_number (flight_number),
    INDEX idx_departure (departure_time),
    INDEX idx_route (origin_id, destination_id)
);

-- Bookings Table
CREATE TABLE bookings (
    booking_id INT PRIMARY KEY AUTO_INCREMENT,
    booking_reference VARCHAR(20) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    flight_id INT NOT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    booking_status ENUM('confirmed', 'cancelled') DEFAULT 'confirmed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (flight_id) REFERENCES flights(flight_id),
    INDEX idx_reference (booking_reference),
    INDEX idx_user (user_id),
    INDEX idx_flight (flight_id)
);

-- Passengers Table
CREATE TABLE passengers (
    passenger_id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    date_of_birth DATE,
    passport_number VARCHAR(20),
    nationality VARCHAR(50),
    seat_number VARCHAR(5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
    INDEX idx_booking (booking_id)
);

-- Tickets Table
CREATE TABLE tickets (
    ticket_id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT NOT NULL,
    passenger_id INT NOT NULL,
    ticket_number VARCHAR(20) UNIQUE NOT NULL,
    barcode VARCHAR(100),
    issue_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
    FOREIGN KEY (passenger_id) REFERENCES passengers(passenger_id) ON DELETE CASCADE,
    INDEX idx_ticket_number (ticket_number)
);

-- -- Notifications Table
-- CREATE TABLE notifications (
--     notification_id INT PRIMARY KEY AUTO_INCREMENT,
--     user_id INT NOT NULL,
--     title VARCHAR(200) NOT NULL,
--     message TEXT NOT NULL,
--     type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
--     is_read BOOLEAN DEFAULT FALSE,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
--     INDEX idx_user (user_id),
--     INDEX idx_read (is_read)
-- );

-- -- Password Resets Table
-- CREATE TABLE password_resets (
--     reset_id INT PRIMARY KEY AUTO_INCREMENT,
--     user_id INT NOT NULL,
--     token VARCHAR(100) UNIQUE NOT NULL,
--     expires_at DATETIME NOT NULL,
--     used BOOLEAN DEFAULT FALSE,
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
--     INDEX idx_token (token)
-- );

-- -- Audit Log Table (for admin actions)
-- CREATE TABLE audit_log (
--     log_id INT PRIMARY KEY AUTO_INCREMENT,
--     user_id INT,
--     action VARCHAR(100) NOT NULL,
--     table_name VARCHAR(50),
--     record_id INT,
--     description TEXT,
--     ip_address VARCHAR(45),
--     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
--     FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL,
--     INDEX idx_user (user_id),
--     INDEX idx_action (action)
-- );