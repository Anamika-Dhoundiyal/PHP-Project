
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'db_connection.php';

// Schema-aware product fetching
$columns = [];
$colRes = mysqli_query($connect, "SHOW COLUMNS FROM products");
if ($colRes) { while ($c = mysqli_fetch_assoc($colRes)) { $columns[$c['Field']] = true; } }

// Determine column names based on schema
$colId = isset($columns['ID']) ? 'ID' : 'product_id';
$colName = isset($columns['Item_name']) ? 'Item_name' : 'product_name';
$colImage = isset($columns['product_image']) ? 'product_image' : (isset($columns['image']) ? 'image' : 'image');
$colPrice = isset($columns['cost']) ? 'cost' : 'price';
$colStock = isset($columns['no_of_items']) ? 'no_of_items' : 'stock_quantity';
$colActive = isset($columns['is_active']) ? 'is_active' : (isset($columns['active']) ? 'active' : null);
$colFeatured = isset($columns['is_featured']) ? 'is_featured' : null;

// Build WHERE clause based on available columns
$where_conditions = [];
if ($colActive) $where_conditions[] = "$colActive = 1";
if ($colFeatured) $where_conditions[] = "$colFeatured = 1";
$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Fetch featured products for homepage
$featured_products = [];
$product_query = mysqli_query($connect, "SELECT * FROM products $where_clause ORDER BY $colId DESC LIMIT 8");
while ($product = mysqli_fetch_array($product_query)) {
    $featured_products[] = $product;
}

// Fallback: if no featured/active products found, show latest products
if (empty($featured_products)) {
    $fallback_query = mysqli_query($connect, "SELECT * FROM products ORDER BY $colId DESC LIMIT 8");
    while ($product = mysqli_fetch_array($fallback_query)) {
        $featured_products[] = $product;
    }
}

// Fetch categories for homepage
$categories = [];
$category_query = mysqli_query($connect, "SELECT * FROM categories ORDER BY category_name");
while ($category = mysqli_fetch_array($category_query)) {
    $categories[] = $category['category_name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>FreshMart - Your Online Grocery Store</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="keywords" content="grocery, organic, fresh, vegetables, fruits, online shopping">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #45a049;
            --accent-color: #FF9800;
            --text-dark: #2c3e50;
            --text-light: #7f8c8d;
            --bg-light: #f8f9fa;
            --white: #ffffff;
            --shadow: 0 8px 32px rgba(0,0,0,0.1);
            --shadow-hover: 0 12px 40px rgba(0,0,0,0.15);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --border-radius: 16px;
            --gradient-primary: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            --gradient-secondary: linear-gradient(135deg, #FF9800 0%, #F57C00 100%);
            --gradient-tertiary: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            background-color: var(--bg-light);
            overflow-x: hidden;
        }

        .category-slider-container { overflow: hidden; }
        .category-slider-track { overflow: hidden; }

        /* Modern Navbar */
        .navbar {
            background: var(--white);
            box-shadow: var(--shadow);
            padding: 1rem 0;
            transition: var(--transition);
        }

        .navbar-brand {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color) !important;
            text-decoration: none;
        }

        .navbar-brand i {
            color: var(--accent-color);
            margin-right: 0.5rem;
        }

        .nav-link {
            color: var(--text-dark) !important;
            font-weight: 500;
            margin: 0 0.5rem;
            transition: var(--transition);
            position: relative;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 50%;
            background: var(--primary-color);
            transition: var(--transition);
            transform: translateX(-50%);
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-primary:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        /* Modern Banner Slider */
        .banner-slider {
            position: relative;
            height: 85vh;
            overflow: hidden;
            margin-top: 6px;
            margin-bottom: 3rem;
            border-radius: 0 0 var(--border-radius) var(--border-radius);
        }
        
        .banner-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
            background: linear-gradient(135deg, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.1) 100%), url('images/banner1.png');
            background-size: cover;
            background-position: center ;
            background-repeat: no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .banner-slide:nth-child(2) {
            background: linear-gradient(135deg, rgba(0,0,0,0.3) 0%, rgba(0,0,0,0.1) 100%), url('images/banner2.jpg');
            background-size: cover;
            background-position: right center;
            background-repeat: no-repeat;
        }
        

        .banner-slide.active {
            opacity: 1;
        }
        
        /* Responsive hero background positioning */
        @media (max-width: 768px) {
            .banner-slide {
                background-position: 70% center;
            }
            
            .banner-slide:nth-child(2) {
                background-position: 70% center;
            }
        }
        
        @media (max-width: 480px) {
            .banner-slide {
                background-position: 80% center;
            }
            
            .banner-slide:nth-child(2) {
                background-position: 80% center;
            }
        }
        
        .banner-content {
            position: relative;
            z-index: 3;
            color: white;
            padding: 120px 5% 80px;
            max-width: 800px;
            text-align: center;
            animation: slideInUp 1s ease-out;
        }
        
        .banner-image {
            position: relative;
            z-index: 2;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .banner-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 20px;
        }
        
        .banner-image-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: rgba(255, 255, 255, 0.6);
            background: rgba(255, 255, 255, 0.05);
        }
        
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .banner-content h1 {
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            line-height: 1.2;
        }
        
        .banner-content p {
            font-size: 1.4rem;
            margin-bottom: 2.5rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
            opacity: 0.9;
        }
        
        .banner-btn {
            background: rgba(255,255,255,0.2);
            border: 2px solid white;
            color: white;
            border-radius: 50px;
            padding: 1rem 2.5rem;
            font-weight: 600;
            font-size: 1.1rem;
            transition: var(--transition);
            backdrop-filter: blur(10px);
            margin: 0 0.5rem;
        }
        
        .banner-btn:hover {
            background: white;
            color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        .banner-indicators {
            position: absolute;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 15px;
        }
        
        /* Hero Animated Icons */
        .hero-animated-icon {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        
        @media (max-width: 991px) {
            .hero-animated-icon {
                display: none;
            }
        }
        
        .indicator {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: rgba(255,255,255,0.4);
            cursor: pointer;
            transition: var(--transition);
            border: 2px solid transparent;
        }
        
        .indicator.active {
            background: white;
            transform: scale(1.3);
            border-color: rgba(255,255,255,0.5);
        }
        
        .banner-controls {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.15);
            color: white;
            border: none;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            z-index: 10;
            backdrop-filter: blur(10px);
            font-size: 1.5rem;
        }
        
        .banner-controls:hover {
            background: rgba(255,255,255,0.25);
            transform: translateY(-50%) scale(1.1);
        }
        
        .banner-prev {
            left: 40px;
        }
        
        .banner-next {
            right: 40px;
        }

        /* Hero Section Original */
        .hero {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: var(--white);
            min-height: 100vh;
            position: relative;
            overflow: hidden;
            margin-top: 76px;
            display: flex;
            align-items: center;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="1" fill="white" opacity="0.1"/><circle cx="10" cy="60" r="1" fill="white" opacity="0.1"/><circle cx="90" cy="40" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .hero-image-container {
            position: relative;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .hero-icon {
            font-size: 15rem;
            opacity: 0.2;
            margin-bottom: 2rem;
        }

        .hero-badges {
            margin-top: 2rem;
        }

        .btn-outline-light:hover {
            background: rgba(255,255,255,0.2);
            border-color: white;
        }

        /* Search Form */
        .search-form .input-group {
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .search-form .form-control {
            border: none;
            padding: 1rem 1.5rem;
            font-size: 1.1rem;
        }

        .search-form .btn {
            border-radius: 0 10px 10px 0;
            padding: 0 2rem;
            font-weight: 600;
        }

        /* Product Cards */
        .row {
            display: flex;
            flex-wrap: wrap;
        }
        
        .row > [class*='col-'] {
            display: flex;
            flex-direction: column;
        }
        
        .product-card {
            background: var(--white);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            margin-bottom: 2rem;
            border: none;
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .product-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: var(--shadow-hover);
        }

        .product-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
            opacity: 0;
            transition: var(--transition);
        }

        .product-card:hover::before {
            opacity: 1;
        }

        .product-image {
            height: 220px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
            border: 3px solid #ffffff;
            box-shadow: 0 0 0 1px #e0e0e0;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
            filter: brightness(0.95);
        }

        .product-card:hover .product-image img {
            transform: scale(1.15);
            filter: brightness(1.05);
        }

        .product-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--gradient-secondary);
            color: var(--white);
            padding: 0.4rem 1rem;
            border-radius: 25px;
            font-size: 0.8rem;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(255, 152, 0, 0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: float 3s ease-in-out infinite;
            z-index: 5;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-3px); }
        }

        .product-info {
            padding: 1.8rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            position: relative;
        }

        .product-info::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--primary-light), transparent);
            opacity: 0.3;
        }

        .product-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            line-height: 1.3;
        }

        .product-category {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }

        .product-price {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            margin-top: auto;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .product-stock {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-bottom: 1.2rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .product-stock::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--success);
            display: inline-block;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        .btn-add-cart {
            background: var(--gradient-primary);
            border: none;
            border-radius: 30px;
            padding: 0.8rem 1.5rem;
            color: var(--white);
            font-weight: 600;
            transition: var(--transition);
            width: 100%;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-add-cart:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.4);
        }

        .btn-add-cart::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-add-cart:hover::before {
            left: 100%;
        }

        /* Categories */
        .category-card {
            background: var(--white);
            border-radius: 20px;
            padding: 2.5rem 2rem;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            transition: var(--transition);
            margin-bottom: 2rem;
            cursor: pointer;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(0,0,0,0.05);
            transform: translateY(0);
        }

        .category-card:hover {
            transform: translateY(-10px) scale(1.03);
            box-shadow: var(--shadow-hover);
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        }

        .category-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 0;
            background: linear-gradient(135deg, #4CAF50, #FF9800);
            transition: height 0.3s ease;
            z-index: 1;
            border-radius: 12px 12px 0 0;
        }

        .category-card:hover::before {
            height: 4px;
        }

        .category-icon {
            font-size: 3.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
            transition: var(--transition);
            position: relative;
            z-index: 2;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .category-card:hover .category-icon {
            transform: scale(1.1) rotate(5deg);
            background: var(--gradient-secondary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .category-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 0.8rem;
            color: var(--text-dark);
            position: relative;
            z-index: 2;
            transition: var(--transition);
        }

        .category-card:hover .category-title {
            color: var(--primary-color);
        }

        .category-card p {
            flex-grow: 1;
            color: var(--text-light);
            font-size: 0.95rem;
            line-height: 1.5;
            position: relative;
            z-index: 2;
            margin-bottom: 1.5rem;
        }

        .category-arrow {
            position: absolute;
            bottom: 20px;
            right: 20px;
            color: var(--primary-color);
            font-size: 1.2rem;
            opacity: 0;
            transition: var(--transition);
            transform: translateX(-10px);
        }

        .category-card:hover .category-arrow {
            opacity: 1;
            transform: translateX(0);
        }
            /* margin-bottom: 1rem;
        } */

        /* Footer */
        .footer {
            background: var(--text-dark);
            color: var(--white);
            padding: 3rem 0 1rem;
            margin-top: 4rem;
        }

        .footer h5 {
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .footer a {
            color: #bdc3c7;
            text-decoration: none;
            transition: var(--transition);
        }

        .footer a:hover {
            color: var(--primary-color);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .navbar-brand {
                font-size: 1.5rem;
            }
        }

        /* Modern Form Styles */
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 0.8rem 1rem;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(46, 204, 113, 0.25);
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 3rem;
            color: var(--text-dark);
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--gradient-primary);
            border-radius: 2px;
        }

        /* Section Animations */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Modern Button Styles */
        .btn-primary {
            background: var(--gradient-primary);
            border: none;
            border-radius: 25px;
            padding: 0.6rem 1.5rem;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(76, 175, 80, 0.4);
        }

        /* Scrollable Category Styles */
        .category-scroll-container {
            position: relative;
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 2rem 0;
        }

        .category-scroll-wrapper {
            flex: 1;
            overflow: hidden;
            position: relative;
        }

        .category-cards-row {
            display: flex;
            gap: 1.5rem;
            padding: 1rem 0;
            transition: transform 0.3s ease;
            will-change: transform;
        }

        .category-scroll-btn {
            background: var(--white);
            border: 2px solid var(--primary);
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1.2rem;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            flex-shrink: 0;
            z-index: 10;
        }

        .category-scroll-btn:hover {
            background: var(--primary);
            color: var(--white);
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.3);
        }

        .category-scroll-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .category-card-small {
            background: var(--white);
            border-radius: 15px;
            padding: 1.5rem 1.2rem;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.05);
            min-width: 170px;
            max-width: 170px;
            height: 160px;
            flex-shrink: 0;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .category-card-small:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
            border-color: #fd7e14;
        }

        .category-card-small::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #28a745, #fd7e14);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .category-card-small:hover::before {
            opacity: 1;
        }

        .category-icon-small {
            font-size: 2.2rem;
            margin-bottom: 0.8rem;
            color: #28a745;
            transition: var(--transition);
        }

        .category-card-small:hover .category-icon-small {
            transform: scale(1.1);
            color: #fd7e14;
        }

        .category-title-small {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.4rem;
            color: var(--dark);
            transition: var(--transition);
        }

        .category-card-small:hover .category-title-small {
            color: var(--primary-dark);
        }

        .category-desc-small {
            font-size: 0.8rem;
            color: var(--gray);
            line-height: 1.4;
            margin: 0;
        }

        @media (max-width: 768px) {
            .category-scroll-container {
                gap: 0.5rem;
                margin: 1.5rem 0;
            }

            .category-scroll-btn {
                width: 35px;
                height: 35px;
                font-size: 1rem;
            }

            .category-card-small {
                min-width: 140px;
                padding: 1.2rem 0.8rem;
            }

            .category-icon-small {
                font-size: 1.8rem;
                margin-bottom: 0.6rem;
            }

            .category-title-small {
                font-size: 0.9rem;
            }

            .category-desc-small {
                font-size: 0.75rem;
            }
        }

        @media (max-width: 480px) {
            .category-card-small {
                min-width: 120px;
                padding: 1rem 0.6rem;
            }

            .category-icon-small {
                font-size: 1.5rem;
            }

            .category-title-small {
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'partials/navbar_simple.php'; ?>

    <!-- Hero Section with Sliding Banners -->
    <section class="banner-slider">
        <!-- Banner 1 -->
        <div class="banner-slide active">
            <div class="container h-100">
                <div class="row align-items-center h-100">
                    <div class="col-lg-12 text-center">
                        <div class="banner-content text-white">
                            <h1 class="display-3 fw-bold mb-4">Fresh Groceries<br>Delivered to Your Door</h1>
                            <p class="lead mb-4">Shop from our wide selection of fresh fruits, vegetables, and daily essentials. Quality guaranteed with every order.</p>
                            <div class="d-flex gap-3 justify-content-center">
                                <a href="products.php" class="btn btn-light btn-lg px-4">
                                    <i class="fas fa-shopping-bag me-2"></i>Shop Now
                                </a>
                                <a href="#categories" class="btn btn-outline-light btn-lg px-4">
                                    <i class="fas fa-arrow-down me-2"></i>Browse Categories
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Banner 2 -->
        <div class="banner-slide">
            <div class="container h-100">
                <div class="row align-items-center h-100">
                    <div class="col-lg-12 text-center">
                        <div class="banner-content text-white">
                            <h1 class="display-3 fw-bold mb-4">Organic & Healthy<br>Living Made Easy</h1>
                            <p class="lead mb-4">Discover our premium collection of organic products and healthy alternatives for your family.</p>
                            <div class="d-flex gap-3 justify-content-center">
                                <a href="products.php" class="btn btn-light btn-lg px-4">
                                    <i class="fas fa-leaf me-2"></i>Explore Organic
                                </a>
                                <a href="#featured" class="btn btn-outline-light btn-lg px-4">
                                    <i class="fas fa-star me-2"></i>Featured Products
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Slider Navigation -->
        <button class="banner-nav banner-prev" onclick="changeSlide(-1)">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="banner-nav banner-next" onclick="changeSlide(1)">
            <i class="fas fa-chevron-right"></i>
        </button>
        
        <!-- Slider Indicators -->
        <div class="banner-indicators">
            <span class="indicator active" onclick="currentSlide(1)"></span>
            <span class="indicator" onclick="currentSlide(2)"></span>
        </div>
    </section>
    
    <!-- Banner Slider JavaScript -->
    <script>
        let currentSlideIndex = 0;
        const slides = document.querySelectorAll('.banner-slide');
        const indicators = document.querySelectorAll('.indicator');
        
        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.toggle('active', i === index);
                indicators[i].classList.toggle('active', i === index);
            });
        }
        
        function changeSlide(direction) {
            currentSlideIndex += direction;
            if (currentSlideIndex >= slides.length) currentSlideIndex = 0;
            if (currentSlideIndex < 0) currentSlideIndex = slides.length - 1;
            showSlide(currentSlideIndex);
        }
        
        function currentSlide(index) {
            currentSlideIndex = index - 1;
            showSlide(currentSlideIndex);
        }
        
        // Auto-rotate slides every 5 seconds
        setInterval(() => {
            changeSlide(1);
        }, 5000);
    </script>

    <!-- Scroll Animation Script -->
    <script>
        // Fade in animation on scroll
        function animateOnScroll() {
            const elements = document.querySelectorAll('.fade-in');
            
            elements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const elementVisible = 150;
                
                if (elementTop < window.innerHeight - elementVisible) {
                    element.classList.add('visible');
                }
            });
        }

        // Add fade-in class to sections
        document.addEventListener('DOMContentLoaded', function() {
            const sections = document.querySelectorAll('section');
            sections.forEach(section => {
                section.classList.add('fade-in');
            });
            
            // Initial animation check
            animateOnScroll();
        });

        // Check animation on scroll
        window.addEventListener('scroll', animateOnScroll);
    </script>

    <!-- CTA Section will be added here -->

    <!-- Featured Categories -->
    <section class="py-5"> 
        <style>
            .section-title {
                font-size: 2.5rem;
                font-weight: 700;
                color: #2c3e50;
                margin-bottom: 3rem;
                position: relative;
                display: inline-block;
                background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }
            
            .section-title::after {
                content: '';
                position: absolute;
                bottom: -10px;
                left: 50%;
                transform: translateX(-50%);
                width: 80px;
                height: 4px;
                background: linear-gradient(135deg, #4CAF50, #FF9800);
                border-radius: 2px;
            }
            
            /* Category Slider Styles */
            .category-slider-container {
                position: relative;
                padding: 20px 60px;
                margin: 2rem 0;
                scroll-margin-top: 100px; /* For smooth scrolling */
            }
            
            .category-actions {
                display: flex;
                gap: 0.5rem;
                align-items: center;
            }
            
            .category-actions .btn {
                border-radius: 25px;
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
                font-weight: 500;
                transition: all 0.3s ease;
                border: 2px solid transparent;
            }
            
            .category-actions .btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            }
            
            .category-actions .btn-outline-primary:hover {
                background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
                border-color: transparent;
            }
            
            .category-actions .btn-outline-secondary:hover {
                background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
                border-color: transparent;
                color: white;
            }
            
            .category-slider-wrapper {
                overflow: visible;
                position: relative;
            }
            
            .category-slider-track {
                display: flex;
                gap: 1.5rem;
                transition: transform 0.3s ease;
                will-change: transform;
            }
            
            .category-slide {
                flex: 0 0 200px;
                min-width: 200px;
            }
            
            .category-card {
                background: white;
                border-radius: 12px;
                padding: 25px 20px;
                text-align: center;
                transition: all 0.3s ease;
                box-shadow: 0 3px 15px rgba(0,0,0,0.08);
                border: 1px solid #f0f0f0;
                height: 180px;
                display: flex;
                flex-direction: column;
                justify-content: center;
                cursor: pointer;
                position: relative;
                overflow: visible;
                margin: 10px 0;
            }
            
            .category-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 0;
                background: linear-gradient(135deg, #4CAF50, #FF9800);
                transition: height 0.3s ease;
                z-index: 1;
                border-radius: 12px 12px 0 0;
            }
            
            .category-card:hover::before {
                height: 4px;
            }
            
            .category-card:hover {
                transform: translateY(-8px);
                box-shadow: 0 15px 40px rgba(76, 175, 80, 0.2);
                border-color: #4CAF50;
            }
            
            .category-icon {
                font-size: 3rem;
                margin: 0 auto 12px;
                color: #4CAF50;
                transition: all 0.3s ease;
                text-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
                position: relative;
                z-index: 2;
            }
            
            .category-card:hover .category-icon {
                color: #FF9800;
                transform: scale(1.1);
                text-shadow: 0 4px 12px rgba(255, 152, 0, 0.4);
            }
            
            .category-title {
                color: #2c3e50;
                font-weight: 600;
                margin-bottom: 8px;
                font-size: 1.1rem;
            }
            
            .category-description {
                color: #7f8c8d; 
                font-size: 0.85rem; 
                margin-bottom: 0;
                line-height: 1.3;
            }
            
            /* Slider Navigation */
            .category-nav {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                background: white;
                border: 2px solid #4CAF50;
                border-radius: 50%;
                width: 50px;
                height: 50px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #4CAF50;
                font-size: 1.3rem;
                cursor: pointer;
                transition: all 0.3s ease;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                z-index: 10;
            }
            
            .category-nav:hover {
                background: #4CAF50;
                color: white;
                transform: translateY(-50%) scale(1.1);
                box-shadow: 0 6px 20px rgba(76, 175, 80, 0.3);
            }
            
            .category-nav.prev {
                left: 0;
            }
            
            .category-nav.next {
                right: 0;
            }
            
            .category-nav:disabled {
                opacity: 0.5;
                cursor: not-allowed;
                transform: translateY(-50%) scale(1);
            }
            
            @media (max-width: 768px) {
                .category-slider-container {
                    padding: 0 40px;
                }
                
                .category-slide {
                    flex: 0 0 160px;
                    min-width: 160px;
                }
                
                .category-card {
                    padding: 20px 15px;
                    height: 160px;
                }
                
                .category-icon {
                    font-size: 2.5rem;
                    margin-bottom: 12px;
                }
                
                .category-nav {
                    width: 40px;
                    height: 40px;
                    font-size: 1.1rem;
                }
            }
            
            @media (max-width: 480px) {
                .category-slider-container {
                    padding: 0 30px;
                }
                
                .category-slide {
                    flex: 0 0 140px;
                    min-width: 140px;
                }
                
                .category-card {
                    padding: 15px 10px;
                    height: 140px;
                }
                
                .category-icon {
                    font-size: 2rem;
                    margin-bottom: 10px;
                }
                
                .category-actions {
                    flex-direction: column;
                    gap: 0.5rem;
                    width: 100%;
                }
                
                .category-actions .btn {
                    width: 100%;
                    justify-content: center;
                }
            }
        </style>
        <div class="container"> 
            <div class="d-flex justify-content-between align-items-center mb-5">
                <h2 class="section-title mb-0">Shop by Category</h2>
                <div class="category-actions">
                    <button class="btn btn-outline-primary btn-sm me-2" onclick="shareCategorySection()" title="Share this section">
                        <i class="fas fa-share-alt me-1"></i>Share
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="scrollToSection('categories')" title="Scroll to categories">
                        <i class="fas fa-arrow-down me-1"></i>Jump to Categories
                    </button>
                </div>
            </div>
            
            <div class="category-slider-container" id="categories">
                <button class="category-nav prev" onclick="slideCategories('prev')">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="category-nav next" onclick="slideCategories('next')">
                    <i class="fas fa-chevron-right"></i>
                </button>
                
                <div class="category-slider-wrapper">
                    <div class="category-slider-track" id="categorySliderTrack">
                        <?php 
                        // Get distinct categories from categories table
                        $category_result = mysqli_query($connect, "SELECT * FROM categories WHERE is_active = TRUE ORDER BY category_name");
                        $categories = [];
                        while ($category_row = mysqli_fetch_assoc($category_result)) {
                            $categories[] = $category_row['category_name'];
                        }
                        
                        // Category mapping for icons and descriptions 
                        $category_data = [ 
                            'fruits' => ['icon' => 'fas fa-apple-alt', 'desc' => 'Organic & fresh fruits delivered daily'], 
                            'vegetables' => ['icon' => 'fas fa-carrot', 'desc' => 'Fresh vegetables from local farms'], 
                            'vegetable' => ['icon' => 'fas fa-carrot', 'desc' => 'Fresh vegetables from local farms'],
                            'bakery' => ['icon' => 'fas fa-bread-slice', 'desc' => 'Fresh bread & baked goods'], 
                            'dairy' => ['icon' => 'fas fa-cheese', 'desc' => 'Fresh dairy products daily'], 
                            'diary' => ['icon' => 'fas fa-cheese', 'desc' => 'Fresh dairy products daily'],
                            'household' => ['icon' => 'fas fa-home', 'desc' => 'Essential household items'],
                            'frozen' => ['icon' => 'fas fa-snowflake', 'desc' => 'Frozen foods & ready meals'],
                            'snacks' => ['icon' => 'fas fa-cookie', 'desc' => 'Tasty snacks & treats'], 
                            'beverages' => ['icon' => 'fas fa-glass-water', 'desc' => 'Refreshing drinks & beverages'], 
                            'meat' => ['icon' => 'fas fa-drumstick-bite', 'desc' => 'Fresh meat & poultry'], 
                            'seafood' => ['icon' => 'fas fa-fish', 'desc' => 'Fresh seafood daily'] 
                        ]; 
                        
                        foreach ($categories as $category): 
                            $category_key = strtolower(str_replace(' ', '', $category)); 
                            $icon = $category_data[$category_key]['icon'] ?? 'fas fa-shopping-basket'; 
                            $desc = $category_data[$category_key]['desc'] ?? 'Quality ' . strtolower($category) . ' products'; 
                        ?> 
                        <div class="category-slide">
                            <div class="category-card" onclick="window.location.href='products.php?category=<?php echo urlencode($category); ?>'"> 
                                <div class="category-icon"> 
                                    <i class="<?php echo $icon; ?>"></i> 
                                </div> 
                                <h5 class="category-title"><?php echo ucfirst($category); ?></h5> 
                                <p class="category-description"><?php echo $desc; ?></p> 
                            </div>
                        </div>
                        <?php endforeach; ?> 
                    </div>
                </div>
            </div>
        </div> 
        
        <script>
            let currentCategorySlide = 0;
        const categorySlides = document.querySelectorAll('.category-slide');
        const categoryTrack = document.getElementById('categorySliderTrack');
        
        // Enhanced category navigation functions
        function slideCategories(direction) {
            if (categorySlides.length === 0) return;
            
            // Get the actual width of a slide including gap
            const slideStyle = window.getComputedStyle(categorySlides[0]);
            const slideWidth = categorySlides[0].offsetWidth + 24; // 24px is the gap (1.5rem)
            const containerWidth = categoryTrack.parentElement.offsetWidth;
            const maxSlide = Math.max(0, categorySlides.length - Math.floor(containerWidth / slideWidth));
            
            if (direction === 'prev') {
                currentCategorySlide = Math.max(0, currentCategorySlide - 1);
            } else {
                currentCategorySlide = Math.min(maxSlide, currentCategorySlide + 1);
            }
            
            // Smooth scroll animation
            categoryTrack.style.transition = 'transform 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
            categoryTrack.style.transform = `translateX(-${currentCategorySlide * slideWidth}px)`;
            
            // Update button states
            const prevBtn = document.querySelector('.category-nav.prev');
            const nextBtn = document.querySelector('.category-nav.next');
            
            if (prevBtn) prevBtn.disabled = currentCategorySlide === 0;
            if (nextBtn) nextBtn.disabled = currentCategorySlide >= maxSlide;
            
            // Update URL with current category position for sharing
            updateCategoryURL();
        }
        
        // Scroll to specific section
        function scrollToSection(sectionId) {
            const element = document.getElementById(sectionId);
            if (element) {
                element.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'start',
                    inline: 'nearest'
                });
                
                // Add highlight effect
                element.style.transition = 'box-shadow 0.3s ease';
                element.style.boxShadow = '0 0 0 3px rgba(46, 204, 113, 0.3)';
                setTimeout(() => {
                    element.style.boxShadow = '';
                }, 2000);
            }
        }
        
        // Share category section
        function shareCategorySection() {
            const currentUrl = window.location.href.split('#')[0];
            const categoryUrl = currentUrl + '#categories';
            
            if (navigator.share) {
                // Use Web Share API if available
                navigator.share({
                    title: 'Grocery Store Categories',
                    text: 'Check out our amazing grocery categories!',
                    url: categoryUrl
                }).catch(err => console.log('Error sharing:', err));
            } else {
                // Fallback: Copy to clipboard
                navigator.clipboard.writeText(categoryUrl).then(() => {
                    // Show success message
                    showToast('Link copied to clipboard!');
                }).catch(err => {
                    console.error('Failed to copy link:', err);
                    // Fallback to manual copy
                    prompt('Copy this link:', categoryUrl);
                });
            }
        }
        
        // Update URL with category position
        function updateCategoryURL() {
            const url = new URL(window.location);
            url.searchParams.set('category_pos', currentCategorySlide);
            window.history.replaceState({}, '', url);
        }
        
        // Show toast notification
        function showToast(message) {
            // Create toast element if it doesn't exist
            let toast = document.getElementById('shareToast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'shareToast';
                toast.className = 'position-fixed top-0 end-0 m-3 alert alert-success';
                toast.style.zIndex = '9999';
                toast.style.transform = 'translateX(100%)';
                toast.style.transition = 'transform 0.3s ease';
                document.body.appendChild(toast);
            }
            
            toast.textContent = message;
            toast.style.transform = 'translateX(0)';
            
            setTimeout(() => {
                toast.style.transform = 'translateX(100%)';
            }, 3000);
        }
        
        // Handle URL parameters on page load
        function handleCategoryURL() {
            const urlParams = new URLSearchParams(window.location.search);
            const categoryPos = urlParams.get('category_pos');
            const hash = window.location.hash;
            
            if (categoryPos !== null) {
                currentCategorySlide = parseInt(categoryPos) || 0;
                setTimeout(() => slideCategories('none'), 100); // Update position without animation
            }
            
            if (hash === '#categories') {
                setTimeout(() => scrollToSection('categories'), 500);
            }
        }
            
            // Initialize button states
        document.addEventListener('DOMContentLoaded', function() {
            // Handle URL parameters first
            handleCategoryURL();
            
            // Set initial state without moving
            if (categorySlides.length > 0) {
                const slideWidth = categorySlides[0].offsetWidth + 24;
                const containerWidth = categoryTrack.parentElement.offsetWidth;
                const maxSlide = Math.max(0, categorySlides.length - Math.floor(containerWidth / slideWidth));
                
                const prevBtn = document.querySelector('.category-nav.prev');
                const nextBtn = document.querySelector('.category-nav.next');
                
                if (prevBtn) prevBtn.disabled = true;
                if (nextBtn) nextBtn.disabled = maxSlide === 0;
            }
            
            // Add smooth scroll behavior to category cards
            const categoryCards = document.querySelectorAll('.category-card');
            categoryCards.forEach(card => {
                card.addEventListener('click', function(e) {
                    // Add click animation
                    this.style.transform = 'translateY(-8px) scale(1.02) scale(0.98)';
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);
                });
            });
        });
            
            // Handle window resize
            window.addEventListener('resize', function() {
                // Recalculate max slides on resize
                if (categorySlides.length > 0) {
                    const slideWidth = categorySlides[0].offsetWidth + 24;
                    const containerWidth = categoryTrack.parentElement.offsetWidth;
                    const maxSlide = Math.max(0, categorySlides.length - Math.floor(containerWidth / slideWidth));
                    
                    // Adjust current slide if it's beyond the new max
                    if (currentCategorySlide > maxSlide) {
                        currentCategorySlide = maxSlide;
                        categoryTrack.style.transform = `translateX(-${currentCategorySlide * slideWidth}px)`;
                    }
                    
                    // Update button states
                    const prevBtn = document.querySelector('.category-nav.prev');
                    const nextBtn = document.querySelector('.category-nav.next');
                    
                    if (prevBtn) prevBtn.disabled = currentCategorySlide === 0;
                    if (nextBtn) nextBtn.disabled = currentCategorySlide >= maxSlide;
                }
            });
        </script>
    </section>

    <!-- Featured Products -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="section-title">Featured Products</h2>
            <div class="row">
                <?php
                foreach ($featured_products as $row) {
                    $name = isset($row[$colName]) ? $row[$colName] : ($row['product_name'] ?? $row['Item_name'] ?? 'Product');
                    $priceVal = isset($row[$colPrice]) ? $row[$colPrice] : ($row['price'] ?? $row['cost'] ?? 0);
                    $stockVal = isset($row[$colStock]) ? $row[$colStock] : ($row['stock_quantity'] ?? $row['no_of_items'] ?? 0);
                    $idVal = isset($row[$colId]) ? $row[$colId] : ($row['product_id'] ?? $row['ID'] ?? 0);
                    $imgCol = $colImage;
                    $image_path = '';
                    if ($imgCol && !empty($row[$imgCol])) { $image_path = $row[$imgCol]; }
                    if (!$image_path) { $image_path = 'images/default.jpg'; }
                    else {
                        if (strpos($image_path, '/') === false && strpos($image_path, '\\') === false) {
                            $image_path = 'images/' . $image_path;
                        }
                    }
                    ?>
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="product-card">
                            <div class="product-image">
                                <img src="<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($name); ?>">
                                <?php if ($stockVal <= 0) { ?>
                                    <span class="product-badge" style="background:#e74c3c">Out of Stock</span>
                                <?php } elseif ($stockVal > 0 && $stockVal <= 10) { ?>
                                    <span class="product-badge">Limited Stock</span>
                                <?php } ?>
                            </div>
                            <div class="product-info">
                                <h5 class="product-title"><?php echo htmlspecialchars($name); ?></h5>
                                <p class="product-category"><?php echo htmlspecialchars($row['brand'] ?? 'General'); ?></p>
                                <div class="product-price">₹<?php echo htmlspecialchars($priceVal); ?></div>
                                <p class="product-stock"><?php echo intval($stockVal); ?> items available</p>
                                <?php if (intval($stockVal) > 0) { ?>
                                    <form class="add-to-cart-form" onsubmit="addToCartAjax(event, <?php echo intval($idVal); ?>, <?php echo floatval($priceVal); ?>)">
                                        <input type="hidden" name="cpid" value="<?php echo intval($idVal); ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" name="submit" class="btn btn-add-cart">
                                            <i class="fas fa-cart-plus"></i> Add to Cart
                                        </button>
                                    </form>
                                <?php } else { ?>
                                    <button class="btn btn-secondary" disabled>
                                        <i class="fas fa-ban"></i> Out of Stock
                                    </button>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
            <div class="text-center mt-4">
                <a href="products.php" class="btn btn-primary btn-lg">View All Products</a>
            </div>
        </div>
    </section>

    <!-- Modern CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <div class="cta-text">
                    <h2 class="cta-title">Fresh Groceries Delivered to Your Door</h2>
                    <p class="cta-description">Experience the convenience of premium quality groceries with our fast delivery service. Shop from our wide selection of fresh produce, pantry essentials, and more.</p>
                    <div class="cta-buttons">
                        <a href="products.php" class="btn btn-primary btn-lg cta-primary">Start Shopping</a>
                        <a href="register.php" class="btn btn-outline-primary btn-lg cta-secondary">Join Free Today</a>
                    </div>
                </div>
                <div class="cta-image">
                    <div class="cta-image-placeholder">
                        <i class="fas fa-shopping-basket"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .cta-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 80px 0;
            margin-top: 60px;
            position: relative;
            overflow: hidden;
        }
        
        .cta-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="%23dee2e6" opacity="0.3"/><circle cx="75" cy="75" r="1" fill="%23dee2e6" opacity="0.3"/><circle cx="50" cy="10" r="0.5" fill="%23dee2e6" opacity="0.2"/><circle cx="10" cy="60" r="0.5" fill="%23dee2e6" opacity="0.2"/><circle cx="90" cy="40" r="0.5" fill="%23dee2e6" opacity="0.2"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.6;
        }
        
        .cta-content {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 60px;
            align-items: center;
            position: relative;
            z-index: 1;
        }
        
        .cta-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
            line-height: 1.2;
        }
        
        .cta-description {
            font-size: 1.2rem;
            color: #6c757d;
            margin-bottom: 30px;
            line-height: 1.6;
            max-width: 90%;
        }
        
        .cta-buttons {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .cta-primary {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            border: none;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
            transition: all 0.3s ease;
        }
        
        .cta-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.4);
        }
        
        .cta-secondary {
            border: 2px solid #3498db;
            color: #3498db;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            transition: all 0.3s ease;
            background: transparent;
        }
        
        .cta-secondary:hover {
            background: #3498db;
            color: white;
            transform: translateY(-2px);
        }
        
        .cta-image-placeholder {
            width: 250px;
            height: 250px;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(52, 152, 219, 0.3);
            animation: float 3s ease-in-out infinite;
        }
        
        .cta-image-placeholder i {
            font-size: 100px;
            color: white;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        @media (max-width: 768px) {
            .cta-section {
                padding: 60px 0;
            }
            
            .cta-content {
                grid-template-columns: 1fr;
                gap: 40px;
                text-align: center;
            }
            
            .cta-title {
                font-size: 2rem;
            }
            
            .cta-description {
                max-width: 100%;
            }
            
            .cta-buttons {
                justify-content: center;
            }
            
            .cta-image {
                order: -1;
            }
            
            .cta-image-placeholder {
                width: 200px;
                height: 200px;
                margin: 0 auto;
            }
            
            .cta-image-placeholder i {
                font-size: 80px;
            }
        }
        
        @media (max-width: 480px) {
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .cta-primary, .cta-secondary {
                width: 100%;
                max-width: 280px;
            }
        }
    </style>

    <!-- Footer spacing fix -->
    <style>
        .footer {
            margin-top: 10px;
        }
    </style>

    <?php include 'partials/footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Smooth scrolling for navigation links (guard when target is missing)
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (!target) return;
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth' });
            });
        });

        // Function to update cart count
        function updateCartCount() {
            fetch('get_cart_count.php')
                .then(response => response.json())
                .then(data => {
                    const cartCountElement = document.getElementById('cart-count');
                    
                    if (cartCountElement && data.count !== undefined) {
                        cartCountElement.textContent = data.count;
                        
                        // Show/hide cart counter based on count
                        if (data.count > 0) {
                            cartCountElement.style.display = 'inline-block';
                        } else {
                            cartCountElement.style.display = 'none';
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Function to show toast notification
        function showToast(message, type = 'success') {
            const toastContainer = document.getElementById('toast-container');
            const toastTemplate = document.getElementById('toast-template');
            const toast = toastTemplate.cloneNode(true);
            
            // Remove template ID and show the toast
            toast.removeAttribute('id');
            toast.style.display = 'block';
            
            // Update message
            toast.querySelector('.toast-message').textContent = message;
            
            // Update icon based on type
            const icon = toast.querySelector('.toast-header i');
            if (type === 'error') {
                icon.className = 'fas fa-exclamation-triangle text-danger me-2';
            } else if (type === 'warning') {
                icon.className = 'fas fa-exclamation-circle text-warning me-2';
            }
            
            // Add to container
            toastContainer.appendChild(toast);
            
            // Initialize Bootstrap toast with 4 second duration
            const bsToast = new bootstrap.Toast(toast, {
                delay: 4000, // 4 seconds
                autohide: true
            });
            bsToast.show();
            
            // Remove from DOM after hiding
            toast.addEventListener('hidden.bs.toast', function() {
                toast.remove();
            });
        }

        // Function to add to cart via AJAX (avoid conflict with global addToCart in js/cart.js)
        function addToCartAjax(event, productId, cost) {
            event.preventDefault();
            
            const button = (event.submitter) ? event.submitter : event.target.querySelector('button');
            const originalHTML = button.innerHTML;
            
            // Show loading state
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            button.disabled = true;
            
            // Create form data
            const formData = new FormData();
            formData.append('cpid', productId);
            formData.append('quantity', 1);
            formData.append('submit', 'Add to Cart');
            
            // Send AJAX request
            fetch('add_to_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(async (response) => {
                const text = await response.text();
                let data;
                try { data = JSON.parse(text); } catch(e) { data = { success:false, message:'Invalid server response' }; }
                return data;
            })
            .then(data => {
                if (data.success) {
                    // Show success state
                    button.innerHTML = '<i class="fas fa-check"></i> Added!';
                    button.style.background = '#27ae60';
                    
                    // Show toast notification
                    showToast(data.message, 'success');
                    
                    // Update cart count
                    updateCartCount();
                } else {
                    // Show error state
                    button.innerHTML = '<i class="fas fa-times"></i> Error!';
                    button.style.background = '#e74c3c';
                    
                    // Show error toast
                    showToast(data.message, 'error');
                }
                
                // Reset button after 2 seconds
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.style.background = '';
                    button.disabled = false;
                }, 2000);
            })
            .catch(error => {
                console.error('Error:', error);
                button.innerHTML = '<i class="fas fa-times"></i> Error!';
                button.style.background = '#e74c3c';
                
                // Show error toast
                showToast('Failed to add item to cart. Please try again.', 'error');
                
                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.style.background = '';
                    button.disabled = false;
                }, 2000);
            });
        }

        // Navbar background change on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                navbar.style.backdropFilter = 'blur(10px)';
            } else {
                navbar.style.background = 'var(--white)';
                navbar.style.backdropFilter = 'none';
            }
        });

        // Update cart count on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
        });

        // Scrollable Categories Function
        function scrollCategories(direction) {
            const wrapper = document.getElementById('categoryScrollWrapper');
            const cardsRow = wrapper.querySelector('.category-cards-row');
            const scrollAmount = 200; // Amount to scroll in pixels
            
            // Get current scroll position
            const currentTransform = cardsRow.style.transform || 'translateX(0px)';
            const currentX = parseInt(currentTransform.match(/-?\d+/)?.[0] || 0);
            
            let newX;
            if (direction === 'left') {
                newX = Math.min(currentX + scrollAmount, 0);
            } else {
                const maxScroll = -(cardsRow.scrollWidth - wrapper.clientWidth);
                newX = Math.max(currentX - scrollAmount, maxScroll);
            }
            
            // Apply the transform
            cardsRow.style.transform = `translateX(${newX}px)`;
            
            // Update button states
            updateScrollButtons(newX, cardsRow.scrollWidth - wrapper.clientWidth);
        }

        function updateScrollButtons(currentX, maxScroll) {
            const leftBtn = document.querySelector('.category-scroll-left');
            const rightBtn = document.querySelector('.category-scroll-right');
            
            // Disable left button if at the beginning
            if (currentX >= 0) {
                leftBtn.disabled = true;
                leftBtn.style.opacity = '0.5';
            } else {
                leftBtn.disabled = false;
                leftBtn.style.opacity = '1';
            }
            
            // Disable right button if at the end
            if (Math.abs(currentX) >= maxScroll) {
                rightBtn.disabled = true;
                rightBtn.style.opacity = '0.5';
            } else {
                rightBtn.disabled = false;
                rightBtn.style.opacity = '1';
            }
        }

        // Initialize scroll buttons on page load (guard when wrapper missing)
        document.addEventListener('DOMContentLoaded', function() {
            const wrapper = document.getElementById('categoryScrollWrapper');
            if (!wrapper) return;
            const cardsRow = wrapper.querySelector('.category-cards-row');
            if (!cardsRow) return;
            updateScrollButtons(0, cardsRow.scrollWidth - wrapper.clientWidth);
        });

        // Handle touch/swipe for mobile
        let touchStartX = 0;
        let touchEndX = 0;

        (function(){
            const el = document.getElementById('categoryScrollWrapper');
            if (!el) return;
            el.addEventListener('touchstart', function(e) { touchStartX = e.changedTouches[0].screenX; });
            el.addEventListener('touchend', function(e) { touchEndX = e.changedTouches[0].screenX; handleSwipe(); });
        })();

        function handleSwipe() {
            const swipeThreshold = 50;
            const diff = touchStartX - touchEndX;
            
            if (Math.abs(diff) > swipeThreshold) {
                if (diff > 0) {
                    // Swipe left - scroll right
                    scrollCategories('right');
                } else {
                    // Swipe right - scroll left
                    scrollCategories('left');
                }
            }
        }
    </script>
    
    <!-- Toast Notification Container -->
    <div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;"></div>
    
    <!-- Toast Notification Template -->
    <div id="toast-template" class="toast" role="alert" aria-live="assertive" aria-atomic="true" style="display: none;">
        <div class="toast-header">
            <i class="fas fa-shopping-cart text-success me-2"></i>
            <strong class="me-auto">FreshMart</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            <span class="toast-message"></span>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <script src="js/main.js"></script>
    <script src="js/cart.js"></script>
</body>
</html>
    </div>
</body>
</html>
