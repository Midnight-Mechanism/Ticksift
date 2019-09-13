@extends('layouts.app')

@section('template_title')
    Security Explorer
@endsection

@section('content')
    <div class="container-fluid">
        @include('partials/date-picker')
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
    @include('scripts/date-picker')
    @include('scripts/process-chart-data')
@endsection