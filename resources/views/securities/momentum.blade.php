@extends('layouts.app')

@section('template_title')
    Security Momentum
@endsection

@section('content')
    <div class="container-fluid">
        @include('partials/date-picker')
        <div id="security-results" style="visibility: hidden">
            <div class="row">
                <div class="chart col-12 text-center pb-3">
                    <h3 class="table-title">Large Cap</h3>
                    <div id="treemap-chart" class="chart"></div>
                </div>
                <div class="chart col-12 col-lg-6 text-center pb-3">
                    <h3 class="table-title">Winners</h3>
                    <div id="table-winners" style="height: 400px"></div>
                </div>
                <div class="chart col-12 col-lg-6 text-center pb-3">
                    <h3 class="table-title">Losers</h3>
                    <div id="table-losers" style="height: 400px"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer_scripts')
    @include('scripts/date-picker')
    <script>
        const chartColor = "#000F0F";
        const gridColor = "#154B4B";

        var treemapChart = document.getElementById("treemap-chart");

        function formatCurrency(number, code) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: code,
            }).format(number);
        }

        var baseColumns = [
            {
                title: "Ticker",
                field: "ticker",
                sorter: "string",
                responsive: 0,
                minWidth: 60,
                cellClick: function(event, cell) {
                    window.location = "/securities/explorer?add_ticker=" + cell._cell.value;
                },
            },
            {
                title: "Name",
                field: "name",
                sorter: "string",
                responsive: 1,
                minWidth: 150,
            },
            {
                title: "Sector",
                field: "sector",
                sorter: "string",
                responsive: 2,
                minWidth: 150,
            },
            {
                title: "Industry",
                field: "industry",
                sorter: "string",
                responsive: 2,
                minWidth: 150,
            },
            {
                title: "Earliest Close",
                field: "earliest_close",
                sorter: "number",
                responsive: 2,
                minWidth: 80,
                formatter: function(cell, formatterParams, onRendered) {
                    return formatCurrency(cell.getValue(), cell.getData().currency_code);
                },
            },
            {
                title: "Latest Close",
                field: "latest_close",
                sorter: "number",
                responsive: 2,
                minWidth: 80,
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
                    responsive: 0,
                    minWidth: 80,
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
                    responsive: 0,
                    minWidth: 80,
                    formatter: function(cell, formatterParams, onRendered) {
                        return (100 * cell.getValue()).toFixed(2) + "%";
                    },
                },
            ]),
            layout:"fitColumns",
            responsiveLayout: "hide",
        });

        function buildTreemap(data) {

            data = _(data).map(function (securityData) {
                // consolidate "increase" and "decrease" into "change"
                if ("increase" in securityData) {
                    securityData.change = securityData.increase;
                    delete securityData["increase"];
                } else if ("decrease" in securityData) {
                    securityData.change = securityData.decrease * -1;
                    delete securityData["decrease"];
                }
                // only include > large cap stocks
                if (securityData.scale_marketcap >= 5) {
                    return(securityData);
                }
            }).without(undefined).groupBy('sector').mapValues(function(securities) {
                sector_color = securities[0].sector_color;
                return {
                    'color': sector_color ? "#" + sector_color : null,
                    'industries': _.groupBy(securities, 'industry'),
                };
            }).value();

            // remove securities with no sector
            delete data[""];

            let labels = [];
            let parents = [];
            let texts = [];
            let colors = [];
            let values = [];
            let customdata = [];

            for (const[sector, sectorData] of Object.entries(data)) {
                let sectorLabel = "<b><span style='text-transform: uppercase'>" +
                    sector +
                    "</span></b>";
                labels.push(sectorLabel);
                parents.push("");
                texts.push(null);
                colors.push(sectorData.color);
                values.push(0);
                customdata.push(null);
                for (const[industry, industryData] of Object.entries(sectorData.industries)) {
                    let industryLabel = "<b><span style='text-transform: uppercase'>" +
                        industry +
                        "</span></b>";
                    labels.push(industryLabel);
                    parents.push(sectorLabel);
                    texts.push(null);
                    colors.push(sectorData.color);
                    values.push(0);
                    customdata.push(null);
                    for (const securityData of Object.values(industryData)) {
                        parents.push(industryLabel);
                        customdata.push(securityData.ticker);

                        let label = "<b><span style='font-size: 200%'>" + securityData.ticker + "</span></b>";
                        let text = securityData.name;

                        let absChange = Math.abs(securityData.change);
                        let colorChange = Math.min(absChange * 15, 8.5);
                        let percent = absChange * 100;

                        text += "<b><span style='font-size: 150%;";
                        if (securityData.change < 0) {
                            text += "color: #FFE6E6'><br><br>-";
                            colors.push(Color("#1A0000").lighten(colorChange).hex());
                        } else {
                            text += "color: #E6FFEA'><br><br>+";
                            colors.push(Color("#001A04").lighten(colorChange).hex());
                        }
                        text += percent.toFixed(2) + "%</span></b>";
                        text += "<br>" + formatCurrency(securityData.latest_close, securityData.currency_code);

                        labels.push(label);
                        texts.push(text);
                        values.push(securityData.latest_close * securityData.volume);
                    }
                }
            }

            Plotly.newPlot(
                treemapChart,
                traces = [{
                    type: "treemap",
                    labels: labels,
                    parents: parents,
                    text: texts,
                    values: values,
                    customdata: customdata,
                    hoverinfo: "label+text",
                    textposition: "middle center",
                    marker: {
                        colors: colors,
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
                            "large-cap",
                            $("#input-dates").val(),
                        ].join("_").split(" ").join("_"),
                    },
                },
            );


            treemapChart.on('plotly_treemapclick', function(data) {
                ticker = data.points[0].customdata;
                if (ticker) {
                    window.location = "/securities/explorer?add_ticker=" + ticker;
                    return false;
                }
            });
        }

        function updateMomentum() {
            $("body").addClass("waiting");
            $(".chart").addClass("outdated");
            $.post("{{ route('securities.get-momentum') }}", data = {
                dates: $("#input-dates").val(),
                min_volume: $("#input-min-volume").val(),
                min_close: $("#input-min-close").val(),
            }).done(function(data) {
                winnersTable.setData(data.winners);
                losersTable.setData(data.losers);

                const mergedData = [].concat.apply([], Object.values(_.cloneDeep(data)));
                buildTreemap(mergedData);

                $("body").removeClass("waiting");
                $(".chart").removeClass("outdated");
                $("#security-results").css("visibility", "visible");
            });
        }

        $("#input-dates").change(updateMomentum);

        updateMomentum();
    </script>
@endsection
