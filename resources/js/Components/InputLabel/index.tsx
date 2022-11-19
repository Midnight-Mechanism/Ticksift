export default function InputLabel({
  forInput,
  value,
  className,
  children,
}: {
  forInput?: string;
  value?: any;
  className?: string;
  children?: any;
}) {
  return (
    <label htmlFor={forInput} className={`block font-medium text-sm text-gray-700 ` + className}>
      {value ? value : children}
    </label>
  );
}
