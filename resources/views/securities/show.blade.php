@extends('layouts.app')

@section('template_title')
    Security Explorer
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row pb-3">
            <div class="col-12">
                <select id="select-ticker" style="display: none"></select>
            </div>
        </div>
       <div class="row">
           <div class="col-12">
                <div class="sim-card">
                    <span id="last-date"></span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="sim-card">
                    <span id="last-open"></span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="sim-card">
                    <span id="last-high"></span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="sim-card">
                    <span id="last-low"></span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="sim-card">
                    <span id="last-close"></span>
                </div>
            </div>
        </div>
        <div class="row my-2">
            <div class="col-12">
                <div id="candlestick-chart"></div>
            </div>
        </div>
    </div>
@endsection

@section('footer_scripts')
    @include('scripts/process-chart-data')
@endsection
