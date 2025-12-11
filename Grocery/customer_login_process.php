<?php
session_start();
include('dbconnection.php');

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input data
    $username = sanitize_input($_POST['username']);
    $password = sanitize_input($_POST['password']);
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validation
    $errors = array();
    
    // Validate username
    if (empty($username)) {
        $errors[] = "Username is required.";
    }
    
    // Validate password
    if (empty($password)) {
        $errors[] = "Password is required.";
    }
    
    // If there are validation errors, redirect back with error message
    if (!empty($errors)) {
        $_SESSION['error'] = implode(" ", $errors);
        header("Location: customer_login.php");
        exit();
    }
    
    // Check if user exists and password is correct
    $login_query = "SELECT * FROM customer WHERE user = '$username'";
    $result = mysqli_query($conn, $login_query);
    
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        
        // Verify password (plain text comparison since passwords are stored as varchar(20))
        if ($password === $user['password']) {
            // Password is correct, login successful
            
            // Set session variables
            $_SESSION['cid12'] = $user['ID']; // Use ID for consistency with existing pages
            $_SESSION['customer_id'] = $user['ID']; // Also set customer_id for newer pages
            $_SESSION['customer_username'] = $user['user'];
            $_SESSION['customer_fullname'] = $user['user']; // Use username as fullname since fullname column doesn't exist
            $_SESSION['customer_email'] = ''; // Email column doesn't exist, set empty
            
            // Set remember me cookie if requested
            if ($remember) {
                setcookie('customer_username', $username, time() + (86400 * 30), "/"); // 30 days
                setcookie('customer_password', $password, time() + (86400 * 30), "/"); // 30 days
            }
            
            // Merge guest cart into user cart after login
            require_once 'cart_functions.php';
            mergeGuestCartWithUser($_SESSION['cid12']);

            // Redirect to customer dashboard or the page they were trying to access
            if (isset($_SESSION['redirect_url'])) {
                $redirect_url = $_SESSION['redirect_url'];
                unset($_SESSION['redirect_url']);
                header("Location: $redirect_url");
            } else {
                header("Location: customer_logged.php");
            }
            exit();
            
        } else {
            // Password is incorrect
            $_SESSION['error'] = "Invalid username or password.";
            header("Location: customer_login.php");
            exit();
        }
        
    } else {
        // Username doesn't exist
        $_SESSION['error'] = "Invalid username or password.";
        header("Location: customer_login.php");
        exit();
    }
    
} else {
    // If someone tries to access this page directly without submitting the form
    header("Location: customer_login.php");
    exit();
}

// Close database connection
mysqli_close($conn);
?>
