-- ============================================================
-- AIRTIX DUMMY DATA
-- Complete realistic flight data for Kenyan & East African routes
-- ============================================================

USE airtix;

-- Clear existing data (be careful in production!)
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE passengers;
TRUNCATE TABLE tickets;
TRUNCATE TABLE bookings;
TRUNCATE TABLE flights;
TRUNCATE TABLE aircraft;
TRUNCATE TABLE destinations;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

-- ==================== USERS ====================

-- Admin user (password: admin123)
INSERT INTO users (email, password, first_name, last_name, phone, is_admin, created_at) VALUES
('admin@airtix.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
 'System', 'Administrator', '+254700000000', TRUE, NOW());

-- Regular users (password for all: user123)
INSERT INTO users (email, password, first_name, last_name, phone, date_of_birth, created_at) VALUES
('james.kamau@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
 'James', 'Kamau', '+254712345678', '1985-03-15', NOW()),
 
('mary.wanjiku@yahoo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
 'Mary', 'Wanjiku', '+254723456789', '1990-07-22', NOW()),
 
('david.omondi@outlook.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
 'David', 'Omondi', '+254734567890', '1988-11-10', NOW()),
 
('grace.akinyi@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
 'Grace', 'Akinyi', '+254745678901', '1992-05-18', NOW()),
 
('peter.mwangi@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
 'Peter', 'Mwangi', '+254756789012', '1987-09-25', NOW());

-- ==================== DESTINATIONS ====================

INSERT INTO destinations (city, country, airport_code, airport_name, timezone) VALUES
-- Kenya
('Nairobi', 'Kenya', 'NBO', 'Jomo Kenyatta International Airport', 'Africa/Nairobi'),
('Mombasa', 'Kenya', 'MBA', 'Moi International Airport', 'Africa/Nairobi'),
('Kisumu', 'Kenya', 'KIS', 'Kisumu International Airport', 'Africa/Nairobi'),
('Eldoret', 'Kenya', 'EDL', 'Eldoret International Airport', 'Africa/Nairobi'),
('Malindi', 'Kenya', 'MYD', 'Malindi Airport', 'Africa/Nairobi'),
('Lamu', 'Kenya', 'LAU', 'Manda Airport', 'Africa/Nairobi'),

-- Tanzania
('Dar es Salaam', 'Tanzania', 'DAR', 'Julius Nyerere International Airport', 'Africa/Dar_es_Salaam'),
('Zanzibar', 'Tanzania', 'ZNZ', 'Abeid Amani Karume International Airport', 'Africa/Dar_es_Salaam'),
('Kilimanjaro', 'Tanzania', 'JRO', 'Kilimanjaro International Airport', 'Africa/Dar_es_Salaam'),

-- Uganda
('Entebbe', 'Uganda', 'EBB', 'Entebbe International Airport', 'Africa/Kampala'),

-- Rwanda
('Kigali', 'Rwanda', 'KGL', 'Kigali International Airport', 'Africa/Kigali'),

-- Ethiopia
('Addis Ababa', 'Ethiopia', 'ADD', 'Addis Ababa Bole International Airport', 'Africa/Addis_Ababa'),

-- South Africa
('Johannesburg', 'South Africa', 'JNB', 'O.R. Tambo International Airport', 'Africa/Johannesburg'),

-- UAE
('Dubai', 'UAE', 'DXB', 'Dubai International Airport', 'Asia/Dubai');

-- ==================== AIRCRAFT ====================

INSERT INTO aircraft (model, manufacturer, total_seats, economy_seats, business_seats) VALUES
('Boeing 737-800', 'Boeing', 189, 165, 24),
('Airbus A320', 'Airbus', 180, 156, 24),
('Embraer E190', 'Embraer', 100, 88, 12),
('Boeing 787 Dreamliner', 'Boeing', 242, 216, 26),
('Bombardier Dash 8 Q400', 'Bombardier', 78, 78, 0),
('Airbus A330-300', 'Airbus', 277, 251, 26);

-- ==================== FLIGHTS ====================
-- Creating realistic flight schedules for the next 7 days
-- Flight patterns: Morning (6-11), Afternoon (12-17), Evening (18-23)

-- DAY 0 (TODAY)
-- Nairobi to Mombasa (Popular domestic route)
INSERT INTO flights (flight_number, aircraft_id, origin_id, destination_id, departure_time, arrival_time, price, available_seats, status) VALUES
('AT101', 1, 1, 2, DATE_ADD(NOW(), INTERVAL 0 DAY) + INTERVAL 6 HOUR, DATE_ADD(NOW(), INTERVAL 0 DAY) + INTERVAL 7 HOUR, 8500.00, 150, 'boarding'),
('AT102', 2, 1, 2, DATE_ADD(NOW(), INTERVAL 0 DAY) + INTERVAL 10 HOUR, DATE_ADD(NOW(), INTERVAL 0 DAY) + INTERVAL 11 HOUR, 8500.00, 140, 'scheduled'),
('AT103', 1, 1, 2, DATE_ADD(NOW(), INTERVAL 0 DAY) + INTERVAL 14 HOUR, DATE_ADD(NOW(), INTERVAL 0 DAY) + INTERVAL 15 HOUR, 9000.00, 165, 'scheduled'),
('AT104', 2, 1, 2, DATE_ADD(NOW(), INTERVAL 0 DAY) + INTERVAL 18 HOUR, DATE_ADD(NOW(), INTERVAL 0 DAY) + INTERVAL 19 HOUR, 9500.00, 156, 'scheduled'),

-- Mombasa to Nairobi (Return flights)
('AT201', 1, 2, 1, DATE_ADD(NOW(), INTERVAL 0 DAY) + INTERVAL 8 HOUR, DATE_ADD(NOW(), INTERVAL 0 DAY) + INTERVAL 9 HOUR, 8500.00, 145, 'scheduled'),
('AT202', 2, 2, 1, DATE_ADD(NOW(), INTERVAL 0 DAY) + INTERVAL 12 HOUR, DATE_ADD(NOW(), INTERVAL 0 DAY) + INTERVAL 13 HOUR, 8500.00, 138, 'scheduled'),
('AT203', 1, 2, 1, DATE_ADD(NOW(), INTERVAL 0 DAY) + INTERVAL 16 HOUR, DATE_ADD(NOW(), INTERVAL 0 DAY) + INTERVAL 17 HOUR, 9000.00, 160, 'scheduled'),

-- Nairobi to Kisumu
('AT301', 3, 1, 3, DATE_ADD(NOW(), INTERVAL 0 DAY) + INTERVAL 7 HOUR, DATE_ADD(NOW(), INTERVAL 0 DAY) + INTERVAL 8 HOUR, 6500.00, 80, 'scheduled'),
('AT302', 3, 1, 3, DATE_ADD(NOW(), INTERVAL 0 DAY) + INTERVAL 15 HOUR, DATE_ADD(NOW(), INTERVAL 0 DAY) + INTERVAL 16 HOUR, 7000.00, 88, 'scheduled'),

-- Kisumu to Nairobi
('AT401', 3, 3, 1, DATE_ADD(NOW(), INTERVAL 0 DAY) + INTERVAL 9 HOUR, DATE_ADD(NOW(), INTERVAL 0 DAY) + INTERVAL 10 HOUR, 6500.00, 85, 'scheduled'),
('AT402', 3, 3, 1, DATE_ADD(NOW(), INTERVAL 0 DAY) + INTERVAL 17 HOUR, DATE_ADD(NOW(), INTERVAL 0 DAY) + INTERVAL 18 HOUR, 7000.00, 88, 'scheduled');

-- DAY 1 (TOMORROW)
INSERT INTO flights (flight_number, aircraft_id, origin_id, destination_id, departure_time, arrival_time, price, available_seats, status) VALUES
-- Nairobi to Mombasa
('AT105', 1, 1, 2, DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 6 HOUR, DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 7 HOUR, 8500.00, 165, 'scheduled'),
('AT106', 2, 1, 2, DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 10 HOUR, DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 11 HOUR, 8500.00, 156, 'scheduled'),
('AT107', 1, 1, 2, DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 14 HOUR, DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 15 HOUR, 9000.00, 165, 'scheduled'),
('AT108', 2, 1, 2, DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 18 HOUR, DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 19 HOUR, 9500.00, 156, 'scheduled'),

-- Nairobi to Eldoret
('AT501', 5, 1, 4, DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 8 HOUR, DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 9 HOUR, 5500.00, 78, 'scheduled'),
('AT502', 5, 1, 4, DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 16 HOUR, DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 17 HOUR, 6000.00, 78, 'scheduled'),

-- Eldoret to Nairobi
('AT601', 5, 4, 1, DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 10 HOUR, DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 11 HOUR, 5500.00, 78, 'scheduled'),
('AT602', 5, 4, 1, DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 18 HOUR, DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 19 HOUR, 6000.00, 78, 'scheduled'),

-- Nairobi to Dar es Salaam (International)
('AT701', 4, 1, 7, DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 9 HOUR, DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 10.5 HOUR, 15000.00, 220, 'scheduled'),
('AT702', 4, 1, 7, DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 15 HOUR, DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 16.5 HOUR, 16000.00, 216, 'scheduled'),

-- Dar es Salaam to Nairobi
('AT801', 4, 7, 1, DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 11 HOUR, DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 12.5 HOUR, 15000.00, 215, 'scheduled'),
('AT802', 4, 7, 1, DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 17 HOUR, DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 18.5 HOUR, 16000.00, 216, 'scheduled');

-- DAY 2
INSERT INTO flights (flight_number, aircraft_id, origin_id, destination_id, departure_time, arrival_time, price, available_seats, status) VALUES
-- Nairobi to Entebbe
('AT901', 2, 1, 10, DATE_ADD(NOW(), INTERVAL 2 DAY) + INTERVAL 7 HOUR, DATE_ADD(NOW(), INTERVAL 2 DAY) + INTERVAL 8.5 HOUR, 12000.00, 156, 'scheduled'),
('AT902', 2, 1, 10, DATE_ADD(NOW(), INTERVAL 2 DAY) + INTERVAL 14 HOUR, DATE_ADD(NOW(), INTERVAL 2 DAY) + INTERVAL 15.5 HOUR, 13000.00, 156, 'scheduled'),

-- Entebbe to Nairobi
('AT1001', 2, 10, 1, DATE_ADD(NOW(), INTERVAL 2 DAY) + INTERVAL 9 HOUR, DATE_ADD(NOW(), INTERVAL 2 DAY) + INTERVAL 10.5 HOUR, 12000.00, 150, 'scheduled'),
('AT1002', 2, 10, 1, DATE_ADD(NOW(), INTERVAL 2 DAY) + INTERVAL 16 HOUR, DATE_ADD(NOW(), INTERVAL 2 DAY) + INTERVAL 17.5 HOUR, 13000.00, 156, 'scheduled'),

-- Nairobi to Kigali
('AT1101', 3, 1, 11, DATE_ADD(NOW(), INTERVAL 2 DAY) + INTERVAL 8 HOUR, DATE_ADD(NOW(), INTERVAL 2 DAY) + INTERVAL 9.5 HOUR, 11000.00, 88, 'scheduled'),
('AT1102', 3, 1, 11, DATE_ADD(NOW(), INTERVAL 2 DAY) + INTERVAL 17 HOUR, DATE_ADD(NOW(), INTERVAL 2 DAY) + INTERVAL 18.5 HOUR, 12000.00, 88, 'scheduled'),

-- Kigali to Nairobi
('AT1201', 3, 11, 1, DATE_ADD(NOW(), INTERVAL 2 DAY) + INTERVAL 10 HOUR, DATE_ADD(NOW(), INTERVAL 2 DAY) + INTERVAL 11.5 HOUR, 11000.00, 85, 'scheduled'),
('AT1202', 3, 11, 1, DATE_ADD(NOW(), INTERVAL 2 DAY) + INTERVAL 19 HOUR, DATE_ADD(NOW(), INTERVAL 2 DAY) + INTERVAL 20.5 HOUR, 12000.00, 88, 'scheduled'),

-- Mombasa to Zanzibar (Coastal connection)
('AT1301', 3, 2, 8, DATE_ADD(NOW(), INTERVAL 2 DAY) + INTERVAL 11 HOUR, DATE_ADD(NOW(), INTERVAL 2 DAY) + INTERVAL 12.5 HOUR, 10000.00, 88, 'scheduled'),

-- Zanzibar to Mombasa
('AT1401', 3, 8, 2, DATE_ADD(NOW(), INTERVAL 2 DAY) + INTERVAL 13 HOUR, DATE_ADD(NOW(), INTERVAL 2 DAY) + INTERVAL 14.5 HOUR, 10000.00, 88, 'scheduled');

-- DAY 3
INSERT INTO flights (flight_number, aircraft_id, origin_id, destination_id, departure_time, arrival_time, price, available_seats, status) VALUES
-- Nairobi to Addis Ababa
('AT1501', 4, 1, 12, DATE_ADD(NOW(), INTERVAL 3 DAY) + INTERVAL 6 HOUR, DATE_ADD(NOW(), INTERVAL 3 DAY) + INTERVAL 8.5 HOUR, 18000.00, 216, 'scheduled'),
('AT1502', 4, 1, 12, DATE_ADD(NOW(), INTERVAL 3 DAY) + INTERVAL 13 HOUR, DATE_ADD(NOW(), INTERVAL 3 DAY) + INTERVAL 15.5 HOUR, 19000.00, 216, 'scheduled'),

-- Addis Ababa to Nairobi
('AT1601', 4, 12, 1, DATE_ADD(NOW(), INTERVAL 3 DAY) + INTERVAL 9 HOUR, DATE_ADD(NOW(), INTERVAL 3 DAY) + INTERVAL 11.5 HOUR, 18000.00, 210, 'scheduled'),
('AT1602', 4, 12, 1, DATE_ADD(NOW(), INTERVAL 3 DAY) + INTERVAL 16 HOUR, DATE_ADD(NOW(), INTERVAL 3 DAY) + INTERVAL 18.5 HOUR, 19000.00, 216, 'scheduled'),

-- Nairobi to Malindi (Tourism route)
('AT1701', 5, 1, 5, DATE_ADD(NOW(), INTERVAL 3 DAY) + INTERVAL 9 HOUR, DATE_ADD(NOW(), INTERVAL 3 DAY) + INTERVAL 10 HOUR, 7500.00, 78, 'scheduled'),
('AT1702', 5, 1, 5, DATE_ADD(NOW(), INTERVAL 3 DAY) + INTERVAL 15 HOUR, DATE_ADD(NOW(), INTERVAL 3 DAY) + INTERVAL 16 HOUR, 8000.00, 78, 'scheduled'),

-- Malindi to Nairobi
('AT1801', 5, 5, 1, DATE_ADD(NOW(), INTERVAL 3 DAY) + INTERVAL 11 HOUR, DATE_ADD(NOW(), INTERVAL 3 DAY) + INTERVAL 12 HOUR, 7500.00, 75, 'scheduled'),
('AT1802', 5, 5, 1, DATE_ADD(NOW(), INTERVAL 3 DAY) + INTERVAL 17 HOUR, DATE_ADD(NOW(), INTERVAL 3 DAY) + INTERVAL 18 HOUR, 8000.00, 78, 'scheduled');

-- DAY 4
INSERT INTO flights (flight_number, aircraft_id, origin_id, destination_id, departure_time, arrival_time, price, available_seats, status) VALUES
-- Nairobi to Johannesburg (Long haul)
('AT1901', 6, 1, 13, DATE_ADD(NOW(), INTERVAL 4 DAY) + INTERVAL 8 HOUR, DATE_ADD(NOW(), INTERVAL 4 DAY) + INTERVAL 12.5 HOUR, 35000.00, 251, 'scheduled'),
('AT1902', 6, 1, 13, DATE_ADD(NOW(), INTERVAL 4 DAY) + INTERVAL 15 HOUR, DATE_ADD(NOW(), INTERVAL 4 DAY) + INTERVAL 19.5 HOUR, 37000.00, 251, 'scheduled'),

-- Johannesburg to Nairobi
('AT2001', 6, 13, 1, DATE_ADD(NOW(), INTERVAL 4 DAY) + INTERVAL 10 HOUR, DATE_ADD(NOW(), INTERVAL 4 DAY) + INTERVAL 14.5 HOUR, 35000.00, 245, 'scheduled'),
('AT2002', 6, 13, 1, DATE_ADD(NOW(), INTERVAL 4 DAY) + INTERVAL 17 HOUR, DATE_ADD(NOW(), INTERVAL 4 DAY) + INTERVAL 21.5 HOUR, 37000.00, 251, 'scheduled'),

-- Mombasa to Lamu (Island hopping)
('AT2101', 5, 2, 6, DATE_ADD(NOW(), INTERVAL 4 DAY) + INTERVAL 10 HOUR, DATE_ADD(NOW(), INTERVAL 4 DAY) + INTERVAL 10.5 HOUR, 5000.00, 78, 'scheduled'),
('AT2102', 5, 2, 6, DATE_ADD(NOW(), INTERVAL 4 DAY) + INTERVAL 16 HOUR, DATE_ADD(NOW(), INTERVAL 4 DAY) + INTERVAL 16.5 HOUR, 5500.00, 78, 'scheduled'),

-- Lamu to Mombasa
('AT2201', 5, 6, 2, DATE_ADD(NOW(), INTERVAL 4 DAY) + INTERVAL 11 HOUR, DATE_ADD(NOW(), INTERVAL 4 DAY) + INTERVAL 11.5 HOUR, 5000.00, 75, 'scheduled'),
('AT2202', 5, 6, 2, DATE_ADD(NOW(), INTERVAL 4 DAY) + INTERVAL 17 HOUR, DATE_ADD(NOW(), INTERVAL 4 DAY) + INTERVAL 17.5 HOUR, 5500.00, 78, 'scheduled');

-- DAY 5
INSERT INTO flights (flight_number, aircraft_id, origin_id, destination_id, departure_time, arrival_time, price, available_seats, status) VALUES
-- Nairobi to Dubai (International hub)
('AT2301', 6, 1, 14, DATE_ADD(NOW(), INTERVAL 5 DAY) + INTERVAL 2 HOUR, DATE_ADD(NOW(), INTERVAL 5 DAY) + INTERVAL 7.5 HOUR, 45000.00, 251, 'scheduled'),
('AT2302', 6, 1, 14, DATE_ADD(NOW(), INTERVAL 5 DAY) + INTERVAL 23 HOUR, DATE_ADD(NOW(), INTERVAL 6 DAY) + INTERVAL 4.5 HOUR, 42000.00, 251, 'scheduled'),

-- Dubai to Nairobi
('AT2401', 6, 14, 1, DATE_ADD(NOW(), INTERVAL 5 DAY) + INTERVAL 9 HOUR, DATE_ADD(NOW(), INTERVAL 5 DAY) + INTERVAL 14.5 HOUR, 45000.00, 240, 'scheduled'),
('AT2402', 6, 14, 1, DATE_ADD(NOW(), INTERVAL 5 DAY) + INTERVAL 21 HOUR, DATE_ADD(NOW(), INTERVAL 6 DAY) + INTERVAL 2.5 HOUR, 42000.00, 251, 'scheduled'),

-- Nairobi to Kilimanjaro
('AT2501', 3, 1, 9, DATE_ADD(NOW(), INTERVAL 5 DAY) + INTERVAL 10 HOUR, DATE_ADD(NOW(), INTERVAL 5 DAY) + INTERVAL 11.5 HOUR, 9000.00, 88, 'scheduled'),

-- Kilimanjaro to Nairobi
('AT2601', 3, 9, 1, DATE_ADD(NOW(), INTERVAL 5 DAY) + INTERVAL 12 HOUR, DATE_ADD(NOW(), INTERVAL 5 DAY) + INTERVAL 13.5 HOUR, 9000.00, 85, 'scheduled');

-- DAY 6
INSERT INTO flights (flight_number, aircraft_id, origin_id, destination_id, departure_time, arrival_time, price, available_seats, status) VALUES
-- Weekend flights (higher demand)
('AT2701', 1, 1, 2, DATE_ADD(NOW(), INTERVAL 6 DAY) + INTERVAL 6 HOUR, DATE_ADD(NOW(), INTERVAL 6 DAY) + INTERVAL 7 HOUR, 10000.00, 165, 'scheduled'),
('AT2702', 2, 1, 2, DATE_ADD(NOW(), INTERVAL 6 DAY) + INTERVAL 9 HOUR, DATE_ADD(NOW(), INTERVAL 6 DAY) + INTERVAL 10 HOUR, 10000.00, 156, 'scheduled'),
('AT2703', 1, 1, 2, DATE_ADD(NOW(), INTERVAL 6 DAY) + INTERVAL 12 HOUR, DATE_ADD(NOW(), INTERVAL 6 DAY) + INTERVAL 13 HOUR, 10500.00, 165, 'scheduled'),
('AT2704', 2, 1, 2, DATE_ADD(NOW(), INTERVAL 6 DAY) + INTERVAL 15 HOUR, DATE_ADD(NOW(), INTERVAL 6 DAY) + INTERVAL 16 HOUR, 11000.00, 156, 'scheduled'),
('AT2705', 1, 1, 2, DATE_ADD(NOW(), INTERVAL 6 DAY) + INTERVAL 18 HOUR, DATE_ADD(NOW(), INTERVAL 6 DAY) + INTERVAL 19 HOUR, 11500.00, 165, 'scheduled'),

('AT2801', 1, 2, 1, DATE_ADD(NOW(), INTERVAL 6 DAY) + INTERVAL 8 HOUR, DATE_ADD(NOW(), INTERVAL 6 DAY) + INTERVAL 9 HOUR, 10000.00, 160, 'scheduled'),
('AT2802', 2, 2, 1, DATE_ADD(NOW(), INTERVAL 6 DAY) + INTERVAL 11 HOUR, DATE_ADD(NOW(), INTERVAL 6 DAY) + INTERVAL 12 HOUR, 10000.00, 150, 'scheduled'),
('AT2803', 1, 2, 1, DATE_ADD(NOW(), INTERVAL 6 DAY) + INTERVAL 14 HOUR, DATE_ADD(NOW(), INTERVAL 6 DAY) + INTERVAL 15 HOUR, 10500.00, 165, 'scheduled'),
('AT2804', 2, 2, 1, DATE_ADD(NOW(), INTERVAL 6 DAY) + INTERVAL 17 HOUR, DATE_ADD(NOW(), INTERVAL 6 DAY) + INTERVAL 18 HOUR, 11000.00, 156, 'scheduled'),
('AT2805', 1, 2, 1, DATE_ADD(NOW(), INTERVAL 6 DAY) + INTERVAL 20 HOUR, DATE_ADD(NOW(), INTERVAL 6 DAY) + INTERVAL 21 HOUR, 11500.00, 165, 'scheduled');

-- ==================== SAMPLE BOOKINGS ====================
-- Creating some sample bookings to show booking history

INSERT INTO bookings (booking_reference, user_id, flight_id, booking_date, total_amount, payment_status, booking_status) VALUES
('BK7A3F9D2', 2, 1, DATE_SUB(NOW(), INTERVAL 2 DAY), 8500.00, 'completed', 'confirmed'),
('BK8B4G1E3', 3, 5, DATE_SUB(NOW(), INTERVAL 1 DAY), 17000.00, 'completed', 'confirmed'),
('BK9C5H2F4', 4, 9, DATE_SUB(NOW(), INTERVAL 3 HOUR), 6500.00, 'completed', 'confirmed');

-- Sample passengers for bookings
INSERT INTO passengers (booking_id, first_name, last_name, date_of_birth, passport_number, nationality, seat_number) VALUES
(1, 'James', 'Kamau', '1985-03-15', 'KE1234567', 'Kenyan', '12A'),
(2, 'Mary', 'Wanjiku', '1990-07-22', 'KE2345678', 'Kenyan', '15B'),
(2, 'John', 'Wanjiku', '2015-03-10', 'KE2345679', 'Kenyan', '15C'),
(3, 'David', 'Omondi', '1988-11-10', 'KE3456789', 'Kenyan', '8A');

-- Sample tickets
INSERT INTO tickets (booking_id, passenger_id, ticket_number, barcode) VALUES
(1, 1, 'TK7A3F9D21', 'BAR7A3F9D21'),
(2, 2, 'TK8B4G1E31', 'BAR8B4G1E31'),
(2, 3, 'TK8B4G1E32', 'BAR8B4G1E32'),
(3, 4, 'TK9C5H2F41', 'BAR9C5H2F41');

-- Update seat availability for booked flights
UPDATE flights SET available_seats = available_seats - 1 WHERE flight_id = 1;
UPDATE flights SET available_seats = available_seats - 2 WHERE flight_id = 5;
UPDATE flights SET available_seats = available_seats - 1 WHERE flight_id = 9;

-- ==================== VERIFICATION QUERIES ====================

-- Check total flights
SELECT COUNT(*) as total_flights FROM flights;

-- Check flights by route
SELECT 
    o.city as origin, 
    d.city as destination, 
    COUNT(*) as flight_count 
FROM flights f
JOIN destinations o ON f.origin_id = o.destination_id
JOIN destinations d ON f.destination_id = d.destination_id
GROUP BY o.city, d.city
ORDER BY flight_count DESC;

-- Check upcoming flights
SELECT 
    f.flight_number,
    o.city as origin,
    d.city as destination,
    f.departure_time,
    f.price,
    f.available_seats
FROM flights f
JOIN destinations o ON f.origin_id = o.destination_id
JOIN destinations d ON f.destination_id = d.destination_id
WHERE f.departure_time > NOW()
ORDER BY f.departure_time ASC
LIMIT 10;

-- Check bookings
SELECT 
    b.booking_reference,
    u.first_name,
    u.last_name,
    f.flight_number,
    b.total_amount,
    b.payment_status
FROM bookings b
JOIN users u ON b.user_id = u.user_id
JOIN flights f ON b.flight_id = f.flight_id;

-- All done!
SELECT '✓ Database populated successfully!' as status;