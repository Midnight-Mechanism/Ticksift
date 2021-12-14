<nav class="navbar navbar-expand-md navbar-dark navbar-laravel">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <img style="height: 30px" src="/images/ticksift.png">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                                                                            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
            <span class="sr-only">{!! trans('titles.toggleNav') !!}</span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            {{-- Left Side Of Navbar --}}
            <ul class="navbar-nav mr-auto">
                <li>
                    <a
                        class="nav-link{{ Route::currentRouteName() === 'securities.explorer' ? ' active' : '' }}"
                        href="{{ route('securities.explorer') }}"
                        >
                        Explorer
                    </a>
                </li>
                <li>
                    <a
                        class="nav-link{{ Route::currentRouteName() === 'securities.momentum' ? ' active' : '' }}"
                        href="{{ route('securities.momentum') }}"
                        >
                        Momentum
                    </a>
                </li>
                @auth
                    <li>
                        <a
                            class="nav-link{{ Route::currentRouteName() === 'portfolios.index' ? ' active' : '' }}"
                            href="{{ route('portfolios.index') }}"
                            >
                            Portfolios
                        </a>
                    </li>
                @endauth
            </ul>
            {{-- Right Side Of Navbar --}}
            <ul class="navbar-nav ml-auto">
            </ul>
        </div>
    </div>
</nav>
