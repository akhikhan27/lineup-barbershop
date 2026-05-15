# Self-Hosting Lineup

Lineup is a barbershop appointment booking system built with PHP, Slim Framework, and Twig. This guide walks you through setting it up on your own server.

## Requirements

Before you start, make sure you have the following installed:

- **PHP 7.4 or higher** — Check with `php -v`
- **Composer** — PHP dependency manager. Check with `composer --version`
- **MySQL or MariaDB** — Database server
- **Git** — For cloning the repository

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/akhikhan27/lineup-barbershop.git
cd lineup-barbershop
```

### 2. Install PHP Dependencies

Composer will install Slim, Twig, and all other required packages:

```bash
composer install
```

### 3. Create Environment Configuration

Copy the example environment file and fill in your database details:

```bash
cp .env.example .env
```

Edit `.env` with your database credentials:

```
DB_HOST=localhost
DB_NAME=lineup
DB_USER=lineup_user
DB_PASSWORD=your_password
```

### 4. Set Up the Database

Create a MySQL database and user:

```bash
mysql -u root -p
```

```sql
CREATE DATABASE lineup;
CREATE USER 'lineup_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON lineup.* TO 'lineup_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

If you have a database schema file (`database.sql`), import it:

```bash
mysql -u lineup_user -p lineup < database.sql
```

### 5. Run the Application

**For testing locally:**

```bash
cd lineup
php -S localhost:8000
```

Then open `http://localhost:8000` in your browser.

**For production:**

Point your web server (Apache or Nginx) to the `lineup/public/` directory. Make sure your server is configured to route requests through `index.php` (this is standard for Slim Framework apps).

## Next Steps

- Configure your web server with a proper domain
- Set up HTTPS with Let's Encrypt
- Configure proper file permissions for your server user
- Keep dependencies updated with `composer update`

For more help or issues, check the [GitHub repository](https://github.com/akhikhan27/lineup-barbershop)
