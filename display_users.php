<?php
// Database connection
$serverName = "tcp:mycardiffmet-replicaa.database.windows.net,1433";
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

// Get ALL registered users
$sql = "SELECT name, email, password, created_at FROM shopusers ORDER BY created_at DESC";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die("Query failed: " . print_r(sqlsrv_errors(), true));
}

// Fetch all users into an array
$users = array();
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $users[] = $row;
}
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
            max-width: 800px;
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
        .all-users {
            background: #f9f9f9;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: left;
            max-height: 400px;
            overflow-y: auto;
        }
        .all-users h3 {
            color: #333;
            margin-top: 0;
            text-align: center;
        }
        .user-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .user-table th, .user-table td {
            padding: 8px 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .user-table th {
            background-color: #4CAF50;
            color: white;
        }
        .user-table tr:hover {
            background-color: #f5f5f5;
        }
        .password-hash {
            font-family: monospace;
            font-size: 10px;
            color: #666;
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .user-count {
            background: #e8f5e8;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            text-align: center;
            font-weight: bold;
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
        
        <div class="user-count">
            Total Registered Users: <?php echo count($users); ?>
        </div>
        
        <?php if (count($users) > 0): ?>
            <div class="all-users">
                <h3>All Registered Users (Newest First)</h3>
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Password Hash</th>
                            <th>Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td class="password-hash" title="<?php echo htmlspecialchars($user['password']); ?>">
                                    <?php 
                                    // Show first 20 characters of hash
                                    echo htmlspecialchars(substr($user['password'], 0, 20) . '...'); 
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    if ($user['created_at'] instanceof DateTime) {
                                        echo $user['created_at']->format('Y-m-d H:i:s');
                                    } else {
                                        echo htmlspecialchars($user['created_at']);
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">
                No users found in the database.
            </div>
        <?php endif; ?>
        
        <div>
            <a href="index.php" class="btn">Home</a>
        </div>
    </div>

    <?php
    // Clean up
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);
    ?>
</body>
</html>
