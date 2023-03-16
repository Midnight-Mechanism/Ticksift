import { Link } from '@inertiajs/react';

export default function Card({
  link,
  imageLink,
  title,
  body,
}: {
  link: string;
  imageLink: string;
  title: string;
  body: string;
}) {
  return (
    <Link href={link}>
      <div className="h-full rounded overflow-hidden shadow-lg bg-teal-800">
        <img className="w-full" src={imageLink} />
        <div className="px-6 py-4">
          <div className="font-bold text-xl mb-2">{title}</div>
          <p className="text-gray-300 text-base">{body}</p>
        </div>
      </div>
    </Link>
  );
}
