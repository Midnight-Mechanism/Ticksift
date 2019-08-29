@extends('layouts.app')

@section('template_title')
    Simulations
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex pb-2 mb-2 border-bottom">
                    <h1 class="mr-auto">Simulations</h1>
                    <span>
                        <a href={{ route("simulations.create") }} class="btn btn-primary mx-2 p-2">New Simulation</a>
                    </span>
                </div>
                <div class="card-container">
                    <div class="row">
                        @foreach(Auth::user()->simulations->where('saved') as $s)
                            <div class="col-sm-6 col-lg-4 p-0">
                                <a href="{{ route('simulations.show', [
                                    'simulation' => $s->id,
                                ]) }}" class="card row-card m-3 p-0">
                                <div class="card-header">
                                    <h3>{{ $s->name }}</h3>
                                </div>
                                <div class="card-body">
                                    <div class="card-content">
                                        <p>
                                            <b>Created on:</b> {{ $s->created_at->toDateString() }}
                                            <br>
                                            <b>Updated on:</b> {{ $s->updated_at->toDateString() }}
                                            <br>
                                            <b>Description:</b> {{ $s->description }}
                                        </p>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        {!! Form::open(['route' => [
                                            'simulations.destroy',
                                            $s->id,
                                        ]]) !!}
                                        {!! Form::hidden('_method', 'DELETE') !!}
                                        {!! Form::button('Delete', [
                                            'class' => 'btn btn-link btn-sm',
                                            'type' => 'button',
                                            'data-toggle' => 'modal',
                                            'data-target' => '#confirmArchival',
                                            'data-title' => 'Delete Simulation',
                                            'data-message' => 'Are you sure you want to delete this simulation?'
                                        ]) !!}
                                        {!! Form::close() !!}
                                    </div>
                                </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('modals.modal-archive')
@endsection

@section('footer_scripts')
    @include('scripts.archive-modal')
    @include('scripts.prevent-cardclick')
@endsection
