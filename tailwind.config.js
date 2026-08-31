import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './app/Helpers/**/*.php',
        './app/Http/Controllers/**/*.php',
    ],
    darkMode: 'class',
    theme: {
        screens: {
            'xs': '375px',
            'sm': '640px',
            'md': '768px',
            'lg': '1024px',
            'xl': '1280px',
            '2xl': '1536px',
        },
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                alpine: {
                    50: '#f0f9ff',
                    100: '#e0f2fe',
                    200: '#bae6fd',
                    300: '#7dd3fc',
                    400: '#38bdf8',
                    500: '#0ea5e9',
                    600: '#0284c7',
                    700: '#0369a1',
                    800: '#075985',
                    900: '#0c4a6e',
                    950: '#082f49',
                },
                podium: {
                    gold: '#F59E0B',
                    'gold-dark': '#D97706',
                    silver: '#94A3B8',
                    'silver-dark': '#64748B',
                    bronze: '#D97706',
                    'bronze-dark': '#92400E',
                }
            },
            boxShadow: {
                'podium-gold': '0 4px 20px -2px rgba(245, 158, 11, 0.25)',
                'podium-silver': '0 4px 20px -2px rgba(148, 163, 184, 0.25)',
                'podium-bronze': '0 4px 20px -2px rgba(217, 119, 6, 0.25)',
                'glass': '0 8px 32px 0 rgba(15, 23, 42, 0.08)',
            },
            backdropBlur: {
                'xs': '2px',
            }
        },
    },
    plugins: [],
};
