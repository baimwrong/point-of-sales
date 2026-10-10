# POS System — Point of Sales Web Application

A production-ready, sophisticated Point of Sales (POS) web application built with:
- **Laravel 13** (and Laravel 12 LTS / PHP 8.2+)
- **PostgreSQL 18** (fully compatible with PostgreSQL 16, 17, and 18)
- **Bootstrap 5.3.3** (100% offline — downloaded locally)
- **Font Awesome 6.6.0** (100% offline — all webfonts downloaded locally)
- **Alpine.js 3.14.1** (100% offline — reactive POS checkout terminal)
- **Chart.js 4.4.4** (100% offline — interactive revenue & sales dashboards)
- **SweetAlert2 11.x**, **Select2 4.x**, & **jQuery 3.7.1** (100% offline)

All frontend assets are stored directly in `public/vendor/` — **no internet connection is required** to run the UI in an isolated internal LAN / offline store network.

---

## ✅ System Requirements

| Component | Target Version | Compatibility Notes |
|---|---|---|
| **Framework** | **Laravel 13 / 12** | Uses standard Laravel 12/13 structure (`bootstrap/app.php`, action closures, PSR-4) |
| **Database** | **PostgreSQL 18** | Supports PostgreSQL 16, 17, and 18 via native `pdo_pgsql` driver |
| **PHP Runtime** | **PHP 8.2 or higher** | Required extensions: `pdo_pgsql`, `zip`, `xml`, `mbstring`, `bcmath`, `fileinfo` |
| **Dependency Manager** | **Composer 2.x** | Optimized autoloader with PSR-4 |
| **Network** | **Offline / Intranet** | Zero CDN calls; completely self-hosted in `public/vendor/` |

---

## 🚀 Installation & Deployment Guide

### Step 1: Prepare PostgreSQL 18

Log in to your PostgreSQL 18 instance using `psql` or pgAdmin:

```sql
-- 1. Create dedicated user
CREATE USER pos_user WITH ENCRYPTED PASSWORD 'your_secure_password';

-- 2. Create the POS database
CREATE DATABASE pos_db OWNER pos_user;

-- 3. Grant full privileges
GRANT ALL PRIVILEGES ON DATABASE pos_db TO pos_user;

-- 4. Switch to pos_db and grant schema privileges (PostgreSQL 15+ convention)
\c pos_db
GRANT ALL ON SCHEMA public TO pos_user;
```

### Step 2: Configure Environment Variables

Edit your `.env` file in the project root:

```env
APP_NAME="POS System"
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-server-ip/point-of-sales/public

# Database Configuration for PostgreSQL 18
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pos_db
DB_USERNAME=pos_user
DB_PASSWORD=your_secure_password
DB_SSLMODE=prefer

# Session & Cache (File-based for high speed without Redis dependency)
SESSION_DRIVER=file
SESSION_LIFETIME=480
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

### Step 3: Install PHP Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

### Step 4: Generate Application Key

```bash
php artisan key:generate
```

### Step 5: Run Database Migrations & Seed Default Data

```bash
php artisan migrate --seed
```

This will automatically create all tables and populate initial roles, admin user, cashier user, default units, Indonesian categories, default settings, and sample products.

### Step 6: Create Storage Symlink

```bash
php artisan storage:link
```

### Step 7: Set Directory Permissions (Linux / macOS)

```bash
chmod -R 775 storage bootstrap/cache public/images
chown -R www-data:www-data storage bootstrap/cache public/images
```

### Step 8: Production Optimization (Recommended)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 🔑 Default Login Credentials

| Role | Email | Default Password | Permissions |
|---|---|---|---|
| **Administrator** | `admin@pos.local` | `password` | Full system access (products, users, settings, purchases, reports) |
| **Cashier** | `cashier@pos.local` | `password` | POS terminal, customers, sales history, print receipts |

> ⚠️ **Important Security Notice**: Always change default passwords immediately after initial login via the **My Profile** menu (`/profile`).

---

## 📱 Application Modules & Route Map

| Module | URL | Allowed Roles | Description |
|---|---|---|---|
| **Login** | `/login` | Public | Authentication with session remember & password toggle |
| **Dashboard** | `/dashboard` | All | Today's revenue, sales chart, top sellers, low stock alerts |
| **POS Terminal** | `/pos` | All | Real-time cart, barcode scanner, multi-payments, discount & tax |
| **Print Receipt** | `/pos/receipt/{id}` | All | 80mm thermal receipt formatted layout |
| **Products** | `/products` | All | Product catalog with stock levels, cost, and selling price |
| **Bulk Import Excel** | `/products/import` | Admin, Manager | **Bulk upload `.xlsx` / `.csv` spreadsheets with duplicate update toggle** |
| **Export Catalog** | `/products/export` | Admin, Manager | **Export all products to Excel/CSV spreadsheet** |
| **Sample Template** | `/products/import/template` | Admin, Manager | **Download pre-formatted import CSV template for Excel** |
| **Categories** | `/categories` | Admin, Manager | Hierarchical categories with parent-child support |
| **Units** | `/units` | Admin, Manager | Measurement units (pcs, kg, gram, liter, box, botol, etc.) |
| **Suppliers** | `/suppliers` | Admin, Manager | Vendor directory with contact details & order history |
| **Customers** | `/customers` | All | Customer directory with loyalty points & lifetime spend |
| **Sales History** | `/sales` | All | Detailed sales audit trail with CSV export |
| **Purchase Orders** | `/purchases` | Admin, Manager | Inventory procurement with dynamic line items & one-click stock receive |
| **Returns / Refunds** | `/returns` | All | Process returned goods with stock restoration & cash/credit refund |
| **Sales Analytics** | `/reports/sales` | Admin, Manager | Revenue breakdown by date & payment with bar chart & CSV export |
| **Product Analytics** | `/reports/products`| Admin, Manager | Best-selling products ranking by units sold & revenue |
| **Inventory Report** | `/reports/inventory`| Admin, Manager | Asset cost valuation, depleted items & low stock warnings |
| **User Accounts** | `/users` | Admin only | Create cashiers/managers with one-click status toggle |
| **My Profile** | `/profile` | All | Update user name, email, phone, and password |
| **Store Settings** | `/settings` | Admin only | Store branding, address, tax rate, document prefixes, thermal footer |

---

## 📊 Excel & CSV Bulk Product Import Guide

Admins can quickly populate or update thousands of products in bulk:

1. Go to **Products** &rarr; click **Import Excel/CSV** (`/products/import`).
2. Click **Download Sample Excel/CSV** to get the template.
3. Fill in your data in Microsoft Excel or any spreadsheet software:
   - `name`: Product name *(Required)*
   - `sku`: Unique item SKU *(Optional, auto-generated if blank)*
   - `barcode`: Barcode string for scanner *(Optional)*
   - `category`: Category name *(Automatically created if not existing)*
   - `unit`: Unit abbreviation, e.g. `pcs`, `kg`, `box` *(Optional)*
   - `cost_price`: Buy price / harga modal *(Optional, e.g. 25000)*
   - `selling_price`: Sell price / harga jual *(Optional, e.g. 35000)*
   - `wholesale_price`: Wholesale price *(Optional)*
   - `stock_quantity`: Initial stock quantity *(Optional)*
   - `min_stock_alert`: Low stock alert threshold *(Optional)*
   - `description`: Notes or specifications *(Optional)*
4. Select your `.xlsx` or `.csv` file.
5. Choose whether to **"Update existing products if SKU matches"** (checked = update prices/stock; unchecked = skip duplicates).
6. Click **Upload and Import Products**. A detailed report showing added, updated, and skipped rows will be displayed.

---

## 🖨️ Thermal Receipt Printing

- The receipt view ([`resources/views/pos/receipt.blade.php`](file:///c:/xampp/htdocs/point-of-sales/resources/views/pos/receipt.blade.php)) is formatted for standard **80mm thermal receipt printers**.
- Features auto-calculated subtotal, tax amount, itemized discounts, payment method, tendered cash, change returned, cashier name, customer name, and configurable receipt footer text.
- Supports browser printing dialog (`window.print()`) with print CSS (`@media print`) that removes browser margins, headers, and navigation bars automatically.

---

## 🌐 Web Server Configuration

### Nginx (Production Linux)

```nginx
server {
    listen 80;
    server_name pos.yourdomain.local;
    root /var/www/point-of-sales/public;

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Apache (XAMPP / Windows / Linux)

```apache
<VirtualHost *:80>
    DocumentRoot "c:/xampp/htdocs/point-of-sales/public"
    ServerName pos.local

    <Directory "c:/xampp/htdocs/point-of-sales/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

---

## 🏗️ Project Architecture & File Structure

```
point-of-sales/
├── app/
│   ├── Helpers/
│   │   └── PosHelper.php                # Currency formatting (Rp), numbering (INV-, PO-, RTN-)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/LoginController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── PosController.php        # POS checkout engine & thermal receipt
│   │   │   ├── ProductController.php
│   │   │   ├── ProductImportController.php # Excel & CSV bulk import/export
│   │   │   ├── CategoryController.php
│   │   │   ├── CustomerController.php
│   │   │   ├── SupplierController.php
│   │   │   ├── UnitController.php
│   │   │   ├── SaleController.php
│   │   │   ├── PurchaseController.php
│   │   │   ├── ReturnController.php
│   │   │   ├── ReportController.php
│   │   │   ├── UserController.php
│   │   │   ├── ProfileController.php
│   │   │   └── SettingController.php
│   │   ├── Middleware/
│   │   │   └── RoleMiddleware.php       # Role-based access control (admin, manager, cashier)
│   │   └── Requests/                    # Form validation rules
│   ├── Models/                          # Eloquent models with scopes & accessors
│   │   ├── ActivityLog.php
│   │   ├── Category.php
│   │   ├── Customer.php
│   │   ├── Product.php
│   │   ├── Purchase.php
│   │   ├── Role.php
│   │   ├── Sale.php
│   │   ├── SaleReturn.php
│   │   ├── Setting.php
│   │   ├── Supplier.php
│   │   ├── Unit.php
│   │   └── User.php
│   └── Services/
│       └── SpreadsheetService.php       # Native XLSX OpenXML & CSV spreadsheet parser
├── config/
│   └── database.php                     # PostgreSQL 18 connection settings
├── database/
│   ├── migrations/                      # Clean PostgreSQL DDL migrations
│   └── seeders/                         # Database seeders (roles, users, catalog, settings)
├── public/
│   ├── images/                          # Product images and avatars storage
│   └── vendor/                          # 🔌 100% Offline downloaded frontend assets
│       ├── alpinejs/                    # Alpine.js 3.14.1
│       ├── bootstrap/                   # Bootstrap 5.3.3 (CSS + JS bundle)
│       ├── chartjs/                     # Chart.js 4.4.4
│       ├── fontawesome/                 # Font Awesome 6.6.0 (CSS + webfonts)
│       ├── jquery/                      # jQuery 3.7.1
│       ├── select2/                     # Select2 4.x
│       └── sweetalert2/                 # SweetAlert2 11.x
├── resources/views/                     # All Blade UI views
└── routes/
    └── web.php                          # Complete 85 routes map
```

---

*Production-Ready Point of Sales System — Built for Laravel 13 & PostgreSQL 18*
