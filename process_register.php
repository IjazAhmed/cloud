<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = "apptestdb.mysql.database.azure.com";
$username = "AdminTest";
$password = "Abcdefgh0!";
$database = "mydatabase";

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Basic validation
    if (!empty($name) && !empty($email) && !empty($password)) {
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
            
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Prepare and execute INSERT statement
            $sql = "INSERT INTO shopusers (name, email, password) VALUES (:name, :email, :password)";
            $stmt = $conn->prepare($sql);
            
            // Bind parameters
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            
            // Execute the statement
            if ($stmt->execute()) {
                // Redirect to success page
                header("Location: success.php");
                exit();
            } else {
                $error_message = "Database insert failed";
                header("Location: register.html?error=" . urlencode($error_message));
                exit();
            }
            
        } catch (PDOException $e) {
            // Handle database connection/query errors
            $error_message = "Database error: " . $e->getMessage();
            header("Location: register.html?error=" . urlencode($error_message));
            exit();
        }
    } else {
        // Validation failed
        header("Location: register.html?error=Please+fill+all+fields");
        exit();
    }
} else {
    // If someone tries to access this page directly
    header("Location: register.html");
    exit();
}
?>
