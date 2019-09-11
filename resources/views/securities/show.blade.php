@extends('layouts.app')

@section('template_title')
    Security Explorer
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-12 col-lg-3">
                <input id="input-dates" type="text" placeholder="Select Date.." data-input>
            </div>
            <div class="col-12 col-lg-9">
                <div class="row date-buttons">
                    <div class="col-4 col-sm-2 mt-3 mt-lg-0">
                        <button id="button-week" class="btn btn-primary">1 Week</button>
                    </div>
                    <div class="col-4 col-sm-2 mt-3 mt-lg-0">
                        <button id="button-1mo" class="btn btn-primary">1 Month</button>
                    </div>
                    <div class="col-4 col-sm-2 mt-3 mt-lg-0">
                        <button id="button-ytd" class="btn btn-primary">YTD</button>
                    </div>
                    <div class="col-4 col-sm-2 mt-3 mt-lg-0">
                        <button id="button-1yr" class="btn btn-primary">1 Year</button>
                    </div>
                    <div class="col-4 col-sm-2 mt-3 mt-lg-0">
                        <button id="button-5yr" class="btn btn-primary">5 Years</button>
                    </div>
                    <div class="col-4 col-sm-2 mt-3 mt-lg-0">
                        <button id="button-all" class="btn btn-primary">All</button>
                    </div>
                </div>
            </div>
        </div>
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
        <div class="row">
            <div class="col-12 col-lg-6 my-2">
                <div id="candlestick-chart"></div>
            </div>
            <div class="col-12 col-lg-6 my-2">
                <div id="correlation-chart"></div>
            </div>
        </div>
    </div>
@endsection

@section('footer_scripts')
    <script>
        var calendar = $("#input-dates").flatpickr({
            mode: "range",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "M j, Y",
            defaultDate: [
                @if(Session::has('security_dates'))
                    "{{ Session::get('security_dates')[0] }}",
                    "{{ Session::get('security_dates')[1] }}"
                @else
                    moment().subtract(1, "month").format("YYYY-MM-DD"),
                    moment().format("YYYY-MM-DD")
                @endif
            ],
            maxDate: moment().format("YYYY-MM-DD"),
        });
        $("#button-week").click(function() {
            calendar.setDate([
                moment().subtract(1, "week").format("YYYY-MM-DD"),
                moment().format("YYYY-MM-DD")
            ], true);
        });
        $("#button-1mo").click(function() {
            calendar.setDate([
                moment().subtract(1, "month").format("YYYY-MM-DD"),
                moment().format("YYYY-MM-DD")
            ], true);
        });
        $("#button-ytd").click(function() {
            calendar.setDate([
                moment().startOf("year").format("YYYY-MM-DD"),
                moment().format("YYYY-MM-DD")
            ], true);
        });
        $("#button-1yr").click(function() {
            calendar.setDate([
                moment().subtract(1, "year").format("YYYY-MM-DD"),
                moment().format("YYYY-MM-DD")
            ], true);
        });
        $("#button-5yr").click(function() {
            calendar.setDate([
                moment().subtract(5, "year").format("YYYY-MM-DD"),
                moment().format("YYYY-MM-DD")
            ], true);
        });
        $("#button-all").click(function() {
            calendar.setDate([
                "{{ \App\Models\Price::min('date') }}",
                moment().format("YYYY-MM-DD")
            ], true);
        });
    </script>
    @include('scripts/process-chart-data')
@endsection
