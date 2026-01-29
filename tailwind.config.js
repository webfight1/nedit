// tailwind.config.js
module.exports = {
  content: [
    "./*.php",
    "./inc/**/*.php",
    "./page-*.php",
    "./single-*.php",
    "./template-*.php",
  ],
  theme: {
    extend: {
      colors: {
        primary:  '#671768',
        secondary:'#f1ca71',
        third:    '#915694',
        fourth:' rgba(245, 204, 106, 1)'
      },
      fontFamily: { 
        sans: ['Montserrat', 'system-ui', 'sans-serif'],
        nailedit: ['NaileditFont', 'system-ui', 'sans-serif'],
      },
      borderRadius: {
        '24': '24px',   // -> klass: rounded-24
      },
    },
  },
  plugins: [],
};