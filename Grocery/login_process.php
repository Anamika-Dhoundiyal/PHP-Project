<?php
session_start();
include 'dbconnection.php';
include 'cart_functions.php';

function sanitize($s) { return htmlspecialchars(stripslashes(trim($s))); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = strtolower(sanitize($_POST['role'] ?? 'customer'));
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($role === 'admin') {
        if ($username === 'Admin' && $password === 'dbms_pro1') {
            $_SESSION['role'] = 'admin';
            $_SESSION['user_id'] = 1;
            $_SESSION['admin_username'] = $username;
            $_SESSION['full_name'] = 'Administrator';
            header('Location: admin_dashboard.php');
            exit();
        } else {
            $_SESSION['error'] = 'Invalid admin credentials.';
            header('Location: login.php');
            exit();
        }
    }

    // customer: match legacy schema (customer.user, customer.password)
    $safe = mysqli_real_escape_string($conn, $username);
    $q = "SELECT * FROM customer WHERE user = '$safe' LIMIT 1";
    $r = mysqli_query($conn, $q);
    if ($r && mysqli_num_rows($r) === 1) {
        $user = mysqli_fetch_assoc($r);
        $ok = false;
        if (isset($user['password'])) {
            $ok = $user['password'] === $password; // legacy plain text
        }
        if ($ok) {
            $_SESSION['role'] = 'customer';
            $_SESSION['cid12'] = $user['ID'] ?? null;
            $_SESSION['customer_id'] = $_SESSION['cid12'];
            $_SESSION['customer_username'] = $user['user'] ?? $username;
            
            // Merge guest cart with user cart after login
            if ($_SESSION['cid12']) {
                mergeGuestCartWithUser($_SESSION['cid12']);
            }
            
            header('Location: customer_logged.php');
            exit();
        }
    }
    $_SESSION['error'] = 'Invalid customer credentials.';
    header('Location: login.php');
    exit();
}

header('Location: login.php');
exit();