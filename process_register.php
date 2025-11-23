<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "apptestdb.mysql.database.azure.com";
$username = "AdminTest";
$password = "Abcdefgh0!";
$database = "mydatabase";

try {
    $dsn = "mysql:host=$host;port=3306;dbname=$database;charset=utf8";
    $options = [
        PDO::MYSQL_ATTR_SSL_CA => '/home/site/wwwroot/BaltimoreCyberTrustRoot.crt.pem',
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ];
    
    $conn = new PDO($dsn, $username, $password, $options);
    echo "SUCCESS: Connected to database using PDO<br>";
    
    // Your insert code here using PDO
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO shopusers (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $hashed_password]);
        
        header("Location: success.php");
        exit();
    }
    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "<br>";
}
?>
