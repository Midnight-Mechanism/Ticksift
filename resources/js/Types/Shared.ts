export type SelectOption = {
  value: string;
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
  date: string;
  open: number;
  high: number;
  low: number;
  close: number;
  volume: number;
  ratio: number;
  ratio_close: number;
};

export type Portfolio = {
  id: number;
  name: string;
  securities: Security[];
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

export type ChartTrace = {
  x?: string[] | number[];
  y?: string[] | number[];
  z?: string[] | number[];
  xbins?: {
    size: number;
  };
  xaxis?: string;
  yaxis?: string;
  open?: number[];
  high?: number[];
  low?: number[];
  close?: number[];
  name?: string;
  legendgroup?: string;
  showlegend?: boolean;
  type?: string;
  fill?: string;
  mode?: string;
  marker?: {
    color?: string;
    line?: {
      color?: string;
      width?: number;
    };
    size?: string | number[];
    sizemode?: string;
    sizeref?: number;
  };
  customdata?: any;
  text?: string[];
  hovertemplate?: string;
  line?: {
    color?: string;
    dash?: string;
    shape?: string;
    width?: number;
  };
};

export type ChartLayout = {
  autosize?: boolean;
  barmode?: string;
  font?: {
    color?: string;
  };
  dragmode?: string;
  hovermode?: string;
  xaxis: {
    title?:
      | string
      | {
          text?: string;
          standoff?: number;
        };
    gridcolor?: string;
    automargin?: boolean;
    range?: string[] | number[];
    type?: string;
    tickformat?: string;
  };
  yaxis: {
    title?: string;
    domain?: string[] | number[];
    gridcolor?: string;
    automargin?: boolean;
    type?: string;
    tickprefix?: string;
  };
  yaxis2?: {
    domain?: string[] | number[];
    gridcolor?: string;
    automargin?: true;
  };
  legend?: {
    orientation?: string;
  };
  paper_bgcolor?: string;
  plot_bgcolor?: string;
  shapes: {
    type?: string;
    xref?: string;
    yref?: string;
    x0?: string | number;
    x1?: string | number;
    y0: string | number;
    y1: string | number;
    fillcolor: string;
    line: {
      width: number;
    };
  }[];
  title?: string;
  annotations: {
    x?: string;
    y?: string;
    text?: string;
    font?: {
      color?: string;
    };
    showarrow?: boolean;
  }[];
  grid?: {
    rows: number;
    columns: number;
  };
};

export type Recession = {
  start_date: string;
  end_date: string;
};
