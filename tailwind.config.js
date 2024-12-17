/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: ["class"],
    content: [
        './resources/**/*.{js,jsx,ts,tsx}',
        './resources/views/**/*.blade.php',
    ],
    prefix: "",
    theme: {
        container: {
            center: true,
            padding: "2rem",
            screens: {
                "2xl": "1400px",
            },
        },
        extend: {
            colors: {
                // Define actual color values instead of CSS variables
                border: "#e2e8f0",
                input: "#e2e8f0",
                ring: "#1e293b",
                background: "#ffffff",
                foreground: "#0f172a",
                primary: {
                    DEFAULT: "#1e293b",
                    foreground: "#f8fafc",
                },
                secondary: {
                    DEFAULT: "#f1f5f9",
                    foreground: "#1e293b",
                },
                destructive: {
                    DEFAULT: "#ef4444",
                    foreground: "#f8fafc",
                },
                muted: {
                    DEFAULT: "#f1f5f9",
                    foreground: "#64748b",
                },
                accent: {
                    DEFAULT: "#f1f5f9",
                    foreground: "#1e293b",
                },
            },
            borderRadius: {
                lg: "0.5rem",
                md: "0.375rem",
                sm: "0.25rem",
            },
        },
    },
    plugins: [],
}
