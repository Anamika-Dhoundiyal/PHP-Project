<?php
session_start();
header('Location: register.php');
exit();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Registration - Grocery Store</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #27ae60;
            --accent-color: #3498db;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --bg-light: #f8f9fa;
            --border-color: #e9ecef;
            --success-color: #27ae60;
            --error-color: #e74c3c;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            min-height: 600px;
        }

        .register-left {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .register-left h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .register-left p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 40px;
        }

        .register-left .icon {
            font-size: 4rem;
            margin-bottom: 30px;
            opacity: 0.8;
        }

        .register-right {
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .register-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .register-header h3 {
            color: var(--primary-color);
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .register-header p {
            color: var(--text-light);
            font-size: 1rem;
        }

        .form-floating {
            margin-bottom: 20px;
        }

        .form-floating .form-control {
            border: 2px solid var(--border-color);
            border-radius: 12px;
            padding: 12px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-floating .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .form-floating label {
            padding: 12px 20px;
            color: var(--text-light);
        }

        .btn-register {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--success-color) 100%);
            border: none;
            border-radius: 12px;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            color: white;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(39, 174, 96, 0.3);
            color: white;
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .form-text {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .login-link {
            text-align: center;
            margin-top: 30px;
            color: var(--text-light);
        }

        .login-link a {
            color: var(--accent-color);
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .back-to-home {
            position: absolute;
            top: 20px;
            left: 20px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .back-to-home:hover {
            color: var(--accent-color);
            transform: translateX(-5px);
        }

        @media (max-width: 768px) {
            .register-container {
                max-width: 400px;
            }
            
            .register-left {
                display: none;
            }
            
            .register-right {
                padding: 40px 30px;
            }
            
            .register-header h3 {
                font-size: 1.8rem;
            }
        }

        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 5px;
            transition: all 0.3s ease;
        }

        .password-strength.weak {
            background-color: var(--error-color);
            width: 33%;
        }

        .password-strength.medium {
            background-color: #f39c12;
            width: 66%;
        }

        .password-strength.strong {
            background-color: var(--success-color);
            width: 100%;
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-to-home">
        <i class="fas fa-arrow-left"></i>
        Back to Home
    </a>

    <div class="register-container">
        <div class="row g-0">
            <div class="col-md-6">
                <div class="register-left">
                    <div class="icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h2>Join Our Community</h2>
                    <p>Create your account and start shopping with us today. Get access to exclusive deals and track your orders easily.</p>
                    <div class="mt-4">
                        <i class="fas fa-shopping-cart fa-2x me-3"></i>
                        <i class="fas fa-heart fa-2x me-3"></i>
                        <i class="fas fa-star fa-2x"></i>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="register-right">
                    <div class="register-header">
                        <h3>Create Account</h3>
                        <p>Fill in your details to get started</p>
                    </div>

                    <?php
                    if (isset($_SESSION['error'])) {
                        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>' . $_SESSION['error'] . '
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                              </div>';
                        unset($_SESSION['error']);
                    }
                    
                    if (isset($_SESSION['success'])) {
                        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>' . $_SESSION['success'] . '
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                              </div>';
                        unset($_SESSION['success']);
                    }
                    ?>

                    <form action="customer_register_process.php" method="post" id="registerForm">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="fullname" name="fullname" placeholder="Full Name" required>
                            <label for="fullname">
                                <i class="fas fa-user"></i> Full Name
                            </label>
                        </div>

                        <div class="form-floating">
                            <input type="email" class="form-control" id="email" name="email" placeholder="Email Address" required>
                            <label for="email">
                                <i class="fas fa-envelope"></i> Email Address
                            </label>
                        </div>

                        <div class="form-floating">
                            <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                            <label for="username">
                                <i class="fas fa-user-tag"></i> Username
                            </label>
                        </div>

                        <div class="form-floating">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                            <label for="password">
                                <i class="fas fa-lock"></i> Password
                            </label>
                            <div class="password-strength" id="passwordStrength"></div>
                            <div class="form-text">Password must be at least 6 characters long</div>
                        </div>

                        <div class="form-floating">
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required>
                            <label for="confirm_password">
                                <i class="fas fa-lock"></i> Confirm Password
                            </label>
                        </div>

                        <div class="form-floating">
                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="Phone Number" required>
                            <label for="phone">
                                <i class="fas fa-phone"></i> Phone Number
                            </label>
                        </div>

                        <div class="form-floating">
                            <textarea class="form-control" id="address" name="address" placeholder="Delivery Address" style="height: 100px; resize: none;" required></textarea>
                            <label for="address">
                                <i class="fas fa-home"></i> Delivery Address
                            </label>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="#" class="text-decoration-none">Terms and Conditions</a>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-register">
                            <i class="fas fa-user-plus"></i> Create Account
                        </button>
                    </form>

                    <div class="login-link">
                        Already have an account? <a href="customer_login.php">Sign In</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthBar.className = 'password-strength';
                return;
            }
            
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            if (strength <= 2) {
                strengthBar.className = 'password-strength weak';
            } else if (strength <= 4) {
                strengthBar.className = 'password-strength medium';
            } else {
                strengthBar.className = 'password-strength strong';
            }
        });

        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
        });
    </script>
</body>
</html>