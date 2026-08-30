/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      fontFamily: {
        display: ['"Playfair Display"', 'serif'],
        sans: ['Inter', 'sans-serif'],
        body: ['Lora', 'serif'],
      },
      colors: {
        'brand-cream': '#F9F6F0',
        'brand-dark': '#1E1E1E',
        'brand-black': '#2C2C2C',
        'brand-grey': '#5A5A5A',
        'brand-gold': '#C9A96E',
        'brand-gold-dark': '#B59458',
        'brand-burgundy': '#6B2E3E',
        'brand-beige': '#E5D9C5',
        primary: {
          50: '#FAF7F2',
          100: '#F4EFE6',
          200: '#E5D9C5',
          300: '#D7C4A5',
          400: '#C9A96E',
          500: '#B59458',
          600: '#9A7A42',
          700: '#6B2E3E',
          800: '#4A1E2B',
          900: '#2C121A',
        }
      }
    },
  },
  plugins: [],
}
