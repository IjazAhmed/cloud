<?php
session_start();
require_once 'config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$name = $email = $password = $confirm_password = "";
$name_err = $email_err = $password_err = $confirm_password_err = "";
$success_message = "";

// Debug: Log the request method and POST data
error_log("=== SIGNUP.PH DEBUG ===");
error_log("Request Method: " . $_SERVER["REQUEST_METHOD"]);
error_log("POST Data: " . print_r($_POST, true));
error_log("SCRIPT_NAME: " . $_SERVER["SCRIPT_NAME"]);
error_log("REQUEST_URI: " . $_SERVER["REQUEST_URI"]);

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("POST request detected - starting form processing");
    
    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter your name.";
        error_log("Name validation failed: empty");
    } else {
        $name = trim($_POST["name"]);
        if (!preg_match("/^[a-zA-Z ]*$/", $name)) {
            $name_err = "Only letters and white space allowed.";
            error_log("Name validation failed: invalid characters");
        } else {
            error_log("Name validation passed: " . $name);
        }
    }
    
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email.";
        error_log("Email validation failed: empty");
    } else {
        $email = trim($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email_err = "Invalid email format.";
            error_log("Email validation failed: invalid format");
        } else {
            error_log("Email validation passed: " . $email);
            // Check if email already exists in database
            $conn = getDBConnection();
            if ($conn) {
                error_log("Database connection successful for email check");
                $sql = "SELECT id FROM users WHERE email = ?";
                $params = array($email);
                $stmt = sqlsrv_query($conn, $sql, $params);
                
                if ($stmt && sqlsrv_has_rows($stmt)) {
                    $email_err = "This email is already taken.";
                    error_log("Email validation failed: already exists");
                } else {
                    error_log("Email validation passed: not in database");
                }
                sqlsrv_free_stmt($stmt);
                sqlsrv_close($conn);
            } else {
                error_log("Database connection failed for email check");
            }
        }
    }
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password."; 
        error_log("Password validation failed: empty");    
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
        error_log("Password validation failed: too short");
    } else {
        $password = trim($_POST["password"]);
        error_log("Password validation passed");
    }
    
    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password."; 
        error_log("Confirm password validation failed: empty");    
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
            error_log("Confirm password validation failed: mismatch");
        } else {
            error_log("Confirm password validation passed");
        }
    }
    
    // Check input errors before inserting in database
    if (empty($name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)) {
        error_log("All validations passed - attempting database insert");
        
        $conn = getDBConnection();
        if ($conn) {
            error_log("Database connection successful for insert");
            $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $params = array($name, $email, $hashed_password);
            
            $stmt = sqlsrv_query($conn, $sql, $params);
            
            if ($stmt) {
                $success_message = "Registration completed successfully!";
                error_log("Database insert successful");
                // Don't clear form fields immediately - let user see the success message
            } else {
                $success_message = "Error: Could not register. Please try again.";
                $errors = sqlsrv_errors();
                error_log("Database insert failed: " . print_r($errors, true));
            }
            
            sqlsrv_free_stmt($stmt);
            sqlsrv_close($conn);
        } else {
            error_log("Database connection failed for insert");
            $success_message = "Error: Database connection failed.";
        }
    } else {
        error_log("Validation errors present - skipping database insert");
        error_log("Errors - Name: $name_err, Email: $email_err, Password: $password_err, Confirm: $confirm_password_err");
    }
} else {
    error_log("No POST request detected - showing empty form");
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
        .debug-panel {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            font-family: monospace;
            font-size: 14px;
        }
        .debug-title {
            font-weight: bold;
            color: #495057;
            margin-bottom: 10px;
        }
        .debug-info {
            color: #6c757d;
        }
        .test-buttons {
            margin: 10px 0;
        }
        .test-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 5px 10px;
            margin-right: 10px;
            border-radius: 3px;
            cursor: pointer;
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
        <!-- Debug Information Panel -->
        <div class="debug-panel">
            <div class="debug-title">Debug Information</div>
            <div class="debug-info">
                <strong>Request Method:</strong> <?php echo $_SERVER["REQUEST_METHOD"]; ?><br>
                <strong>PHP Self:</strong> <?php echo $_SERVER["PHP_SELF"]; ?><br>
                <strong>Script Name:</strong> <?php echo $_SERVER["SCRIPT_NAME"]; ?><br>
                <strong>Request URI:</strong> <?php echo $_SERVER["REQUEST_URI"]; ?><br>
                <strong>POST Data:</strong> <?php echo !empty($_POST) ? print_r($_POST, true) : 'Empty'; ?><br>
                <strong>Session Status:</strong> <?php echo session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Not Active'; ?><br>
                <strong>Config Loaded:</strong> <?php echo function_exists('getDBConnection') ? 'Yes' : 'No'; ?>
            </div>
            
            <div class="test-buttons">
                <strong>Test Links:</strong>
                <button class="test-btn" onclick="window.location.href='signup.php?test=get'">Test GET</button>
                <button class="test-btn" onclick="testPost()">Test POST via JS</button>
                <button class="test-btn" onclick="window.location.href='index.php'">Back to Home</button>
            </div>
        </div>

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
            
            <!-- Try different form actions to see which works -->
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" id="signupForm">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" 
                           placeholder="Enter your full name" required>
                    <span class="error"><?php echo $name_err; ?></span>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" 
                           placeholder="Enter your email" required>
                    <span class="error"><?php echo $email_err; ?></span>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" value="<?php echo htmlspecialchars($password); ?>"
                           placeholder="Enter your password" required>
                    <span class="error"><?php echo $password_err; ?></span>
                </div>
                
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" value="<?php echo htmlspecialchars($confirm_password); ?>"
                           placeholder="Confirm your password" required>
                    <span class="error"><?php echo $confirm_password_err; ?></span>
                </div>
                
                <div class="form-group">
                    <input type="submit" class="btn" value="Submit" id="submitBtn">
                    <input type="button" class="btn" value="Test Validation" onclick="testValidation()">
                </div>
            </form>
            
            <p>Already have an account? <a href="index.php">Back to Home</a></p>
        </div>
    </div>

    <script>
        // Client-side debugging
        console.log("Signup form loaded successfully");
        
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            console.log("Form submission started");
            console.log("Form action:", this.action);
            console.log("Form method:", this.method);
            console.log("Form data:", new FormData(this));
        });
        
        function testPost() {
            console.log("Testing POST request");
            // Simple test to see if we can make a POST request
            fetch('signup.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'test=post&name=TestUser&email=test@example.com'
            })
            .then(response => {
                console.log("POST test response status:", response.status);
                return response.text();
            })
            .then(data => {
                console.log("POST test response received");
                // Reload to see any changes
                location.reload();
            })
            .catch(error => {
                console.error("POST test failed:", error);
            });
        }
        
        function testValidation() {
            // Test client-side validation
            const form = document.getElementById('signupForm');
            const name = form.name.value;
            const email = form.email.value;
            const password = form.password.value;
            const confirm = form.confirm_password.value;
            
            console.log("Validation Test:");
            console.log("Name:", name);
            console.log("Email:", email);
            console.log("Password length:", password.length);
            console.log("Passwords match:", password === confirm);
        }
        
        // Check if we have any URL parameters for testing
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('test')) {
            console.log("Test parameter found:", urlParams.get('test'));
        }
    </script>
</body>
</html>
