// tailwind.config.js
module.exports = {
  content: [
    "./*.php",
    "./inc/**/*.php",
    "./page-*.php",
    "./single-*.php",
    "./template-*.php",
    "./template-parts/**/*.php",
    "./assets/js/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        primary:  '#5c285c',
        secondary:'#d9c3ad',
        third:    '#2e133e',
        fourth:' rgba(245, 204, 106, 1)'
      },
      fontFamily: { 
        sans: ['Montserrat', 'system-ui', 'sans-serif'],
        nailedit: ['Playfair Display', 'serif'],
      },
      borderRadius: {
        
        '24': '24px',   // -> klass: rounded-24
      },
    },
  },
  plugins: [],
};