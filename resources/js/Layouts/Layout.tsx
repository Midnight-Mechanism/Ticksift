import { Link } from '@inertiajs/inertia-react';
import { useMatomo } from '@jonkoops/matomo-tracker-react';
import { useState, useEffect } from 'react';

import Logo from '@/Components/Logo';
import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';

export default function Layout({ auth, header, children }: { auth?: any; header?: any; children?: any }) {
  const [showingNavigationDropdown, setShowingNavigationDropdown] = useState(false);
  const { trackPageView } = useMatomo();

  useEffect(() => {
    trackPageView();
  }, []);

  type NavLinkData = {
    name: string;
    route: string;
    method?: string;
    as?: string;
  };

  const navLinks: { left: NavLinkData[]; right: NavLinkData[] } = {
    left: [
      { name: 'Explorer', route: 'securities.explorer' },
      { name: 'Momentum', route: 'securities.momentum' },
    ],
    right: [],
  };

  if (auth?.user) {
    //navLinks.left.push({ name: 'Portfolios', route: 'portfolios.index' });
    navLinks.right.push({ name: 'Log Out', route: 'logout', method: 'post', as: 'button' });
  } else {
    navLinks.right.push({ name: 'Log In', route: 'login' });
    navLinks.right.push({ name: 'Register', route: 'register' });
  }

  const renderNavLinks = (data: NavLinkData[]) => {
    return data.map(navLink => {
      return (
        <NavLink
          key={navLink.route}
          href={window.route(navLink.route)}
          active={window.route().current(navLink.route)}
          method={navLink.method}
          as={navLink.as}
        >
          {navLink.name}
        </NavLink>
      );
    });
  };

  const renderResponsiveNavLinks = (data: NavLinkData[]) => {
    return data.map(navLink => {
      return (
        <ResponsiveNavLink
          key={navLink.route}
          href={window.route(navLink.route)}
          active={window.route().current(navLink.route)}
          method={navLink.method}
          as={navLink.as}
        >
          {navLink.name}
        </ResponsiveNavLink>
      );
    });
  };

  return (
    <div className="flex flex-col min-h-screen bg-ticksift-dark text-white">
      <nav className="bg-ticksift-light border-b border-gray-500">
        <div className="mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between h-16">
            <div className="flex">
              <div className="shrink-0 flex items-center">
                <Link href="/">
                  <Logo />
                </Link>
              </div>

              <div className="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">{renderNavLinks(navLinks.left)}</div>
            </div>
            <div className="hidden float-right space-x-8 sm:flex">{renderNavLinks(navLinks.right)}</div>

            <div className="-mr-2 flex items-center sm:hidden">
              <button
                onClick={() => setShowingNavigationDropdown(previousState => !previousState)}
                className="inline-flex items-center justify-center p-2 rounded-md text-gray-400"
              >
                <svg className="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                  <path
                    className={!showingNavigationDropdown ? 'inline-flex' : 'hidden'}
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth="2"
                    d="M4 6h16M4 12h16M4 18h16"
                  />
                  <path
                    className={showingNavigationDropdown ? 'inline-flex' : 'hidden'}
                    strokeLinecap="round"
                    strokeLinejoin="round"
                    strokeWidth="2"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <div className={(showingNavigationDropdown ? 'block' : 'hidden') + ' sm:hidden'}>
          <div className="pt-2 pb-2 space-y-1">{renderResponsiveNavLinks(navLinks.left)}</div>
          <div className="pt-2 pb-2 border-t border-gray-200 space-y-1">{renderResponsiveNavLinks(navLinks.right)}</div>
        </div>
      </nav>

      {header && (
        <header className="bg-white shadow">
          <div className="mx-auto py-6 px-4 sm:px-6 lg:px-8">{header}</div>
        </header>
      )}

      <main className="grow py-12">{children}</main>
      <nav className="bg-ticksift-light">
        <div className="mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between h-16">
            <div className="shrink-0 flex items-center">
              <a href="https://midnightmechanism.com">
                <img style={{ width: '30px' }} src="https://midnightmechanism.com/static/circle_logo.png" />
              </a>
            </div>
            <div className="shrink-0 flex items-center text-white">
              <span>
                &copy; Midnight Mechanism, LLC 2019-
                {new Date().getFullYear()}
              </span>
            </div>
          </div>
        </div>
      </nav>
    </div>
  );
}
