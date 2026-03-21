module.exports = {
  content: [
    "./app/**/*.php",
    "./resources/**/*.php",
    "./public/**/*.php",
  ],
  theme: {
    extend: {},
  },
  plugins: [],

  "scripts": {
  "dev": "tailwindcss -i ./resources/css/app.css -o ./public/css/app.css --watch"
}
}