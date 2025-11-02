<?php
// SQL Server configuration

$connectionInfo = array("UID" => "myadmin", "pwd" => "Abcdefgh0!", "Database" => "myDatabase", "LoginTimeout" => 30, "Encrypt" => 1, "TrustServerCertificate" => 0);
$serverName = "tcp:mycardiffmet.database.windows.net,1433";
$conn = sqlsrv_connect($serverName, $connectionInfo);


// Initialize variables
$name = $email = $password = $confirm_password = "";
$name_err = $email_err = $password_err = $confirm_password_err = "";
$success_message = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter your name.";
    } else {
        $name = trim($_POST["name"]);
        if (!preg_match("/^[a-zA-Z ]*$/", $name)) {
            $name_err = "Only letters and white space allowed.";
        }
    }
    
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
    } else {
        $email = trim($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email_err = "Invalid email format.";
        } else {
            // Check if email already exists in database
            try {
                $conn = sqlsrv_connect($serverName, $connectionOptions);
                
                if ($conn === false) {
                    die(print_r(sqlsrv_errors(), true));
                }
                
                $sql = "SELECT id FROM users WHERE email = ?";
                $params = array($email);
                $stmt = sqlsrv_query($conn, $sql, $params);
                
                if ($stmt === false) {
                    die(print_r(sqlsrv_errors(), true));
                }
                
                if (sqlsrv_has_rows($stmt)) {
                    $email_err = "This email is already taken.";
                }
                
                sqlsrv_free_stmt($stmt);
                sqlsrv_close($conn);
            } catch(Exception $e) {
                echo "Error: " . $e->getMessage();
            }
        }
    }
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";     
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";     
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }
    
    // Check input errors before inserting in database
    if (empty($name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)) {
        
        try {
            $conn = sqlsrv_connect($serverName, $connectionOptions);
            
            if ($conn === false) {
                die(print_r(sqlsrv_errors(), true));
            }
            
            $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
            
            // Hash password for security
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $params = array($name, $email, $hashed_password);
            $stmt = sqlsrv_query($conn, $sql, $params);
            
            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            } else {
                $success_message = "Registration completed successfully!";
                // Clear form fields
                $name = $email = $password = $confirm_password = "";
            }
            
            sqlsrv_free_stmt($stmt);
            sqlsrv_close($conn);
        } catch(Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up Form</title>
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
        .error {
            color: red;
            font-size: 14px;
            margin-top: 5px;
        }
        .success {
            color: green;
            font-size: 14px;
            margin-bottom: 15px;
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
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="signup-form">
        <h2>Sign Up</h2>
        <p>Please fill this form to create an account.</p>
        
        <?php 
        if (!empty($success_message)) {
            echo '<div class="success">' . $success_message . '</div>';
        }
        ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" value="<?php echo $name; ?>" 
                       placeholder="Enter your full name">
                <span class="error"><?php echo $name_err; ?></span>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo $email; ?>" 
                       placeholder="Enter your email">
                <span class="error"><?php echo $email_err; ?></span>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" 
                       placeholder="Enter your password">
                <span class="error"><?php echo $password_err; ?></span>
            </div>
            
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" 
                       placeholder="Confirm your password">
                <span class="error"><?php echo $confirm_password_err; ?></span>
            </div>
            
            <div class="form-group">
                <input type="submit" class="btn" value="Submit">
            </div>
            
            <p>Already have an account? <a href="login.php">Login here</a>.</p>
        </form>
    </div>
</body>
</html>
