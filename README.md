# Event Management System

A comprehensive PHP-based event management system with user authentication, event registration, and payment integration using Razorpay.

## 🎥 Demo Video

📹 **[Watch Demo Video](media/demo-video.mp4)** - Complete screen recording of the Event Management System

*Download and watch the full demonstration of all features*

## 📸 Screenshots

### Homepage
![Homepage](media/screenshots/homepage.png)
*Main landing page with event listings*

### User Dashboard
![User Dashboard](media/screenshots/dashboard.png)
*User dashboard after login*

### Event Registration
![Event Registration](media/screenshots/registration.png)
*Event registration and payment process*

### Admin Panel
![Admin Panel](media/screenshots/admin-panel.png)
*Administrative interface for managing events and users*

### Mobile View
![Mobile View](media/screenshots/mobile-view.png)
*Responsive design on mobile devices*

## Features

- **User Authentication**: Registration, login, password reset functionality
- **Event Management**: Create, view, and manage events with categories
- **Event Registration**: Users can register for events with ticket booking
- **Payment Integration**: Secure payment processing using Razorpay
- **Admin Panel**: Complete administrative interface for managing users and events
- **Responsive Design**: Mobile-friendly interface
- **Image Upload**: Support for event image uploads
- **CSRF Protection**: Built-in security measures

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Payment Gateway**: Razorpay
- **Server**: Apache/Nginx (XAMPP recommended for development)

## Installation

### Prerequisites

- XAMPP or similar local server environment
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web browser

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/event-management.git
   cd event-management
   ```

2. **Database Setup**
   - Start XAMPP and ensure MySQL is running
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the `database.sql` file to create the database and tables
   - The database will be created with sample data including an admin user

3. **Configuration**
   - Update database credentials in `includes/config.php` if needed:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'event_management');
     ```

4. **Razorpay Configuration** (Optional)
   - Sign up at [Razorpay](https://razorpay.com/)
   - Get your API keys from the dashboard
   - Update the payment configuration in the API files

5. **File Permissions**
   - Ensure the `uploads/` directory is writable:
     ```bash
     chmod 755 uploads/
     ```

6. **Access the Application**
   - Open your browser and navigate to `http://localhost/Event Management/`
   - Use the default admin credentials:
     - Email: admin@example.com
     - Password: admin123

## Project Structure

```
Event Management/
├── admin/                 # Admin panel files
├── api/                   # API endpoints for payments
├── assets/                # CSS and JavaScript files
├── auth/                  # Authentication pages
├── includes/              # Configuration and database files
├── pages/                 # Main application pages
├── partials/              # Reusable header and footer
├── uploads/               # Event images and files
├── database.sql           # Database schema and sample data
└── index.php             # Main entry point
```

## Default Admin Account

- **Email**: admin@example.com
- **Password**: admin123

⚠️ **Important**: Change the default admin password immediately after first login for security.

## API Endpoints

- `POST /api/register.php` - User registration
- `POST /api/create-razorpay-order.php` - Create payment order
- `POST /api/verify-payment.php` - Verify payment

## Security Features

- CSRF token protection
- Password hashing using PHP's password_hash()
- SQL injection prevention with prepared statements
- Session management
- Input validation and sanitization

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is open source and available under the [MIT License](LICENSE).

## Support

If you encounter any issues or have questions, please open an issue on GitHub.

## Screenshots

[Add screenshots of your application here]

## Changelog

### Version 1.0.0
- Initial release
- User authentication system
- Event management
- Payment integration
- Admin panel
