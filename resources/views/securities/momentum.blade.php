@extends('layouts.app')

@section('template_title')
    Security Momentum
@endsection

@section('content')
    <div class="container-fluid">
        @include('partials/date-picker')
        <div id="security-results" style="visibility: hidden">
            <div class="row">
                <div class="col-12 text-center pb-3">
                    <h3 class="chart-title">Sectors</h3>
                    <div id="momentum-loader" class="loader loader-spacer"></div>
                    <div id="treemap-chart" class="chart loading-spacer"></div>
                </div>
                <div class="col-12 col-lg-6 text-center pb-3">
                    <h3 class="chart-title">Winners</h3>
                    <div id="table-winners" class="chart" style="height: 400px"></div>
                </div>
                <div class="col-12 col-lg-6 text-center pb-3">
                    <h3 class="chart-title">Losers</h3>
                    <div id="table-losers" class="chart" style="height: 400px"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer_scripts')
    @include('scripts/date-picker')
    @include('scripts/security-treemap-worker')
    <script>

        const chartColor = "#000F0F";
        var treemapChart = document.getElementById("treemap-chart");
        var selectedTreemapLevel = null;

        function formatCurrency(number, code) {
            return new Intl.NumberFormat("en-US", {
                style: "currency",
                currency: code,
            }).format(number);
        }

        var baseColumns = [
            {
                title: "Ticker",
                field: "ticker",
                sorter: "string",
                minWidth: 50,
                responsive: 0,
                cellClick: function(event, cell) {
                    let ticker = cell._cell.value;
                    if (ticker) {
                        window.location = "{{ route('securities.explorer') }}?add_tickers=" + ticker;
                    }
                },
            },
            {
                title: "Name",
                field: "name",
                sorter: "string",
                minWidth: 150,
                responsive: 1,
            },
            {
                title: "Sector",
                field: "sector",
                sorter: "string",
                minWidth: 150,
                responsive: 2,
            },
            {
                title: "Industry",
                field: "industry",
                sorter: "string",
                minWidth: 150,
                responsive: 2,
            },
            {
                title: "Earliest Close",
                field: "earliest_close",
                sorter: "number",
                minWidth: 50,
                responsive: 2,
                formatter: function(cell, formatterParams, onRendered) {
                    return formatCurrency(cell.getValue(), cell.getData().currency_code);
                },
            },
            {
                title: "Latest Close",
                field: "latest_close",
                sorter: "number",
                minWidth: 50,
                responsive: 2,
                formatter: function(cell, formatterParams, onRendered) {
                    return formatCurrency(cell.getValue(), cell.getData().currency_code);
                },
            },
        ]

        var winnersTable = new Tabulator("#table-winners", {
            columns: baseColumns.concat([
                {
                    title: "Increase",
                    field: "increase",
                    sorter: "number",
                    minWidth: 50,
                    responsive: 0,
                    formatter: function(cell, formatterParams, onRendered) {
                        return (100 * cell.getValue()).toFixed(2) + "%";
                    },
                },
            ]),
            layout:"fitColumns",
            responsiveLayout: "hide",
        });

        var losersTable = new Tabulator("#table-losers", {
            columns: baseColumns.concat([
                {
                    title: "Decrease",
                    field: "decrease",
                    sorter: "number",
                    minWidth: 50,
                    responsive: 0,
                    formatter: function(cell, formatterParams, onRendered) {
                        return (100 * cell.getValue()).toFixed(2) + "%";
                    },
                },
            ]),
            layout:"fitColumns",
            responsiveLayout: "hide",
            layoutColumnsOnNewData: true,
        });

        function updateMomentum() {
            $("body").addClass("waiting");
            $(".chart").addClass("outdated");
            $.get("{{ route('securities.get-momentum') }}", data = {
                dates: $("#input-dates").val(),
            }).done(function(data) {
                winnersTable.setData(data.winners);
                losersTable.setData(data.losers);

                // Set loading styles
                treemapChart.classList.add("loading");
                let momentumLoader = document.getElementById("momentum-loader");
                momentumLoader.classList.add("loader");

                // Create momentum treemap worker
                var blob = new Blob([document.querySelector("#security-treemap-worker").textContent])
                var momentumWorker = new Worker(window.URL.createObjectURL(blob));

                // Handle worker's return message
                momentumWorker.onmessage = function(e) {
                    var labels = e.data.labels;
                    var parents = e.data.parents;
                    var texts = e.data.texts;
                    var colors = e.data.colors;
                    var lineColors = e.data.colors;
                    var values = e.data.values;
                    var customdata = e.data.customdata;

                    var treemapChart = document.getElementById("treemap-chart");
                    const chartColor = "#000F0F";
                    var selectedTreemapLevel = null;
                    Plotly.react(
                        treemapChart,
                        traces = [{
                            type: "treemap",
                            labels: labels,
                            parents: parents,
                            text: texts,
                            values: values,
                            level: selectedTreemapLevel,
                            customdata: customdata,
                            hoverinfo: "label+text",
                            textposition: "middle center",
                            marker: {
                                colors: colors,
                                line: {
                                    color: lineColors,
                                },
                            },
                        }],
                        layout = {
                            autosize: true,
                            height: 800,
                            paper_bgcolor: chartColor,
                            plot_bgcolor: chartColor,
                            margin: {
                                l: 0,
                                r: 0,
                                b: 0,
                                t: 20,
                                pad: 0,
                            },
                        },
                        config = {
                            collaborate: false,
                            displaylogo: false,
                            responsive: true,
                            toImageButtonOptions: {
                                format: "png",
                                height: 1080,
                                width: 1920,
                                filename: [
                                    "ticksift",
                                    "sectors",
                                    $("#input-dates").val(),
                                ].join("_").split(" ").join("_"),
                            },
                        },
                    ).then(function() {
                        treemapChart.classList.remove("loading");
                        momentumLoader.classList.remove("loader");
                    });
                    treemapChart.on("plotly_treemapclick", function(data) {
                        ticker = data.points[0].customdata;
                        if (ticker) {
                            window.location = "{{ route('securities.explorer') }}?add_tickers=" + ticker;
                            return false;
                        }
                        selectedTreemapLevel = data.nextLevel;
                    });
                    momentumLoader.classList.remove("loader");
                    momentumLoader.classList.remove("loader-spacer");
                    treemapChart.classList.remove("loading");
                    treemapChart.classList.remove("loading-spacer");
                }
                // Handle worker errors
                momentumWorker.addEventListener("error", function(e) {
                    console.log("Error returned from momentum worker...");
                    console.table(e);
                });
                // Send message to momentum worker to calculate security treemaps for graph
                let mergedData = [].concat.apply([], Object.values(_.cloneDeep(data)));
                mergedData = _(mergedData).map(function (securityData) {
                    if (!securityData.sector) {
                        securityData.sector = "No Sector"
                        securityData.industry = "No Industry"
                    }
                    // consolidate "increase" and "decrease" into "change"
                    if ("increase" in securityData) {
                        securityData.change = securityData.increase;
                        delete securityData["increase"];
                    } else if ("decrease" in securityData) {
                        securityData.change = securityData.decrease * -1;
                        delete securityData["decrease"];
                    }
                    return(securityData);
                }).without(undefined).groupBy("sector").mapValues(function(securities) {
                    sector_color = securities[0].sector_color;
                    return {
                        "color": sector_color ? "#" + sector_color : null,
                        "industries": _.groupBy(securities, "industry"),
                    };
                }).value();
                momentumWorker.postMessage({data: mergedData, fileSlug: "sectors"});

                $("body").removeClass("waiting");
                $(".chart").removeClass("outdated");
                $("#security-results").css("visibility", "visible");
            });
        }

        $("#input-dates").change(updateMomentum);

        updateMomentum();
    </script>
@endsection
