# ⚡ Quick Start Guide - E-Store

## 🎯 What is E-Store?
Professional online shopping platform with user accounts, product management, shopping cart, order tracking, and more.

## 📋 Prerequisites
- XAMPP (or any LAMP server)
- PHP 7.4+
- MySQL 5.7+
- Modern web browser

## 🚀 Getting Started in 5 Minutes

### Step 1: Setup Database
```bash
# Import the database schema
mysql -u root -p inventory_db < database.sql
```

### Step 2: Access the Application
```
Customers: http://localhost/projet_php/e-store/public/home.php
Admins:    http://localhost/projet_php/e-store/admin/dashboard.php
```

### Step 3: Login Credentials
```
Email:    admin@example.com
Password: admin
```

## 🛍️ Key Features

### For Customers
- 🏪 Browse products by category
- 🛒 Shopping cart management  
- 💳 Secure checkout
- 📦 Order tracking with timeline
- 📧 Email notifications
- 👤 User account management

### For Admins
- 📊 Dashboard with sales analytics
- 📦 Manage products
- 🏷️ Manage categories
- 👥 Manage users
- 📋 View and update orders
- 🌍 View geolocation stats

## 📂 Directory Structure
```
public/           - Customer-facing pages
  ├─ home.php        - Homepage
  ├─ shop.php        - Product listing
  ├─ product.php     - Product details
  ├─ cart.php        - Shopping cart
  ├─ checkout.php    - Checkout process
  ├─ order-tracking.php - Order tracking
  └─ orders.php      - Order history

admin/            - Admin dashboard
  ├─ dashboard.php   - Main dashboard
  ├─ orders.php      - Order management
  ├─ products/       - Product management
  ├─ categories.php  - Category management
  └─ users.php       - User management

auth/             - Authentication
  ├─ login.php       - Login page
  ├─ register.php    - Registration
  └─ logout.php      - Logout

app/              - Core application
  ├─ bootstrap.php   - Initialize app
  └─ core/
      ├─ Database.php - Database connection
      └─ Email.php    - Email service

config/           - Configuration
  └─ pdo.php         - Database config
```

## 🔑 Important Files to Know
- `database.sql` - Database schema
- `.env` - Environment variables
- `composer.json` - Dependencies

## 💡 Common Tasks

### Register a New Customer
1. Go to `http://localhost/projet_php/e-store/auth/register.php`
2. Fill in name, email, password
3. Click "Register"
4. Login with credentials

### Add a Product (Admin)
1. Login as admin
2. Go to Products → Add Product
3. Fill in product details
4. Upload product image
5. Click Save

### Track an Order (Customer)
1. Login as customer
2. Go to "Orders" in navigation
3. Click on an order
4. See full timeline of status changes

## 📧 Email Notifications
The system sends automatic emails for:
- ✉️ Order confirmation (when placed)
- 📦 Shipment notification (status change)
- ✅ Delivery confirmation

Emails are sent via PHP `mail()` function (configure in `php.ini`)

## 🔒 Security Features
- ✅ Password hashing (bcrypt)
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS protection (output escaping)
- ✅ Session security (regeneration on login)
- ✅ Authorization checks (role-based access)

## 📊 Documentation
- `ORDER_TRACKING.md` - Order tracking feature
- `SYSTEM_OVERVIEW.md` - System architecture
- `TESTING_GUIDE.md` - Testing procedures
- `README.md` - Project overview

## ⚡ Troubleshooting

### Database Connection Error?
1. Check MySQL is running
2. Verify DB credentials in `config/pdo.php`
3. Run `database.sql` to create tables

### Cannot Login?
1. Make sure database is imported
2. Use `admin@example.com` / `admin`
3. Check if users table exists

### Emails not sending?
1. Check PHP mail configuration
2. Look for errors in error.log
3. Verify email format is correct

## 🎓 Next Steps
1. Explore the admin dashboard
2. Create some test products
3. Test the shopping experience
4. Check the order tracking feature

---

**Need more help?** See README.md or the documentation files listed above.
