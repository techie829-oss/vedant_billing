import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: 'var(--color-primary)',
                secondary: 'var(--color-secondary)',
                accent: 'var(--color-accent)',
                danger: 'var(--color-danger)',
                success: 'var(--color-success)',
                warning: 'var(--color-warning)',
                info: 'var(--color-info)',
                surface: 'var(--color-surface)',
                background: 'var(--color-background)',
                text: 'var(--color-text)',
                'text-muted': 'var(--color-text-muted)',
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
};
