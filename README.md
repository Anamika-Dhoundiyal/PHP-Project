# Grocery Store Management System

A comprehensive web-based inventory and order management system built with PHP and MySQL. This application allows customers to browse products, manage shopping carts, and place orders, while administrators can manage inventory, process orders, and generate reports.

## 🌟 Features

### 👥 Customer Features
- **User Registration & Authentication** - Secure signup and login system
- **Product Browsing** - View products with detailed descriptions and images
- **Advanced Search** - Search by name, category, and price range
- **Shopping Cart** - Add/remove items, update quantities, persistent cart
- **Checkout Process** - Complete order flow with confirmation
- **Order Tracking** - View order history and current order status
- **Account Management** - Update profile and view transactions

### 🛠️ Administrator Features
- **Inventory Management** - Add, edit, delete products with images
- **Category Management** - Manage product categories
- **Order Processing** - View and manage customer orders
- **Transaction Reports** - View all system transactions
- **Dashboard** - Quick overview of system statistics
- **Admin Panel** - Centralized administration interface

## 🔧 System Requirements

- **Web Server**: Apache (included in XAMPP/WAMP)
- **PHP Version**: 7.0 or higher
- **Database**: MySQL 5.6+ or MariaDB
- **Browser**: Modern browser with JavaScript enabled

## 📦 Installation

### 1. Prerequisites
- Install [XAMPP](https://www.apachefriends.org/) or WAMP
- Start Apache and MySQL services

### 2. Clone/Download Project
```bash
git clone https://github.com/Anamika-Dhoundiyal/GROCERY-STORE-MANAGEMENT-SYSTEM.git
cd GROCERY-STORE-MANAGEMENT-SYSTEM
```

### 3. Setup Database
```bash
# Using phpMyAdmin
1. Go to http://localhost/phpmyadmin
2. Create a new database named 'grocery_store'
3. Import grocery_store_schema.sql file
```

### 4. Configure Database Connection
Edit `Grocery/db_connection.php`:
```php
$server = "localhost";
$username = "root";
$password = "";
$database = "grocery_store";
```

### 5. Access the Application
```
Customer Portal: http://localhost/GROCERY-STORE-MANAGEMENT-SYSTEM/Grocery/index.php
Admin Panel: http://localhost/GROCERY-STORE-MANAGEMENT-SYSTEM/Grocery/admin_login.php
```

## 🔐 Default Login Credentials

### Admin Account
- **Username**: `Admin`
- **Password**: `dbms_pro1`

### Sample Customer Account
- **Username**: `Dharani`
- **Password**: `Ds`

*Note: Change default credentials in production*

## 📊 Key Features
- ✅ Product Management (Add/Update/Delete)
- ✅ Customer Management
- ✅ Employee Management
- ✅ Transaction History
- ✅ Advanced Product Search
- ✅ Inventory Management

#### 👤 Customer Features
- ✅ Browse Products
- ✅ Add to Cart
- ✅ Purchase Items
- ✅ View Transaction History

#### 👨‍🔧 Employee Features
- ✅ Product Management
- ✅ Customer Service
- ✅ Transaction Processing

### 🔍 Search Features

1. **General Search**: Search by ID, Category, Name, Price
2. **Name Search**: Search products by item name
3. **Price Range**: Search products within price range
4. **Price Sort**: Sort products by price (Low to High/High to Low)

### 📁 Database Structure

**Tables Created**:
- `customer` - Customer accounts and details
- `employee` - Employee accounts and details  
- `products` - Product inventory
- `cart` - Shopping cart items
- `purchase` - Transaction history

### 🐛 Fixed Issues

✅ **Database Connection Errors** - Fixed missing database errors
✅ **SQL Syntax Errors** - Fixed search query syntax
✅ **Logic Errors** - Fixed assignment vs comparison operators
✅ **Empty Field Handling** - Improved search functionality

### 📞 Support

If you encounter any issues:
1. Check if XAMPP/WAMP services are running
2. Verify database is created using `test_connection.php`
3. Check PHP error logs in XAMPP
4. Ensure all files are in correct directory structure

---
**Happy Grocery Management! 🛒**
This project contains my project work on a grocery store management system done on PHP and MYSQL using XAMPP AND PHPMYADMIN


STEPS TO RUN:-
1. INSTALL XAMPP AND RUN APACHE AND SQL.
2. COPY THE GROCERY FOLDER TO htdocs in xampp FOLDER
3. LOAD THE grocery.sql table TO PHPMYADMIN USING IMPORT IN PHPMYADMIN IN LOCAL SERVER.
4. FIRST PAGE IS SIGNUP.
5. THREE MODES-ADMIN,EMPLOYEEE,AND CUSTOMER. CUSTOMER CREDENTIAL SIGNUPS ARE GIVEN. FOR EMPLOYEE AND ADMIN MODE SEE CREDENTIAL FROM TABLE IN PHPMYADMIN AND LOGIN. ONLY ADMIN CAN ADD EMPLOYEE AS OF NOW.
6. ADD EMPLOYEE OR ADMIN MANUALLY IN TABLE admin or emploee.
