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
  daisyui: {
    themes: [
      {
        mytheme: {
        
            "primary": "#023618",
                    
            "secondary": "#f3f4f6",
                    
            "accent": "#ff0000",
                    
            "neutral": "#111827",
                    
            "base-100": "#ffffff",
                    
            "info": "#008dac",
                    
            "success": "#00e079",
                    
            "warning": "#db7000",
                    
            "error": "#ff0057",
        },
      },
    ],
  },
  plugins: [require("daisyui")],
  
}
