import './owner/dashboard-shell.js';
import './owner/dashboard-range-chart.js';
import './shared/persian-calendar.js';
import './shared/persian-money.js';
import './owner/people-modals.js';
import './owner-admin-common.js';
import './owner/workspace-tabs.js';
import './owner/exam-builder.js';
import './owner/academy-form-options.js';
import { bootPanelRuntime, closePanelMenu } from './panel-runtime.js';
export { closePanelMenu };

bootPanelRuntime();

/* Academy-owner single JS entry: imports page-scoped interactions and shared input helpers. */
