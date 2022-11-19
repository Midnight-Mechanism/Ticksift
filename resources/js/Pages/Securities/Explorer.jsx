import { useCallback, useState, useEffect } from 'react';
import Layout from '@/Layouts/Layout';
import { Head } from '@inertiajs/inertia-react';
import DatePicker from '@/Components/DatePicker';
import TextInput from '@/Components/TextInput';
import ChartSelect from '@/Components/ChartSelect';
import { getNumberWithOrdinal, formatCurrency } from '@/Utilities/NumberHelpers';
import Plot from 'react-plotly.js';
import pcorrtest from '@stdlib/stats/pcorrtest';
import { useLocalStorage } from '@/Hooks/UseLocalStorage';
import dayjs from 'dayjs';
import { SMA, EMA, BollingerBands, RSI, TRIX } from 'technicalindicators';
import { chartColor, gridColor } from '@/Utilities/Constants';

export default function Explorer(props) {
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

  const [selectedDates, setSelectedDates] = useState();
  const [selectedSecurities, setSelectedSecurities] = useLocalStorage('selectedSecurities');
  const [selectedChart, setSelectedChart] = useLocalStorage('selectedChart', chartOptions[0]);
  const [selectedScale, setSelectedScale] = useLocalStorage('selectedScale', scaleOptions[0]);
  const [selectedIndicators, setSelectedIndicators] = useLocalStorage('selectedIndicators');
  const [selectedRatioSecurity, setSelectedRatioSecurity] = useLocalStorage('selectedRatioSecurity');

  const [varThreshold, setVarThreshold] = useState(5);

  const [priceData, setPriceData] = useState();
  const [ratioPriceData, setRatioPriceData] = useState();
  const [chartData, setChartData] = useLocalStorage('chartData');
  const [recessions, setRecessions] = useState();

  const getSecurityOptions = useCallback(
    _.debounce((input, callback) => {
      axios
        .get(route('securities.search'), {
          params: {
            q: input,
          },
        })
        .then(res => {
          callback(res.data);
        });
    }, 250),
    []
  );

  const getPriceData = (securities, callback) => {
    if (selectedDates && securities) {
      axios
        .get(route('securities.prices'), {
          params: {
            security_ids: securities,
            dates: selectedDates,
          },
        })
        .then(res => callback(res.data));
    }
  };

  useEffect(() => {
    axios.get(route('indicators.recessions')).then(res => setRecessions(res.data));
  }, []);

  const generateChartData = () => {
    if (!priceData?.length) {
      return;
    }

    const layout = {
      autosize: true,
      font: {
        color: 'white',
      },
      dragmode: 'zoom',
      hovermode: 'x unified',
      xaxis: {
        gridcolor: gridColor,
        automargin: true,
        range: selectedDates?.length >= 2 ? selectedDates : [selectedDates[0], selectedDates[0]],
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
        priceData.forEach(security => {
          data.push({
            name: security.short_name,
            legendgroup: security.short_name,
            type: 'scattergl',
            x: security.prices.map(p => p.date),
            y: security.prices.map(p => p.close),
            text: security.prices.map(p => formatCurrency(p.close, security.currency_code)),
            hovertemplate: 'Close: %{text}',
          });
        });
        layout.title = 'Closing Prices';
        break;
      }
      case 'candlestick':
      case 'ohlc': {
        priceData.forEach(security => {
          data.push({
            name: security.short_name,
            legendgroup: security.short_name,
            type: selectedChart.value,
            x: security.prices.map(p => p.date),
            open: security.prices.map(p => p.open),
            high: security.prices.map(p => p.high),
            low: security.prices.map(p => p.low),
            close: security.prices.map(p => p.close),
          });
        });
        layout.title = 'Prices';
        break;
      }
      case 'bubble': {
        // determine max volume across all securities
        // this factors into bubble size
        let maxVolume = 0;
        priceData.forEach(security => {
          maxVolume = Math.max(maxVolume, Math.max(...security.prices.map(p => p.volume)));
        });

        priceData.forEach(security => {
          data.push({
            name: security.short_name,
            legendgroup: security.short_name,
            type: 'scattergl',
            mode: 'markers',
            x: security.prices.map(p => p.date),
            y: security.prices.map(p => p.close),
            marker: {
              size: security.prices.map(p => p.volume),
              sizemode: 'area',
              sizeref: (2.0 * maxVolume) / 15 ** 2,
            },
            text: security.prices.map(p => formatCurrency(p.close, security.currency_code)),
            hovertemplate: 'Close: %{text}<br>Volume: %{marker.size:,} shares',
          });
        });
        break;
      }
      case 'ratio': {
        if (ratioPriceData) {
          const ratioSecurity = ratioPriceData[0];

          priceData.forEach(security => {
            const overlappingPrices = security.prices
              .map(price => {
                const ratioPrice = ratioSecurity.prices.find(ratioPrice => ratioPrice.date == price.date);
                if (ratioPrice) {
                  price.ratio_close = ratioPrice.close;
                  price.ratio = price.close / price.ratio_close;
                  return price;
                }
              })
              .filter(price => price != null);
            if (overlappingPrices.length > 0) {
              data.push({
                name: security.short_name,
                legendgroup: security.short_name,
                type: 'scattergl',
                x: overlappingPrices.map(a => a.date),
                y: overlappingPrices.map(a => a.ratio),
                customdata: overlappingPrices.map(a => [
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
        const returns = [];
        priceData.forEach(security => {
          let previousPrice;
          security.prices.map(price => {
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
        const trace = {
          x: [],
          y: [],
          z: [],
          type: 'heatmap',
          colorscale: 'Electric',
          hovertemplate: '%{x} to %{y} correlation: %{z}<extra></extra>',
          zmin: -1,
          zmax: 1,
        };
        const sortedPriceData = _(_.cloneDeep(priceData)).sortBy('short_name').value();
        sortedPriceData.forEach(security => {
          const dates = security.prices.map(p => p.date);
          const close = security.prices.map(p => p.close);
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
              const comparedDates = comparedSecurity.prices.map(p => p.date);
              const comparedClose = comparedSecurity.prices.map(p => p.close);
              const overlappingDates = dates.filter(date => comparedDates.includes(date));

              if (overlappingDates.length <= 1) {
                return;
              }

              const coeffData = [];
              const comparedCoeffData = [];

              overlappingDates.forEach(date => {
                coeffData.push(close[dates.indexOf(date)]);
                comparedCoeffData.push(comparedClose[comparedDates.indexOf(date)]);
              });

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
      const indicators = selectedIndicators.map(i => i.value);
      const indicatorHoverTemplate = '%{y:$,.2f}';

      // calculate date values across all securities
      const dateValues = _.reduce(
        data,
        (results, trace) => {
          const keyed = _.zipObject(trace.x, trace.y || trace.close);
          return _.mergeWith(results, keyed, function (obj, src) {
            if (_.isArray(obj)) {
              return obj.concat(src);
            } else {
              return [src];
            }
          });
        },
        _.zipObject(data[0].x, data[0].y || data[0].close)
      );

      // get mean for each date
      const dateMeans = _.map(dateValues, (values, key) => {
        return {
          date: key,
          mean: _.mean(values),
        };
      }).sort((a, b) => (a.date > b.date ? 1 : -1));

      const startDate = dayjs(dateMeans[0]['date']);
      const endDate = dayjs(dateMeans[dateMeans.length - 1]['date']);
      const dayRange = endDate.diff(startDate, 'days');
      const indicatorPeriod = parseInt(dayRange / 10);

      // add recessions
      if (indicators.includes('recessions')) {
        recessions?.forEach(indicator => {
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
        let smaValues = SMA.calculate({
          period: indicatorPeriod,
          values: dateMeans.map(a => a.mean),
        });
        data.push({
          name: 'Simple Moving Average',
          x: dateMeans.map(a => a.date).slice(indicatorPeriod - 1),
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
        let emaValues = EMA.calculate({
          period: indicatorPeriod,
          values: dateMeans.map(a => a.mean),
        });
        data.push({
          name: 'Exponential Moving Average',
          x: dateMeans.map(a => a.date).slice(indicatorPeriod - 1),
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
        let bollingerValues = [];
        if (indicatorPeriod) {
          bollingerValues = BollingerBands.calculate({
            period: indicatorPeriod,
            values: dateMeans.map(a => a.mean),
            stdDev: 2,
          });
        }
        data.push({
          name: 'Bollinger Bands',
          mode: 'lines',
          x: dateMeans.map(a => a.date).slice(indicatorPeriod - 1),
          y: bollingerValues.map(a => a.middle),
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
          x: dateMeans.map(a => a.date).slice(indicatorPeriod - 1),
          y: bollingerValues.map(a => a.upper),
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
          x: dateMeans.map(a => a.date).slice(indicatorPeriod - 1),
          y: bollingerValues.map(a => a.lower),
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
          let rsiValues = RSI.calculate({
            period: indicatorPeriod,
            values: dateMeans.map(a => a.mean),
          });
          data.push({
            name: 'Relative Strength Index',
            mode: 'lines',
            x: dateMeans.map(a => a.date).slice(indicatorPeriod - 1),
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
          let trixValues = TRIX.calculate({
            period: indicatorPeriod / 3,
            values: dateMeans.map(a => a.mean),
          });
          data.push({
            name: 'Triple Exponential Average',
            mode: 'lines',
            x: dateMeans.map(a => a.date).slice(indicatorPeriod - 1),
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
        selectedSecurities?.map(s => s.value),
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
            handleChange={e => setVarThreshold(e.target.value)}
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
          label="Ratio"
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
          className="w-full"
          style={{
            minHeight: '400px',
            height: '70vmin',
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
    <Layout auth={props.auth} errors={props.errors}>
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
