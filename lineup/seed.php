<?php
// Use environment variables (Railway) or fallback to local defaults
$host = getenv('DB_HOST') ?: 'localhost';
$dbname = getenv('DB_NAME') ?: 'lineup';
$user = getenv('DB_USER') ?: 'lineup_user';
$password = getenv('DB_PASSWORD') ?: 'lineup';
// Railway usually provides a PORT variable as well
$port = getenv('DB_PORT') ?: '3306';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // ... rest of your code ...
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
// Create tables if they don't exist
$pdo->exec("CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category_id INT NOT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id)
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstName VARCHAR(100) NOT NULL,
    lastName VARCHAR(100) NOT NULL,
    phoneNumber VARCHAR(20),
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    2fa_secret VARCHAR(255),
    role ENUM('admin', 'customer') DEFAULT 'customer'
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS barbers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstName VARCHAR(100) NOT NULL,
    lastName VARCHAR(100) NOT NULL,
    bio TEXT,
    photo VARCHAR(255)
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    barber_id INT NULL,
    service_id INT NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (service_id) REFERENCES services(id)
)");

$pdo->exec("CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    appointment_id INT NOT NULL UNIQUE,
    comment TEXT,
    rating INT CHECK (rating BETWEEN 1 AND 5),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (appointment_id) REFERENCES appointments(id)
)");

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