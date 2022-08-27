@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row py-5">
            <div class="col-12 text-center">
                <h1>Welcome to Ticksift</h1>
                <p class="lead">Ticksift is a tool for diving into the historical performance of more than 25,000 financial securities.</p>
            </div>
        </div>
        <div class="landing-card">
            <a class="text-reset text-decoration-none" href="{{ route('securities.explorer') }}">
                <div class="card-header">
                    <h3>Explore Securities</h3>
                </div>
                <div class="card-body mb-5">
                    <div class="lead">
                        Compare the performance and trajectory of specific assets.
                        <ul>
                            <li>Choose from plenty of chart options.</li>
                            <li>Add indicators for additional insight.</li>
                        </ul>
                    </div>
                    <img class="img-fluid rounded" src="/images/landing/explorer.png">
                </div>
            </a>
        </div>
        <div class="landing-card">
            <a class="text-reset text-decoration-none" href="{{ route('securities.momentum') }}">
                <div class="card-header">
                    <h3>Examine Momentum</h3>
                </div>
                <div class="card-body mb-5">
                    <div class="lead">
                        Quickly summarize the movement of large-cap stocks.
                        <ul>
                            <li>Skim the sector treemap to get a quick idea of the largest movers.</li>
                            <li>Browse winner and loser tables for a detailed breakdown.</li>
                        </ul>
                    </div>
                    <img class="img-fluid rounded" src="/images/landing/momentum.png">
                </div>
            </a>
        </div>
        @if(!Auth::guest())
            <div class="landing-card">
                <a class="text-reset text-decoration-none" href="{{ route('portfolios.index') }}">
                    <div class="card-header">
                        <h3>Establish Portfolios</h3>
                    </div>
                    <div class="card-body mb-5">
                        <div class="lead">
                            Create portfolios of securities to watch group performance.
                            <ul>
                            </ul>
                        </div>
                        <img class="img-fluid rounded" src="/images/landing/portfolios.png">
                    </div>
                </a>
            </div>
        @endif
    </div>
@endsection

