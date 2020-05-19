@extends('layouts.app')

@section('template_title')
    Security Explorer
@endsection

@section('content')
    <div class="container-fluid">
        @include('partials.date-picker')
        @auth
            <div class="row pb-2">
                <div class="col-12">
                    <select id="select-portfolios" class="invisible">
                        <option></option>
                        @foreach(
                            \Auth::check() ?
                            \Auth::user()->portfolios :
                            \App\Models\Portfolio::doesntHave('users')->get()
                            as $portfolio
                            )
                            <option value="{{ $portfolio->id }}">{{$portfolio->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endauth
        @include('partials.security-picker')
        <div id="security-results" class="invisible">
            <div class="row">
                <div class="col-12 col-lg-6 d-flex py-1">
                    <select id="select-explorer-chart-type" class="invisible">
                        <option value="line" selected>Line Chart</option>
                        <option value="candlestick">Candlestick Chart</option>
                        <option value="ohlc">OHLC Chart</option>
                        <option value="bubble">Bubble Chart</option>
                        <option value="ratio">Ratio Chart</option>
                        <option value="histvar">Historical VaR Chart</option>
                        <option value="correlation">Correlation Chart</option>
                    </select>
                </div>
                <div class="col-12 col-lg-6 d-flex py-1">
                    <select id="select-explorer-chart-scale" class="invisible">
                        <option value="linear" selected>Linear Scale</option>
                        <option value="log">Logarithmic Scale</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-12 d-flex py-1">
                    <select id="select-explorer-chart-indicators" class="invisible" multiple="multiple">
                        <option value="simple-moving-average">Simple Moving Average (SMA)</option>
                        <option value="exponential-moving-average">Exponential Moving Average (EMA)</option>
                        <option value="bollinger-bands">Bollinger Bands</option>
                        <option value="rsi">Relative Strength Index (RSI)</option>
                        <option value="trix">Triple Exponential Average (TRIX)</option>
                        <option value="recessions">Recessions</option>
                    </select>
                </div>
            </div>
            <div id="select-ratio-container" class="row pt-2 d-none">
                <div class="col-12">
                    <label class="label-centered">Denominator Security:</label>
                    <select id="select-ratio" class="invisible"></select>
                </div>
            </div>
            <div id="input-threshold-container" class="row pt-2 d-none">
                <div class="col-12">
                    <label class="label-centered">Highlight Percentile:</label>
                    <input id="input-threshold" class="text-center" type="number" min="0" max="100" value="5">
                </div>
            </div>
            <div class="row">
                <div class="col-12 pt-2">
                    <div id="explorer-loader" class="loader"></div>
                    <div id="explorer-chart" class="chart"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer_scripts')
    @include('scripts.date-picker')
    @include('scripts.security-picker')
    @include('scripts.explorer-worker')

    <script type="text/javascript">

        const chartColor = "#000F0F";
        const gridColor = "#154B4B";

        var explorerLayout = {
            autosize: true,
            font: {
                family: "Hind Madurai",
                color: "white",
            },
            dragmode: "zoom",
            xaxis: {
                gridcolor: gridColor,
                automargin: true,
            },
            yaxis: {
                gridcolor: gridColor,
                automargin: true,
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
        var ratioPrices = null;
        var indicatorData = {};
        var explorerChart = document.getElementById("explorer-chart");
        var explorerLoader = document.getElementById("explorer-loader");
        // Start explorer worker
        var blob = new Blob([document.querySelector("#explorer-worker").textContent]);
        var explorerWorker = new Worker(window.URL.createObjectURL(blob));

        function processChartData() {
            // Terminate old explorer worker in case one still running
            explorerWorker.terminate();
            // Create new worker for updated parameters
            explorerWorker = new Worker(window.URL.createObjectURL(blob));
            // Show loading animation over explorer chart
            explorerChart.classList.add("loading");
            explorerLoader.classList.add("loader");

            const chartType = $("#select-explorer-chart-type").val();
            const short_names = securityPrices.map(a => a.short_name);

            let traces = [];
            let explorerConfig = _.cloneDeep(config);

            let filename = [
                "ticksift",
                chartType,
                "chart",
                $("#input-dates").val(),
            ];
            filename = filename.join("_").split(" ").join("_");

            if (securityPrices.every(a => a.currency_code === "USD")) {
                explorerLayout.yaxis.tickprefix = "$";
                indicatorHoverTemplate = "%{y:$,.2f}";
            } else {
                explorerLayout.yaxis.tickprefix = null;
                indicatorHoverTemplate = "%{y:,.2f}";
            }

            explorerLayout.xaxis.type = "date";
            explorerLayout.yaxis.type = $("#select-explorer-chart-scale").val();
            explorerLayout.xaxis.range = null;
            explorerLayout.xaxis.autorange = true;
            explorerLayout.xaxis.tickformat = "";
            explorerLayout.showlegend = true;
            explorerLayout.annotations = [];
            explorerLayout.xaxis.rangeslider = null;
            explorerLayout.legend.orientation = window.innerWidth < 576 ? "h" : "v",

            explorerLayout.xaxis.title = "";
            explorerLayout.yaxis.title = "";

            explorerConfig.toImageButtonOptions.filename = filename;

            explorerLayout.shapes = [];

            explorerLayout.grid = {};
            delete explorerLayout.yaxis.domain;

            // Handle worker's return message
            explorerWorker.onmessage = function(e) {
                traces = e.data.traces;
                explorerLayout = e.data.layout;

                if (explorerLayout.xaxis.type == "date" && traces.length) {
                    dates = $("#input-dates").val().split(" to ");
                    if (dates.length < 2) {
                        dates[1] = dates[0];
                    }

                    explorerLayout.xaxis.range = dates;
                    explorerLayout.xaxis.autorange = false;
                    let indicators = $("#select-explorer-chart-indicators").val();

                    // add indicators
                    if (indicators.length) {
                        // calculate date values across all securities
                        let dateValues = _.reduce(traces, (results, trace) => {
                            keyed = _.zipObject(trace.x, trace.y || trace.close);
                            return _.mergeWith(results, keyed, function(obj, src) {
                                if (_.isArray(obj)) {
                                    return obj.concat(src);
                                } else {
                                    return [src];
                                }
                            });
                        }, _.zipObject(traces[0].x, traces[0].y || traces[0].close));

                        // get mean for each date
                        let dateMeans = _.map(dateValues, (values, key) => {
                            return {
                                date: key,
                                mean: _.mean(values),
                            };
                        }).sort((a, b) => a.date > b.date ? 1 : -1);

                        startDate = moment(dateMeans[0]["date"]);
                        let endDate = moment(dateMeans[dateMeans.length - 1]["date"]);
                        let dayRange = endDate.diff(startDate, "days");
                        let indicatorPeriod = parseInt(dayRange / 10);

                        // add recessions
                        if(indicators.includes("recessions")) {
                            indicatorData['recessions'].forEach(indicator => {
                                explorerLayout.shapes.push({
                                    type: "rect",
                                    xref: "x",
                                    yref: "paper",
                                    x0: indicator.start_date,
                                    x1: indicator.end_date || moment().format("YYYY-MM-DD"),
                                    y0: 0,
                                    y1: 1,
                                    fillcolor: "rgba(211, 211, 211, 0.15)",
                                    line: {
                                        width: 0,
                                    }
                                });
                            });
                        }

                        // add simple moving average
                        if(indicators.includes("simple-moving-average")) {
                            let smaValues = TechnicalIndicators.SMA.calculate({
                                period: indicatorPeriod,
                                values: dateMeans.map(a => a.mean),
                            });

                            traces.push({
                                name: "Simple Moving Average",
                                x: dateMeans.map(a => a.date).slice(indicatorPeriod - 1),
                                y: smaValues,
                                hovertemplate: indicatorHoverTemplate,
                                mode: "lines",
                                line: {
                                    color: "lavender",
                                    dash: "dashdot",
                                    shape: "spline",
                                    width: 4,
                                },
                            });
                        }

                        // add exponential moving average
                        if(indicators.includes("exponential-moving-average")) {
                            let emaValues = TechnicalIndicators.EMA.calculate({
                                period: indicatorPeriod,
                                values: dateMeans.map(a => a.mean),
                            });

                            traces.push({
                                name: "Exponential Moving Average",
                                x: dateMeans.map(a => a.date).slice(indicatorPeriod - 1),
                                y: emaValues,
                                hovertemplate: indicatorHoverTemplate,
                                mode: "lines",
                                line: {
                                    color: "orchid",
                                    dash: "dashdot",
                                    shape: "spline",
                                    width: 4,
                                },
                            });
                        }

                        // add Bollinger Bands
                        if(indicators.includes("bollinger-bands")) {

                            let bollingerValues = [];
                            if (indicatorPeriod) {
                                bollingerValues = TechnicalIndicators.BollingerBands.calculate({
                                    period: indicatorPeriod,
                                    values: dateMeans.map(a => a.mean),
                                    stdDev: 2,
                                });
                            }

                            traces.push({
                                name: "Bollinger Bands",
                                mode: "lines",
                                x: dateMeans.map(a => a.date).slice(indicatorPeriod - 1),
                                y: bollingerValues.map(a => a.middle),
                                hovertemplate: indicatorHoverTemplate,
                                legendgroup: "Bollinger Bands",
                                line: {
                                    color: "fuchsia",
                                    dash: "dashdot",
                                    shape: "spline",
                                    width: 4,
                                },
                            });
                            traces.push({
                                name: "Upper BB",
                                mode: "lines",
                                x: dateMeans.map(a => a.date).slice(indicatorPeriod - 1),
                                y: bollingerValues.map(a => a.upper),
                                hovertemplate: indicatorHoverTemplate,
                                legendgroup: "Bollinger Bands",
                                showlegend: false,
                                line: {
                                    color: "gray",
                                    shape: "spline",
                                    width: 1,
                                },
                            });
                            traces.push({
                                name: "Lower BB",
                                mode: "lines",
                                fill: "tonexty",
                                x: dateMeans.map(a => a.date).slice(indicatorPeriod - 1),
                                y: bollingerValues.map(a => a.lower),
                                hovertemplate: indicatorHoverTemplate,
                                legendgroup: "Bollinger Bands",
                                showlegend: false,
                                line: {
                                    color: "gray",
                                    shape: "spline",
                                    width: 1,
                                },
                            });
                        }

                        if(
                            indicators.includes("rsi") ||
                            indicators.includes("trix")
                        ) {

                            explorerLayout.grid = {
                                rows: 2,
                                columns: 1,
                            };
                            explorerLayout.yaxis.domain = [0.25, 1];

                            // add RSI
                            if(indicators.includes("rsi")) {
                                let rsiValues = TechnicalIndicators.RSI.calculate({
                                    period: indicatorPeriod,
                                    values: dateMeans.map(a => a.mean),
                                });

                                traces.push({
                                    name: "Relative Strength Index",
                                    mode: "lines",
                                    x: dateMeans.map(a => a.date).slice(indicatorPeriod - 1),
                                    y: rsiValues,
                                    yaxis: "y2",
                                    line: {
                                        color: "crimson",
                                        shape: "spline",
                                    },
                                });
                            }

                            // add TRIX
                            if(indicators.includes("trix")) {
                                let trixValues = TechnicalIndicators.TRIX.calculate({
                                    period: indicatorPeriod / 3,
                                    values: dateMeans.map(a => a.mean),
                                });

                                traces.push({
                                    name: "Triple Exponential Average",
                                    mode: "lines",
                                    x: dateMeans.map(a => a.date).slice(indicatorPeriod - 1),
                                    y: trixValues,
                                    yaxis: "y2",
                                    line: {
                                        color: "indianred",
                                        shape: "spline",
                                    },
                                });
                            }
                        }
                    }

                    $("#select-explorer-chart-indicators").next(".select2-container").show();
                } else {
                    $("#select-explorer-chart-indicators").next(".select2-container").hide();
                }

                // show chart
                if (traces.length) {
                    Plotly.react(
                        explorerChart,
                        traces,
                        explorerLayout,
                        explorerConfig
                    );
                } else {
                    Plotly.purge(explorerChart);
                }
                explorerChart.classList.remove("loading");
                explorerLoader.classList.remove("loader");
            }

            // handle worker errors
            explorerWorker.addEventListener("error", function(e) {
                console.log("Error returned from explorer worker...");
                console.log(e);
            });

            const sortedSecurityPrices = _(_.cloneDeep(securityPrices)).sortBy("short_name").value();
            // Send message to worker to make calculations for graph
            explorerWorker.postMessage({
                "securityPrices": securityPrices,
                "sortedSecurityPrices": sortedSecurityPrices,
                "traces": traces,
                "explorerLayout": explorerLayout,
                "chartType": chartType,
                "ratioPrices": ratioPrices,
                "inputThreshold": $("#input-threshold").val(),
            });
        }

        function getPortfolioData() {
            let id = $("#select-portfolios").val();

            if (id) {
                $.get("{{ route('portfolios.securities') }}", data = {
                    portfolio_ids: [id],
                }).done(appendSecurities);
            }
        }

        function getSecurityData() {
            let security_ids = $("#select-securities").val();

            $("body").addClass("waiting");
            $(".chart").addClass("outdated");
            $.get("{{ route('securities.prices') }}", data = {
                security_ids: security_ids,
                dates: $("#input-dates").val(),
            }).done(function(prices) {
                securityPrices = Object.values(prices);
                if (security_ids.length > 0) {
                    $("#security-results").removeClass("invisible");
                    $("#create-portfolio-button").removeClass("d-none");
                    processChartData();
                } else {
                    $("#create-portfolio-button").addClass("d-none");
                    explorerChart.classList.remove("loading");
                    explorerLoader.classList.remove("loader");
                }
                $("body").removeClass("waiting");
                $(".chart").removeClass("outdated");
            });
        }

        function getRatioData() {
            let security_id = $("#select-ratio").val();

            $.ajax({
                url: "{{ route('securities.prices') }}",
                data: {
                    security_ids: security_id ? [security_id] : null,
                    dates: $("#input-dates").val(),
                    is_ratio: true,
                },
                async: false,
            }).done(function(prices) {
                ratioPrices = Object.values(prices)[0];
            });
        }

        $.ajax({
            url: "{{ route('indicators.recessions') }}",
            async: false,
        }).done(function(recessions) {
            indicatorData['recessions'] = recessions;
        });

        $("#select-securities").on("select2:unselect", function () {
            let vals = $("#select-securities").val();
            if (!vals || !vals.length) {
                $("#security-results").addClass("invisible");
            }
        });

        $("#select-portfolios").select2(({
            placeholder: "Add any of your portfolios...",
            escapeMarkup: function (text) {
                return text;
            },
        })).on("select2:select", function() {
            getPortfolioData();
            $("#select-portfolios").val(null).trigger("change");
        });

        $("#select-ratio").select2({
            placeholder: "Price selected securities in terms of another security...",
            allowClear: true,
            minimumInputLength: 1,
            escapeMarkup: function(text) {
                return text;
            },
            ajax: {
                url: "{{ route('securities.search') }}",
                delay: 250,
                processResults: function(data) {
                    return {"results": data};
                },
            },
        });

        $("#select-securities").change(getSecurityData);
        $("#input-dates").change(function() {
            explorerChart.classList.add("loading");
            explorerLoader.classList.add("loader");
            getRatioData();
            getSecurityData();
        });
        $("#select-ratio").change(function() {
            getRatioData();
            processChartData();
        });
        $("#input-threshold").change(function() {
            processChartData();
        });

        function storeChartOptions() {
            $.post("{{ route('users.store-chart-options') }}", data = {
                chart_type: $("#select-explorer-chart-type").val(),
                chart_scale: $("#select-explorer-chart-scale").val(),
                chart_indicators: $("#select-explorer-chart-indicators").val(),
            });
        }

        $("#select-explorer-chart-type").select2({
            minimumResultsForSearch: -1,
        });
        $("#select-explorer-chart-type").change(function() {
            storeChartOptions();
            processChartData();
        });
        $("#select-explorer-chart-type").on("change.select2", function() {
            let chartType = $("#select-explorer-chart-type").val();

            if (chartType == "ratio") {
                $("#select-ratio-container").removeClass("d-none");
            } else {
                $("#select-ratio-container").addClass("d-none");
            }

            if (chartType == "histvar") {
                $("#input-threshold-container").removeClass("d-none");
            } else {
                $("#input-threshold-container").addClass("d-none");
            }

            if (chartType == "correlation") {
                $("#select-explorer-chart-scale").next(".select2-container").hide();
            } else {
                $("#select-explorer-chart-scale").next(".select2-container").show();
            }

        });

        $("#select-explorer-chart-scale").select2({
            minimumResultsForSearch: -1,
        });
        $("#select-explorer-chart-scale").change(function() {
            storeChartOptions();
            Plotly.relayout(explorerChart, {
                "yaxis.type": $(this).val(),
            })
        });

        $("#select-explorer-chart-indicators").select2({
            minimumResultsForSearch: -1,
            placeholder: "Add indicators...",
            allowClear: true,
        });
        $("#select-explorer-chart-indicators").change(function() {
            storeChartOptions();
            processChartData();
        });

        @if(Session::has('ratio_security_id'))
            let security = {!! \App\Models\Security::find(Session::get('ratio_security_id'), ['id', 'ticker', 'name']) !!};
            $("#select-ratio").append(new Option(
                security.ticker ? security.ticker + " - " + security.name : security.name,
                security.id,
                false,
                true
            ));
            $("#select-ratio").trigger("change");
        @endif
        @if(Session::has('chart_type'))
            $("#select-explorer-chart-type").val("{{ Session::get('chart_type') }}");
            $("#select-explorer-chart-type").trigger("change.select2");
        @endif
        @if(Session::has('chart_scale'))
            $("#select-explorer-chart-scale").val("{{ Session::get('chart_scale') }}");
            $("#select-explorer-chart-scale").trigger("change.select2");
        @endif
        @if(Session::has('chart_indicators'))
            $("#select-explorer-chart-indicators").val("{{ implode(',', Session::get('chart_indicators')) }}".split(","));
            $("#select-explorer-chart-indicators").trigger("change.select2");
        @endif

        @if($securities = \App\Models\Security::findMany(Session::get('security_ids'), ['id', 'ticker', 'name']))
            appendSecurities({!! $securities !!});
        @endif
        $("#select-securities").trigger("change");

        let portfoliosToAdd = new URLSearchParams(location.search).get("add_portfolios");
        if (portfoliosToAdd) {
            $.get("{{ route('portfolios.securities') }}", data = {
                portfolio_ids: portfoliosToAdd.split(",")
            }).done(appendSecurities);
        }

        let tickersToAdd = new URLSearchParams(location.search).get("add_tickers");
        if (tickersToAdd) {
            $.get("{{ route('securities.find') }}", data = {
                tickers: tickersToAdd.split(","),
            }).done(appendSecurities);
        }
    </script>
@endsection
