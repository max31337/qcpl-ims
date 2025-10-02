import './bootstrap';
import('./charts/dashboard-charts').then(mod => {
	// noop - module initializes event listeners on import
}).catch(() => {});

import('./charts/supplies-charts').then(mod => {
  // noop - supplies charts module initializes listeners on import
}).catch(() => {});

import('./charts/analytics-charts').then(mod => {
  // noop - analytics charts module initializes listeners on import
}).catch(() => {});
