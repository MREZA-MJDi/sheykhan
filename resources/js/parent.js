/* Parent panel entry. Shared shell behavior lives in panel-runtime.js. */
import './shared/persian-calendar.js';
import './shared/persian-money.js';
import { bootPanelRuntime, closePanelMenu } from './panel-runtime.js';
export { closePanelMenu };

bootPanelRuntime();

document.addEventListener('DOMContentLoaded', () => {
    document.documentElement.classList.add('parent-ready');
});
