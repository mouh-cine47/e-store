# 🛍️ E-Store - Professional Online Shopping Platform

A full-featured PHP/MySQL e-commerce platform with user accounts, product management, shopping cart, secure checkout, and order tracking system.

**Status:** ✅ Production Ready | 📦 Order Tracking Included | 📧 Email Notifications

---

## 🎯 Features

### For Customers
- ✅ **Browse Products** : Categories, search, filters
- ✅ **Shopping Cart** : Add/remove items, manage quantities
- ✅ **Secure Checkout** : Shipping address, order placement
- ✅ **Order Tracking** : Real-time status updates with timeline
- ✅ **Email Notifications** : Order confirmation and status updates
- ✅ **User Account** : Registration, login, order history

### For Admins
- ✅ **Dashboard** : Sales analytics, quick stats
- ✅ **Product Management** : Add, edit, delete products
- ✅ **Category Management** : Organize products
- ✅ **Order Management** : View and update order status
- ✅ **User Management** : View and manage customers
- ✅ **Geolocation Stats** : Analyze customer locations

---

## 📦 Project Structure

```
e-store/
├── public/              # Customer-facing pages
│   ├── home.php         # Homepage
│   ├── shop.php         # Product listing
│   ├── product.php      # Product details
│   ├── cart.php         # Shopping cart
│   ├── checkout.php     # Checkout process
│   ├── order-tracking.php # Track orders
│   └── ...
├── admin/               # Admin dashboard
│   ├── dashboard.php    # Main dashboard
│   ├── orders.php       # Order management
│   ├── products/        # Product management
│   ├── categories.php   # Category management
│   └── users.php        # User management
├── auth/                # Authentication
│   ├── login.php        # Login page
│   ├── register.php     # Registration
│   └── logout.php       # Logout
├── app/                 # Core application
│   ├── bootstrap.php    # App initialization
│   └── core/
│       ├── Database.php # Database connection
│       └── Email.php    # Email service
├── config/              # Configuration
│   └── pdo.php          # Database config
├── includes/            # Reusable templates
│   ├── header.php       # Header template
│   ├── footer.php       # Footer template
│   └── ...
├── assets/              # Static files
│   └── css/
│       └── style.css    # Styling
├── tmp/uploads/         # Temporary files
├── database.sql         # Database schema
└── README.md            # This file
```

---

## 🚀 Quick Start

### Prerequisites
- **XAMPP** (Apache + MySQL + PHP 7.4+)
- **Git** (optional, for cloning)
- Modern web browser

### Installation (5 minutes)

#### 1. Clone or Download
```bash
git clone https://github.com/YOUR_USERNAME/e-store.git
cd e-store
```

#### 2. Import Database
```bash
mysql -u root -p inventory_db < database.sql
```

Or use phpMyAdmin:
- Create database: `inventory_db`
- Import `database.sql`

#### 3. Access the Application
```
Customers: http://localhost/projet_php/e-store/public/home.php
Admins:    http://localhost/projet_php/e-store/admin/dashboard.php
```

#### 4. Login
```
Email:    admin@example.com
Password: admin
```

---

## 🔑 Default Credentials

**Admin Account:**
- Email: `admin@example.com`
- Password: `admin`
- Role: `admin`

**Register new customer accounts** at: `/auth/register.php`

---

## 📖 Documentation

- **[QUICK_START.md](QUICK_START.md)** - 5-minute setup guide
- **[ORDER_TRACKING.md](ORDER_TRACKING.md)** - Order tracking system
- **[SYSTEM_OVERVIEW.md](SYSTEM_OVERVIEW.md)** - System architecture
- **[GITHUB_PUSH.md](GITHUB_PUSH.md)** - GitHub deployment
- **[TESTING_GUIDE.md](TESTING_GUIDE.md)** - Testing procedures

---

## 🎓 Common Tasks

### For Customers

1. **Create Account**
   - Go to `/auth/register.php`
   - Fill in details
   - Login

2. **Browse & Shop**
   - Visit `/public/home.php`
   - Browse by category or search
   - Add items to cart
   - Checkout

3. **Track Order**
   - Login to account
   - Go to "Orders" section
   - View order timeline and status

### For Admins

1. **Add Product**
   - Login as admin
   - Go to Products → Add Product
   - Fill in details
   - Upload image
   - Save

2. **Manage Orders**
   - Go to Orders
   - View customer details
   - Update status (pending → shipped → delivered)
   - Customers receive email automatically

3. **View Analytics**
   - Dashboard shows sales stats
   - Top selling products
   - Recent orders
   - Geolocation insights

---

## 🔒 Security Features

- ✅ **Password Hashing** : bcrypt encryption
- ✅ **SQL Injection Prevention** : Prepared statements
- ✅ **XSS Protection** : Output escaping
- ✅ **Session Security** : Regeneration on login
- ✅ **Authorization** : Role-based access control
- ✅ **Email Validation** : Secure email system

---

## 📊 Key Technologies

- **Backend** : PHP 7.4+
- **Database** : MySQL 5.7+
- **Frontend** : HTML5, CSS3, Bootstrap 5
- **Email** : PHP mail() function
- **Database Design** : Normalized relational schema

---

## 📧 Email System

The system sends automatic emails for:
- ✉️ Order confirmation (when placed)
- 📦 Shipment notification (status: shipped)
- ✅ Delivery confirmation (status: delivered)

**Configuration:**
- Located in: `app/core/Email.php`
- Transport: PHP `mail()` function
- Format: HTML templates
- No external dependencies

---

## 🚨 Troubleshooting

### Database Connection Error
```
Solution: Check MySQL is running
         Verify DB credentials in config/pdo.php
         Run database.sql to create tables
```

### Cannot Login
```
Solution: Make sure database is imported
         Use admin@example.com / admin
         Check users table exists
```

### Emails Not Sending
```
Solution: Check PHP mail configuration in php.ini
         Look for errors in error.log
         Verify email format is correct
```

### File Upload Issues
```
Solution: Check tmp/uploads/ directory exists
         Verify folder permissions (chmod 755)
         Check MAX_FILE_SIZE in .env
```

---

## 📈 Database Schema

**Main Tables:**
- `users` - Customer and admin accounts
- `products` - Product inventory
- `categories` - Product categories
- `orders` - Customer orders
- `order_items` - Items in each order
- `order_status_history` - Order tracking timeline
- `product_views` - Analytics data

---

## 🎯 Project Goals

✅ Learn PHP/MySQL web development
✅ Understand e-commerce workflows
✅ Practice security best practices
✅ Build real-world features (cart, checkout, tracking)
✅ Deploy to production

---

## 🤝 Contributing

This is a student project. Feel free to fork and improve!

---

## 📄 License

Educational use. Student project created in 2026.

---

**Ready to start?** See [QUICK_START.md](QUICK_START.md) for detailed setup instructions.
