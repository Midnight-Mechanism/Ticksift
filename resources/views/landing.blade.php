@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row py-5">
            <div class="col-12 text-center">
                <h1>Welcome to Ticksift</h1>
                <p class="lead">Ticksift is a tool for diving into the historical performance of more than 25,000 financial securities.</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 col-xl-4 mb-5">
                <div class="card landing-card">
                    <div class="card-header">
                        <h3>Explore Securities</h3>
                    </div>
                    <div class="card-body">
                        <div class="lead">
                            Compare the performance and trajectory of specific assets.
                            <ul>
                                <li>Choose from plenty of chart options.</li>
                                <li>Add indicators for additional insight.</li>
                            </ul>
                        </div>
                        <img class="img-fluid rounded" src="/images/landing/explorer.png">
                        <a class="stretched-link" href="{{ route('securities.explorer') }}"></a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-4 mb-5">
                <div class="card landing-card">
                    <div class="card-header">
                        <h3>Examine Momentum</h3>
                    </div>
                    <div class="card-body">
                        <div class="lead">
                            Quickly summarize the movement of large-cap stocks.
                            <ul>
                                <li>Skim the sector treemap to get a quick idea of the largest movers.</li>
                                <li>Browse winner and loser tables for a detailed breakdown.</li>
                            </ul>
                        </div>
                        <img class="img-fluid rounded" src="/images/landing/momentum.png">
                        <a class="stretched-link" href="{{ route('securities.momentum') }}"></a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 offset-md-3 col-xl-4 offset-xl-0 mb-5">
                @if(Route::has('login'))
                    <div class="card landing-card">
                        <div class="card-header">
                            <h3>Establish Portfolios</h3>
                        </div>
                        <div class="card-body">
                            <div class="lead">
                                Create portfolios of securities to watch group performance.
                                <ul>
                                </ul>
                            </div>
                            <img class="img-fluid rounded" src="/images/landing/portfolios.png">
                            <a class="stretched-link" href="{{ route('portfolios.index') }}"></a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

