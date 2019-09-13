<nav class="navbar navbar-expand-md navbar-dark navbar-laravel">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <img src="/images/ticksift.svg">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                                                                            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
            <span class="sr-only">{!! trans('titles.toggleNav') !!}</span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            {{-- Left Side Of Navbar --}}
            <ul class="navbar-nav mr-auto">
                @auth
                    @if(Route::currentRouteName() !== 'securities.explorer')
                        <li>
                            <a class="nav-link" href="{{ route('securities.explorer') }}">Explorer</a>
                        </li>
                    @endif
                    @if(Route::currentRouteName() !== 'securities.momentum')
                        <li>
                            <a class="nav-link" href="{{ route('securities.momentum') }}">Momentum</a>
                        </li>
                    @endif
                    @if(Route::currentRouteName() !== 'simulations.index')
                    <!--
                        <li>
                            <a class="nav-link" href="{{ route('simulations.index') }}">Simulations</a>
                        </li>
                    -->
                    @endif
                @endauth
            </ul>
            {{-- Right Side Of Navbar --}}
            <ul class="navbar-nav ml-auto">
                @if(Route::currentRouteName() === 'simulations.show')
                    <li>
                        <a class="nav-link" data-toggle="modal" data-target="#saveSim">
                            @if($simulation->saved)
                                Rename
                            @else
                                Save
                            @endif
                        </a>
                    </li>
                @endif
                @auth
                    <li>
                        <a class="nav-link" href="{{ url('profile') }}">Profile</a>
                    </li>
                @endauth
                @role('admin')
                <li>
                    <a class="nav-link" href="{{ route('activity') }}">
                        Activity Log
                    </a>
                </li>
            @endrole
            @guest
                <li>
                    <a class="nav-link" href="{{ route('login') }}">Login</a>
                </li>
                <li>
                    <a class="nav-link" href="{{ route('register') }}">Register</a>
                </li>
            @endguest
            @auth
                <li>
                    <a class="nav-link" href="{{ route('logout') }}"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        {{ __('Logout') }}
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </li>
            @endauth
            </ul>
        </div>
    </div>
</nav>
