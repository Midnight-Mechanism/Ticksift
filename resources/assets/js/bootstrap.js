
window.Popper = require('popper.js').default;

/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
    window.$ = window.jQuery = require('jquery');

    require('bootstrap');
} catch (e) {}

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.Plotly = require('plotly.js-dist');
window.moment = require('moment');
window.jStat = require('jstat');
window.flatpickr = require('flatpickr');
window.Tabulator = require('tabulator-tables');
window._ = require('lodash');
window.Color = require('color');
