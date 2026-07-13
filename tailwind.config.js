/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    darkMode: 'class',
    theme: {
        extend: {
            colors: {
                primary: {
                    50: '#eef2ff',
                    100: '#e0e7ff',
                    200: '#c7d2fe',
                    300: '#a5b4fc',
                    400: '#818cf8',
                    500: '#6366f1',
                    600: '#4f46e5',
                    700: '#4338ca',
                    800: '#3730a3',
                    900: '#312e81',
                    950: '#1e1b4b',
                },
                gray: {
                    50: '#f9fafb',
                    100: '#f3f4f6',
                    200: '#e5e7eb',
                    300: '#d1d5db',
                    400: '#9ca3af',
                    500: '#6b7280',
                    600: '#4b5563',
                    700: '#374151',
                    800: '#1f2937',
                    900: '#111827',
                    950: '#030712',
                }
            }
        },
    },
    plugins: [require('daisyui')],
    daisyui: {
        themes: [
            {
                light: {
                    ...require('daisyui/src/theming/themes')['light'],
                    primary: '#6366f1',
                    'primary-content': '#ffffff',
                    secondary: '#4f46e5',
                    'secondary-content': '#ffffff',
                    accent: '#818cf8',
                    'accent-content': '#ffffff',
                    neutral: '#6b7280',
                    'neutral-content': '#ffffff',
                    'base-100': '#ffffff',
                    'base-200': '#f3f4f6',
                    'base-300': '#e5e7eb',
                    'base-content': '#1f2937',
                },
            },
            {
                dark: {
                    ...require('daisyui/src/theming/themes')['dark'],
                    primary: '#818cf8',
                    'primary-content': '#1e1b4b',
                    secondary: '#6366f1',
                    'secondary-content': '#1e1b4b',
                    accent: '#a5b4fc',
                    'accent-content': '#1e1b4b',
                    neutral: '#4b5563',
                    'neutral-content': '#f3f4f6',
                    'base-100': '#1f2937',
                    'base-200': '#111827',
                    'base-300': '#030712',
                    'base-content': '#f3f4f6',
                },
            },
        ],
    },
}
