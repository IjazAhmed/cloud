<?php
// Simple database connection for Azure MySQL

$host = "apptestdb.mysql.database.azure.com";
$username = "AdminTest";
$password = "Abcdefgh0!";
$database = "mydatabase";
$port = 3306;

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Basic validation
    if (!empty($name) && !empty($email) && !empty($password)) {
        // Connect to database using MySQLi with SSL
        $conn = mysqli_init();
        
        // Set SSL options for Azure MySQL
        mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
        
        // Establish connection
        if (mysqli_real_connect($conn, $host, $username, $password, $database, $port, NULL, MYSQLI_CLIENT_SSL)) {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Prepare and execute INSERT statement
            $sql = "INSERT INTO shopusers (name, email, password) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            
            if ($stmt) {
                // Bind parameters
                mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashed_password);
                
                // Execute the statement
                if (mysqli_stmt_execute($stmt)) {
                    // Redirect to success page
                    header("Location: success.php");
                    exit();
                } else {
                    $error_message = "Database insert failed: " . mysqli_error($conn);
                    header("Location: register.html?error=" . urlencode($error_message));
                    exit();
                }
                
                mysqli_stmt_close($stmt);
            } else {
                $error_message = "Statement preparation failed: " . mysqli_error($conn);
                header("Location: register.html?error=" . urlencode($error_message));
                exit();
            }
            
            mysqli_close($conn);
        } else {
            $conn_error_message = "Database connection failed: " . mysqli_connect_error();
            header("Location: register.html?error=" . urlencode($conn_error_message));
            exit();
        }
    } else {
        header("Location: register.html?error=Please+fill+all+fields");
        exit();
    }
} else {
    // If someone tries to access this page directly
    header("Location: register.html");
    exit();
}
?>
