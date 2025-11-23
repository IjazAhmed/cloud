<?php
// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h3>MySQL Connection Debug</h3>";

$host = "apptestdb.mysql.database.azure.com";
$username = "AdminTest";
$password = "Abcdefgh0!"; // Your actual password
$database = "mydatabase";
$port = 3306;

echo "Connection Details:<br>";
echo "Host: " . htmlspecialchars($host) . "<br>";
echo "Username: " . htmlspecialchars($username) . "<br>";
echo "Database: " . htmlspecialchars($database) . "<br>";
echo "Port: " . $port . "<br>";
echo "Web App IP: 20.162.57.127<br><br>";

// Test 1: Check if we can reach the server
echo "Test 1: Network Connectivity<br>";
$timeout = 10;
$socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
if ($socket) {
    echo "SUCCESS: Server is reachable on port {$port}<br>";
    fclose($socket);
} else {
    echo "FAILED: Cannot reach server - {$errstr} ({$errno})<br>";
    echo "This means the server is not accepting connections or firewall is blocking<br>";
    exit;
}

echo "<br>Test 2: MySQLi Extension<br>";
if (!function_exists('mysqli_connect')) {
    echo "FAILED: MySQLi extension not loaded<br>";
    exit;
} else {
    echo "SUCCESS: MySQLi extension is available<br>";
}

echo "<br>Test 3: Database Connection<br>";

// Test without SSL first
echo "Testing without SSL...<br>";
$conn_no_ssl = @mysqli_connect($host, $username, $password, $database, $port);
if ($conn_no_ssl) {
    echo "SUCCESS: Connected without SSL<br>";
    mysqli_close($conn_no_ssl);
} else {
    echo "FAILED without SSL: " . mysqli_connect_error() . "<br>";
}

// Test with SSL
echo "Testing with SSL...<br>";
$conn_ssl = mysqli_init();
mysqli_options($conn_ssl, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);

if (@mysqli_real_connect($conn_ssl, $host, $username, $password, $database, $port, NULL, MYSQLI_CLIENT_SSL)) {
    echo "SUCCESS: Connected with SSL<br>";
    
    // Test if database exists
    echo "<br>Test 4: Database Access<br>";
    $result = mysqli_query($conn_ssl, "SELECT DATABASE() as db");
    $row = mysqli_fetch_assoc($result);
    echo "Current database: " . ($row['db'] ?? 'None') . "<br>";
    
    // Test if table exists
    $table_check = mysqli_query($conn_ssl, "SHOW TABLES LIKE 'shopusers'");
    if (mysqli_num_rows($table_check) > 0) {
        echo "Table 'shopusers' exists<br>";
    } else {
        echo "Table 'shopusers' does NOT exist<br>";
    }
    
    mysqli_close($conn_ssl);
} else {
    echo "FAILED with SSL: " . mysqli_connect_error() . "<br>";
}

echo "<br>Test 5: Detailed Error Analysis<br>";

// Create one more connection to get detailed error
$conn_final = mysqli_init();
mysqli_options($conn_final, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);

if (!mysqli_real_connect($conn_final, $host, $username, $password, $database, $port, NULL, MYSQLI_CLIENT_SSL)) {
    $error = mysqli_connect_error();
    $errno = mysqli_connect_errno();
    
    echo "Final Connection Failed:<br>";
    echo "Error: " . htmlspecialchars($error) . "<br>";
    echo "Error Code: " . $errno . "<br>";
    
    // Common error codes
    switch ($errno) {
        case 1045:
            echo "This means: Access denied - wrong username/password, or user doesn't have access from this IP<br>";
            echo "Possible solutions:<br>";
            echo "1. Check username/password in Azure Portal<br>";
            echo "2. Add IP 20.162.57.127 to MySQL firewall rules<br>";
            echo "3. Enable 'Allow access from Azure services' in MySQL networking<br>";
            break;
        case 2002:
            echo "This means: Cannot connect to server - network/firewall issue<br>";
            break;
        case 1044:
            echo "This means: Access denied for database - user doesn't have permissions<br>";
            break;
        default:
            echo "Check Azure MySQL server status and networking settings<br>";
    }
}

echo "<br>Summary:<br>";
echo "The error 'Access denied for user 'AdminTest'@'20.162.57.127'' means:<br>";
echo "1. Your Web App can reach the MySQL server<br>";
echo "2. But the MySQL server is rejecting the credentials<br>";
echo "3. You need to either:<br>";
echo "   - Add IP 20.162.57.127 to MySQL firewall, OR<br>";
echo "   - Enable 'Allow public access from Azure services' in MySQL networking<br>";

// Don't redirect - show all debug info
?>
