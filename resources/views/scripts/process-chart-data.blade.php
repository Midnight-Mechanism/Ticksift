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

        candlestickLayout.title = "Prices: " + Object.keys(securityPrices).sort().join(" - ");

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
                    compTickerData.push(compClose[dateIndex]);
                });
                correlationTraces[0].x.push(ticker);
                correlationTraces[0].y.push(compTicker);
                correlationTraces[0].z.push(jStat.corrcoeff(tickerData, compTickerData));
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

    function getSecurityData() {
        ids = $("#select-ticker").val();
        $("body").addClass("waiting");
        $.post("{{ route('securities.prices') }}", data = {
            ids: ids
        }).done(function(msg) {
            securityPrices = msg;
            processChartData();
            $("body").removeClass("waiting");
        });
    }

    $("#select-ticker").select2(({
        placeholder: "Please enter a ticker symbol (e.g. AAPL)...",
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
        let vals = $("#select-ticker").val();
        if (!vals || !vals.length) {
            Plotly.purge(candlestickChart);
            Plotly.purge(correlationChart);
            $("body").removeClass("waiting");
        }
    });

    $("#select-ticker").change(getSecurityData);

</script>
