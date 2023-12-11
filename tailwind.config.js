/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  theme: {
    extend: {
      // Ajoutez vos extensions ici
    },
  },
  variants: {
    extend: {
      backgroundColor: ['peer-checked'], // Active la variante 'peer-checked' pour la couleur de fond
      borderColor: ['peer-checked'],     // Active la variante 'peer-checked' pour la couleur de bordure
      // Ajoutez d'autres variantes si nécessaire
    },
  },
  plugins: [],
}
