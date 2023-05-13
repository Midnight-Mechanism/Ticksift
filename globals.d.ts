/* eslint-disable @typescript-eslint/ban-types */
export {};

declare global {
  function route(routeName?: string, parameters?: any[] | any, absolute? = true): Function[string];
  interface InertiaPageProps {
    errors: Errors & ErrorBag;
    auth?: {
      user: {
        id: number;
        name: string;
        email: string;
        email_verified_at: string;
      };
    };
  }
}
