/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                'sc-teal': {
                    50:  '#f1f9f5',
                    100: '#e1f3ec',
                    300: '#9dd9c5',
                    500: '#2ba888',
                    600: '#0e8e6d',
                    700: '#007a5c',
                    800: '#005a44',
                    900: '#003a2c',
                },
                'sc-orange': {
                    100: '#fde6cf',
                    300: '#f8c187',
                    500: '#f19121',
                    600: '#d8731a',
                    700: '#b85a10',
                },
                'sc-yellow': {
                    100: '#fbf3c5',
                    300: '#f0db77',
                    500: '#e0c020',
                    600: '#c8a91a',
                    700: '#97810f',
                },
                'sc-ink': {
                    300: '#a3b3ac',
                    500: '#61756c',
                    700: '#324840',
                    900: '#15201c',
                },
                'sc-bg':    '#f7f6f1',
                'sc-line':  '#d9e2dc',
                'sc-paper': '#f5efe2',
            },
            fontFamily: {
                sans:    ['"Plus Jakarta Sans"', 'Segoe UI', 'system-ui', 'sans-serif'],
                display: ['Grandeur', '"Plus Jakarta Sans"', 'Georgia', 'serif'],
                mono:    ['"JetBrains Mono"', 'ui-monospace', 'Menlo', 'monospace'],
            },
            boxShadow: {
                'sc-1': '0 1px 2px rgba(21,32,28,0.04), 0 1px 1px rgba(21,32,28,0.03)',
                'sc-2': '0 2px 6px rgba(21,32,28,0.05), 0 1px 2px rgba(21,32,28,0.04)',
                'sc-3': '0 8px 24px rgba(21,32,28,0.07), 0 2px 4px rgba(21,32,28,0.04)',
                'sc-focus': '0 0 0 3px rgba(14,142,109,0.28)',
            },
        },
    },
    plugins: [
        require('@tailwindcss/typography'),
    ],
}
