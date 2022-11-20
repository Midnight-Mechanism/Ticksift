import { Link } from '@inertiajs/inertia-react';

export default function NavLink({
  method = 'get',
  as = 'a',
  href,
  active,
  children,
}: {
  method?: string;
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
      className={
        active
          ? 'uppercase inline-flex items-center px-1 pt-1 border-b-2 border-white text-sm font-medium leading-5 text-white focus:outline-none focus:border-white transition duration-150 ease-in-out'
          : 'uppercase inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-300 hover:text-white hover:border-gray-300 focus:outline-none focus:text-gray-200 focus:border-gray-300 transition duration-150 ease-in-out'
      }
    >
      {children}
    </Link>
  );
}
