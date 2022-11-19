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
}) {
  if (isAsync) {
    return (
      <div>
        <AsyncSelect
          className={`react-select-container ${className}`}
          classNamePrefix="react-select"
          isMulti={isMulti}
          defaultValue={defaultValue}
          onChange={onChange}
          placeholder={placeholder}
          loadOptions={loadOptions}
          options={options}
        />
      </div>
    );
  }
  return (
    <div>
      <Select
        className={`react-select-container ${className}`}
        classNamePrefix="react-select"
        isMulti={isMulti}
        defaultValue={defaultValue}
        placeholder={placeholder}
        onChange={onChange}
        options={options}
      />
    </div>
  );
}
