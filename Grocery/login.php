<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FreshMart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #2ecc71; --primary-dark: #27ae60; --bg: #f8f9fa; --text: #2c3e50; }
        body { font-family: 'Poppins', sans-serif; background: var(--bg); }
        .login-wrapper { max-width: 960px; margin: 3rem auto; background: #fff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,.08); overflow: hidden; }
        .login-left { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); color: #fff; padding: 3rem; }
        .login-right { padding: 3rem !important; }
        .form-control { border-radius: 12px; padding: .8rem 1rem; }
        .btn-primary { background: var(--primary); border: none; border-radius: 12px; padding: .8rem 1rem; }
        .btn-primary:hover { background: var(--primary-dark); }
        .role-select .btn { border-radius: 999px; }
    </style>
    </head>
<body>
    <div class="login-wrapper">
        <div class="row g-0">
            <div class="col-lg-5 login-left d-flex flex-column justify-content-center">
                <div class="mb-4"><i class="fas fa-leaf fa-3x"></i></div>
                <h3 class="fw-bold mb-3">Welcome Back</h3>
                <p class="opacity-75">Sign in as Customer, Employee, or Admin using a single, unified form.</p>
            </div>
            <div class="col-lg-7 login-right">
                <?php if (isset($_SESSION['error'])) { echo '<div class="alert alert-danger">'.$_SESSION['error'].'</div>'; unset($_SESSION['error']); } ?>
                <form action="login_process.php" method="post">
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" required>
                            <option value="customer">Customer</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="d-grid">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-sign-in-alt me-2"></i>Login</button>
                    </div>
                    <div class="text-center mt-3">
                        <small>Don’t have an account? <a href="register.php">Register</a></small>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php include 'partials/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
