import './bootstrap';
import '../css/app.css';

import React from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/inertia-react';
import { InertiaProgress } from '@inertiajs/progress';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

// clear local storage if last visit more than a day ago
try {
  const lastVisited = window.localStorage.getItem('lastVisited');
  if (Date.now() - lastVisited > 86400000) {
    localStorage.clear();
  }
} finally {
  window.localStorage.setItem('lastVisited', Date.now());
}

createInertiaApp({
  title: title => `${title} - ${appName}`,
  resolve: name => resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx')),
  setup({ el, App, props }) {
    const root = createRoot(el);

    root.render(<App {...props} />);
  },
});

InertiaProgress.init({ color: '#4B5563' });
