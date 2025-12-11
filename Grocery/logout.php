<?php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Use JavaScript redirect to ensure proper page reload and dropdown reinitialization
?>
<!DOCTYPE html>
<html>
<head>
    <title>Logging out...</title>
    <script>
        // Redirect to homepage after a short delay to ensure session cleanup
        setTimeout(function() {
            window.location.href = 'index.php';
        }, 100);
    </script>
</head>
<body>
    <p>Logging out...</p>
</body>
</html>
<?php
exit();
?>