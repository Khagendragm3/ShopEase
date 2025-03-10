# ShopEase

A comprehensive e-commerce platform built with HTML, CSS, JavaScript, PHP, and MySQL. This project includes both user-facing features and an admin panel for managing the online store.

## Features

### User Features
- User registration and authentication
- Product browsing and searching
- Product filtering and sorting
- Product details with image gallery
- Shopping cart functionality
- Wishlist management
- Checkout process
- Order tracking
- User profile management
- Product reviews and ratings
- Coupon code application
- Newsletter subscription
- Dynamic content pages (About Us, Contact Us, FAQ, Privacy Policy, Returns & Refunds)
- Blog with rich text content

### Admin Features
- Dashboard with statistics and insights
- Product management (add, edit, delete, inventory)
- Category management
- Brand management
- Order management and processing
- User management
- Review moderation
- Coupon management
- Blog post management with TinyMCE editor
- Testimonial management
- Contact message management
- Newsletter subscriber management
- Dynamic content management for all pages
- Settings configuration with responsive interface
- Image upload and management

## Technologies Used

- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Backend**: PHP
- **Database**: MySQL
- **Libraries**: jQuery, Font Awesome, Chart.js, TinyMCE

## Installation

1. Clone the repository:
   ```
   git clone https://github.com/yourusername/e-commerce-website.git
   ```

2. Import the database:
   - Create a new MySQL database named `ecommerce`
   - Import the `database/ecommerce.sql` file

3. Configure the database connection:
   - Open `includes/config.php`
   - Update the database credentials if needed:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'ecommerce');
     ```

4. Configure the URL root:
   - In `includes/config.php`, update the URL_ROOT constant:
     ```php
     define('URL_ROOT', 'http://localhost/E-commerceWebsite');
     ```

5. Place the project in your web server's document root (e.g., htdocs for XAMPP).

6. Access the website:
   - Frontend: `http://localhost/E-commerceWebsite`
   - Admin Panel: `http://localhost/E-commerceWebsite/admin`

## Default Admin Credentials

- Email: admin@example.com
- Password: password

## Project Structure

```
E-commerceWebsite/
├── admin/                  # Admin panel files
├── assets/                 # Static assets
│   ├── css/                # CSS files
│   ├── js/                 # JavaScript files
│   └── images/             # Image files
├── database/               # Database schema
├── includes/               # PHP includes
├── uploads/                # Uploaded files
│   ├── products/           # Product images
│   ├── blog/               # Blog post images
│   ├── settings/           # Site settings images
│   ├── testimonials/       # Testimonial images
│   ├── team/               # Team member images
│   └── hero/               # Hero slider images
└── ...                     # Frontend pages
```

## Key Features Explained

### Dynamic Content Management
The admin panel allows for complete customization of all website content, including:
- Homepage hero section (static or slider)
- About Us page with team members
- Contact Us page with Google Maps integration
- FAQ section
- Privacy Policy
- Returns & Refunds Policy

### Blog System
- Rich text editor with TinyMCE
- Image upload capabilities
- Category assignment
- Publish/draft status

### Newsletter System
- User subscription from the footer
- Admin management of subscribers
- Export functionality for marketing purposes
- Automatic email confirmation

### Responsive Design
- Mobile-friendly frontend
- Responsive admin panel
- Optimized image handling
- Touch-friendly controls

## Contributing

1. Fork the repository
2. Create your feature branch: `git checkout -b feature/my-new-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin feature/my-new-feature`
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgements

- [Bootstrap](https://getbootstrap.com/)
- [Font Awesome](https://fontawesome.com/)
- [Chart.js](https://www.chartjs.org/)
- [jQuery](https://jquery.com/)
- [TinyMCE](https://www.tiny.cloud/) 