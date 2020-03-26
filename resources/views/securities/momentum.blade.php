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
    @include('scripts/security-treemap')
    <script>
        var baseColumns = [
            {
                title: "Ticker",
                field: "ticker",
                sorter: "string",
                responsive: 0,
                minWidth: 60,
                cellClick: function(event, cell) {
                    window.location = "{{ route('securities.explorer') }}?add_tickers=" + cell._cell.value;
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

        function updateMomentum() {
            $("body").addClass("waiting");
            $(".chart").addClass("outdated");
            $.get("{{ route('securities.get-momentum') }}", data = {
                dates: $("#input-dates").val(),
            }).done(function(data) {
                winnersTable.setData(data.winners);
                losersTable.setData(data.losers);

                const mergedData = [].concat.apply([], Object.values(_.cloneDeep(data)));
                buildTreemap(mergedData, function(security) {
                    return security.latest_close * security.volume;
                });

                $("body").removeClass("waiting");
                $(".chart").removeClass("outdated");
                $("#security-results").css("visibility", "visible");
            });
        }

        $("#input-dates").change(updateMomentum);

        updateMomentum();
    </script>
@endsection
