var _paq = window._paq || [];
/* tracker methods like "setCustomDimension" should be called before "trackPageView" */
_paq.push(['setDocumentTitle', document.title]);
_paq.push(['trackPageView']);
_paq.push(['enableLinkTracking']);
(function () {
  var u = 'https://stats.midnightmechanism.com/';
  _paq.push(['setTrackerUrl', u + 'main']);
  _paq.push(['setSiteId', '2']);
  _paq.push(['enableHeartBeatTimer', 5]);
  var d = document,
    g = d.createElement('script'),
    s = d.getElementsByTagName('script')[0];
  g.type = 'text/javascript';
  g.async = true;
  g.defer = true;
  g.src = u + 'main.js';
  s.parentNode.insertBefore(g, s);
})();
