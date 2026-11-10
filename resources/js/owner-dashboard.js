import './shared/persian-calendar.js';
import './shared/persian-money.js';
import './owner-admin-common.js';
import './owner/people-tabs.js';
import './owner/exam-builder.js';
import './owner/academy-form-options.js';
import { bootPanelRuntime, closePanelMenu } from './panel-runtime.js';
export { closePanelMenu };

bootPanelRuntime();

/* Academy-owner single JS entry: owner interactions, exam editor, calendar and Persian input helpers. */

import './owner/dashboard-shell.js';
import './owner/dashboard-range-chart.js';
