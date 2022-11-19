import { ReactTabulator } from 'react-tabulator';
import { formatCurrency } from '@/Utilities/NumberHelpers';

import 'tabulator-tables/dist/css/tabulator_midnight.css';

export default function MomentumTable({ data, type }) {
  if (data) {
    return (
      <ReactTabulator
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
              return formatCurrency(cell.getValue(), cell.getData().currency_code);
            },
          },
          {
            title: 'Latest Close',
            field: 'latest_close',
            sorter: 'number',
            minWidth: 50,
            responsive: 2,
            formatter: function (cell) {
              return formatCurrency(cell.getValue(), cell.getData().currency_code);
            },
          },
          {
            title: type === 'winners' ? 'Increase' : 'Decrease',
            field: 'change',
            sorter: 'number',
            minWidth: 50,
            responsive: 0,
            formatter: function (cell) {
              return (100 * cell.getValue()).toFixed(2) + '%';
            },
          },
        ]}
      />
    );
  } else {
    return <div />;
  }
}
