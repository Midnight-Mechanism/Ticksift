import { Method } from '@inertiajs/core';
import { Link } from '@inertiajs/react';

export default function ResponsiveNavLink({
  method = 'get',
  as = 'a',
  href,
  active = false,
  children,
}: {
  method?: Method;
  as?: string;
  href: string;
  active?: boolean;
  children?: any;
}) {
  return (
    <Link
      method={method}
      as={as}
      href={href}
      className={`w-full flex items-start pl-3 pr-4 py-2 border-l-4 ${
        active
          ? 'border-teal-400 text-teal-700 bg-teal-50 focus:outline-none focus:text-teal-800 focus:bg-teal-100 focus:border-teal-700'
          : 'border-transparent text-gray-300 hover:text-black hover:bg-gray-50 hover:border-gray-300'
      } text-base font-medium focus:outline-none transition duration-150 ease-in-out`}
    >
      {children}
    </Link>
  );
}
