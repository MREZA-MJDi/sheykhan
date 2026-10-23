/* Shared application behavior: calendars, Persian money, Alpine and responsive panel shell. */
import './persian-calendar.js';
import './persian-money.js';
import { bootPanel } from './panel-base.js';

const bootSharedFoundation = () => {
    bootPanel();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootSharedFoundation, { once: true });
} else {
    bootSharedFoundation();
}
