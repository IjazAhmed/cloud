<?php
session_start();
require_once 'config.php';

// Initialize variables
$name = $email = $password = $confirm_password = "";
$name_err = $email_err = $password_err = $confirm_password_err = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and process form data
    $name = trim($_POST["name"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $password = $_POST["password"] ?? '';
    $confirm_password = $_POST["confirm_password"] ?? '';
    
    // Validation
    if (empty($name)) {
        $name_err = "Please enter your name.";
    } elseif (!preg_match("/^[a-zA-Z ]*$/", $name)) {
        $name_err = "Only letters and white space allowed.";
    }
    
    if (empty($email)) {
        $email_err = "Please enter an email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_err = "Invalid email format.";
    } else {
        // Check if email exists
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
    
    if (empty($password)) {
        $password_err = "Please enter a password.";     
    } elseif (strlen($password) < 6) {
        $password_err = "Password must have at least 6 characters.";
    }
    
    if (empty($confirm_password)) {
        $confirm_password_err = "Please confirm password.";     
    } elseif ($password != $confirm_password) {
        $confirm_password_err = "Password did not match.";
    }
    
    // Insert if no errors
    if (empty($name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)) {
        $conn = getDBConnection();
        $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $params = array($name, $email, $hashed_password);
        
        $stmt = sqlsrv_query($conn, $sql, $params);
        
        if ($stmt) {
            $success_message = "Registration completed successfully!";
            // Clear form
            $name = $email = $password = $confirm_password = "";
        } else {
            echo "Error: " . print_r(sqlsrv_errors(), true);
        }
        
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
    }
    
    // Store in session for form display
    $_SESSION['form_data'] = [
        'name' => $name,
        'email' => $email,
        'errors' => [
            'name' => $name_err,
            'email' => $email_err,
            'password' => $password_err,
            'confirm_password' => $confirm_password_err
        ],
        'success' => $success_message
    ];
    
    header("Location: signup_form.html");
    exit();
}
?>