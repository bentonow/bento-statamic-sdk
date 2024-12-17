console.log('Bento Statamic CP loaded');

import React from 'react';
import { createRoot } from 'react-dom/client';
import BentoSwitch from './components/BentoSwitch';

document.addEventListener('DOMContentLoaded', () => {
    // Initialize React components
    const switchElements = document.querySelectorAll('[data-react-component="bento-switch"]');
    switchElements.forEach(element => {
        try {
            const props = JSON.parse(element.dataset.props || '{}');
            const root = createRoot(element);
            root.render(React.createElement(BentoSwitch, props));
        } catch (error) {
            console.error('Error initializing BentoSwitch:', error);
        }
    });
});

// CSS imports
import '../css/addon.css';
