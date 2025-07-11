# Nour Adhkar (اذکار نور)

> A comprehensive platform for daily Islamic prayers and remembrances with a beautiful UI.

<p align="center">
  <img src="Nour Adhkar/src/assets/images/cover.webp" alt="Nour Adhkar Screenshot" width="800">
</p>

## About

Nour Adhkar is a web application that provides a collection of Islamic adhkar (remembrances) and prayers in a user-friendly interface. The application includes features such as:

- Morning and evening adhkar
- Prayer counter (Tasbih)
- Daily Quran verse
- Theme customization (light/dark mode)
- User profiles and achievements

## Dependencies

### Frontend Dependencies
- Node.js (v14 or higher)
- npm (v7 or higher)
- Vue.js 3
- Vite

### Backend Dependencies
- PHP 8.1 or higher
- Composer 2.0 or higher
- Laravel 11
- MySQL 8.0 or higher / PostgreSQL 13 or higher
- PHP Extensions:
  - BCMath PHP Extension
  - Ctype PHP Extension
  - JSON PHP Extension
  - Mbstring PHP Extension
  - OpenSSL PHP Extension
  - PDO PHP Extension
  - Tokenizer PHP Extension
  - XML PHP Extension
  - FileInfo PHP Extension

## Tech Stack

- Frontend: Vue.js 3 with Vite
- Backend: Laravel 11

## Local Development Setup

### Branch Strategy

This project follows a branch strategy where:
- `main` branch is reserved for production releases
- `development` branch is the primary branch for development work

When starting development:
1. Clone the repository
   ```bash
   git clone https://github.com/yourusername/nour-adhkar.git
   ```

2. Switch to the development branch
   ```bash
   git checkout development
   ```

3. Create a feature branch from development
   ```bash
   git checkout -b feature/your-feature-name
   ```

4. After completing your work, create a pull request to merge into the `development` branch, not `main`

### Frontend Setup

1. Navigate to the frontend directory
   ```bash
   cd "Nour Adhkar"
   ```

2. Install dependencies
   ```bash
   npm install
   ```

3. Start the development server
   ```bash
   npm run dev
   ```

4. Open your browser and visit `http://localhost:5173`

### Backend Setup

1. Navigate to the backend directory
   ```bash
   cd backend
   ```

2. Install PHP dependencies
   ```bash
   composer install
   ```

3. Create and configure your `.env` file
   ```bash
   cp .env.example .env
   ```

4. Generate application key
   ```bash
   php artisan key:generate
   ```

5. Generate JWT secret key
   ```bash
   php artisan jwt:secret
   ```

6. Run database migrations and seed data
   ```bash
   php artisan migrate --seed
   ```

7. Start the backend server
   ```bash
   php artisan serve
   ```

8. The backend API will be available at `http://localhost:8000`

## Testing

### Running Backend Tests

1. Configure your testing environment in `backend/.env.testing`
   ```bash
   cp .env.example .env.testing
   # Update database and other settings for testing
   ```

2. Run all tests
   ```bash
   cd backend
   php artisan test
   ```

3. Run tests with coverage report
   ```bash
   php artisan test --coverage
   # Or for HTML coverage report
   php artisan test --coverage-html reports/
   ```

## Building for Production

```bash
npm run build
```

The built files will be in the `dist` directory.

## How to Contribute

We welcome contributions from the community! Here's how you can help:

1. **Report bugs or suggest features**
   - Open an issue on GitHub describing the bug or feature request

2. **Contribute code**
   - Fork the repository
   - Switch to the `development` branch (`git checkout development`)
   - Create a new branch from development (`git checkout -b feature/your-feature-name`)
   - Make your changes
   - Run tests and ensure they pass
   - Commit with clear, descriptive messages
   - Push to your fork
   - Submit a pull request to the `development` branch

3. **Improve documentation**
   - Help us improve this README or add documentation

4. **Share the project**
   - Star the repository
   - Share with others who might find it useful

## Code Style and Guidelines

- Follow the existing code style
- Write meaningful commit messages
- Add comments for complex logic
- Write tests for new features
- Maintain or improve code coverage
- Follow PSR-12 coding standard for PHP
- Use Vue.js style guide recommendations

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Contact

For questions or suggestions, please open an issue on GitHub or contact the maintainers.

---

Made with ❤️ and ☕