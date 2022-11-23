export type SelectOption = {
  value: string | number;
  label: string;
};

export type Price = {
  date: string;
  open: number;
  high: number;
  low: number;
  close: number;
  volume: number;
  ratio: number;
  ratio_close: number;
};

export type Security = {
  id: number;
  ticker: string;
  ticker_name: string;
  name: string;
  short_name: string;
  prices: Price[];
  currency_code: string;
};

export type MomentumResult = {
  ticker: string;
  name: string;
  short_name: string;
  industry: string;
  sector: string;
  sector_color: string;
  scale_marketcap: number;
  currency_code: string;
  earliest_close: number;
  latest_close: number;
  volume: number;
  change: number;
};

export type Portfolio = {
  id: number;
  name: string;
  securities: Security[];
};

export type Recession = {
  start_date: string;
  end_date: string;
};

export type User = {
  id: number;
};

export type Auth = {
  user: User;
};

export type TotalDateRange = {
  min: string;
  max: string;
};
