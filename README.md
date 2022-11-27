# Ticksift

[Ticksift](https://ticksift.com) is a web-based tool for visualizing financial asset performance. It's written in [Laravel](https://laravel.com/), [Inertia](https://inertiajs.com/), [React](https://reactjs.org/), and [TypeScript](https://www.typescriptlang.org/), and makes heavy use of [Plotly.js](https://github.com/plotly/plotly.js/).

## Local Development

First, install the JavaScript and PHP dependencies:

```bash
npm install
composer install
```

Next, copy the example Laravel configuration file:

```bash
cp .env.example .env
php artisan key:generate
```

Now you can set up your SQLite database:

```bash
php artisan migrate
```

Ticksift uses security data from [SHARADAR](https://data.nasdaq.com/publishers/SHARADAR). You can download sample data [here](https://cloud.midnightmechanism.com/s/i4wA4QcjE9KFozA), then import it into the database:

```bash
mv <path to your downloaded files>/{prices.csv,securities.csv} storage/app/
php artisan db:seed
```

Now you can run the application:

```bash
npm run dev
```
