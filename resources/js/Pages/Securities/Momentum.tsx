import { Head } from '@inertiajs/inertia-react';
import { useEffect, useState } from 'react';

import ChartTitle from '@/Components/ChartTitle';
import DatePicker from '@/Components/DatePicker';
import MomentumTable from '@/Components/MomentumTable';
import MomentumTreemap from '@/Components/MomentumTreemap';
import Layout from '@/Layouts/Layout';

export default function Momentum(props: any) {
  const [loading, setLoading] = useState<boolean>();
  const [selectedDates, setSelectedDates] = useState<string[]>([]);
  const [results, setResults] = useState<any>();

  useEffect(() => {
    if (selectedDates.length) {
      setLoading(true);
      window.axios
        .post(window.route('securities.momentum-results'), {
          dates: selectedDates,
        })
        .then((r: any) => {
          setResults(r.data);
        });
    }
  }, [selectedDates]);

  useEffect(() => {
    setLoading(false);
  }, [results]);

  return (
    <Layout auth={props.auth}>
      <Head title="Momentum" />

      <div className="mx-auto px-4 sm:px-6 lg:px-8">
        <DatePicker minDate={props.priceDates.min} maxDate={props.priceDates.max} handleChange={setSelectedDates} />
        <ChartTitle text="Sectors" />
        <div className="chart-placeholder" style={{ height: '70vmin', minHeight: 800 }}>
          <MomentumTreemap
            data={results ? [...results.winners, ...results.losers] : null}
            calculateSecuritySize={(s: any) => s.latest_close * s.volume}
            screenshotFilename={['ticksift', 'momentum', selectedDates.join('_to_')].join('_')}
            className={loading ? 'loading' : ''}
          />
        </div>
        <div className="grid grid-cols-1 sm:grid-cols-1 2xl:grid-cols-2 gap-x-8 justify-items-center">
          <div className="w-full mt-3">
            <ChartTitle text="Winners" />
            <div className="chart-placeholder" style={{ height: '50vmin', minHeight: 300 }}>
              <MomentumTable type="winners" data={results?.winners} className={loading ? 'loading' : ''} />
            </div>
          </div>
          <div className="w-full mt-3">
            <ChartTitle text="Losers" />
            <div className="chart-placeholder" style={{ height: '50vmin', minHeight: 300 }}>
              <MomentumTable type="losers" data={results?.losers} className={loading ? 'loading' : ''} />
            </div>
          </div>
        </div>
      </div>
    </Layout>
  );
}
