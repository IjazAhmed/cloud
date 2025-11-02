<?php
// Simple database connection
$serverName = "tcp:mycardiffmet.database.windows.net,1433";
$connectionOptions = array(
    "Database" => "myDatabase",
    "Uid" => "myadmin", 
    "PWD" => "cdefgh0!",
    "Encrypt" => 1,
    "TrustServerCertificate" => 0
);

// Process form submission
if ($_POST) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Connect to database
    $conn = sqlsrv_connect($serverName, $connectionOptions);
    
    if ($conn) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert into database
        $sql = "INSERT INTO shopusers (name, email, password) VALUES (?, ?, ?)";
        $params = array($name, $email, $hashed_password);
        $stmt = sqlsrv_query($conn, $sql, $params);
        
        if ($stmt) {
            $message = "User registered successfully!";
        } else {
            $message = "Error: " . print_r(sqlsrv_errors(), true);
        }
        
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
    } else {
        $message = "Could not connect to database. pls check your connection option";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Sign Up</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .signup-form {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            text-align: center;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="signup-form">
        <h2>Simple Sign Up</h2>
        
        <?php if (isset($message)): ?>
            <div class="message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); ?>">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="Enter your name">
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="Enter your email">
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password">
            </div>
            
            <div class="form-group">
                <input type="submit" class="btn" value="Sign Up">
            </div>
        </form>
        
        <p><a href="index.php">Back to Home</a></p>
    </div>
</body>
</html>
