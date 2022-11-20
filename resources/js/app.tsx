import { createInertiaApp } from '@inertiajs/inertia-react';
import { InertiaProgress } from '@inertiajs/progress';
import { MatomoProvider, createInstance } from '@jonkoops/matomo-tracker-react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot } from 'react-dom/client';

import '../css/app.css';
import './bootstrap';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

const baseStatsDomain = 'https://stats.midnightmechanism.com';
const instance = createInstance({
  urlBase: baseStatsDomain,
  srcUrl: `${baseStatsDomain}/main.js`,
  trackerUrl: `${baseStatsDomain}/main`,
  siteId: 2,
  heartBeat: {
    active: true,
    seconds: 5,
  },
});

// clear local storage if last visit more than a day ago
try {
  const lastVisited: string | null = localStorage.getItem('lastVisited');
  if (lastVisited && Date.now() - parseInt(lastVisited) > 86400000) {
    localStorage.clear();
  }
} finally {
  localStorage.setItem('lastVisited', Date.now().toString());
}

createInertiaApp({
  title: title => `${title} - ${appName}`,
  resolve: name => resolvePageComponent(`./Pages/${name}.tsx`, import.meta.glob('./Pages/**/*.tsx')),
  setup({ el, App, props }) {
    const root = createRoot(el);

    root.render(
      <MatomoProvider value={instance}>
        <App {...props} />
      </MatomoProvider>
    );
  },
});

InertiaProgress.init({ color: 'gray' });
