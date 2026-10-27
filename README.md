# Campus Second-Hand Marketplace

A full-stack, responsive web application designed for college/university campuses where students can list, browse, search, and negotiate prices on pre-owned textbooks, gadgets, and hostel essentials.

---

## 🌟 Key Features

1. **Authentication & Campus Verification**
   - Registration with institutional `.edu` / university email validation.
   - Secure password hashing (`PASSWORD_BCRYPT`).
   - Session management with active user display and CSRF protection on all forms.
   - Pre-seeded demo student accounts for rapid testing.

2. **Listing Management**
   - Create new listings with image uploads (automatic fallback to styled placeholders if no image is uploaded).
   - Dynamic key-value technical specifications stored in MySQL `JSON` column (`product_data`).
   - Edit listing details and update status: `Available`, `Reserved`, `Sold`, `Archived`.
   - Automatic archiving timestamp tracking (`archived_at`).

3. **Search, Filter & Browse**
   - Full text search across title, description, and seller name.
   - Filter by Category (Textbooks, Electronics, Hostel Essentials, Lab Equipment, Sports, Other).
   - Filter by Condition (Brand New, Like New, Good, Fair).
   - Filter by Price Range (Min/Max).
   - Sort by Newest, Oldest, Price: Low to High, Price: High to Low.
   - Server-side pagination with query string preservation.

4. **Interactive Offer & Price Negotiation System**
   - Make offers directly from the product page with custom message.
   - Seller inbox (`received-offers.php`) to **Accept**, **Reject**, or propose a **Counter-Offer**.
   - Buyer tracking (`my-offers.php`) to monitor proposal status and withdraw pending offers.
   - Counter-offer threading via `parent_offer_id` and AJAX real-time updates.
   - Accepting an offer automatically sets the product status to `Reserved` and declines competing pending offers inside an atomic DB transaction.

5. **Modern, Responsive Dark-Themed UI**
   - Crafted with Bootstrap 5 and custom CSS.
   - Glassmorphism touches, vibrant gold accent (`#f59e0b`), smooth animations, and toast notifications.

---

## 📂 Project Architecture

```
campus-marketplace/
├── api/
│   ├── offers.php            # AJAX endpoint for creating, accepting, rejecting & cancelling offers
│   └── counteroffers.php     # AJAX endpoint for submitting counter-offers
├── assets/
│   ├── css/
│   │   └── style.css         # Modern dark-mode custom stylesheet
│   └── js/
│       └── app.js            # Toast notifications, modal handlers, AJAX offer actions
├── config/
│   └── database.php          # PDO connection singleton & credentials
├── database/
│   └── schema.sql            # MySQL schema with users, products, offers & seed data
├── includes/
│   ├── auth.php              # Session checks, login/logout helpers, user getters
│   ├── csrf.php              # Anti-CSRF token generation & validation
│   ├── footer.php            # Footer template & JavaScript bundle scripts
│   ├── functions.php         # Formatting, categories, badges, image helpers, flash messages
│   └── header.php            # Navbar, toast container, flash alerts & responsive menu
├── browse.php                # Marketplace browsing with multi-filter sidebar & pagination
├── create-listing.php        # Post a new second-hand product with specifications
├── dashboard.php             # Student dashboard with live KPI counters & quick actions
├── edit-listing.php          # Edit listing & update status
├── index.php                 # Landing page with hero, live search, categories & featured items
├── login.php                 # Student login page with demo account switcher
├── logout.php                # Session destruction & redirect
├── my-listings.php           # Seller's listings table filtered by status tabs
├── my-offers.php             # Buyer's sent offers table with withdraw actions
├── product-details.php       # Product page with specification cards & offer negotiation history
├── profile.php               # Student account settings, verification badge, and password change
├── received-offers.php       # Seller's offer inbox with Accept/Reject/Counter actions
└── register.php              # Campus registration page with institutional email validation
```

---

## 🚀 Setup & Installation Instructions

### 1. Requirements
- PHP 8.0+ with `pdo_mysql` extension enabled.
- MySQL 5.7+ or MariaDB 10.3+ (requires JSON support).
- Apache or Nginx web server (e.g., XAMPP, WampServer, Laragon, or PHP built-in server).

### 2. Database Import
1. Open your MySQL client (e.g., phpMyAdmin, HeidiSQL, or MySQL CLI).
2. Create a database named `campus_marketplace`:
   ```sql
   CREATE DATABASE campus_marketplace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
3. Import `database/schema.sql`:
   ```bash
   mysql -u root -p campus_marketplace < database/schema.sql
   ```

### 3. Database Configuration
Edit [config/database.php](file:///c:/Users/aaron/web-prgramming/campus-marketplace/config/database.php) if your MySQL port, username, or password differs from default XAMPP/Laragon credentials:
```php
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'campus_marketplace');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 4. Running the Application
If using PHP built-in server from `c:\Users\aaron\web-prgramming`:
```bash
php -S localhost:8000
```
Then navigate in your browser to:
[http://localhost:8000/campus-marketplace/](http://localhost:8000/campus-marketplace/)

*(Or copy `campus-marketplace` into your XAMPP `htdocs/` folder and open `http://localhost/campus-marketplace/`)*

---

## 🔑 Demo Accounts (Pre-Seeded)

All demo accounts have password: `Password123`

| Name | College Email | Student ID |
| :--- | :--- | :--- |
| **Aarav Sharma** | `aarav.sharma@campus.edu` | `STU2024-001` |
| **Priya Patel** | `priya.patel@campus.edu` | `STU2024-002` |
| **Rohan Gupta** | `rohan.gupta@campus.edu` | `STU2024-003` |
| **Sneha Reddy** | `sneha.reddy@campus.edu` | `STU2024-004` |
| **Vikram Singh** | `vikram.singh@campus.edu` | `STU2024-005` |
