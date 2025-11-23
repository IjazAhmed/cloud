<?php
// Simple script to add 100 users - run from command line or browser
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration for Azure MySQL
$host = "apptestdb.mysql.database.azure.com";
$username = "AdminTest";
$password = "Abcdefgh0!";
$database = "mydatabase";

try {
    // PDO connection with SSL for Azure MySQL
    $dsn = "mysql:host=$host;port=3306;dbname=$database;charset=utf8";
    $options = [
        PDO::MYSQL_ATTR_SSL_CA => '/home/site/wwwroot/BaltimoreCyberTrustRoot.crt.pem',
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];
    
    $conn = new PDO($dsn, $username, $password, $options);
    
    echo "Connected to database successfully!<br>";
    echo "Starting to add 100 sample users...<br>";

    $firstNames = ['John', 'Jane', 'Michael', 'Sarah', 'David', 'Lisa', 'Robert', 'Emily'];
    $lastNames = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis'];

    $successCount = 0;
    $errorCount = 0;

    // Prepare the INSERT statement once for better performance
    $sql = "INSERT INTO shopusers (name, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);

    for ($i = 1; $i <= 100; $i++) {
        $firstName = $firstNames[array_rand($firstNames)];
        $lastName = $lastNames[array_rand($lastNames)];
        $name = $firstName . ' ' . $lastName;
        $email = strtolower($firstName . '.' . $lastName . $i . '@example.com');
        $password = 'Pass' . $i . '!';
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            // Execute the prepared statement with new parameters
            $stmt->execute([$name, $email, $hashed_password]);
            
            echo "Added user $i: $name ($email)<br>";
            $successCount++;
            
        } catch (PDOException $e) {
            // Check if it's a duplicate email error
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                echo "Skipped user $i: Duplicate email ($email)<br>";
                $errorCount++;
            } else {
                echo "Error adding user $i: " . $e->getMessage() . "<br>";
                $errorCount++;
            }
        }
        
        // Flush output if running in browser
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
        
        // Small delay to avoid overwhelming the server
        usleep(100000); // 0.1 second
    }

    echo "<br><strong>Completed adding users!</strong><br>";
    echo "Successfully added: $successCount users<br>";
    echo "Errors/Skipped: $errorCount users<br>";
    echo "Total attempted: " . ($successCount + $errorCount) . " users<br>";

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
