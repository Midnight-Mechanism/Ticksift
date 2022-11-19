import './bootstrap';
import '../css/app.css';

import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/inertia-react';
import { InertiaProgress } from '@inertiajs/progress';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

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

    root.render(<App {...props} />);
  },
});

InertiaProgress.init({ color: '#4B5563' });
