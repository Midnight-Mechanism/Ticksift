import { Link } from '@inertiajs/inertia-react';
import { useState } from 'react';

import Logo from '@/Components/Logo';
import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';

export default function Layout({ auth, header, children }: { auth: any; header?: any; children?: any }) {
  const [showingNavigationDropdown, setShowingNavigationDropdown] = useState(false);

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

              <div className="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                <NavLink
                  href={window.route('securities.explorer')}
                  active={window.route().current('securities.explorer')}
                >
                  Explorer
                </NavLink>
                <NavLink
                  href={window.route('securities.momentum')}
                  active={window.route().current('securities.momentum')}
                >
                  Momentum
                </NavLink>
                {auth?.user && (
                  <NavLink method="post" href={window.route('logout')}>
                    Log Out
                  </NavLink>
                )}
              </div>
            </div>

            <div className="-mr-2 flex items-center sm:hidden">
              <button
                onClick={() => setShowingNavigationDropdown(previousState => !previousState)}
                className="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
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
          <div className="pt-2 pb-3 space-y-1">
            <ResponsiveNavLink
              href={window.route('securities.explorer')}
              active={window.route().current('securities.explorer')}
            >
              Explorer
            </ResponsiveNavLink>
            <ResponsiveNavLink
              href={window.route('securities.momentum')}
              active={window.route().current('securities.momentum')}
            >
              Momentum
            </ResponsiveNavLink>
          </div>

          {auth?.user && (
            <div className="pt-4 pb-1 border-t border-gray-200">
              <div className="mt-3 space-y-1">
                <ResponsiveNavLink method="post" href={window.route('logout')} as="button">
                  Log Out
                </ResponsiveNavLink>
              </div>
            </div>
          )}
        </div>
      </nav>

      {header && (
        <header className="bg-white shadow">
          <div className="mx-auto py-6 px-4 sm:px-6 lg:px-8">{header}</div>
        </header>
      )}

      <main className="grow">{children}</main>
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
