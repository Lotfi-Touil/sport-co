/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./assets/**/*.js",
        "./templates/**/*.html.twig",
        "./node_modules/flowbite/**/*.js"
    ],
    theme: {
        colors: {
            "primary": "#4f46e5",
            "secondary":"#c45f2c",
        },
        extend: {
            animation: {
                blob: "blob 7s infinite",
            },
            keyframes: {
                blob: {
                    "0%": {
                        transform: "translate(0px, 0px) scale(1)",
                    },
                    "33%": {
                        transform: "translate(30px, -50px) scale(1.1)",
                    },
                    "66%": {
                        transform: "translate(-20px, 20px) scale(0.9)",
                    },
                    "100%": {
                        transform: "translate(0px, 0px) scale(1)",
                    },
                },
            },
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

                    "primary": "#4f46e5",

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
    plugins:
        [
            require("daisyui"),
            require('flowbite/plugin')
        ],
    darkMode: 'class',
}
