declare global {
  interface Window {
    _paq: any;
    axios: any;
    route: any;
  }
}

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const _paq = window._paq || [];
/* tracker methods like "setCustomDimension" should be called before "trackPageView" */
_paq.push(['setDocumentTitle', document.title]);
_paq.push(['trackPageView']);
_paq.push(['enableLinkTracking']);
(function () {
  const u = 'https://stats.midnightmechanism.com/';
  _paq.push(['setTrackerUrl', u + 'main']);
  _paq.push(['setSiteId', '2']);
  _paq.push(['enableHeartBeatTimer', 5]);
  const d = document,
    g = d.createElement('script'),
    s = d.getElementsByTagName('script')[0];
  g.type = 'text/javascript';
  g.async = true;
  g.defer = true;
  g.src = u + 'main.js';
  s.parentNode?.insertBefore(g, s);
})();
