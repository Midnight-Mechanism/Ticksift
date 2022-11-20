declare global {
  interface Window {
    axios: any;
    route: any;
  }
}

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
