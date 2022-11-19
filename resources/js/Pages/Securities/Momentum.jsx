import Layout from '@/Layouts/Layout';
import { Head } from '@inertiajs/inertia-react';
import DatePicker from '@/Components/DatePicker';
import MomentumTreemap from '@/Components/MomentumTreemap';
import MomentumTable from '@/Components/MomentumTable';
import ChartTitle from '@/Components/ChartTitle';
import { useLocalStorage } from '@/Hooks/UseLocalStorage';

export default function Momentum(props) {
  //const data = axios.get('securities.get-momentum')
  const [results, setResults] = useLocalStorage('momentumResults');

  const onDateChange = dates => {
    if (dates.length) {
      axios
        .post(route('securities.momentum-results'), {
          dates: dates,
        })
        .then(r => {
          setResults(r.data);
        });
    }
  };

  return (
    <Layout auth={props.auth} errors={props.errors}>
      <Head title="Momentum" />

      <div className="py-12">
        <div className="mx-auto px-4 sm:px-6 lg:px-8">
          <DatePicker minDate={props.priceDates.min} maxDate={props.priceDates.max} handleChange={onDateChange} />
          <ChartTitle text="Sectors" />
          <MomentumTreemap
            data={results ? [...results.winners, ...results.losers] : null}
            calculateSecuritySize={s => s.latest_close * s.volume}
          />
          <ChartTitle className="mt-3" text="Winners" />
          <MomentumTable type="winners" data={results?.winners} />
          <ChartTitle className="mt-3" text="Losers" />
          <MomentumTable type="losers" data={results?.losers} />
        </div>
      </div>
    </Layout>
  );
}
