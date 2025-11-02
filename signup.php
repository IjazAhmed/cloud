<?php


session_start();
require_once 'config.php';

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
            $conn = getDBConnection();
            $sql = "SELECT id FROM users WHERE email = ?";
            $params = array($email);
            $stmt = sqlsrv_query($conn, $sql, $params);
            
            if ($stmt && sqlsrv_has_rows($stmt)) {
                $email_err = "This email is already taken.";
            }
            sqlsrv_free_stmt($stmt);
            sqlsrv_close($conn);
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
        
        $conn = getDBConnection();
        $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $params = array($name, $email, $hashed_password);
        
        $stmt = sqlsrv_query($conn, $sql, $params);
        
        if ($stmt) {
            $success_message = "Registration completed successfully!";
            // Don't clear form fields immediately - let user see the success message
        } else {
            $success_message = "Error: Could not register. Please try again.";
            error_log("Database error: " . print_r(sqlsrv_errors(), true));
        }
        
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up Form</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 2.5em;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>MyShop</h1>
            <p>Create Your Account</p>
        </div>
    </div>

    <div class="container">
        <div class="signup-form">
            <h2>Sign Up</h2>
            <p>Please fill this form to create an account.</p>
            
            <?php 
            if (!empty($success_message)) {
                echo '<div class="success">' . $success_message . '</div>';
                
                // If registration was successful, show a link to go back home
                if (strpos($success_message, 'successfully') !== false) {
                    echo '<div style="text-align: center; margin-top: 20px;">
                            <a href="index.php" class="btn">Go Back to Home</a>
                          </div>';
                    // Clear form for new registration
                    $name = $email = $password = $confirm_password = "";
                }
            }
            ?>
            
            <form action="signup.php" method="post">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" 
                           placeholder="Enter your full name">
                    <span class="error"><?php echo $name_err; ?></span>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" 
                           placeholder="Enter your email">
                    <span class="error"><?php echo $email_err; ?></span>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" value="<?php echo htmlspecialchars($password); ?>"
                           placeholder="Enter your password">
                    <span class="error"><?php echo $password_err; ?></span>
                </div>
                
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" value="<?php echo htmlspecialchars($confirm_password); ?>"
                           placeholder="Confirm your password">
                    <span class="error"><?php echo $confirm_password_err; ?></span>
                </div>
                
                <div class="form-group">
                    <input type="submit" class="btn" value="Submit">
                </div>
                
                <p>Already have an account? <a href="index.php">Back to Home</a></p>
            </form>
        </div>
    </div>
</body>
</html>
