@extends('layouts.app')

@section('template_title')
    Security Momentum
@endsection

@section('content')
    <div class="container-fluid">
        @include('partials/date-picker')
        <div id="security-results" style="visibility: hidden">
            <div class="row">
                <div class="col-12 position-relative text-center pb-3">
                    <h3 class="chart-title">Sectors</h3>
                    <div id="treemap-loader"></div>
                    <div id="treemap-chart" class="chart"></div>
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
    @include('scripts/security-treemap')
    <script>
        function formatCurrency(number, code) {
            if (!code) {
                return new Intl.NumberFormat("en-US", {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                }).format(number);
            }
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

                const mergedData = [].concat.apply([], Object.values(_.cloneDeep(data)));
                buildTreemap(mergedData, "sectors", function(security) {
                    return security.latest_close * security.volume;
                });

                $("body").removeClass("waiting");
                $(".chart").removeClass("outdated");
                $("#security-results").css("visibility", "visible");
            });
        }

        $("#input-dates").change(updateMomentum);

        losersTable.on("tableBuilt", function() {
            updateMomentum();
        });
    </script>
@endsection
