# Online Book Store System

A comprehensive online book store system with three user roles: Admin, Reader, and Writer. The system allows for book management, purchases, and user management.

## Features

- Three user roles with different dashboards:
  - Admin: Book management, user monitoring, payment tracking
  - Reader: Book browsing, searching, purchasing, and downloading
  - Writer: Book upload, sales tracking, payment monitoring

- Secure payment system in Kenyan Shillings
- Responsive and attractive UI with card-based book display
- Book preview and purchase system
- User account management

## Installation Instructions

1. Install XAMPP Server:
   - Download and install XAMPP from https://www.apachefriends.org/
   - Make sure to install PHP 7.4 or higher

2. Setup the Project:
   - Clone or download this repository
   - Place the project files in the `htdocs` folder of your XAMPP installation
     (Usually located at `C:\xampp\htdocs\Online_Book_Store`)

3. Database Setup:
   - Start XAMPP Control Panel
   - Start Apache and MySQL services
   - Open your web browser and navigate to `http://localhost/phpmyadmin`
   - Create a new database named `online_book_store`
   - Import the `database.sql` file from the project root directory

4. Configure Database Connection:
   - Open `config/database.php`
   - Verify the database credentials match your setup

5. Access the System:
   - Open your web browser
   - Navigate to `http://localhost/Online_Book_Store`
   - Default admin credentials:
     - Username: admin
     - Password: admin123

## Directory Structure

```
Online_Book_Store/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── config/
│   └── database.php
├── includes/
│   ├── header.php
│   └── footer.php
├── admin/
│   ├── dashboard.php
│   ├── manage_books.php
│   └── manage_users.php
├── reader/
│   ├── dashboard.php
│   ├── browse_books.php
│   └── my_purchases.php
├── writer/
│   ├── dashboard.php
│   ├── upload_book.php
│   └── sales_report.php
├── index.php
└── database.sql
```

## Security Notes

- This is a development version with plain text passwords
- For production use, implement proper password hashing
- Use HTTPS in production
- Implement proper session management
- Add input validation and sanitization

## Support

For any issues or questions, please create an issue in the repository. 