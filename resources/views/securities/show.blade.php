@extends('layouts.app')

@section('template_title')
    Security Explorer
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row pb-3">
            <div class="col-12">
                <select id="select-ticker" multiple="multiple" style="display: none"></select>
            </div>
        </div>
        <div class="row my-2">
            <div class="col-12">
                <div id="candlestick-chart"></div>
            </div>
        </div>
        <div class="row my-2">
            <div class="col-12">
                <div id="correlation-chart"></div>
            </div>
        </div>
    </div>
@endsection

@section('footer_scripts')
    @include('scripts/process-chart-data')
@endsection
