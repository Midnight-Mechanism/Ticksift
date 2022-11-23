import axios, { AxiosStatic } from 'axios';

declare global {
  interface Window {
    axios: AxiosStatic;
    route: (route: string, params?: any, absolute?: boolean) => string;
  }
}

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
