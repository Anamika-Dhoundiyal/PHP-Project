<?php header('Location: login.php'); exit(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Customer Login - FreshMart</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2ecc71;
            --secondary-color: #27ae60;
            --accent-color: #f39c12;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --bg-light: #f8f9fa;
            --white: #ffffff;
            --shadow: 0 2px 20px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }

        .login-container {
            background: var(--white);
            border-radius: 20px;
            box-shadow: var(--shadow);
            overflow: hidden;
            max-width: 900px;
            width: 95%;
            min-height: 500px;
        }

        .login-left {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .login-left h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .login-left p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }

        .login-left .icon {
            font-size: 4rem;
            margin-bottom: 2rem;
            opacity: 0.8;
        }

        .login-right {
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h3 {
            color: var(--text-dark);
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: var(--text-light);
        }

        .form-floating {
            margin-bottom: 1.5rem;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 0.8rem 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(46, 204, 113, 0.25);
        }

        .btn-login {
            background: var(--primary-color);
            border: none;
            border-radius: 10px;
            padding: 0.8rem;
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--white);
            transition: var(--transition);
            width: 100%;
            margin-bottom: 1rem;
        }

        .btn-login:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        .btn-google {
            background: #db4437;
            border: none;
            border-radius: 10px;
            padding: 0.8rem;
            font-weight: 600;
            color: var(--white);
            transition: var(--transition);
            width: 100%;
            margin-bottom: 1rem;
        }

        .btn-google:hover {
            background: #c23321;
            transform: translateY(-2px);
        }

        .divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e9ecef;
        }

        .divider span {
            background: var(--white);
            padding: 0 1rem;
            position: relative;
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .text-center a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .text-center a:hover {
            color: var(--secondary-color);
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .back-to-home {
            position: absolute;
            top: 1rem;
            left: 1rem;
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .back-to-home:hover {
            color: var(--accent-color);
        }

        @media (max-width: 768px) {
            .login-left {
                padding: 2rem;
            }
            
            .login-left h2 {
                font-size: 2rem;
            }
            
            .login-right {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-to-home">
        <i class="fas fa-arrow-left"></i> Back to Home
    </a>

    <div class="login-container">
        <div class="row g-0">
            <div class="col-md-6 login-left">
                <div class="icon">
                    <i class="fas fa-leaf"></i>
                </div>
                <h2>Welcome Back!</h2>
                <p>Sign in to continue your shopping experience with FreshMart</p>
                <div class="mt-4">
                    <p>Don't have an account?</p>
                    <a href="customer_register.php" class="btn btn-outline-light">
                        <i class="fas fa-user-plus"></i> Create Account
                    </a>
                </div>
            </div>
            <div class="col-md-6 login-right">
                <div class="login-header">
                    <h3>Customer Login</h3>
                    <p>Sign in to your account</p>
                </div>

                <?php
                if (isset($_GET['error'])) {
                    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> ' . htmlspecialchars($_GET['error']) . '
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                          </div>';
                }
                if (isset($_GET['success'])) {
                    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> ' . htmlspecialchars($_GET['success']) . '
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                          </div>';
                }
                ?>

                <form action="customer_login_process.php" method="post">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                        <label for="username">
                            <i class="fas fa-user"></i> Username
                        </label>
                    </div>
                    
                    <div class="form-floating">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                        <label for="password">
                            <i class="fas fa-lock"></i> Password
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>

                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </form>

                <div class="divider">
                    <span>OR</span>
                </div>

                <button class="btn btn-google">
                    <i class="fab fa-google"></i> Continue with Google
                </button>

                <div class="text-center mt-3">
                    <a href="#" class="text-decoration-none">Forgot password?</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                if (alert.classList.contains('show')) {
                    alert.classList.remove('show');
                }
            });
        }, 5000);

        // Add loading animation to login button
        document.querySelector('.btn-login').addEventListener('click', function(e) {
            const button = this;
            const originalText = button.innerHTML;
            
            // Only show loading if form is valid
            const form = button.closest('form');
            if (form.checkValidity()) {
                e.preventDefault();
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
                button.disabled = true;
                
                // Submit form after showing loading
                setTimeout(() => {
                    form.submit();
                }, 1000);
            }
        });
    </script>
</body>
</html>