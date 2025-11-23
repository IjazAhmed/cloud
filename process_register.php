<?php
// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "Debug: Script started<br>";

$host = "apptestdb.mysql.database.azure.com";
$username = "AdminTest";
$password = "Abcdefgh0!";
$database = "mydatabase";
$port = 3306;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    echo "Debug: POST request received<br>";
    
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    echo "Debug: Name: $name, Email: $email<br>";
    
    // Basic validation
    if (!empty($name) && !empty($email) && !empty($password)) {
        echo "Debug: Validation passed<br>";
        
        // Connect to database using MySQLi with SSL
        $conn = mysqli_init();
        
        // Set SSL options for Azure MySQL
        mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
        
        echo "Debug: Attempting database connection...<br>";
        
        // Establish connection
        if (mysqli_real_connect($conn, $host, $username, $password, $database, $port, NULL, MYSQLI_CLIENT_SSL)) {
            echo "Debug: Database connected successfully<br>";
            
            // Check if table exists
            $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'shopusers'");
            if (mysqli_num_rows($table_check) == 0) {
                die("ERROR: shopusers table does not exist! Create it first.");
            }
            
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            echo "Debug: Password hashed<br>";
            
            // Prepare and execute INSERT statement
            $sql = "INSERT INTO shopusers (name, email, password) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            
            if ($stmt) {
                echo "Debug: Statement prepared<br>";
                
                // Bind parameters
                mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashed_password);
                
                // Execute the statement
                if (mysqli_stmt_execute($stmt)) {
                    echo "Debug: Insert successful<br>";
                    // Redirect to success page
                    header("Location: success.php");
                    exit();
                } else {
                    echo "Debug: Insert failed<br>";
                    $error_message = "Database insert failed: " . mysqli_error($conn);
                    header("Location: register.html?error=" . urlencode($error_message));
                    exit();
                }
                
                mysqli_stmt_close($stmt);
            } else {
                echo "Debug: Statement preparation failed<br>";
                $error_message = "Statement preparation failed: " . mysqli_error($conn);
                header("Location: register.html?error=" . urlencode($error_message));
                exit();
            }
            
            mysqli_close($conn);
        } else {
            echo "Debug: Connection failed<br>";
            $conn_error_message = "Database connection failed: " . mysqli_connect_error();
            header("Location: register.html?error=" . urlencode($conn_error_message));
            exit();
        }
    } else {
        echo "Debug: Validation failed<br>";
        header("Location: register.html?error=Please+fill+all+fields");
        exit();
    }
} else {
    echo "Debug: Not a POST request<br>";
    // If someone tries to access this page directly
    header("Location: register.html");
    exit();
}
?>
