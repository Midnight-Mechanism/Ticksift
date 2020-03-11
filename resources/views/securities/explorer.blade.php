@extends('layouts.app')

@section('template_title')
    Security Explorer
@endsection

@section('content')
    <div class="container-fluid">
        @include('partials/date-picker')
        <div class="row pb-3">
            <div class="col-12">
                <select id="select-portfolios" style="display: none"></select>
            </div>
        </div>
        <div class="row pb-3">
            <div class="col-12">
                <select id="select-tickers" multiple="multiple" style="display: none"></select>
            </div>
        </div>
        <div id="security-results" style="visibility: hidden">
            <div class="row">
                <div class="col-12 col-lg-6">
                    <select id="select-time-chart-type" style="display: none">
                        <option value="line" selected>Line</option>
                        <option value="candlestick">Candlestick</option>
                        <option value="ohlc">OHLC</option>
                        <option value="bubble">Bubble</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-lg-6 my-2">
                    <div id="time-chart" class="chart"></div>
                </div>
                <div class="col-12 col-lg-6 my-2">
                    <div id="correlation-chart" class="chart"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer_scripts')
    @include('scripts/date-picker')
    <script type="text/javascript">

        const chartColor = "#000F0F";
        const gridColor = "#154B4B";

        var timeLayout = {
            autosize: true,
            showlegend: true,
            title: "Prices",
            font: {
                family: "Hind Madurai",
                color: "white",
            },
            dragmode: "zoom",
            xaxis: {
                title: "Date",
                gridcolor: gridColor,
                autorange: true,
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
            toImageButtonOptions: {
                format: "png",
                height: 1080,
                width: 1920,
            },
        };

        var securityPrices = [];
        var timeChart = document.getElementById('time-chart');
        var correlationChart = document.getElementById('correlation-chart');

        function buildTimeChart(chartType) {
            let traces = [];
            let timeConfig = _.cloneDeep(config);
            const dates = $("#input-dates").val();
            const tickers = Object.keys(securityPrices);

            let filename = [
                "ticksift",
                chartType,
                "chart",
                $("#input-dates").val(),
            ];
            if (tickers.length === 1) {
                filename.splice(1, 0, tickers[0]);
            }
            filename = filename.join("_").split(" ").join("_");
            timeConfig.toImageButtonOptions.filename = filename;
            switch (chartType) {
                case "line":
                    for (const [ticker, prices] of Object.entries(securityPrices)) {
                        traces.push({
                            name: ticker,
                            legendgroup: ticker,
                            type: "scattergl",
                            mode: "line",
                            x: prices.map(a => a.date),
                            y: prices.map(a => a.close),
                        });
                    }
                    timeLayout.title = "Closing Prices";
                    break;
                case "candlestick":
                case "ohlc":
                    for (const [ticker, prices] of Object.entries(securityPrices)) {
                        traces.push({
                            name: ticker,
                            legendgroup: ticker,
                            type: chartType,
                            x: prices.map(a => a.date),
                            open: prices.map(a => a.open),
                            high: prices.map(a => a.high),
                            low: prices.map(a => a.low),
                            close: prices.map(a => a.close),
                        });
                    }
                    timeLayout.title = "Prices";
                    break;
                case "bubble":
                    // determine max volume across all securities
                    // this factors into bubble size
                    let maxVolume = 0;
                    for (const securityData of Object.values(securityPrices)) {
                        maxVolume = Math.max(
                            maxVolume,
                            Math.max(...securityData.map(a => a.volume))
                        );
                    }

                    for (const [ticker, prices] of Object.entries(securityPrices)) {
                        let volume = prices.map(a => a.volume);

                        traces.push({
                            name: ticker,
                            legendgroup: ticker,
                            type: "scattergl",
                            mode: "markers",
                            x: prices.map(a => a.date),
                            y: prices.map(a => a.close),
                            marker: {
                                size: volume,
                                sizemode: "area",
                                sizeref: 2.0 * (maxVolume) / (15**2),
                            },
                            hovertemplate: "Close: %{y}<br>Volume: %{marker.size:,} shares",
                        });
                    }
                    timeLayout.title = "Closing Prices Weighted by Trading Volume";
                    break;
            }
            Plotly.newPlot(
                timeChart,
                traces,
                timeLayout,
                timeConfig
            );
        }

        function processChartData() {
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
            correlationConfig = _.cloneDeep(config);
            correlationConfig.toImageButtonOptions.filename = [
                "ticksift",
                "correlations",
                $("#input-dates").val(),
            ].join("_").split(" ").join("_");

            const sortedSecurityPrices = _(_.cloneDeep(securityPrices)).toPairs().sortBy(0).fromPairs().value();
            for (let [ticker, prices] of Object.entries(sortedSecurityPrices)) {
                let dates = prices.map(a => a.date);
                let close = prices.map(a => a.close);

                lastDates.push(moment(dates[dates.length - 1]));

                // calculate correlation data for security
                for (let [compTicker, compPrices] of Object.entries(sortedSecurityPrices)) {
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
                        if (dates.length <= 1) {
                            continue;
                        }
                        coeff = 1;
                        // use old coefficient if we've computed the correlation already
                    } else if (oldCoeff != undefined) {
                        coeff = oldCoeff;
                    } else {
                        let compDates = compPrices.map(a => a.date);
                        let compClose = compPrices.map(a => a.close);

                        let overlappingDates = dates.filter(date => compDates.includes(date));
                        if (overlappingDates.length <= 1) {
                            continue;
                        }

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
            }

            // hide correlations and expand time chart if no correlations
            if (correlationTraces[0].z.length > 0) {
                Plotly.newPlot(correlationChart, correlationTraces, correlationLayout, correlationConfig);
                $.merge(
                    $(timeChart).parent(),
                    $("#select-time-chart-type").parent()
                ).addClass("col-lg-6");
            }
            else {
                Plotly.purge(correlationChart);
                $.merge(
                    $(timeChart).parent(),
                    $("#select-time-chart-type").parent()
                ).removeClass("col-lg-6");
            }
            buildTimeChart($("#select-time-chart-type").val());
        }

        function getPortfolioData() {
            let id = $("#select-portfolios").val();

            if (id) {
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
                });
            }
        }

        function getSecurityData() {
            let ids = $("#select-tickers").val();
            let dates = $("#input-dates").val();

            $("body").addClass("waiting");
            $(".chart").addClass("outdated");
            $.post("{{ route('securities.prices') }}", data = {
                ids: ids,
                dates: dates,
            }).done(function(prices) {
                securityPrices = prices;
                if (ids.length > 0) {
                    processChartData();
                    $("#security-results").css("visibility", "visible");
                }
                $("body").removeClass("waiting");
                $(".chart").removeClass("outdated");
            });
        }

        $("#select-tickers").select2(({
            placeholder: "Please enter a security ticker or name (AAPL, Apple, etc.)...",
            allowClear: true,
            minimumInputLength: 1,
            escapeMarkup: function (text) {
                return text;
            },
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
                $("#security-results").css("visibility", "hidden");
                $("body").removeClass("waiting");
            }
        });

        $("#select-portfolios").select2(({
            placeholder: "Add entire portfolios (e.g. FAANG)...",
            minimumInputLength: 1,
            escapeMarkup: function (text) {
                return text;
            },
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

        $("#select-time-chart-type").select2().on("select2:select", function() {
            buildTimeChart($(this).val());
        });

        @if($old_securities)
            @foreach($old_securities as $security)
                if (!$("#select-tickers").val().includes("{{ $security->id }}")) {
                    $("#select-tickers").append(new Option(
                        "{{ $security->ticker }} - {{ $security->name }}",
                        {{ $security->id }},
                        true,
                        true,
                    ));
                }
            @endforeach
            $("#select-tickers").trigger("change");
        @endif

        let tickerToAdd = new URLSearchParams(location.search).get("add_ticker");
        if (tickerToAdd) {
            $.get("{{ route('securities.find') }}", data = {
                ticker: tickerToAdd,
            }).done(function(security) {
                if (!$("#select-tickers").val().includes(security.id.toString())) {
                    let option = new Option(security.ticker + " - " + security.name, security.id, true, true);
                    $("#select-tickers").append(option);
                }
                $("#select-tickers").trigger("change");
            });
        }
    </script>
@endsection
