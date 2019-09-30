@extends('layouts.app')

@section('template_title')
    Security Momentum
@endsection

@section('content')
    <div class="container-fluid">
        @include('partials/date-picker')
        <div class="row pb-4">
            <div class="col-12 col-lg-3">
                <label for="input-volume">Volume Threshold</label>
                <input id="input-volume" type="number" value="{{ Session::get('security_volume_threshold') }}">
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-lg-6 text-center pb-3">
                <h3 class="table-title">Winners</h3>
                <div id="table-winners" style="height: 400px"></div>
            </div>
            <div class="col-12 col-lg-6 text-center pb-3">
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
            $.post("{{ route('securities.calculate-momentum') }}", data = {
                dates: $("#input-dates").val(),
                volume_threshold: $("#input-volume").val(),
            }).done(function(data) {
                winnersTable.setData(data.winners);
                losersTable.setData(data.losers);
                $("body").removeClass("waiting");
            });
        }

        $("#input-dates").change(updateMomentum);
        $("#input-volume").change(_.debounce(updateMomentum, 250));

        updateMomentum();
    </script>
@endsection
