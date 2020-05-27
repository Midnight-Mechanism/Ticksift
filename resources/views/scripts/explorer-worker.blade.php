<script id="explorer-worker" type="javascript/worker">
    importScripts("{{ env('APP_URL') }}/js/worker-deps.js");

    function formatCurrency(number, code) {
        if (!code) {
            return number;
        }
        return new Intl.NumberFormat("en-US", {
            style: "currency",
            currency: code,
        }).format(number);
    }

    onmessage = function(e) {
        // Parse data from message
        securityPrices = e.data.securityPrices;
        sortedSecurityPrices = e.data.sortedSecurityPrices;
        traces = e.data.traces;
        explorerLayout = e.data.explorerLayout;
        chartType = e.data.chartType;
        ratioPrices = e.data.ratioPrices;
        inputThreshold = e.data.inputThreshold;

        // Update traces based on chart type
        switch (chartType) {
            case "line":
                for (const securityData of Object.values(securityPrices)) {
                    traces.push({
                        name: securityData.short_name,
                        legendgroup: securityData.short_name,
                        type: "scattergl",
                        x: securityData.prices.map(a => a.date),
                        y: securityData.prices.map(a => a.close),
                        text: securityData.prices.map(a => formatCurrency(a.close, securityData.currency_code)),
                        hovertemplate: "Close: %{text}",
                    });
                }
                explorerLayout.title = "Closing Prices";
                break;
            case "candlestick":
            case "ohlc":
                for (const securityData of Object.values(securityPrices)) {
                    traces.push({
                        name: securityData.short_name,
                        legendgroup: securityData.short_name,
                        type: chartType,
                        x: securityData.prices.map(a => a.date),
                        open: securityData.prices.map(a => a.open),
                        high: securityData.prices.map(a => a.high),
                        low: securityData.prices.map(a => a.low),
                        close: securityData.prices.map(a => a.close),
                    });
                }
                explorerLayout.title = "Prices";
                explorerLayout.xaxis.rangeslider = {};
                break;
            case "bubble":
                // determine max volume across all securities
                // this factors into bubble size
                let maxVolume = 0;
                for (const securityData of Object.values(securityPrices)) {
                    maxVolume = Math.max(
                        maxVolume,
                        Math.max(...securityData.prices.map(a => a.volume))
                    );
                }

                for (const securityData of Object.values(securityPrices)) {
                    let volume = securityData.prices.map(a => a.volume);

                    traces.push({
                        name: securityData.short_name,
                        legendgroup: securityData.short_name,
                        type: "scattergl",
                        mode: "markers",
                        x: securityData.prices.map(a => a.date),
                        y: securityData.prices.map(a => a.close),
                        marker: {
                            size: volume,
                            sizemode: "area",
                            sizeref: 2.0 * (maxVolume) / (15**2),
                        },
                        text: securityData.prices.map(a => formatCurrency(a.close, securityData.currency_code)),
                        hovertemplate: "Close: %{text}<br>Volume: %{marker.size:,} shares",
                    });
                }
                explorerLayout.title = "Closing Prices Weighted by Trading Volume";
                break;
            case "ratio":
                if(ratioPrices) {
                    for (const securityData of Object.values(securityPrices)) {
                        let overlappingPrices = securityData.prices.map(price => {
                            ratioPrice = ratioPrices.prices.find(ratioPrice => ratioPrice.date == price.date);
                            if (ratioPrice) {
                                price.ratio_close = ratioPrice.close;
                                price.ratio = price.close / price.ratio_close;
                                return price;
                            }
                        }).filter(price => price != null);
                        if(overlappingPrices.length > 0) {
                            traces.push({
                                name: securityData.short_name,
                                legendgroup: securityData.short_name,
                                type: "scattergl",
                                x: overlappingPrices.map(a => a.date),
                                y: overlappingPrices.map(a => a.ratio),
                                customdata: overlappingPrices.map(a => [
                                        formatCurrency(a.close, securityData.currency_code),
                                        formatCurrency(a.ratio_close, ratioPrices.currency_code),
                                ]),
                                hovertemplate: "Close: %{customdata[0]}<br>" + ratioPrices.short_name +" Close: %{customdata[1]}<br>Ratio: %{y}",
                            });
                        }
                    }
                    explorerLayout.title = "Closing Prices to " + ratioPrices.short_name;
                    explorerLayout.yaxis.tickprefix = null;
                }
                break;
            case "histvar":
                let returns = [];
                for (const securityData of Object.values(securityPrices)) {
                    let previousPrice;

                    securityData.prices.map(price => {
                        if (previousPrice) {
                            returns.push(Math.log(price.close / previousPrice.close));
                        }
                        previousPrice = price;
                    });
                }
                returns.sort((a, b) => a - b);

                let threshold = inputThreshold / 100;

                traces.push(
                    {
                        name: "Below " + moment.localeData().ordinal(Number(inputThreshold)) + " Percentile",
                        type: "histogram",
                        x: returns.slice(0, threshold * returns.length),
                        xbins: {
                            size: 0.01,
                        },
                        marker: {
                            color: "#E18D96",
                            line: {
                                color: "red",
                                width: 1,
                            },
                        },
                    },
                    {
                        name: "Above " + moment.localeData().ordinal(Number(inputThreshold)) + " Percentile",
                        type: "histogram",
                        x: returns.slice(threshold * returns.length),
                        xbins: {
                            size: 0.01,
                        },
                        marker: {
                            color: "dodgerblue",
                            line: {
                                color: "blue",
                                width: 1,
                            },
                        },
                    }
                );

                explorerLayout.title = "Historical Value at Risk";
                explorerLayout.xaxis.title = "Continuously Compounded Daily Return";
                explorerLayout.yaxis.title = "Frequency";
                explorerLayout.legend.orientation = "v",

                explorerLayout.xaxis.rangeslider = null;
                explorerLayout.yaxis.tickprefix = null;
                explorerLayout.xaxis.tickformat = "%";
                explorerLayout.xaxis.type = "linear";
                explorerLayout.barmode = "stack";
                delete explorerLayout.showlegend;
                break;
            case "correlation":
                let trace = {
                    x: [],
                    y: [],
                    z: [],
                    type: "heatmap",
                    colorscale: "Electric",
                    hovertemplate: "%{x} to %{y} correlation: %{z}<extra></extra>",
                    zmin: -1,
                    zmax: 1,
                };

                let lastDates = [];

                for (let securityData of Object.values(sortedSecurityPrices)) {
                    let dates = securityData.prices.map(a => a.date);
                    let close = securityData.prices.map(a => a.close);

                    lastDates.push(moment(dates[dates.length - 1]));

                    // calculate correlation data for security
                    for (let compSecurityData of Object.values(sortedSecurityPrices)) {
                        let coeff;
                        let oldCoeff;

                        // check existing points for reverse of security pair
                        if (securityData.short_name !== compSecurityData.short_name) {
                            for (const pointIndex in trace.x) {
                                if (
                                    trace.x[pointIndex] === securityData.short_name &&
                                    trace.y[pointIndex] === compSecurityData.short_name
                                ) {
                                    oldCoeff = trace.z[pointIndex];
                                }
                            }
                        }

                        // skip expensive computations if comparing security against itself
                        if (securityData.short_name === compSecurityData.short_name) {
                            if (dates.length <= 1) {
                                continue;
                            }
                            coeff = 1;
                            // use old coefficient if we've computed the correlation already
                        } else if (oldCoeff != undefined) {
                            coeff = oldCoeff;
                        } else {

                            let compDates = compSecurityData.prices.map(a => a.date);
                            let compClose = compSecurityData.prices.map(a => a.close);

                            let overlappingDates = dates.filter(date => compDates.includes(date));
                            if (overlappingDates.length <= 1) {
                                continue;
                            }

                            let coeffData = [];
                            let compCoeffData = [];
                            overlappingDates.forEach(function(date) {
                                let dateIndex = dates.indexOf(date);
                                let compDateIndex = compDates.indexOf(date);
                                coeffData.push(close[dateIndex]);
                                compCoeffData.push(compClose[compDateIndex]);
                            });

                            coeff = jStat.corrcoeff(coeffData, compCoeffData);
                            if (coeff) {
                                coeff = parseFloat(coeff.toFixed(2));
                            }

                            // correct values outside of correlation range
                            coeff = coeff > 1 ? 1 : coeff;
                            coeff = coeff < -1 ? -1 : coeff;
                        }

                        trace.x.push(securityData.short_name);
                        trace.y.push(compSecurityData.short_name);
                        trace.z.push(coeff);
                        explorerLayout.annotations.push({
                            x: securityData.short_name,
                            y: compSecurityData.short_name,
                            text: coeff,
                            font: {
                                color: coeff > 0 ? "black" : "white",
                            },
                            showarrow: false,
                        });
                    }
                }
                traces.push(trace);

                explorerLayout.title = "Correlations";
                explorerLayout.yaxis.tickprefix = null;
                explorerLayout.xaxis.type = "category";
                explorerLayout.yaxis.type = "category";
                break;
        }

        // Return correlation trace data for graphing
        postMessage({ traces: traces, layout: explorerLayout });
    }
</script>
