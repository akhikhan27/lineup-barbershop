<?php
// seed.php — run with: php seed.php

$host = 'localhost';
$dbname = 'lineup';
$user = 'lineup_user';
$password = 'yourpassword';

$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Clear existing data
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
$pdo->exec('TRUNCATE TABLE reviews');
$pdo->exec('TRUNCATE TABLE appointments');
$pdo->exec('TRUNCATE TABLE services');
$pdo->exec('TRUNCATE TABLE categories');
$pdo->exec('TRUNCATE TABLE barbers');
$pdo->exec('TRUNCATE TABLE users');
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

// Categories
$pdo->exec("INSERT INTO categories (name) VALUES ('Haircuts'), ('Beard & Shave')");

// Services
$pdo->exec("INSERT INTO services (name, description, price, category_id) VALUES
('Classic Fade', 'Clean taper or skin fade with straight razor finish.', 35.00, 1),
('Lineup', 'Sharp edge up along hairline, temples, and neckline.', 20.00, 1),
('Buzz Cut', 'Short all-around cut with clippers.', 25.00, 1),
('Beard Trim', 'Detailed trim and shape to your beard.', 20.00, 2),
('Beard Sculpting', 'Full beard design with hot towel treatment.', 30.00, 2),
('Clean Shave', 'Traditional straight razor shave with hot towel.', 25.00, 2)");

// Barbers
$pdo->exec("INSERT INTO barbers (firstName, lastName, bio, photo) VALUES
('James', 'Carter', 'Specialist in fades and lineups with 8 years experience.', NULL),
('Marcus', 'Lee', 'Known for clean beard sculpting and straight razor shaves.', NULL),
('Darius', 'Brown', 'Expert in classic cuts and modern styles.', NULL)");

// Admin user
$hash = password_hash('Admin@1234', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (firstName, lastName, email, password, phoneNumber, role) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute(['Admin', 'User', 'admin@lineup.com', $hash, '5141234567', 'admin']);

// Sample customer
$hash2 = password_hash('Customer@1234', PASSWORD_DEFAULT);
$stmt2 = $pdo->prepare("INSERT INTO users (firstName, lastName, email, password, phoneNumber, role) VALUES (?, ?, ?, ?, ?, ?)");
$stmt2->execute(['John', 'Doe', 'john@example.com', $hash2, '5149876543', 'customer']);

echo "Database seeded successfully.\n";