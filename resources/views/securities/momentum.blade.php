@extends('layouts.app')

@section('template_title')
    Security Momentum
@endsection

@section('content')
    <div class="container-fluid">
        @include('partials/date-picker')
        <div class="row pb-4">
            <div class="col-6 col-lg-3">
                <label for="input-min-volume">Minimum Volume</label>
                <input id="input-min-volume" type="number" value="{{ Session::get('security_min_volume') }}">
            </div>
            <div class="col-6 col-lg-3">
                <label for="input-min-close">Minimum Close</label>
                <input id="input-min-close" type="number" step="0.01" min="0.00" value="{{ Session::get('security_min_close') }}">
            </div>
        </div>
        <div class="row">
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
@endsection

@section('footer_scripts')
    @include('scripts/date-picker')
    <script>

        var winnersTable = new Tabulator("#table-winners", {
            columns: [
                {
                    title: "Ticker",
                    field: "ticker",
                    sorter: "string",
                    cellClick: function(event, cell) {
                        window.location = "/securities/explorer?add_ticker=" + cell._cell.value;
                    },
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
                {
                    title: "Increase",
                    field: "increase",
                    sorter: "number",
                    formatter: function(cell, formatterParams, onRendered) {
                        return (100 * cell.getValue()).toFixed(2) + "%";
                    },
                },
            ],
            layout:"fitColumns",
        });

        var losersTable = new Tabulator("#table-losers", {
            columns: [
                {
                    title: "Ticker",
                    field: "ticker",
                    sorter: "string",
                    cellClick: function(event, cell) {
                        window.location = "/securities/explorer?add_ticker=" + cell._cell.value;
                    },
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
                {
                    title: "Decrease",
                    field: "decrease",
                    sorter: "number",
                    formatter: function(cell, formatterParams, onRendered) {
                        return (100 * cell.getValue()).toFixed(2) + "%";
                    },
                },
            ],
            layout:"fitColumns",
        });

        function updateMomentum() {
            $("body").addClass("waiting");
            $(".chart").addClass("outdated");
            $.post("{{ route('securities.calculate-momentum') }}", data = {
                dates: $("#input-dates").val(),
                min_volume: $("#input-min-volume").val(),
                min_close: $("#input-min-close").val(),
            }).done(function(data) {
                winnersTable.setData(data.winners);
                losersTable.setData(data.losers);
                $("body").removeClass("waiting");
                $(".chart").removeClass("outdated");
            });
        }

        $("#input-dates").change(updateMomentum);
        $("#input-min-volume").change(_.debounce(updateMomentum, 250));
        $("#input-min-close").change(_.debounce(updateMomentum, 250));

        updateMomentum();
    </script>
@endsection
