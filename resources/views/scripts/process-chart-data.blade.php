<script type="text/javascript">

    const chartColor = "#000F0F";
    const gridColor = "#154B4B";

    var candlestickLayout = {
        autosize: true,
        font: {
            family: "Hind Madurai",
            color: "white",
        },
        dragmode: "zoom",
        xaxis: {
            title: "Date",
            gridcolor: gridColor,
        },
        yaxis: {
            title: "Price",
            tickprefix: "$",
            gridcolor: gridColor,
        },
        paper_bgcolor: chartColor,
        plot_bgcolor: chartColor,
    };

    var correlationLayout = {
        autosize: true,
        title: "Correlations",
        font: {
            family: "Hind Madurai",
            color: "white",
        },
        dragmode: "zoom",
        xaxis: {
            gridcolor: gridColor,
        },
        yaxis: {
            gridcolor: gridColor,
        },
        annotations: [],
        paper_bgcolor: chartColor,
        plot_bgcolor: chartColor,
    };


    var config = {
        collaborate: false,
        displaylogo: false,
        responsive: true,
    };

    var securityPrices = [];
    var candlestickChart = document.getElementById('candlestick-chart');
    var correlationChart = document.getElementById('correlation-chart');
    function processChartData() {

        candlestickLayout.title = "Prices";

        let candleTraces = [];
        let correlationTraces = [{
            x: [],
            y: [],
            z: [],
            type: "heatmap",
            colorscale: "Electric",
            zmin: -1,
            zmax: 1,
        }];

        let lastDates = [];
        correlationLayout.annotations = [];

        for (const security of Object.entries(securityPrices)) {
            let ticker = security[0];
            let prices = security[1];

            let dates = prices.map(a => a.date);
            let open = prices.map(a => a.open);
            let high = prices.map(a => a.high);
            let low = prices.map(a => a.low);
            let close = prices.map(a => a.close);

            lastDates.push(moment(dates[dates.length - 1]));

            // calculate correlation data for security
            for (const compSecurity of Object.entries(securityPrices)) {
                let compTicker = compSecurity[0];
                let coeff;
                let oldCoeff;

                // check existing points for reverse of ticker pair
                if (ticker !== compTicker) {
                    for (const pointIndex in correlationTraces[0].x) {
                        if (
                            correlationTraces[0].x[pointIndex] === compTicker &&
                            correlationTraces[0].y[pointIndex] === ticker
                        ) {
                            oldCoeff = correlationTraces[0].z[pointIndex];
                        }
                    }
                }

                // skip expensive computations if comparing ticker against itself
                if (ticker === compTicker) {
                    coeff = 1;
                    // use old coefficient if we've computed the correlation already
                } else if (oldCoeff != undefined) {
                    coeff = oldCoeff;
                } else {
                    let compPrices = compSecurity[1];
                    let compDates = compPrices.map(a => a.date);
                    let compClose = compPrices.map(a => a.close);
                    let overlappingDates = dates.filter(date => compDates.includes(date));
                    let tickerData = [];
                    let compTickerData = [];
                    overlappingDates.forEach(function(date) {
                        let dateIndex = dates.indexOf(date);
                        let compDateIndex = compDates.indexOf(date);
                        tickerData.push(close[dateIndex]);
                        compTickerData.push(compClose[compDateIndex]);
                    });

                    coeff = jStat.corrcoeff(tickerData, compTickerData);
                    if (coeff) {
                        coeff = parseFloat(coeff.toFixed(2));
                    }

                    // correct values outside of correlation range
                    coeff = coeff > 1 ? 1 : coeff;
                    coeff = coeff < -1 ? -1 : coeff;
                }

                correlationTraces[0].x.push(ticker);
                correlationTraces[0].y.push(compTicker);
                correlationTraces[0].z.push(coeff);
                correlationLayout.annotations.push({
                    x: ticker,
                    y: compTicker,
                    text: coeff,
                    font: {
                        color: coeff > 0 ? "black" : "white",
                    },
                    showarrow: false,
                });
            }

            candleTraces.push(
                {
                    name: ticker + " - Open",
                    legendgroup: ticker,
                    type: "scattergl",
                    x: dates,
                    y: open,
                },
                {
                    name: ticker + " - High",
                    legendgroup: ticker,
                    type: "scattergl",
                    x: dates,
                    y: high,
                },
                {
                    name: ticker + " - Low",
                    legendgroup: ticker,
                    type: "scattergl",
                    x: dates,
                    y: low,
                },
                {
                    name: ticker + " - Close",
                    legendgroup: ticker,
                    type: "scattergl",
                    x: dates,
                    y: close,
                }
            );
        }

        Plotly.newPlot(candlestickChart, candleTraces, candlestickLayout, config);
        Plotly.newPlot(correlationChart, correlationTraces, correlationLayout, config);
    }

    function getPortfolioData() {
        let id = $("#select-portfolios").val();

        if (id) {
            $("body").addClass("waiting");
            $.post("{{ route('portfolios.securities') }}", data = {
                id: id,
            }).done(function(securities) {
                for (security of securities) {
                    if (!$("#select-tickers").val().includes(security.id.toString())) {
                        let option = new Option(security.ticker + " - " + security.name, security.id, true, true);
                        $("#select-tickers").append(option);
                    }
                }
                $("#select-tickers").trigger("change");
                $("body").removeClass("waiting");
            });
        }
    }


    function getSecurityData() {
        let ids = $("#select-tickers").val();
        let dates = $("#input-dates").val();

        if (ids.length > 0) {
            $("body").addClass("waiting");
            $.post("{{ route('securities.prices') }}", data = {
                ids: ids,
                dates: dates,
            }).done(function(prices) {
                securityPrices = prices;
                processChartData();
                $("body").removeClass("waiting");
            });
        }
    }

    $("#select-tickers").select2(({
        placeholder: "Please enter a security ticker or name (AAPL, Apple, etc.)...",
        allowClear: true,
        minimumInputLength: 1,
        ajax: {
            url: "/securities/search",
            delay: 250,
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            text: item.ticker + " - " + item.name,
                            id: item.id
                        }
                    })
                };
            },
        },
    })).on("select2:select", function() {
        // clear results to prevent option list getting too large
        $(".select2-results__option").remove();
    }).on("select2:unselect", function () {
        let vals = $("#select-tickers").val();
        if (!vals || !vals.length) {
            Plotly.purge(candlestickChart);
            Plotly.purge(correlationChart);
            $("body").removeClass("waiting");
        }
    });

    $("#select-portfolios").select2(({
        placeholder: "Add entire portfolios (e.g. FAANG)...",
        minimumInputLength: 1,
        ajax: {
            url: "/portfolios/search",
            delay: 250,
            processResults: function (data) {
                return {
                    results: $.map(data, function (item) {
                        return {
                            text: item.name,
                            id: item.id
                        }
                    })
                };
            },
        },
    })).on("select2:select", function() {
        getPortfolioData();
        $("#select-portfolios").val(null).trigger("change");
    });

    $("#input-dates").change(getSecurityData);
    $("#select-tickers").change(getSecurityData);

</script>
