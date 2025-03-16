<?php
/**
 * Smart Restaurant Installation Script
 * This script helps set up the initial configuration and database for the Smart Restaurant system.
 */

echo "Smart Restaurant Installation Script\n";
echo "==================================\n\n";

// Function to get user input
function prompt($message) {
    echo $message . ": ";
    $handle = fopen("php://stdin", "r");
    $input = trim(fgets($handle));
    fclose($handle);
    return $input;
}

// Function to create directory if it doesn't exist
function createDirectory($path) {
    if (!file_exists($path)) {
        if (mkdir($path, 0755, true)) {
            echo "Created directory: $path\n";
            return true;
        } else {
            echo "Error: Failed to create directory: $path\n";
            return false;
        }
    }
    return true;
}

// Check PHP version
echo "Checking PHP version... ";
if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
    echo "OK (PHP " . PHP_VERSION . ")\n";
} else {
    echo "Error: PHP 7.4 or higher is required\n";
    exit(1);
}

// Check required PHP extensions
echo "\nChecking required PHP extensions...\n";
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'openssl'];
$missing_extensions = [];

foreach ($required_extensions as $ext) {
    if (!extension_loaded($ext)) {
        $missing_extensions[] = $ext;
    }
}

if (!empty($missing_extensions)) {
    echo "Error: The following PHP extensions are required but missing:\n";
    foreach ($missing_extensions as $ext) {
        echo "- $ext\n";
    }
    exit(1);
}
echo "All required extensions are installed\n";

// Create necessary directories
echo "\nCreating required directories...\n";
$directories = [
    'uploads',
    'uploads/menu',
    'uploads/profiles',
    'logs',
    'cache'
];

foreach ($directories as $dir) {
    createDirectory(__DIR__ . '/' . $dir);
}

// Database configuration
echo "\nDatabase Configuration\n";
echo "=====================\n";
$db_host = prompt("Database host (default: localhost)") ?: 'localhost';
$db_name = prompt("Database name (default: smart_restaurant)") ?: 'smart_restaurant';
$db_user = prompt("Database username");
$db_pass = prompt("Database password");

// Test database connection
echo "\nTesting database connection... ";
try {
    $pdo = new PDO(
        "mysql:host=$db_host",
        $db_user,
        $db_pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "OK\n";

    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name`");
    echo "Database '$db_name' created or already exists\n";

    // Select the database
    $pdo->exec("USE `$db_name`");

    // Import database schema
    echo "\nImporting database schema... ";
    $schema = file_get_contents(__DIR__ . '/backend/db/db_schema.sql');
    $pdo->exec($schema);
    echo "OK\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Create configuration file
echo "\nCreating configuration file... ";
$config_template = file_get_contents(__DIR__ . '/backend/config/config.example.php');
$config_content = str_replace(
    [
        "'your_database_user'",
        "'your_database_password'",
        "'your_jwt_secret_key'",
        "'your_password_pepper'"
    ],
    [
        "'$db_user'",
        "'$db_pass'",
        "'" . bin2hex(random_bytes(32)) . "'",
        "'" . bin2hex(random_bytes(16)) . "'"
    ],
    $config_template
);

if (file_put_contents(__DIR__ . '/backend/config/config.php', $config_content)) {
    echo "OK\n";
} else {
    echo "Error: Failed to create configuration file\n";
    exit(1);
}

// Set file permissions
echo "\nSetting file permissions... ";
chmod(__DIR__ . '/uploads', 0755);
chmod(__DIR__ . '/logs', 0755);
chmod(__DIR__ . '/cache', 0755);
echo "OK\n";

// Create default admin user
echo "\nCreating default admin user...\n";
$admin_email = prompt("Admin email");
$admin_password = prompt("Admin password");

try {
    $hash = password_hash($admin_password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO users (name, email, password, role, status) 
        VALUES ('Admin', ?, ?, 'admin', 'active')
    ");
    $stmt->execute([$admin_email, $hash]);
    echo "Admin user created successfully\n";
} catch (PDOException $e) {
    echo "Error creating admin user: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nInstallation completed successfully!\n";
echo "====================================\n";
echo "You can now:\n";
echo "1. Start the development server: php -S localhost:8000\n";
echo "2. Access the application at: http://localhost:8000\n";
echo "3. Login to admin dashboard with your email and password\n\n";
echo "Thank you for installing Smart Restaurant!\n";
