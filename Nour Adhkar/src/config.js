// Determine if we're in production mode
const isProduction = import.meta.env.MODE === 'production';

// Set the default API URL based on environment
const defaultApiUrl = isProduction 
  ? 'https://api.adhkar.ir/api'  // Production default
  : 'http://localhost:8000/api'; // Development default

// Export the BASE_API_URL with fallback
export const BASE_API_URL = import.meta.env.VITE_API_URL || defaultApiUrl;