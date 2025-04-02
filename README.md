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

## Tech Stack

- Frontend: Vue.js 3 with Vite
- Backend: Laravel 11
- Styling: CSS3 with responsive design
- Icons: Font Awesome

## Local Development Setup

### Prerequisites

- Node.js (v14 or higher)
- npm or yarn
- PHP 8.1 or higher
- Composer
- MySQL or PostgreSQL

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
   # Configure your database connection in the .env file
   ```

4. Generate application key
   ```bash
   php artisan key:generate
   ```

5. Run database migrations and seed data
   ```bash
   php artisan migrate --seed
   ```

6. Start the backend server
   ```bash
   php artisan serve
   ```

7. The backend API will be available at `http://localhost:8000`

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
   - Create a new branch (`git checkout -b feature/your-feature-name`)
   - Make your changes
   - Commit with clear, descriptive messages
   - Push to your fork
   - Submit a pull request

3. **Improve documentation**
   - Help us improve this README or add documentation

4. **Share the project**
   - Star the repository
   - Share with others who might find it useful

## Code Style and Guidelines

- Follow the existing code style
- Write meaningful commit messages
- Add comments for complex logic
- Test your changes before submitting

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Contact

For questions or suggestions, please open an issue on GitHub or contact the maintainers.

---

Made with ❤️ and ☕