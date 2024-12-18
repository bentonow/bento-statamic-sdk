import FormEventsManager from "@/components/FormEventsManager";

console.log('Bento Statamic CP loaded');

import React from 'react';
import { createRoot } from 'react-dom/client';
import BentoSwitch from './components/BentoSwitch';
import BentoEvents from "./components/BentoEvents";

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

    // Initialize BentoEvents component
    const eventsContainer = document.getElementById('bento-events');
    if (eventsContainer) {
        try {
            const root = createRoot(eventsContainer);
            root.render(React.createElement(BentoEvents));
            console.log('BentoEvents component mounted');
        } catch (error) {
            console.error('Error initializing BentoEvents:', error);
        }
    } else {
        console.warn('BentoEvents container not found');
    }

    const formEventsManager = document.getElementById('form-events-manager');
    if (formEventsManager) {
        try {
            const root = createRoot(formEventsManager);
            root.render(React.createElement(FormEventsManager));
            console.log('FormEventsManager component mounted');
        } catch (error) {
            console.error('Error initializing FormEventsManager:', error);
        }
    }

});

// CSS imports
import '../css/addon.css';
