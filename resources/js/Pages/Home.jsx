import React from 'react';
import Layout from '@/Layouts/Layout';
import Card from '@/Components/Card';

export default function Home(props) {
  return (
    <Layout auth={props.auth} errors={props.errors}>
      <div className="py-12">
        <div className="mx-auto px-4 sm:px-6 lg:px-8">
          <div className="p-10 grid grid-cols-1 sm:grid-cols-1 md:grid-cols-3 lg:grid-cols-3 xl:grid-cols-3 gap-5 justify-items-center">
            <Card
              link={route('securities.explorer')}
              imageLink="/images/landing/explorer.png"
              title="Explore Securities"
              body="Compare the performance and trajectory of specific assets."
            />
            <Card
              link={route('securities.momentum')}
              imageLink="/images/landing/momentum.png"
              title="Examine Momentum"
              body="Quickly summarize the movement of large-cap stocks."
            />
            <Card
              link={route('portfolios.index')}
              imageLink="/images/landing/portfolios.png"
              title="Establish Portfolios"
              body="Create portfolios of securities to watch group performance."
            />
          </div>
        </div>
      </div>
    </Layout>
  );
}
