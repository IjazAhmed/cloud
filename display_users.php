<?php
// Database connection
$serverName = "tcp:mycardiffmet.database.windows.net,1433";
$connectionOptions = array(
    "Database" => "myDatabase",
    "Uid" => "myadmin", 
    "PWD" => "Abcdefgh0!",
    "Encrypt" => 1,
    "TrustServerCertificate" => 0
);

// Connect to database
$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}

// Get the latest registered user
$sql = "SELECT TOP 1 name, email, password FROM shopusers ORDER BY id DESC";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die("Query failed: " . print_r(sqlsrv_errors(), true));
}

$user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Successful</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f0f8f0;
        }
        .success-message {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .success-icon {
            color: #4CAF50;
            font-size: 48px;
            margin-bottom: 20px;
        }
        .user-details {
            background: #f9f9f9;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: left;
        }
        .user-details h3 {
            color: #333;
            margin-top: 0;
        }
        .btn {
            display: inline-block;
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
        }
        .btn-secondary {
            background-color: #666;
        }
    </style>
</head>
<body>
    <div class="success-message">
        <div class="success-icon">✓</div>
        <h1>Registration Successful!</h1>
        <p>Thank you for registering. Your account has been created successfully.</p>
        
        <?php if ($user): ?>
            <div class="user-details">
                <h3>Your Registration Details:</h3>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Password Hash:</strong> <code><?php echo htmlspecialchars($user['password']); ?></code></p>
            </div>
        <?php endif; ?>
        
        <div>
            <a href="register.html" class="btn">Register Another User</a>
            <a href="display_users.php" class="btn btn-secondary">View All Users</a>
        </div>
    </div>

    <?php
    // Clean up
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
    ?>
</body>
</html>
