# 🧶 Needle & Thread - Handcrafted Bags E-commerce

Needle & Thread is a premium, handcrafted bag e-commerce platform designed to showcase and sell artisanal bags. This project features a robust PHP-based backend, a clean and modern web frontend, and a companion Flutter mobile application.

---

## ✨ Features

### 🌐 Web Frontend
- **Product Catalog**: Beautifully displayed handcrafted bags with detailed descriptions, pricing, and high-quality images.
- **User Authentication**: Secure registration and login system with persistent session management.
- **Shopping Cart**: Fully functional cart system with real-time updates and availability checks.
- **Secure Checkout**: Integrated order processing with **PayPal support** (via proxy) and CSRF protection.
- **Order Management**: Users can place orders, view their order history, and track status transitions.
- **Interactive Comments**: Customer reviews and feedback on products with moderation capabilities.
- **Responsive Design**: Elegant UI using Google Fonts (`Oswald`, `Playfair Display`) that works seamlessly across all devices.

### 🛠 Admin Dashboard
- **Product Management**: Comprehensive CRUD operations for products, including slug generation and automated status updates.
- **Inventory Tracking**: Real-time inventory logs tracking every change (orders, refunds, manual adjustments).
- **Order Oversight**: Detailed order management with status history tracking and automatic restocking for cancelled orders.
- **Asset Management**: Secure image upload validation and automated file cleanup.
- **Comment Moderation**: Control over user-generated content before it goes live.

### 📱 Flutter App
- A companion mobile application providing a native experience for browsing, purchasing, and managing user profiles.

---

## 🚀 Tech Stack

- **Backend**: PHP 8.x (utilizing PDO for secure database interactions)
- **Database**: MySQL / MariaDB (relational schema with constraints and defaults)
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Security**: 
  - **Bcrypt**: For secure password hashing.
  - **CSRF Protection**: Token-based validation for all state-changing requests.
  - **Input Sanitization**: Multi-layer approach to prevent XSS and injection attacks.
  - **Transactions**: Ensuring data integrity during complex operations like order placement and product deletion.
- **Payments**: PayPal API integration (Proxy-based architectural pattern).
- **Mobile**: Flutter

---

## 📂 Project Structure

```text
needle-and-thread/
├── admin/               # Admin panel controllers and views
├── api/                 # API endpoints for mobile/web interaction
├── assets/              # Static files (CSS, JS, Images)
├── auth/                # Authentication logic (Login, Register, Logout)
├── backend/             # Core PHP business logic and controllers
├── flutter_app/         # Source code for the Flutter mobile application
├── partials/            # Reusable HTML snippets (Header, Footer)
├── uploads/             # Product image storage
├── config.php           # Global configuration and DB connection
├── functions.php        # Utility functions and helper methods
├── db.sql               # Database schema and sample data
└── index.php            # Homepage
```

---

## 🛠 Installation & Setup

### 1. Prerequisites
- **Web Server**: Apache or Nginx (XAMPP/WAMP/MAMP recommended)
- **PHP**: Version 7.4 or higher
- **MySQL**: MariaDB 10.4+ or MySQL 5.7+
- **Flutter SDK**: (Optional) For building the mobile app

### 2. Database Setup
1. Create a database named `needle_thread` in your MySQL server.
2. Import the `db.sql` file provided in the root directory.
   ```bash
   mysql -u your_username -p needle_thread < db.sql
   ```
3. A sample admin user is pre-configured:
   - **Email**: `admin@needlethread.com`
   - **Password**: `admin123`

### 3. Application Configuration
1. Open `config.php` in the root directory.
2. Update the database credentials and site URL to match your environment:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'needle_thread');
   define('DB_USER', 'your_db_username');
   define('DB_PASS', 'your_db_password');
   define('SITE_URL', 'http://localhost/needle-and-thread');
   ```

### 4. Running the Project
1. Place the project folder in your web server's document root (e.g., `htdocs` for XAMPP).
2. Access the site via `http://localhost/needle-and-thread` or your configured virtual host.

---

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🤝 Contributing

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

*Handcrafted with ❤️ by the Needle & Thread Team.*
