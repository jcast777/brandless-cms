# Brandless CMS - Backend

<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

<p align="center">
  <a href="https://github.com/laravel/framework/actions">
    <img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status">
  </a>
  <a href="https://packagist.org/packages/laravel/framework">
    <img src="https://img.shields.io/packagist/l/laravel/framework" alt="License">
  </a>
  <a href="https://packagist.org/packages/laravel/framework">
    <img src="https://img.shields.io/badge/PHP-%3E%3D8.1-777BB4" alt="PHP Version">
  </a>
</p>

## About Brandless CMS

Brandless CMS is a modern headless content management system built on Laravel, featuring a powerful admin panel powered by Filament. It provides a flexible and scalable solution for managing content with a clean, brand-agnostic approach.

### Key Features

- **Headless Architecture**: RESTful API for content delivery to any frontend
- **Filament Admin Panel**: Intuitive admin interface built with Filament
- **Content Management**: Flexible content types and fields
- **Media Library**: Built-in media management
- **User Management**: Role-based access control
- **API-First**: Ready for modern frontend frameworks
- **Modular Design**: Easy to extend and customize

## Getting Started

### Prerequisites

- PHP 8.1 or higher
- Composer
- Node.js & NPM
- MySQL 5.7+ or PostgreSQL

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/your-username/brandless-cms.git
   cd brandless-cms/backend
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install NPM dependencies:
   ```bash
   npm install
   ```

4. Copy the environment file:
   ```bash
   cp .env.example .env
   ```

5. Generate application key:
   ```bash
   php artisan key:generate
   ```

6. Configure your `.env` file with your database credentials and other settings.

7. Run database migrations and seeders:
   ```bash
   php artisan migrate --seed
   ```

8. Start the development server:
   ```bash
   php artisan serve
   ```

9. Build frontend assets (for production):
   ```bash
   npm run build
   ```

### Development

To start the Vite development server for frontend assets:

```bash
npm run dev
```

### Testing

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## Contributing

Thank you for considering contributing to Brandless CMS! Please read our [contribution guidelines](CONTRIBUTING.md) before submitting pull requests.

In order to ensure that our community is welcoming to all, please review and abide by our [Code of Conduct](CODE_OF_CONDUCT.md).

## Security Vulnerabilities

If you discover a security vulnerability within Brandless CMS, please send an e-mail to security@example.com. All security vulnerabilities will be promptly addressed.

## License

Brandless CMS is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
