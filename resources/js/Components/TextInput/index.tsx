import { useEffect, useRef } from 'react';

import './styles.css';

export default function TextInput({
  type = 'text',
  label,
  name,
  min,
  max,
  defaultValue,
  value,
  className = 'my-2',
  autoComplete,
  required,
  isFocused,
  handleChange,
}: {
  label?: string;
  type?: string;
  name?: string;
  min?: string;
  max?: string;
  defaultValue?: string | number;
  value?: string | number;
  className?: string;
  autoComplete?: string;
  required?: boolean;
  isFocused?: boolean;
  handleChange: any;
}) {
  const input = useRef<HTMLInputElement>(null);

  useEffect(() => {
    if (isFocused) {
      input.current?.focus();
    }
  }, []);

  return (
    <div className="flex flex-col items-start">
      {label && <label htmlFor={name}>{label}</label>}
      <input
        type={type}
        name={name}
        min={min}
        max={max}
        defaultValue={defaultValue}
        value={value}
        className={`text-input border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm ${className}`}
        ref={input}
        autoComplete={autoComplete}
        required={required}
        onChange={e => handleChange(e)}
      />
    </div>
  );
}
