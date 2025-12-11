<?php
session_start();
include 'db_connection.php';

function s($x){return htmlspecialchars(stripslashes(trim($x)));}

if($_SERVER['REQUEST_METHOD']==='POST'){
    $username = s($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = s($_POST['phone'] ?? '');

    // Check if username already exists
    $checkU = mysqli_query($conn, "SELECT * FROM customer WHERE user='".mysqli_real_escape_string($conn,$username)."'");
    if ($checkU && mysqli_num_rows($checkU)>0){
        $_SESSION['error'] = 'Username already exists.';
        header('Location: register.php'); exit();
    }
    
    // Note: The current customer table has limited columns: ID, user, password, phone_no, Time_of_join
    // For now, we'll just use the available fields
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $time_of_join = date('Y-m-d H:i:s');
    
    $ins = mysqli_query($conn, "INSERT INTO customer (user, password, phone_no, Time_of_join) VALUES ('".mysqli_real_escape_string($conn,$username)."','".mysqli_real_escape_string($conn,$password)."','".mysqli_real_escape_string($conn,$phone)."','".$time_of_join."')");
    
    if($ins){
        $_SESSION['success'] = 'Registration successful. Please login.';
        header('Location: login.php'); exit();
    }
    $_SESSION['error'] = 'Registration failed: '.mysqli_error($conn);
    header('Location: register.php'); exit();
}

header('Location: register.php');
exit();