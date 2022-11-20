import Card from '@/Components/Card';
import Layout from '@/Layouts/Layout';

export default function Home(props: any) {
  return (
    <Layout auth={props.auth}>
      <div className="mx-auto px-4 sm:px-6 lg:px-8">
        <div className="p-10 grid grid-cols-1 md:grid-cols-2 gap-5 justify-items-center">
          <Card
            link={window.route('securities.explorer')}
            imageLink="/images/landing/explorer.png"
            title="Explore Securities"
            body="Compare the performance and trajectory of specific assets."
          />
          <Card
            link={window.route('securities.momentum')}
            imageLink="/images/landing/momentum.png"
            title="Examine Momentum"
            body="Quickly summarize the movement of large-cap stocks."
          />
        </div>
      </div>
    </Layout>
  );
}
