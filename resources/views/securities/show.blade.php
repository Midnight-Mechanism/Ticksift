@extends('layouts.app')

@section('template_title')
    Security Explorer
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row pb-3">
            <div class="col-12">
                <input id="input-dates" type="text" placeholder="Select Date.." data-input>
            </div>
        </div>
        <div class="row pb-3">
            <div class="col-12">
                <select id="select-ticker" multiple="multiple" style="display: none"></select>
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
        $("#input-dates").flatpickr({
            mode: "range",
            dateFormat: "Y-m-d",
            defaultDate: [
                moment().subtract(6, "month").format("YYYY-MM-DD"),
                moment().format("YYYY-MM-DD")
            ],
            maxDate: moment().format("YYYY-MM-DD"),
        });
    </script>
    @include('scripts/process-chart-data')
@endsection
