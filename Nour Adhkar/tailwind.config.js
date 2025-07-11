/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#F5F2EE',
          100: '#EBE5DD',
          200: '#D7CCBD',
          300: '#C4B39D',
          400: '#B0A28A',
          500: '#A79277',
          600: '#8D7A62',
          700: '#73644F',
          800: '#554A3B',
          900: '#373126',
        },
      },
      fontFamily: {
        sans: ['Vazirmatn', 'sans-serif'],
      },
    },
  },
  plugins: [],
}

