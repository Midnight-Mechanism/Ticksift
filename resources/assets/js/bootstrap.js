
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

window.Plotly = require('plotly.js-dist');
window.dayjs = require('dayjs');
window.jStat = require('jstat');
window.flatpickr = require('flatpickr');
import {TabulatorFull as Tabulator} from 'tabulator-tables';
window.Tabulator = Tabulator;
window._ = require('lodash');
window.Color = require('color');
window.TechnicalIndicators = require('technicalindicators');
