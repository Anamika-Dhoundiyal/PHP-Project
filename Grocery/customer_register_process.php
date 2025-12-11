<?php
session_start();
include('db_connection.php');

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input data
    $fullname = sanitize_input($_POST['fullname']);
    $email = sanitize_input($_POST['email']);
    $username = sanitize_input($_POST['username']);
    $password = sanitize_input($_POST['password']);
    $confirm_password = sanitize_input($_POST['confirm_password']);
    $phone = sanitize_input($_POST['phone']);
    $address = sanitize_input($_POST['address']);
    
    // Validation
    $errors = array();
    
    // Validate full name
    if (empty($fullname)) {
        $errors[] = "Full name is required.";
    } elseif (strlen($fullname) < 3) {
        $errors[] = "Full name must be at least 3 characters long.";
    }
    
    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    
    // Validate username
    if (empty($username)) {
        $errors[] = "Username is required.";
    } elseif (strlen($username) < 4) {
        $errors[] = "Username must be at least 4 characters long.";
    } elseif (!preg_match("/^[a-zA-Z0-9_]+$/", $username)) {
        $errors[] = "Username can only contain letters, numbers, and underscores.";
    }
    
    // Validate password
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }
    
    // Validate password confirmation
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    
    // Validate phone
    if (empty($phone)) {
        $errors[] = "Phone number is required.";
    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
        $errors[] = "Please enter a valid 10-digit phone number.";
    }
    
    // Validate address
    if (empty($address)) {
        $errors[] = "Delivery address is required.";
    } elseif (strlen($address) < 10) {
        $errors[] = "Please enter a complete delivery address.";
    }
    
    // Check if username already exists
    if (empty($errors)) {
        $check_username = "SELECT * FROM customer WHERE username = '$username'";
        $result_username = mysqli_query($conn, $check_username);
        
        if (mysqli_num_rows($result_username) > 0) {
            $errors[] = "Username already exists. Please choose a different username.";
        }
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $check_email = "SELECT * FROM customer WHERE email = '$email'";
        $result_email = mysqli_query($conn, $check_email);
        
        if (mysqli_num_rows($result_email) > 0) {
            $errors[] = "Email address is already registered. Please use a different email or login to your existing account.";
        }
    }
    
    // If there are errors, redirect back with error messages
    if (!empty($errors)) {
        $_SESSION['error'] = implode(" ", $errors);
        header("Location: customer_register.php");
        exit();
    }
    
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new customer into database
    $insert_query = "INSERT INTO customer (fullname, email, username, password, phone, address) 
                     VALUES ('$fullname', '$email', '$username', '$hashed_password', '$phone', '$address')";
    
    if (mysqli_query($conn, $insert_query)) {
        // Registration successful
        $_SESSION['success'] = "Registration successful! You can now login with your username and password.";
        header("Location: customer_login.php");
        exit();
    } else {
        // Registration failed
        $_SESSION['error'] = "Registration failed. Please try again. Error: " . mysqli_error($conn);
        header("Location: customer_register.php");
        exit();
    }
    
} else {
    // If someone tries to access this page directly without submitting the form
    header("Location: customer_register.php");
    exit();
}

// Close database connection
mysqli_close($conn);
?>