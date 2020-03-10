@extends('layouts.app')

@section('template_title')
    Security Momentum
@endsection

@section('content')
    <div class="container-fluid">
        @include('partials/date-picker')
        <div class="row">
            <div class="chart col-12 col-lg-6 text-center pb-3">
                <h3 class="table-title">Winners</h3>
                <div id="table-winners" style="height: 400px"></div>
            </div>
            <div class="chart col-12 col-lg-6 text-center pb-3">
                <h3 class="table-title">Losers</h3>
                <div id="table-losers" style="height: 400px"></div>
            </div>
            <div class="chart col-12 text-center pb-3">
                <h3 class="table-title">Sectors</h3>
                <div id="treemap-chart" class="chart"></div>
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

        var baseColumns = [
            {
                title: "Ticker",
                field: "ticker",
                sorter: "string",
                cellClick: function(event, cell) {
                    window.location = "/securities/explorer?add_ticker=" + cell._cell.value;
                },
            },
            {
                title: "Name",
                field: "name",
                sorter: "string",
            },
            {
                title: "Sector",
                field: "sector",
                sorter: "string",
            },
            {
                title: "Earliest Close",
                field: "earliest_close",
                sorter: "number",
                formatter: "money",
                formatterParams: {
                    symbol: "$",
                },
            },
            {
                title: "Latest Close",
                field: "latest_close",
                sorter: "number",
                formatter: "money",
                formatterParams: {
                    symbol: "$",
                },
            },
        ]

        var winnersTable = new Tabulator("#table-winners", {
            columns: baseColumns.concat([
                {
                    title: "Increase",
                    field: "increase",
                    sorter: "number",
                    formatter: function(cell, formatterParams, onRendered) {
                        return (100 * cell.getValue()).toFixed(2) + "%";
                    },
                },
            ]),
            layout:"fitColumns",
        });

        var losersTable = new Tabulator("#table-losers", {
            columns: baseColumns.concat([
                {
                    title: "Decrease",
                    field: "decrease",
                    sorter: "number",
                    formatter: function(cell, formatterParams, onRendered) {
                        return (100 * cell.getValue()).toFixed(2) + "%";
                    },
                },
            ]),
            layout:"fitColumns",
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
                return(securityData);
            }).orderBy([function(securityData) {
                // order by percent change
                return Math.abs(securityData.change);
            }], ['desc']).value();

            // group by sector, limit to most changed
            sectors = _(data).groupBy("sector").mapValues(function (sectorData) {
                return sectorData.slice(0, 10);
            }).value();

            // remove securities with no sector
            delete sectors[""];

            let labels = [];
            let parents = [];
            let changes = [];

            for (const[sector, sectorData] of Object.entries(sectors)) {
                labels.push(sector);
                parents.push("");
                changes.push(null);
                for (const securityData of Object.values(sectorData)) {
                    labels.push(securityData.name);
                    parents.push(sector);
                    if (securityData.change < 0) {
                        changes.push((securityData.change * 100).toFixed(2) + "%");
                    } else {
                        changes.push("+" + (securityData.change * 100).toFixed(2) + "%");
                    }
                }
            }

            Plotly.newPlot(
                treemapChart,
                traces = [{
                    type: "treemap",
                    labels: labels,
                    parents: parents,
                    text: changes,
                }],
                layout = {
                    autosize: true,
                    height: 800,
                    font: {
                        family: "Hind Madurai",
                        color: "white",
                    },
                    paper_bgcolor: chartColor,
                    plot_bgcolor: chartColor,
                    margin: {
                        l: 0,
                        r: 0,
                        b: 0,
                        t: 8,
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
            );
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
            });
        }

        $("#input-dates").change(updateMomentum);

        updateMomentum();
    </script>
@endsection
