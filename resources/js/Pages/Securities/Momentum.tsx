import { Head } from '@inertiajs/inertia-react';
import { useEffect, useState } from 'react';

import ChartTitle from '@/Components/ChartTitle';
import DatePicker from '@/Components/DatePicker';
import MomentumTable from '@/Components/MomentumTable';
import MomentumTreemap from '@/Components/MomentumTreemap';
import Layout from '@/Layouts/Layout';

export default function Momentum(props: any) {
  const [loading, setLoading] = useState<boolean>();
  const [results, setResults] = useState<any>();

  const onDateChange = (dates: string[]) => {
    if (dates.length) {
      setLoading(true);
      window.axios
        .post(window.route('securities.momentum-results'), {
          dates: dates,
        })
        .then((r: any) => {
          setResults(r.data);
        });
    }
  };

  useEffect(() => {
    setLoading(false);
  }, [results]);
  return (
    <Layout auth={props.auth}>
      <Head title="Momentum" />

      <div className="mx-auto px-4 sm:px-6 lg:px-8">
        <DatePicker minDate={props.priceDates.min} maxDate={props.priceDates.max} handleChange={onDateChange} />
        <ChartTitle text="Sectors" />
        <MomentumTreemap
          data={results ? [...results.winners, ...results.losers] : null}
          calculateSecuritySize={(s: any) => s.latest_close * s.volume}
          className={loading ? 'loading' : ''}
        />
        <div className="grid grid-cols-1 sm:grid-cols-1 2xl:grid-cols-2 gap-x-8 justify-items-center">
          <div className="w-full mt-3">
            <ChartTitle text="Winners" />
            <MomentumTable type="winners" data={results?.winners} className={loading ? 'loading' : ''} />
          </div>
          <div className="w-full mt-3">
            <ChartTitle text="Losers" />
            <MomentumTable type="losers" data={results?.losers} className={loading ? 'loading' : ''} />
          </div>
        </div>
      </div>
    </Layout>
  );
}
