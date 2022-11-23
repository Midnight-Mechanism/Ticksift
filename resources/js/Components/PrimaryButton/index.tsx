export default function PrimaryButton({
  type = 'submit',
  backgroundColorClass = 'bg-ticksift-accent-dark',
  className = '',
  onClick,
  processing,
  children,
}: {
  type?: 'submit' | 'reset' | 'button' | undefined;
  backgroundColorClass?: string;
  className?: string;
  onClick?: any;
  processing?: boolean;
  children: any;
}) {
  return (
    <button
      type={type}
      className={`inline-flex items-center justify-center px-4 py-2 ${backgroundColorClass} border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest active:bg-gray-900 transition ease-in-out duration-150 ${
        processing && 'opacity-25'
      } ${className}`}
      onClick={onClick}
      disabled={processing}
    >
      {children}
    </button>
  );
}
