import { Head } from '@inertiajs/inertia-react';
import pcorrtest from '@stdlib/stats/pcorrtest';
import dayjs from 'dayjs';
import { debounce, cloneDeep, reduce, zipObject, mergeWith, isArray, mean, map, sortBy } from 'lodash';
import { useCallback, useState, useEffect } from 'react';
import Plot from 'react-plotly.js';
import { SMA, EMA, BollingerBands, RSI, TRIX } from 'technicalindicators';
import { BollingerBandsOutput } from 'technicalindicators/declarations/volatility/BollingerBands';

import ChartSelect from '@/Components/ChartSelect';
import DatePicker from '@/Components/DatePicker';
import TextInput from '@/Components/TextInput';
import { useLocalStorage } from '@/Hooks/UseLocalStorage';
import Layout from '@/Layouts/Layout';
import { chartColor, gridColor } from '@/Utilities/Constants';
import { getNumberWithOrdinal, formatCurrency } from '@/Utilities/NumberHelpers';

export default function Explorer(props: any) {
  const chartOptions = [
    { value: 'line', label: 'Line Chart' },
    { value: 'candlestick', label: 'Candlestick Chart' },
    { value: 'ohlc', label: 'OHLC Chart' },
    { value: 'bubble', label: 'Bubble Chart' },
    { value: 'ratio', label: 'Ratio Chart' },
    { value: 'histvar', label: 'Historical VaR Chart' },
    { value: 'correlation', label: 'Correlation Chart' },
  ];

  const scaleOptions = [
    { value: 'linear', label: 'Linear Scale' },
    { value: 'log', label: 'Logarithmic Scale' },
  ];

  const indicatorOptions = [
    {
      value: 'simple-moving-average',
      label: 'Simple Moving Average (SMA)',
    },
    {
      value: 'exponential-moving-average',
      label: 'Exponential Moving Average (EMA)',
    },
    { value: 'bollinger-bands', label: 'Bollinger Bands' },
    { value: 'rsi', label: 'Relative Strength Index (RSI)' },
    { value: 'trix', label: 'Triple Exponential Average (TRIX)' },
    { value: 'recessions', label: 'Recessions' },
  ];

  const [loading, setLoading] = useState<boolean>(true);

  const [selectedDates, setSelectedDates] = useState<any>();
  const [selectedSecurities, setSelectedSecurities] = useLocalStorage('selectedSecurities');
  const [selectedChart, setSelectedChart] = useLocalStorage('selectedChart', chartOptions[0]);
  const [selectedScale, setSelectedScale] = useLocalStorage('selectedScale', scaleOptions[0]);
  const [selectedIndicators, setSelectedIndicators] = useLocalStorage('selectedIndicators');
  const [selectedRatioSecurity, setSelectedRatioSecurity] = useLocalStorage('selectedRatioSecurity');

  const [varThreshold, setVarThreshold] = useState(5);

  const [priceData, setPriceData] = useState<any>();
  const [ratioPriceData, setRatioPriceData] = useState<any>();
  const [chartData, setChartData] = useState<any>();
  const [recessions, setRecessions] = useState<any>();

  const getSecurityOptions = useCallback(
    debounce((input, callback) => {
      window.axios
        .get(window.route('securities.search'), {
          params: {
            q: input,
          },
        })
        .then((res: any) => {
          callback(res.data);
        });
    }, 250),
    []
  );

  const getPriceData = (securities: any, callback: any) => {
    if (selectedDates && securities) {
      setLoading(true);
      window.axios
        .get(window.route('securities.prices'), {
          params: {
            security_ids: securities,
            dates: selectedDates,
          },
        })
        .then((res: any) => callback(res.data));
    }
  };

  useEffect(() => {
    window.axios.get(window.route('indicators.recessions')).then((res: any) => setRecessions(res.data));
  }, []);

  const generateChartData = () => {
    if (!priceData?.length) {
      return;
    }

    const layout: any = {
      autosize: true,
      font: {
        color: 'white',
      },
      dragmode: 'zoom',
      hovermode: 'x unified',
      xaxis: {
        gridcolor: gridColor,
        automargin: true,
        range: selectedDates && selectedDates.length < 2 ? [selectedDates[0], selectedDates[0]] : selectedDates,
      },
      yaxis: {
        gridcolor: gridColor,
        automargin: true,
        type: selectedScale?.value,
        tickprefix: '$',
      },
      yaxis2: {
        domain: [0, 0.2],
        gridcolor: gridColor,
        automargin: true,
      },
      legend: {},
      paper_bgcolor: chartColor,
      plot_bgcolor: chartColor,
      shapes: [],
      annotations: [],
    };

    const data = [];

    // add base traces
    switch (selectedChart?.value) {
      case 'line': {
        priceData.forEach((security: any) => {
          data.push({
            name: security.short_name,
            legendgroup: security.short_name,
            type: 'scattergl',
            x: security.prices.map((p: any) => p.date),
            y: security.prices.map((p: any) => p.close),
            text: security.prices.map((p: any) => formatCurrency(p.close, security.currency_code)),
            hovertemplate: 'Close: %{text}',
          });
        });
        layout.title = 'Closing Prices';
        break;
      }
      case 'candlestick':
      case 'ohlc': {
        priceData.forEach((security: any) => {
          data.push({
            name: security.short_name,
            legendgroup: security.short_name,
            type: selectedChart.value,
            x: security.prices.map((p: any) => p.date),
            open: security.prices.map((p: any) => p.open),
            high: security.prices.map((p: any) => p.high),
            low: security.prices.map((p: any) => p.low),
            close: security.prices.map((p: any) => p.close),
          });
        });
        layout.title = 'Prices';
        break;
      }
      case 'bubble': {
        // determine max volume across all securities
        // this factors into bubble size
        let maxVolume = 0;
        priceData.forEach((security: any) => {
          maxVolume = Math.max(maxVolume, Math.max(...security.prices.map((p: any) => p.volume)));
        });

        priceData.forEach((security: any) => {
          data.push({
            name: security.short_name,
            legendgroup: security.short_name,
            type: 'scattergl',
            mode: 'markers',
            x: security.prices.map((p: any) => p.date),
            y: security.prices.map((p: any) => p.close),
            marker: {
              size: security.prices.map((p: any) => p.volume),
              sizemode: 'area',
              sizeref: (2.0 * maxVolume) / 15 ** 2,
            },
            text: security.prices.map((p: any) => formatCurrency(p.close, security.currency_code)),
            hovertemplate: 'Close: %{text}<br>Volume: %{marker.size:,} shares',
          });
        });
        break;
      }
      case 'ratio': {
        if (ratioPriceData) {
          const ratioSecurity = ratioPriceData[0];

          priceData.forEach((security: any) => {
            const overlappingPrices = security.prices
              .map((price: any) => {
                const ratioPrice = ratioSecurity.prices.find((ratioPrice: any) => ratioPrice.date == price.date);
                if (ratioPrice) {
                  price.ratio_close = ratioPrice.close;
                  price.ratio = price.close / price.ratio_close;
                  return price;
                }
              })
              .filter((price: any) => price != null);
            if (overlappingPrices.length > 0) {
              data.push({
                name: security.short_name,
                legendgroup: security.short_name,
                type: 'scattergl',
                x: overlappingPrices.map((a: any) => a.date),
                y: overlappingPrices.map((a: any) => a.ratio),
                customdata: overlappingPrices.map((a: any) => [
                  formatCurrency(a.close, security.currency_code),
                  formatCurrency(a.ratio_close, ratioSecurity.currency_code),
                ]),
                hovertemplate:
                  'Close: %{customdata[0]}<br>' + ratioSecurity.short_name + ' Close: %{customdata[1]}<br>Ratio: %{y}',
              });
            }
          });
          layout.title = `Closing Prices to ${ratioSecurity.short_name}`;
        }
        break;
      }
      case 'histvar': {
        const returns: any[] = [];
        priceData.forEach((security: any) => {
          let previousPrice: any;
          security.prices.map((price: any) => {
            if (previousPrice) {
              returns.push(Math.log(price.close / previousPrice.close));
            }
            previousPrice = price;
          });
        });
        returns.sort((a, b) => a - b);

        const threshold = varThreshold / 100;

        data.push(
          {
            name: 'Below ' + getNumberWithOrdinal(Number(varThreshold)) + ' Percentile',
            type: 'histogram',
            x: returns.slice(0, threshold * returns.length),
            xbins: {
              size: 0.01,
            },
            marker: {
              color: '#E18D96',
              line: {
                color: 'red',
                width: 1,
              },
            },
          },
          {
            name: 'Above ' + getNumberWithOrdinal(Number(varThreshold)) + ' Percentile',
            type: 'histogram',
            x: returns.slice(threshold * returns.length),
            xbins: {
              size: 0.01,
            },
            marker: {
              color: 'dodgerblue',
              line: {
                color: 'blue',
                width: 1,
              },
            },
          }
        );

        layout.title = 'Historical Value at Risk';
        layout.xaxis.title = 'Continuously Compounded Daily Return';
        layout.yaxis.title = 'Frequency';
        layout.xaxis.tickformat = '.0%';
        layout.yaxis.tickprefix = null;
        layout.barmode = 'stack';
        break;
      }
      case 'correlation': {
        const trace: any = {
          x: [],
          y: [],
          z: [],
          type: 'heatmap',
          colorscale: 'Electric',
          hovertemplate: '%{x} to %{y} correlation: %{z}<extra></extra>',
          zmin: -1,
          zmax: 1,
        };
        const sortedPriceData = sortBy(cloneDeep(priceData), 'short_name');
        sortedPriceData.forEach(security => {
          const dates = security.prices.map((p: any) => p.date);
          const close = security.prices.map((p: any) => p.close);
          // calculate correlation data for security
          sortedPriceData.forEach(comparedSecurity => {
            let coeff;
            let oldCoeff;

            // check existing points for reverse of security pair
            if (security.short_name !== comparedSecurity.short_name) {
              for (const pointIndex in trace.x) {
                if (
                  trace.x[pointIndex] === security.short_name &&
                  trace.y[pointIndex] === comparedSecurity.short_name
                ) {
                  oldCoeff = trace.z[pointIndex];
                }
              }
            }

            if (security.short_name === comparedSecurity.short_name) {
              // skip expensive computations if comparing security against itself
              if (dates.length <= 1) {
                return;
              }
              coeff = 1;
            } else if (oldCoeff != undefined) {
              // use old coefficient if we've computed the correlation already
              coeff = oldCoeff;
            } else {
              const comparedDates = comparedSecurity.prices.map((p: any) => p.date);
              const comparedClose = comparedSecurity.prices.map((p: any) => p.close);
              const overlappingDates = dates.filter((date: string) => comparedDates.includes(date));

              if (overlappingDates.length <= 1) {
                return;
              }

              const coeffData: number[] = [];
              const comparedCoeffData: number[] = [];

              overlappingDates.forEach((date: string) => {
                coeffData.push(close[dates.indexOf(date)]);
                comparedCoeffData.push(comparedClose[comparedDates.indexOf(date)]);
              });

              // @ts-ignore
              coeff = pcorrtest(coeffData, comparedCoeffData).pcorr;
              if (coeff) {
                coeff = parseFloat(coeff.toFixed(2));
              }
            }
            trace.x.push(security.short_name);
            trace.y.push(comparedSecurity.short_name);
            trace.z.push(coeff);
            layout.annotations.push({
              x: security.short_name,
              y: comparedSecurity.short_name,
              text: coeff,
              font: {
                color: coeff > 0 ? 'black' : 'white',
              },
              showarrow: false,
            });
          });
        });
        data.push(trace);
        layout.title = 'Correlations';
        layout.yaxis.tickprefix = null;
        layout.xaxis.type = 'category';
        layout.yaxis.type = 'category';
        break;
      }
    }

    // add indicators
    if (selectedIndicators?.length && data.length && !['histvar', 'correlation'].includes(selectedChart?.value)) {
      const indicators = selectedIndicators.map((i: any) => i.value);
      const indicatorHoverTemplate = '%{y:$,.2f}';

      // calculate date values across all securities
      const dateValues = reduce(
        data,
        (results, trace) => {
          const keyed = zipObject(trace.x, trace.y || trace.close);
          return mergeWith(results, keyed, function (obj, src) {
            if (isArray(obj)) {
              return obj.concat(src);
            } else {
              return [src];
            }
          });
        },
        zipObject(data[0].x, data[0].y || data[0].close)
      );

      // get mean for each date
      const dateMeans: any[] = map(dateValues, (values: any[], key: any) => {
        return {
          date: key,
          mean: mean(values),
        };
      }).sort((a: any, b: any) => (a.date > b.date ? 1 : -1));

      const startDate = dayjs(dateMeans[0]['date']);
      const endDate = dayjs(dateMeans[dateMeans.length - 1]['date']);
      const dayRange = endDate.diff(startDate, 'days');
      const indicatorPeriod = parseInt((dayRange / 10).toString());

      // add recessions
      if (indicators.includes('recessions')) {
        recessions?.forEach((indicator: any) => {
          layout.shapes.push({
            type: 'rect',
            xref: 'x',
            yref: 'paper',
            x0: indicator.start_date,
            x1: indicator.end_date || dayjs().format('YYYY-MM-DD'),
            y0: 0,
            y1: 1,
            fillcolor: 'rgba(211, 211, 211, 0.15)',
            line: {
              width: 0,
            },
          });
        });
      }

      // add simple moving average
      if (indicators.includes('simple-moving-average')) {
        const smaValues = SMA.calculate({
          period: indicatorPeriod,
          values: dateMeans.map((a: any) => a.mean),
        });
        data.push({
          name: 'Simple Moving Average',
          x: dateMeans.map((a: any) => a.date).slice(indicatorPeriod - 1),
          y: smaValues,
          hovertemplate: indicatorHoverTemplate,
          mode: 'lines',
          line: {
            color: 'lavender',
            dash: 'dashdot',
            shape: 'spline',
            width: 4,
          },
        });
      }

      // add exponential moving average
      if (indicators.includes('exponential-moving-average')) {
        const emaValues = EMA.calculate({
          period: indicatorPeriod,
          values: dateMeans.map((a: any) => a.mean),
        });
        data.push({
          name: 'Exponential Moving Average',
          x: dateMeans.map((a: any) => a.date).slice(indicatorPeriod - 1),
          y: emaValues,
          hovertemplate: indicatorHoverTemplate,
          mode: 'lines',
          line: {
            color: 'orchid',
            dash: 'dashdot',
            shape: 'spline',
            width: 4,
          },
        });
      }

      // add Bollinger Bands
      if (indicators.includes('bollinger-bands')) {
        let bollingerValues: BollingerBandsOutput[] = [];
        if (indicatorPeriod) {
          bollingerValues = BollingerBands.calculate({
            period: indicatorPeriod,
            values: dateMeans.map((a: any) => a.mean),
            stdDev: 2,
          });
        }
        data.push({
          name: 'Bollinger Bands',
          mode: 'lines',
          x: dateMeans.map((a: any) => a.date).slice(indicatorPeriod - 1),
          y: bollingerValues.map((a: any) => a.middle),
          hovertemplate: indicatorHoverTemplate,
          legendgroup: 'Bollinger Bands',
          line: {
            color: 'fuchsia',
            dash: 'dashdot',
            shape: 'spline',
            width: 4,
          },
        });
        data.push({
          name: 'Upper BB',
          mode: 'lines',
          x: dateMeans.map((a: any) => a.date).slice(indicatorPeriod - 1),
          y: bollingerValues.map((a: any) => a.upper),
          hovertemplate: indicatorHoverTemplate,
          legendgroup: 'Bollinger Bands',
          showlegend: false,
          line: {
            color: 'gray',
            shape: 'spline',
            width: 1,
          },
        });
        data.push({
          name: 'Lower BB',
          mode: 'lines',
          fill: 'tonexty',
          x: dateMeans.map((a: any) => a.date).slice(indicatorPeriod - 1),
          y: bollingerValues.map((a: any) => a.lower),
          hovertemplate: indicatorHoverTemplate,
          legendgroup: 'Bollinger Bands',
          showlegend: false,
          line: {
            color: 'gray',
            shape: 'spline',
            width: 1,
          },
        });
      }
      if (indicators.includes('rsi') || indicators.includes('trix')) {
        layout.grid = {
          rows: 2,
          columns: 1,
        };
        layout.yaxis.domain = [0.25, 1];

        // add RSI
        if (indicators.includes('rsi')) {
          const rsiValues = RSI.calculate({
            period: indicatorPeriod,
            values: dateMeans.map((a: any) => a.mean),
          });
          data.push({
            name: 'Relative Strength Index',
            mode: 'lines',
            x: dateMeans.map((a: any) => a.date).slice(indicatorPeriod - 1),
            y: rsiValues,
            yaxis: 'y2',
            line: {
              color: 'crimson',
              shape: 'spline',
            },
          });
        }

        // add TRIX
        if (indicators.includes('trix')) {
          const trixValues = TRIX.calculate({
            period: indicatorPeriod / 3,
            values: dateMeans.map((a: any) => a.mean),
          });
          data.push({
            name: 'Triple Exponential Average',
            mode: 'lines',
            x: dateMeans.map((a: any) => a.date).slice(indicatorPeriod - 1),
            y: trixValues,
            yaxis: 'y2',
            line: {
              color: 'indianred',
              shape: 'spline',
            },
          });
        }
      }
    }

    setChartData({ data: data, layout: layout });
  };

  // selections requiring updated price data have changed
  useEffect(() => {
    if (selectedSecurities) {
      getPriceData(
        selectedSecurities?.map((s: any) => s.value),
        setPriceData
      );
    } else {
      setChartData(null);
    }
  }, [selectedDates, selectedSecurities]);

  useEffect(() => {
    if (selectedRatioSecurity) {
      getPriceData([selectedRatioSecurity?.value], setRatioPriceData);
    }
  }, [selectedRatioSecurity]);

  useEffect(() => {
    if (!selectedSecurities?.length) {
      setChartData(null);
    }
  }, [selectedSecurities]);

  // selections requiring a chart update have changed
  useEffect(() => {
    generateChartData();
  }, [priceData, ratioPriceData, selectedChart, selectedScale, selectedIndicators, varThreshold]);

  // stop loading once chart has loaded
  useEffect(() => {
    setLoading(false);
  }, [chartData]);

  const renderVarPercentileInput = () => {
    if (selectedChart?.value === 'histvar') {
      return (
        <div>
          <label>Highlight Percentile:</label>
          <TextInput
            type="number"
            className="mb-2"
            min="0"
            max="100"
            defaultValue={varThreshold}
            handleChange={(e: any) => setVarThreshold(e.target.value)}
          />
        </div>
      );
    }
    return null;
  };

  const renderRatioSelect = () => {
    if (selectedChart?.value === 'ratio') {
      return (
        <ChartSelect
          isAsync
          className="pt-0 pb-2"
          placeholder="Search for a denominator security..."
          onChange={setSelectedRatioSecurity}
          loadOptions={getSecurityOptions}
        />
      );
    }
    return null;
  };

  const renderChart = () => {
    if (chartData) {
      return (
        <Plot
          className={`w-full chart-fluid ${loading ? 'loading' : ''}`}
          style={{
            minHeight: '400px',
          }}
          useResizeHandler
          data={chartData?.data}
          layout={chartData?.layout}
          config={{
            displaylogo: false,
            toImageButtonOptions: {
              format: 'png',
              height: 1080,
              width: 1920,
            },
          }}
        />
      );
    }
    return null;
  };

  return (
    <Layout auth={props.auth}>
      <Head title="Explorer" />

      <div className="py-3 py-12">
        <div className="mx-auto px-4 sm:px-6 lg:px-8">
          <DatePicker minDate={props.priceDates.min} maxDate={props.priceDates.max} handleChange={setSelectedDates} />
          <ChartSelect
            isAsync
            isMulti
            placeholder="Search for securities..."
            defaultValue={selectedSecurities}
            onChange={setSelectedSecurities}
            loadOptions={getSecurityOptions}
          />
          <ChartSelect defaultValue={selectedChart} onChange={setSelectedChart} options={chartOptions} />
          {renderVarPercentileInput()}
          {renderRatioSelect()}
          <ChartSelect defaultValue={selectedScale} onChange={setSelectedScale} options={scaleOptions} />
          <ChartSelect
            isMulti
            placeholder="Add technical indicators..."
            defaultValue={selectedIndicators}
            onChange={setSelectedIndicators}
            options={indicatorOptions}
          />
          {renderChart()}
        </div>
      </div>
    </Layout>
  );
}
