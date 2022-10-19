@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="card">
                <div class="card-header">
                    <h3>Login</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <input type="hidden" name="remember" value=true>

                        <div class="row">
                            <label for="email" class="col-sm-3 col-form-label text-md-end">{{ __('forms.email') }}:</label>

                            <div class="col-md-9 mb-3">
                                <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus>

                                @if ($errors->has('email'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <label for="password" class="col-md-3 col-form-label text-md-end">{{ __('forms.password') }}:</label>

                            <div class="col-md-9 mb-3">
                                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>

                                @if ($errors->has('password'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mt-3 d-flex justify-content-between align-items-baseline">
                                <a class="btn btn-link" href="{{ route('password.request') }}">
                                    {{ __('forms.reset_password') }}
                                </a>

                                <button type="submit" class="btn btn-success">{{ __('forms.login') }}</button>
                           </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
