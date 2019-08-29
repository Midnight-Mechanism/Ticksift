@extends('layouts.app')

@section('template_title')
    {{ $simulation->name }}
@endsection

@section('content')
    <div class="container-fluid">
    @include('modals.sim-save')
@endsection

@section('footer_scripts')
@endsection
