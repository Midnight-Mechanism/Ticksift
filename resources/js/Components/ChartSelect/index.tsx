import Select from 'react-select';
import AsyncSelect from 'react-select/async';

import './styles.css';

export default function ChartSelect({
  isAsync = false,
  isMulti,
  placeholder,
  className = 'py-2',
  defaultValue,
  onChange,
  loadOptions,
  options,
  value,
}: {
  isAsync?: boolean;
  isMulti?: boolean;
  placeholder?: string;
  className?: string;
  defaultValue?: string;
  onChange?: any;
  loadOptions?: any;
  options?: any[];
  value?: any;
}) {
  if (isAsync) {
    return (
      <AsyncSelect
        className={`react-select-container ${className}`}
        classNamePrefix="react-select"
        isMulti={isMulti}
        defaultValue={defaultValue}
        onChange={onChange}
        placeholder={placeholder}
        loadOptions={loadOptions}
        options={options}
        value={value}
      />
    );
  }
  return (
    <Select
      className={`react-select-container ${className}`}
      classNamePrefix="react-select"
      isMulti={isMulti}
      defaultValue={defaultValue}
      placeholder={placeholder}
      onChange={onChange}
      options={options}
      value={value}
    />
  );
}
