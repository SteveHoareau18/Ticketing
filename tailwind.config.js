/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./assets/**/*.js",
        "./templates/**/*.html.twig",
        "*.config.js"
    ],
    theme: {
        extend: {},
    },
    daisyui: {
        themes: [
            {
                mytheme: {

                    "primary": "#f2dd74",

                    "secondary": "#eface6",

                    "accent": "#a1c9e2",

                    "neutral": "#1f242e",

                    "base-100": "#e3e4e8",

                    "info": "#a3c7ef",

                    "success": "#18af82",

                    "warning": "#f29f40",

                    "error": "#e13d60",
                },
            },
        ],
    },
    plugins: [require("daisyui")],
}