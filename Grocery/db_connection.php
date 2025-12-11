<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "grocery";

$server = mysqli_connect($host, $username, $password);
if (!$server) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_query($server, "CREATE DATABASE IF NOT EXISTS `$database`");
$connect = mysqli_connect($host, $username, $password, $database);
if (!$connect) {
    die("Connection failed: " . mysqli_connect_error());
}
mysqli_set_charset($connect, "utf8");

mysqli_query($connect, "CREATE TABLE IF NOT EXISTS customer (
    ID INT(10) NOT NULL AUTO_INCREMENT,
    user VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone_no BIGINT(10) NOT NULL,
    Time_of_join DATETIME NOT NULL,
    PRIMARY KEY (ID),
    UNIQUE KEY user (user)
)");

$cols = [
    ['cname', "ALTER TABLE customer ADD COLUMN cname VARCHAR(100) AFTER user"],
    ['email', "ALTER TABLE customer ADD COLUMN email VARCHAR(150) AFTER cname"],
    ['address', "ALTER TABLE customer ADD COLUMN address TEXT"],
    ['city', "ALTER TABLE customer ADD COLUMN city VARCHAR(100)"],
    ['state', "ALTER TABLE customer ADD COLUMN state VARCHAR(100)"],
    ['zip_code', "ALTER TABLE customer ADD COLUMN zip_code VARCHAR(20)"],
    ['created_at', "ALTER TABLE customer ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP"]
];
foreach ($cols as $c) {
    $r = mysqli_query($connect, "SHOW COLUMNS FROM customer LIKE '".$c[0]."'");
    if (mysqli_num_rows($r) == 0) {
        mysqli_query($connect, $c[1]);
    }
}

mysqli_query($connect, "CREATE TABLE IF NOT EXISTS employee (
    eid INT(10) NOT NULL AUTO_INCREMENT,
    e_username VARCHAR(20) NOT NULL,
    e_password VARCHAR(20) NOT NULL,
    e_phone_no BIGINT(10) NOT NULL,
    e_date_join DATETIME NOT NULL,
    PRIMARY KEY (eid),
    UNIQUE KEY e_username (e_username)
)");

mysqli_query($connect, "CREATE TABLE IF NOT EXISTS products (
    product_id INT(10) NOT NULL AUTO_INCREMENT,
    category_id INT(10) DEFAULT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock_quantity INT(10) NOT NULL DEFAULT 0,
    min_stock_level INT(10) DEFAULT 5,
    unit VARCHAR(20) DEFAULT 'piece',
    brand VARCHAR(100) DEFAULT NULL,
    product_image VARCHAR(255) DEFAULT 'images/default.jpg',
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (product_id),
    UNIQUE KEY product_name (product_name)
)");

mysqli_query($connect, "CREATE TABLE IF NOT EXISTS categories (
    category_id INT(10) NOT NULL AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL,
    category_description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (category_id),
    UNIQUE KEY category_name (category_name)
)");

mysqli_query($connect, "CREATE TABLE IF NOT EXISTS users (
    user_id INT(10) NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'customer',
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    UNIQUE KEY email (email)
)");

mysqli_query($connect, "CREATE TABLE IF NOT EXISTS cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY,
    uid INT NOT NULL,
    pid INT NOT NULL,
    no_of_items INT NOT NULL DEFAULT 1,
    cost_of_item DECIMAL(10,2) NOT NULL,
    UNIQUE KEY unique_cart_item (uid, pid)
)");

mysqli_query($connect, "CREATE TABLE IF NOT EXISTS purchase (
    pcid INT(10) NOT NULL,
    ppid INT(10) NOT NULL,
    no_of_items INT(10) NOT NULL,
    cost_of_items INT(10) NOT NULL,
    date_time DATETIME NOT NULL,
    status VARCHAR(50) DEFAULT 'pending'
)");

$ordersExists = mysqli_query($connect, "SHOW TABLES LIKE 'orders'");
if (mysqli_num_rows($ordersExists) == 0) {
    mysqli_query($connect, "CREATE TABLE orders (
        order_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        address TEXT NOT NULL,
        phone VARCHAR(20) NOT NULL,
        payment_method VARCHAR(50) NOT NULL DEFAULT 'cod',
        status VARCHAR(20) NOT NULL DEFAULT 'pending',
        order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    )");
}

$orderItemsExists = mysqli_query($connect, "SHOW TABLES LIKE 'order_items'");
if (mysqli_num_rows($orderItemsExists) == 0) {
    mysqli_query($connect, "CREATE TABLE order_items (
        item_id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL
    )");
}

// Insert default categories if none exist
$categoriesCount = mysqli_query($connect, "SELECT COUNT(*) as count FROM categories");
$categoriesData = mysqli_fetch_assoc($categoriesCount);
if ($categoriesData['count'] == 0) {
    $defaultCategories = [
        'Fruits & Vegetables',
        'Dairy & Eggs',
        'Meat & Seafood',
        'Bakery',
        'Beverages',
        'Snacks',
        'Frozen Foods',
        'Pantry Staples',
        'Cleaning Supplies',
        'Personal Care'
    ];
    
    foreach ($defaultCategories as $category) {
        $stmt = $connect->prepare("INSERT INTO categories (category_name) VALUES (?)");
        $stmt->bind_param("s", $category);
        $stmt->execute();
    }
}

if (!isset($conn)) {
    $conn = $connect;
}
?>