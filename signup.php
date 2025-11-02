<?php
// Simple database connection
$serverName = "tcp:mycardiffmet.database.windows.net,1433";
$connectionOptions = array(
    "Database" => "myDatabase",
    "Uid" => "myadmin", 
    "PWD" => "Abcdefgh0!",
    "Encrypt" => 1,
    "TrustServerCertificate" => 0
);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Connect to database
    $conn = sqlsrv_connect($serverName, $connectionOptions);
    
    if ($conn) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert into database
        $sql = "INSERT INTO shopu (name, email, password) VALUES (?, ?, ?)";
        $params = array($name, $email, $hashed_password);
        $stmt = sqlsrv_query($conn, $sql, $params);
        
        if ($stmt) {
            // Redirect to success page
            header("Location: success.php");
            exit();
        } else {
            $message = "Error: " . print_r(sqlsrv_errors(), true);
        }
        
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
    } else {
        $message = "Could not connect to database";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <style>
        /* ... your existing styles ... */
    </style>
</head>
<body>
    <div class="signup-form">
        <h2>Sign Up</h2>
        
        <?php if (isset($message)): ?>
            <div class="message error">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="Enter your name" required>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>
            
            <div class="form-group">
                <input type="submit" class="btn" value="Sign Up">
            </div>
        </form>
        
        <p><a href="index.php">Back to Home</a></p>
    </div>
</body>
</html>
