# 🛍️ E-Store - Professional Online Shopping Platform

A full-featured PHP/MySQL e-commerce platform with user accounts, product management, shopping cart, secure checkout, and order tracking system.

**Status:** ✅ Production Ready | 📦 Order Tracking Included | 📧 Email Notifications

---

## 📂 Project Structure

```
e-store/
├── 📄 docs/                 # 📚 All documentation
│   ├── README.md            # Complete project overview
│   ├── QUICK_START.md       # 5-minute setup guide
│   ├── ORDER_TRACKING.md    # Order tracking system
│   ├── SYSTEM_OVERVIEW.md   # Architecture & flows
│   ├── TESTING_GUIDE.md     # Testing procedures
│   └── ... (more docs)
│
├── 🔧 setup/                # Setup & configuration files
│   ├── database.sql         # Database schema
│   ├── composer.json        # PHP dependencies
│   ├── .env.example         # Environment template
│   └── push-to-github.ps1   # GitHub deployment script
│
├── 🏪 public/               # Customer-facing pages
│   ├── home.php
│   ├── shop.php
│   ├── cart.php
│   ├── checkout.php
│   ├── order-tracking.php   # ✨ NEW: Track orders
│   └── uploads/
│
├── 👨‍💼 admin/               # Admin dashboard
│   ├── dashboard.php
│   ├── orders.php
│   ├── products/
│   └── users.php
│
├── 🔐 auth/                 # Authentication
│   ├── login.php
│   ├── register.php
│   └── logout.php
│
├── 📱 app/                  # Core application logic
│   ├── bootstrap.php
│   └── core/
│       ├── Database.php
│       └── Email.php        # ✨ NEW: Email service
│
├── ⚙️ config/              # Configuration files
│   └── pdo.php
│
├── 🎨 assets/              # Static files (CSS, JS, images)
│   └── css/
│       └── style.css
│
├── 📋 includes/            # Reusable templates
│   ├── header.php
│   ├── footer.php
│   └── sidebar.php
│
├── 📦 tmp/                 # Temporary files
│   └── uploads/
│
├── 🛒 products/            # Product management utilities
├── index.php               # Landing page
├── add_admin.php           # Admin creation utility
└── .env, .gitignore        # Environment & Git config
```

---

## 🎯 Quick Start

📖 **Start here:** [docs/QUICK_START.md](docs/QUICK_START.md) - Complete 5-minute setup guide

### For Admins
- Dashboard: `/admin/dashboard.php`
- Manage Orders: `/admin/orders.php`
- Manage Products: `/admin/products/`

### For Customers
- Home: `/public/home.php`
- Shop: `/public/shop.php`
- Orders: `/public/orders.php`
- Track Order: `/public/order-tracking.php`

---

## 📚 Documentation

| Document | Purpose |
|----------|---------|
| [README.md](docs/README.md) | 📖 Complete project overview |
| [QUICK_START.md](docs/QUICK_START.md) | ⚡ 5-minute setup |
| [ORDER_TRACKING.md](docs/ORDER_TRACKING.md) | 📦 Order tracking system |
| [SYSTEM_OVERVIEW.md](docs/SYSTEM_OVERVIEW.md) | 🏗️ Architecture & design |
| [TESTING_GUIDE.md](docs/TESTING_GUIDE.md) | 🧪 Testing procedures |
| [GITHUB_PUSH.md](docs/GITHUB_PUSH.md) | 🚀 GitHub deployment |

---

## 🔑 Default Credentials

```
Email:    admin@example.com
Password: admin
```

---

## 🚀 Setup Steps

1. **Import Database**
   ```bash
   mysql -u root < setup/database.sql
   ```

2. **Configure Environment**
   ```bash
   cp setup/.env.example .env
   ```

3. **Access Application**
   ```
   http://localhost/projet_php/e-store/public/home.php
   ```

4. **Read Documentation**
   - Start with: `docs/QUICK_START.md`
   - Full guide: `docs/README.md`

---

## ✨ Key Features

✅ **Shopping**
- Browse products by category
- Advanced search & filtering
- Shopping cart
- Secure checkout

✅ **Order Management**
- Real-time order tracking with timeline
- Email notifications
- Order history
- Admin dashboard

✅ **Admin Panel**
- Product management
- Order management
- User management
- Sales analytics
- Geolocation insights

✅ **Security**
- Password hashing (bcrypt)
- SQL injection prevention
- XSS protection
- Session security
- Role-based authorization

---

## 🔒 Security

- ✅ Prepared statements (SQL injection prevention)
- ✅ Output escaping (XSS protection)
- ✅ bcrypt password hashing
- ✅ Session regeneration on login
- ✅ Role-based access control
- ✅ Email validation

---

## 📧 Email System

Automatic emails for:
- Order confirmation
- Shipment notifications
- Delivery confirmation

**Location:** `app/core/Email.php`

---

## 🛠️ Tech Stack

- **Backend:** PHP 7.4+
- **Database:** MySQL 5.7+
- **Frontend:** HTML5, CSS3, Bootstrap 5
- **Email:** PHP mail()
- **Architecture:** MVC-style

---

## 📊 Database Tables

- `users` - Customer & admin accounts
- `products` - Product inventory
- `categories` - Product categories
- `orders` - Customer orders
- `order_items` - Items per order
- `order_status_history` - Order tracking
- `product_views` - Analytics

---

## 🐛 Troubleshooting

### Database Error
→ See: [docs/QUICK_START.md](docs/QUICK_START.md#troubleshooting)

### Cannot Login
→ Use: `admin@example.com` / `admin`

### Emails Not Sending
→ Check PHP mail config in `php.ini`

---

## 📖 Full Documentation

**All docs are in the `docs/` folder:**
```
docs/
├── README.md                    # Complete overview
├── QUICK_START.md              # Setup guide
├── ORDER_TRACKING.md           # Order system
├── SYSTEM_OVERVIEW.md          # Architecture
├── TESTING_GUIDE.md            # Testing
├── GITHUB_PUSH.md              # GitHub workflow
└── ... (more)
```

---

## 🚀 Getting Started

**👉 Start here:** [docs/QUICK_START.md](docs/QUICK_START.md)

Then read: [docs/README.md](docs/README.md)

---

## 🎓 Project Goals

✅ Learn PHP/MySQL web development
✅ Build real e-commerce features
✅ Practice security best practices
✅ Deploy to production
✅ Master order tracking systems

---

**Status:** ✅ Production Ready

Made with ❤️ for e-commerce learning
