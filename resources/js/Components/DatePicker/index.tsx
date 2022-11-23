import dayjs from 'dayjs';
import 'flatpickr/dist/themes/dark.css';
import { useRef, useEffect } from 'react';
import Flatpickr from 'react-flatpickr';

import { useLocalStorage } from '@/Hooks/UseLocalStorage';

import './styles.css';

export default function DatePicker({
  minDate,
  maxDate,
  handleChange,
}: {
  minDate: string;
  maxDate: string;
  handleChange: any;
}) {
  const calendar = useRef<Flatpickr>(null);
  const [dateRange, setDateRange] = useLocalStorage('dates', [
    dayjs(maxDate).subtract(1, 'month').format('YYYY-MM-DD'),
    maxDate,
  ]);

  useEffect(() => {
    if (dateRange) {
      handleChange(dateRange);
    }
  }, [dateRange]);

  const renderDateButton = (id: string, label: string, dates: string[]) => {
    return (
      <button
        id={`button-${id}`}
        className="bg-ticksift-light w-full hover:bg-teal-800 text-white font-bold py-2 px-4 rounded uppercase"
        onClick={() => calendar.current?.flatpickr.setDate(dates, true)}
      >
        {label}
      </button>
    );
  };

  return (
    <div className="grid grid-cols-3 gap-3 items-center mb-3">
      <div className="col-span-full lg:col-span-1">
        <Flatpickr
          className="w-full bg-ticksift-black rounded"
          ref={calendar}
          options={{
            mode: 'range',
            dateFormat: 'Y-m-d',
            defaultDate: dateRange,
            altInput: true,
            altFormat: 'M j, Y',
            minDate: minDate,
            maxDate: maxDate,
          }}
          onChange={dates => {
            setDateRange(dates.map(d => dayjs(d).format('YYYY-MM-DD')));
          }}
        />
      </div>
      <div className="col-span-full lg:col-span-2 grid grid-cols-3 lg:grid-cols-6 gap-3">
        {renderDateButton('day', '1 Day', [maxDate])}
        {renderDateButton('week', '1 Week', [dayjs(maxDate).subtract(1, 'week').format('YYYY-MM-DD'), maxDate])}
        {renderDateButton('month', '1 Month', [dayjs(maxDate).subtract(1, 'month').format('YYYY-MM-DD'), maxDate])}
        {renderDateButton('ytd', 'YTD', [dayjs(maxDate).startOf('year').format('YYYY-MM-DD'), maxDate])}
        {renderDateButton('1yr', '1 Year', [dayjs(maxDate).subtract(1, 'year').format('YYYY-MM-DD'), maxDate])}
        {renderDateButton('all', 'All', [minDate, maxDate])}
      </div>
    </div>
  );
}
