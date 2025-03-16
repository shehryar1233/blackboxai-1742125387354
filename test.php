<?php
/**
 * Smart Restaurant System Test Script
 * Performs basic tests to ensure the system is working correctly
 */

echo "\nSmart Restaurant System Test\n";
echo "===========================\n\n";

$tests_passed = 0;
$tests_failed = 0;

// Function to run a test
function runTest($name, $callback) {
    global $tests_passed, $tests_failed;
    
    echo "Testing $name... ";
    try {
        if ($callback()) {
            echo "✅ PASSED\n";
            $tests_passed++;
        } else {
            echo "❌ FAILED\n";
            $tests_failed++;
        }
    } catch (Exception $e) {
        echo "❌ FAILED (Error: " . $e->getMessage() . ")\n";
        $tests_failed++;
    }
}

// Test database connection
runTest("Database Connection", function() {
    require_once __DIR__ . '/backend/config/config.php';
    require_once __DIR__ . '/backend/db/connection.php';
    
    $db = Database::getInstance()->getConnection();
    return $db instanceof PDO;
});

// Test required directories
runTest("Required Directories", function() {
    $directories = ['uploads', 'logs', 'cache'];
    foreach ($directories as $dir) {
        if (!is_dir($dir) || !is_writable($dir)) {
            return false;
        }
    }
    return true;
});

// Test configuration file
runTest("Configuration File", function() {
    return file_exists(__DIR__ . '/backend/config/config.php');
});

// Test model classes
runTest("Model Classes", function() {
    $models = [
        'User',
        'MenuItem',
        'Order',
        'Reservation',
        'Feedback',
        'Notification'
    ];
    
    foreach ($models as $model) {
        $file = __DIR__ . "/backend/models/$model.php";
        if (!file_exists($file)) {
            throw new Exception("Missing model file: $model.php");
        }
        require_once $file;
        if (!class_exists($model)) {
            throw new Exception("Class not found: $model");
        }
    }
    return true;
});

// Test API endpoints
runTest("API Endpoints", function() {
    $endpoints = [
        '/backend/users/login',
        '/backend/menu',
        '/backend/orders',
        '/backend/reservations',
        '/backend/feedback'
    ];
    
    foreach ($endpoints as $endpoint) {
        $path = __DIR__ . $endpoint;
        if (!file_exists($path) && !is_dir(dirname($path))) {
            throw new Exception("Missing endpoint: $endpoint");
        }
    }
    return true;
});

// Test frontend files
runTest("Frontend Files", function() {
    $files = [
        '/frontend/index.html',
        '/frontend/pages/login.html',
        '/frontend/pages/register.html',
        '/frontend/pages/menu.html',
        '/frontend/pages/order.html',
        '/frontend/pages/reservation.html',
        '/frontend/pages/feedback.html',
        '/frontend/pages/profile.html',
        '/frontend/pages/admin/dashboard.html'
    ];
    
    foreach ($files as $file) {
        if (!file_exists(__DIR__ . $file)) {
            throw new Exception("Missing file: $file");
        }
    }
    return true;
});

// Test file permissions
runTest("File Permissions", function() {
    $paths = [
        'uploads' => 0755,
        'logs' => 0755,
        'cache' => 0755,
        'backend/config/config.php' => 0644
    ];
    
    foreach ($paths as $path => $required_perms) {
        if (!file_exists(__DIR__ . '/' . $path)) {
            throw new Exception("Missing path: $path");
        }
        
        $actual_perms = octdec(substr(sprintf('%o', fileperms(__DIR__ . '/' . $path)), -4));
        if (($actual_perms & $required_perms) != $required_perms) {
            throw new Exception("Incorrect permissions for $path");
        }
    }
    return true;
});

// Test PHP extensions
runTest("PHP Extensions", function() {
    $required_extensions = [
        'pdo',
        'pdo_mysql',
        'json',
        'mbstring',
        'openssl'
    ];
    
    foreach ($required_extensions as $ext) {
        if (!extension_loaded($ext)) {
            throw new Exception("Missing PHP extension: $ext");
        }
    }
    return true;
});

// Print test summary
echo "\nTest Summary\n";
echo "============\n";
echo "Tests Passed: $tests_passed\n";
echo "Tests Failed: $tests_failed\n";
echo "Total Tests: " . ($tests_passed + $tests_failed) . "\n\n";

if ($tests_failed > 0) {
    echo "❌ Some tests failed. Please fix the issues before running the application.\n\n";
    exit(1);
} else {
    echo "✅ All tests passed! The system is ready to use.\n\n";
    exit(0);
}
