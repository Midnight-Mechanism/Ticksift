import Select from 'react-select';
import AsyncSelect from 'react-select/async';

import { SelectOption } from '@/Types/Shared';

import './styles.css';

export default function ChartSelect({
  isAsync = false,
  isMulti,
  placeholder,
  noOptionsMessage,
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
  noOptionsMessage?: any;
  className?: string;
  defaultValue?: string;
  onChange?: any;
  loadOptions?: any;
  options?: SelectOption[];
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
        noOptionsMessage={noOptionsMessage}
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
      noOptionsMessage={noOptionsMessage}
      onChange={onChange}
      options={options}
      value={value}
    />
  );
}
