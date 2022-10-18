<nav class="navbar navbar-expand-md navbar-dark navbar-laravel">
    <div class="container">
        <a class="navbar-brand" href="{{ url('/') }}">
            <img style="height: 30px" src="/images/ticksift.png">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                                                                            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
            <span class="sr-only">{!! trans('titles.toggleNav') !!}</span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            {{-- Left Side Of Navbar --}}
            <ul class="navbar-nav me-auto">
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
            <ul class="navbar-nav ms-auto">
                @auth
                    <li>
                        <a
                            class="nav-link{{ Route::currentRouteName() === 'profile' ? ' active' : '' }}"
                            href="{{ route('profile') }}"
                            >
                            Profile
                        </a>
                    </li>
                @endauth
                @guest
                    <li>
                        <a
                            class="nav-link{{ Route::currentRouteName() === 'login' ? ' active' : '' }}"
                            href="{{ route('login') }}"
                            >
                            Login
                        </a>
                    </li>
                    <li>
                        <a
                            class="nav-link{{ Route::currentRouteName() === 'register' ? ' active' : '' }}"
                            href="{{ route('register') }}"
                            >
                            Register
                        </a>
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
