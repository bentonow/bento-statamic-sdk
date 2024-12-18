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
    		padding: '2rem',
    		screens: {
    			'2xl': '1400px'
    		}
    	},
    	extend: {
    		colors: {
    			border: '#e2e8f0',
    			input: '#e2e8f0',
    			ring: '#1e293b',
    			background: '#ffffff',
    			foreground: '#0f172a',
    			primary: {
    				DEFAULT: '#1e293b',
    				foreground: '#f8fafc'
    			},
    			secondary: {
    				DEFAULT: '#f1f5f9',
    				foreground: '#1e293b'
    			},
    			destructive: {
    				DEFAULT: '#ef4444',
    				foreground: '#f8fafc'
    			},
    			muted: {
    				DEFAULT: '#f1f5f9',
    				foreground: '#64748b'
    			},
    			accent: {
    				DEFAULT: '#f1f5f9',
    				foreground: '#1e293b'
    			}
    		},
    		keyframes: {
    			'accordion-down': {
    				from: {
    					height: '0'
    				},
    				to: {
    					height: 'var(--radix-accordion-content-height)'
    				}
    			},
    			'accordion-up': {
    				from: {
    					height: 'var(--radix-accordion-content-height)'
    				},
    				to: {
    					height: '0'
    				}
    			}
    		},
    		animation: {
    			'accordion-down': 'accordion-down 0.2s ease-out',
    			'accordion-up': 'accordion-up 0.2s ease-out'
    		},
    		borderRadius: {
    			lg: 'var(--radius)',
    			md: 'calc(var(--radius) - 2px)',
    			sm: 'calc(var(--radius) - 4px)'
    		}
    	}
    },
    plugins: [require("tailwindcss-animate")],
}
