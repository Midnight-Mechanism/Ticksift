import { useEffect } from 'react';
import { useLocalStorage } from '@/Hooks/UseLocalStorage';
import Plot from 'react-plotly.js';
import Color from 'color';
import { chartColor } from '@/Utilities/Constants';
import { formatCurrency } from '@/Utilities/NumberHelpers';

export default function MomentumTreemap({ data, calculateSecuritySize }) {
  const [chartData, setChartData] = useLocalStorage('momentumChartData');
  useEffect(() => {
    if (data) {
      setChartData(generateChartData(data));
    }
  }, [data]);

  const generateChartData = rawData => {
    const results = {
      labels: [],
      parents: [],
      text: [],
      colors: [],
      lineColors: [],
      values: [],
      customdata: [],
    };

    rawData = _(rawData)
      .map(securityData => {
        if (!securityData.sector) {
          securityData.sector = 'No Sector';
          securityData.industry = 'No Industry';
        }
        // consolidate "increase" and "decrease" into "change"
        if ('increase' in securityData) {
          securityData.change = securityData.increase;
          delete securityData['increase'];
        } else if ('decrease' in securityData) {
          securityData.change = securityData.decrease * -1;
          delete securityData['decrease'];
        }
        return securityData;
      })
      .without(undefined)
      .groupBy('sector')
      .mapValues(function (securities) {
        const sector_color = securities[0].sector_color;
        return {
          color: sector_color ? '#' + sector_color : null,
          industries: _.groupBy(securities, 'industry'),
        };
      })
      .value();

    for (const [sector, sectorData] of Object.entries(rawData)) {
      let sectorLabel = `<b><span style='text-transform: uppercase'>${sector}</span></b>`;
      results.labels.push(sectorLabel);
      results.parents.push('');
      results.text.push(null);
      results.colors.push(Color(sectorData.color).darken(0.75).hex());
      results.lineColors.push(sectorData.color);
      results.values.push(0);
      results.customdata.push(null);
      for (const [industry, industryData] of Object.entries(sectorData.industries)) {
        let industryLabel = `<span style='text-transform: uppercase'>${industry}</span>`;
        results.labels.push(industryLabel);
        results.parents.push(sectorLabel);
        results.text.push(null);
        results.colors.push(null);
        results.lineColors.push(sectorData.color);
        results.values.push(0);
        results.customdata.push(null);
        for (const securityData of Object.values(industryData)) {
          results.parents.push(industryLabel);
          results.customdata.push(securityData.ticker);
          const label = `<b><span style='font-size: 200%'>${securityData.short_name}</span></b>`;
          let text = securityData.short_name == securityData.name ? '' : securityData.name;
          const absChange = Math.abs(securityData.change);
          const colorChange = Math.min(absChange * 15, 8.5);
          const percent = absChange * 100;

          text += '<br><br><b><span style="font-size: 150%">';
          if (securityData.change < 0) {
            text += '-';
            results.colors.push(Color('#1A0000').lighten(colorChange).hex());
          } else {
            text += '+';
            results.colors.push(Color('#001A04').lighten(colorChange).hex());
          }
          text +=
            percent.toLocaleString(undefined, {
              maximumFractionDigits: 2,
            }) + '%</span></b>';
          text += '<br>' + formatCurrency(securityData.latest_close, securityData.currency_code) + ' (';
          if (securityData.change < 0) {
            text += '➘';
          } else {
            text += '➚';
          }
          text +=
            formatCurrency(
              Math.abs(securityData.latest_close - securityData.earliest_close),
              securityData.currency_code
            ) + ')';
          results.labels.push(label);
          results.text.push(text);
          results.values.push(calculateSecuritySize(securityData));
          results.lineColors.push(null);
        }
      }
    }

    return results;
  };

  if (chartData?.values?.length) {
    return (
      <Plot
        className="w-full"
        useResizeHandler
        style={{
          minHeight: '800px',
          height: '70vmin',
        }}
        data={[
          {
            type: 'treemap',
            labels: chartData.labels,
            parents: chartData.parents,
            text: chartData.text,
            values: chartData.values,
            customdata: chartData.customdata,
            hoverinfo: 'label+text',
            textposition: 'middle center',
            marker: {
              colors: chartData.colors,
              line: {
                color: chartData.lineColors,
              },
            },
          },
        ]}
        layout={{
          autosize: true,
          paper_bgcolor: chartColor,
          plot_bgcolor: chartColor,
          margin: {
            l: 0,
            r: 0,
            b: 0,
            t: 0,
          },
        }}
        config={{
          displaylogo: false,
          toImageButtonOptions: {
            format: 'png',
            height: 3000,
            width: 4000,
            filename: ['ticksift', 'sectors'].join('_').split(' ').join('_'),
          },
        }}
      />
    );
  } else {
    return <div />;
  }
}
