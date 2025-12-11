<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - FreshMart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #2ecc71; --secondary: #27ae60; }
        body { font-family: 'Poppins', sans-serif; background: #f8f9fa; }
        .wrap { max-width: 960px; margin: 3rem auto; background: #fff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,.08); overflow: hidden; }
        .left { background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); color: #fff; padding: 3rem; }
        .right { padding: 3rem; }
        .form-control { border-radius: 12px; }
        .btn-primary { background: var(--primary); border: none; border-radius: 12px; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="row g-0">
            <div class="col-lg-5 left d-flex flex-column justify-content-center">
                <div class="mb-4"><i class="fas fa-user-plus fa-3x"></i></div>
                <h3 class="fw-bold mb-3">Create Your Account</h3>
                <p class="opacity-75">Register as Customer or Employee. Admin accounts are managed separately.</p>
            </div>
            <div class="col-lg-7 right">
                <?php if (isset($_SESSION['error'])) { echo '<div class="alert alert-danger">'.$_SESSION['error'].'</div>'; unset($_SESSION['error']); } ?>
                <?php if (isset($_SESSION['success'])) { echo '<div class="alert alert-success">'.$_SESSION['success'].'</div>'; unset($_SESSION['success']); } ?>
                <form action="register_process.php" method="post">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" name="phone" class="form-control" required>
                    </div>
                    <div class="d-grid">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-user-plus me-2"></i>Register</button>
                    </div>
                    <div class="text-center mt-3">
                        <small>Already have an account? <a href="login.php">Login</a></small>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php include 'partials/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
