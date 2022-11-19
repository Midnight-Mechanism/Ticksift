import { ReactTabulator } from 'react-tabulator';
import 'tabulator-tables/dist/css/tabulator_midnight.css';

import { formatCurrency, formatPercentage } from '@/Utilities/NumberHelpers';

export default function MomentumTable({ data, type, className = '' }: { data: any; type: string; className?: string }) {
  if (data) {
    return (
      <ReactTabulator
        className={`tabulator ${className}`}
        options={{
          layout: 'fitColumns',
          minHeight: '300',
          height: '50vmin',
          responsiveLayout: 'hide',
        }}
        data={data}
        columns={[
          {
            title: 'Ticker',
            field: 'ticker',
            sorter: 'string',
            minWidth: 50,
            responsive: 0,
          },
          {
            title: 'Name',
            field: 'name',
            sorter: 'string',
            minWidth: 150,
            responsive: 1,
          },
          {
            title: 'Sector',
            field: 'sector',
            sorter: 'string',
            minWidth: 150,
            responsive: 2,
          },
          {
            title: 'Industry',
            field: 'industry',
            sorter: 'string',
            minWidth: 150,
            responsive: 2,
          },
          {
            title: 'Earliest Close',
            field: 'earliest_close',
            sorter: 'number',
            minWidth: 50,
            responsive: 2,
            formatter: function (cell) {
              return formatCurrency(cell.getValue(), (cell.getData() as any).currency_code);
            },
          },
          {
            title: 'Latest Close',
            field: 'latest_close',
            sorter: 'number',
            minWidth: 50,
            responsive: 2,
            formatter: function (cell) {
              return formatCurrency(cell.getValue(), (cell.getData() as any).currency_code);
            },
          },
          {
            title: type === 'winners' ? 'Increase' : 'Decrease',
            field: 'change',
            sorter: 'number',
            minWidth: 50,
            responsive: 0,
            formatter: function (cell) {
              return formatPercentage(cell.getValue(), 2);
            },
          },
        ]}
      />
    );
  } else {
    return <div />;
  }
}
