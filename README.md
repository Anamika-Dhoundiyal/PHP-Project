# Grocery Store Management System

A comprehensive web-based grocery store management system built with **PHP** and **MySQL**. This project demonstrates full-stack web development with user authentication, product management, shopping cart functionality, and order processing.

---

## 🌟 Features

### 🛒 Customer Features
- **User Authentication**: Secure registration and login system
- **Product Browsing**: Browse all products with details and images
- **Advanced Search**: Search products by name, category, and price range
- **Shopping Cart**: Add/remove items, update quantities
- **Checkout Process**: Complete order placement with delivery details
- **Order Tracking**: View order history and track orders
- **Account Management**: View and update customer profile

### 🛠️ Admin Features
- **Product Management**: Add, update, delete products with images
- **Inventory Control**: Manage product quantities and prices
- **Order Management**: View and process customer orders
- **Transaction Reports**: View all system transactions
- **Dashboard**: Overview of key metrics
- **Advanced Controls**: Full system administration

---

## 📋 System Requirements

- **Server**: Apache (included in XAMPP/WAMP)
- **PHP**: Version 7.0 or higher
- **Database**: MySQL 5.6+ or MariaDB
- **Browser**: Modern browser with JavaScript enabled

---

## 🚀 Installation & Setup

### Step 1: Prerequisites
Install [XAMPP](https://www.apachefriends.org/) or WAMP and start Apache & MySQL services.

### Step 2: Clone/Download Project
```bash
git clone https://github.com/Anamika-Dhoundiyal/PHP-Project.git
cd GROCERY-STORE-MANAGEMENT-SYSTEM
```

### Step 3: Database Setup
1. Open **phpMyAdmin**: `http://localhost/phpmyadmin`
2. Create a new database named **`grocery_store`**
3. Import the schema: `grocery_store_schema.sql`
4. Run these SQL commands to set up test credentials:

```sql
-- Insert Admin Account
INSERT INTO admin (username, password) VALUES ('Admin', MD5('123'));

-- Insert Customer Account
INSERT INTO customers (name, email, password, username) 
VALUES ('Anamika', 'anamika@example.com', MD5('123'), 'Anamika');
```

### Step 4: Configure Database Connection
Edit `Grocery/db_connection.php`:
```php
$server = "localhost";
$username = "root";
$password = "";  // Leave empty if no password
$database = "grocery_store";
```

### Step 5: Access the Application
- **Homepage**: `http://localhost/GROCERY-STORE-MANAGEMENT-SYSTEM/Grocery/index.php`
- **Admin Panel**: `http://localhost/GROCERY-STORE-MANAGEMENT-SYSTEM/Grocery/admin_login.php`
- **Customer Login**: `http://localhost/GROCERY-STORE-MANAGEMENT-SYSTEM/Grocery/customer_login.php`

---

## 🔐 Default Login Credentials

### Admin Account
| Field | Value |
|-------|-------|
| **Username** | `Admin` |
| **Password** | `123` |

### Customer Account
| Field | Value |
|-------|-------|
| **Username** | `Anamika` |
| **Password** | `123` |
| **Email** | anamika@example.com |

---

## 📂 Project Structure

```
GROCERY-STORE-MANAGEMENT-SYSTEM/
├── Grocery/                          # Main application folder
│   ├── index.php                    # Homepage
│   ├── admin_login.php              # Admin login
│   ├── customer_login.php           # Customer login
│   ├── admin_dashboard.php          # Admin control panel
│   ├── admin_products.php           # Product management
│   ├── admin_orders.php             # Order management
│   ├── products.php                 # Product catalog
│   ├── cart.php                     # Shopping cart
│   ├── checkout.php                 # Checkout process
│   ├── orders.php                   # Customer orders
│   ├── track_order.php              # Order tracking
│   ├── account.php                  # Customer account
│   ├── db_connection.php            # Database configuration
│   ├── cart_functions.php           # Cart utilities
│   ├── css/                         # Stylesheets
│   ├── js/                          # JavaScript files
│   ├── images/                      # Product images
│   └── partials/                    # Reusable components
├── grocery_store_schema.sql         # Database schema
├── README.md                        # This file
└── LICENSE                          # MIT License
```

---

## 💡 Key Technologies

- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap
- **Backend**: PHP (OOP & Procedural)
- **Database**: MySQL
- **Version Control**: Git & GitHub

---

## 📊 Database Schema

### Main Tables

| Table | Purpose |
|-------|---------|
| **customers** | Customer user accounts |
| **products** | Product catalog |
| **admin** | Admin user accounts |
| **cart** | Shopping cart items |
| **orders** | Customer orders |
| **purchase** | Transaction records |

---

## 🎯 How to Use

### For Customers
1. Go to homepage: `index.php`
2. Register or login with credentials
3. Browse and search products
4. Add items to cart
5. Proceed to checkout
6. Track orders in "Orders" section

### For Administrators
1. Login with admin credentials
2. Access dashboard for overview
3. Manage products in "Products" section
4. Process orders in "Orders" section
5. View all transactions in reports

---

## 🔒 Security Features

✅ Password hashing with MD5  
✅ Session-based authentication  
✅ SQL injection prevention (prepared statements)  
✅ Input validation and sanitization  
✅ User role-based access control  

---

## 🚀 Deployment

### Ready for Production?
- ✅ Clean, organized codebase
- ✅ All debug files removed
- ✅ Proper error handling
- ✅ Database schema included
- ✅ Git version control configured

### Deploy to Hosting
1. Upload files via FTP
2. Create MySQL database
3. Import `grocery_store_schema.sql`
4. Update database credentials in `db_connection.php`
5. Set up SSL/HTTPS

---

## 🐛 Troubleshooting

| Issue | Solution |
|-------|----------|
| **Database Connection Error** | Verify credentials in `db_connection.php` |
| **Images Not Showing** | Check `Grocery/images/` folder exists |
| **Login Fails** | Verify username exists in database |
| **404 Errors** | Check file paths and URL structure |

---

## 📝 Features Demonstrated

✅ User authentication & authorization  
✅ CRUD operations (Create, Read, Update, Delete)  
✅ Session management  
✅ Database relationships  
✅ Shopping cart functionality  
✅ Order processing  
✅ Responsive design  
✅ File uploads (product images)  
✅ Search & filtering  
✅ Admin dashboard  

---

## 🤝 Contributing

Contributions are welcome! To contribute:
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Commit and push
5. Submit a pull request

---

## 📄 License

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

---

## 👤 Author

**Anamika Dhoundiyal**
- GitHub: [@Anamika-Dhoundiyal](https://github.com/Anamika-Dhoundiyal)
- Email: dhoundiyalanamika06@gmail.com

---

## 🎓 Learning Value

This project is perfect for learning:
- PHP web development
- MySQL database design
- Authentication & authorization
- E-commerce concepts
- Git & GitHub workflow
- Full-stack development

---

## 📞 Support

For issues or questions:
1. Check the troubleshooting section
2. Review the database schema
3. Check browser console for errors
4. Create an issue on GitHub

---

**Last Updated**: December 11, 2025

**Status**: ✅ Production Ready | 🚀 Portfolio Quality


