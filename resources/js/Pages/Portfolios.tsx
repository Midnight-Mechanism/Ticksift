import { Head } from '@inertiajs/inertia-react';
import { useState, useEffect } from 'react';
import { ReactTabulator } from 'react-tabulator';
import 'tabulator-tables/dist/css/tabulator_midnight.css';

import DatePicker from '@/Components/DatePicker';
import MomentumTreemap from '@/Components/MomentumTreemap';
import SecurityPicker from '@/Components/SecurityPicker';
import Layout from '@/Layouts/Layout';

export default function Portfolios(props: any) {
  const [loading, setLoading] = useState<boolean>();
  const [selectedDates, setSelectedDates] = useState<string[]>([]);
  const [selectedPortfolio, setSelectedPortfolio] = useState<any>();
  const [selectedSecurities, setSelectedSecurities] = useState<string[]>([]);
  const [portfolios, setPortfolios] = useState<any>(props.portfolios);
  const [results, setResults] = useState<any>();

  useEffect(() => {
    setLoading(false);
  }, [results]);

  useEffect(() => {
    if (selectedDates.length && selectedSecurities.length) {
      setLoading(true);
      window.axios
        .post(window.route('securities.momentum-results'), {
          dates: selectedDates,
          security_ids: selectedSecurities?.map((s: any) => s.value),
        })
        .then((r: any) => {
          setResults([...r.data.winners, ...r.data.losers]);
        });
    }
  }, [selectedDates, selectedSecurities]);

  return (
    <Layout auth={props.auth}>
      <Head title="Portfolios" />

      <div className="mx-auto px-4 sm:px-6 lg:px-8">
        <DatePicker minDate={props.priceDates.min} maxDate={props.priceDates.max} handleChange={setSelectedDates} />
        <ReactTabulator
          events={{
            rowSelectionChanged: (rows: any) => {
              if (rows.length) {
                setSelectedPortfolio(rows[0]);
                setSelectedSecurities(
                  rows[0].securities.map((s: any) => {
                    return {
                      value: s.id,
                      label: s.ticker_name,
                    };
                  })
                );
              } else {
                setSelectedPortfolio(null);
              }
            },
          }}
          options={{
            layout: 'fitDataStretch',
            maxHeight: 300,
            selectable: 1,
            responsiveLayout: 'hide',
            placeholder: 'No portfolios yet. Try saving one below!',
          }}
          data={portfolios}
          columns={[
            {
              title: 'Name',
              field: 'name',
              sorter: 'string',
            },
            {
              title: 'Securities',
              field: 'securities',
              headerSort: false,
              formatter: (cell: any) => {
                return cell
                  .getValue()
                  .map((s: any) => s.ticker ?? s.name)
                  .join(', ');
              },
            },
          ]}
        />
        <SecurityPicker
          isMulti
          canSavePortfolio
          portfolioToUpdate={selectedPortfolio}
          onPortfolioUpdate={(p: any) => {
            setPortfolios(p);
            setSelectedSecurities([]);
          }}
          value={selectedSecurities}
          handleChange={setSelectedSecurities}
        />
        {selectedSecurities?.length > 0 && results && (
          <MomentumTreemap
            data={results}
            calculateSecuritySize={() => 1}
            screenshotFilename={[
              'ticksift',
              selectedPortfolio?.name?.replace(/[^a-z0-9]/gi, '_') ?? 'portfolio',
              selectedDates.join('_to_'),
            ].join('_')}
            className={loading ? 'loading' : ''}
          />
        )}
      </div>
    </Layout>
  );
}
