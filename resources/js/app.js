import './bootstrap';
import('./charts/dashboard-charts').then(mod => {
	// noop - module initializes event listeners on import
}).catch(() => {});
