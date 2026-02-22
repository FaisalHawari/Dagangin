<?php
// Test database connection
$host = 'sql208.infinityfree.com';
$dbname = 'if0_41219509_dagangin';
$user = 'if0_41219509';
$pass = '5151fahaw1515';

echo "Testing database connection...\n";
echo "Host: $host\n";
echo "Database: $dbname\n";
echo "User: $user\n\n";

// Test 1: Basic connection
try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    echo "DSN: $dsn\n";
    
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "✅ Connection successful!\n";
    
    // Test 2: Simple query
    $stmt = $pdo->query("SELECT VERSION()");
    $version = $stmt->fetchColumn();
    echo "MySQL Version: $version\n";
    
    // Test 3: Check if tables exist
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables found: " . implode(', ', $tables) . "\n";
    
} catch (PDOException $e) {
    echo "❌ Connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "Error Code: " . $e->getCode() . "\n";
}

echo "\nTesting alternative connection methods...\n";

// Test 4: With port
try {
    $dsn = "mysql:host=$host;port=3306;dbname=$dbname;charset=utf8mb4";
    echo "DSN with port: $dsn\n";
    
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo "✅ Connection with port successful!\n";
    
} catch (PDOException $e) {
    echo "❌ Connection with port failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
}

// Test 5: Without database name
try {
    $dsn = "mysql:host=$host;charset=utf8mb4";
    echo "DSN without database: $dsn\n";
    
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo "✅ Host connection successful!\n";
    
    // Try to select database
    $stmt = $pdo->query("USE $dbname");
    echo "✅ Database selection successful!\n";
    
} catch (PDOException $e) {
    echo "❌ Host connection failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nConnection test completed.\n";
?>
