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
                    <div id="explorer-chart" class="chart"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer_scripts')
    @include('scripts.date-picker')
    @include('scripts.security-picker')
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
            legend: {},
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
        var ratioPrices = null;
        var explorerChart = document.getElementById("explorer-chart");

        function formatCurrency(number, code) {
            return new Intl.NumberFormat("en-US", {
                style: "currency",
                currency: code,
            }).format(number);
        }

        function processChartData() {
            const chartType = $("#select-explorer-chart-type").val();
            const chartScale = $("#select-explorer-chart-scale").val();

            const dates = $("#input-dates").val();
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
            } else {
                explorerLayout.yaxis.tickprefix = null;
            }

            explorerLayout.yaxis.type = chartScale;
            explorerLayout.xaxis.type = "date";
            explorerLayout.xaxis.tickformat = "";
            explorerLayout.showlegend = true;
            explorerLayout.annotations = [];
            explorerLayout.xaxis.rangeslider = null;
            explorerLayout.legend.orientation = window.innerWidth < 576 ? "h" : "v",

            explorerLayout.xaxis.title = "";
            explorerLayout.yaxis.title = "";

            explorerConfig.toImageButtonOptions.filename = filename;
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
                            text: securityData.prices.map(a => {
                                return "Open: " + formatCurrency(a.open, securityData.currency_code) + "<br>" +
                                    "High: " + formatCurrency(a.high, securityData.currency_code) + "<br>" +
                                    "Low: " + formatCurrency(a.low, securityData.currency_code) + "<br>" +
                                    "Close: " + formatCurrency(a.close, securityData.currency_code);
                            }),
                            hoverinfo: "text",
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
                            let overlappingPrices = _.cloneDeep(securityData).prices.map(price => {
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

                    let threshold = $("#input-threshold").val() / 100;

                    traces.push(
                        {
                            name: "Below " + moment.localeData().ordinal(Number($("#input-threshold").val())) + " Percentile",
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
                            name: "Above " + moment.localeData().ordinal(Number($("#input-threshold").val())) + " Percentile",
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

                    const sortedSecurityPrices = _(_.cloneDeep(securityPrices)).sortBy("short_name").value();
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

            Plotly.react(
                explorerChart,
                traces,
                explorerLayout,
                explorerConfig
            );
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
            let dates = $("#input-dates").val();

            $("body").addClass("waiting");
            $(".chart").addClass("outdated");
            $.get("{{ route('securities.prices') }}", data = {
                security_ids: security_ids,
                dates: dates,
            }).done(function(prices) {
                securityPrices = Object.values(prices);
                if (security_ids.length > 0) {
                    processChartData();
                    $("#create-portfolio-button").removeClass("d-none");
                    $("#security-results").removeClass("invisible");
                } else {
                    $("#create-portfolio-button").addClass("d-none");
                }
                $("body").removeClass("waiting");
                $(".chart").removeClass("outdated");
            });
        }

        function getRatioData() {
            let security_id = $("#select-ratio").val();
            let dates = $("#input-dates").val();

            $.ajax({
                url: "{{ route('securities.prices') }}",
                data: {
                    security_ids: security_id ? [security_id] : null,
                    dates: dates,
                    is_ratio: true,
                },
                async: false,
            }).done(function(prices) {
                ratioPrices = Object.values(prices)[0];
            });
        }

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

        $("#input-dates").change(getSecurityData);
        $("#select-securities").change(getSecurityData);
        $("#input-dates").change(function() {
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

        function saveChartData() {
            $.post("{{ route('users.store-chart-options') }}", data = {
                chart_type: $("#select-explorer-chart-type").val(),
                chart_scale: $("#select-explorer-chart-scale").val(),
            });
            processChartData();
        }

        $("#select-explorer-chart-type").select2({
            minimumResultsForSearch: -1,
        });
        $("#select-explorer-chart-type").change(saveChartData);
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
        $("#select-explorer-chart-scale").change(saveChartData);

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
