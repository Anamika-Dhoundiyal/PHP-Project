# GROCERY-STORE-MANAGEMENT-SYSTEM-USING-PHP-AND-MYSQL-PHPMYADMIN-

## 🚀 Quick Start Guide

### 📋 System Requirements
- **XAMPP/WAMP** (Apache, MySQL, PHP)
- **PHP Version**: 7.0 or higher
- **MySQL/MariaDB**: 5.6 or higher

### 🔗 Quick Access Links


**Admin Panel**: Login as Admin → Redirects to `Admin_logged.php`

### 🔑 Default Login Credentials

#### Admin Account
- **Username**: `Admin`
- **Password**: `dbms_pro1`

#### Customer Accounts
- **Username**: `Dharani`
- **Password**: `Ds`

#### Employee Accounts
- **Username**: `ram`
- **Password**: `pingu`

### 🛠️ Setup Instructions

1. **Start XAMPP/WAMP** services (Apache & MySQL)

2. **Place files** in your htdocs/www directory:
   ```
   C:\xampp\htdocs\GROCERY-STORE-MANAGEMENT-SYSTEM-USING-PHP-AND-MYSQL-PHPMYADMIN--master\
   ```

3. **Run Database Setup** (if not already done):
   ```
   http://localhost/GROCERY-STORE-MANAGEMENT-SYSTEM-USING-PHP-AND-MYSQL-PHPMYADMIN--master/setup_database.php
   ```

4. **Access the System**:
   ```
   http://localhost/GROCERY-STORE-MANAGEMENT-SYSTEM-USING-PHP-AND-MYSQL-PHPMYADMIN--master/Grocery/index1.html
   ```

### 🎯 User Roles & Features

#### 👨‍💼 Admin Features
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
