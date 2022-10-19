@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="card">
                <div class="card-header">
                    <h3>Register</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <div class="row">
                            <label for="first_name" class="col-md-4 col-form-label text-md-end">First Name:</label>
                            <div class="col-md-8 mb-3">
                                <input id="first_name" class="form-control" name="first_name" value="{{ old('first_name') }}" required autofocus>
                            </div>

                            <label for="last_name" class="col-md-4 col-form-label text-md-end">Last Name:</label>
                            <div class="col-md-8 mb-3">
                                <input id="last_name" class="form-control" name="last_name" value="{{ old('last_name') }}" required autofocus>
                            </div>
                        </div>

                        <div class="row">
                            <label for="email" class="col-md-4 col-form-label text-md-end">{{ __('forms.email') }}:</label>
                            <div class="col-md-8 mb-3">
                                <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus>

                                @if ($errors->has('email'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('forms.password') }}:</label>
                            <div class="col-md-8 mb-3">
                                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>

                                @if ($errors->has('password'))
                                    <span class="invalid-feedback">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('forms.confirm_password') }}:</label>
                            <div class="col-md-8 mb-3">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 my-3 d-flex justify-content-end">
                                {!! htmlFormSnippet() !!}
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 d-flex justify-content-end align-items-baseline">
                                <button type="submit" class="btn btn-success">{{ __('forms.register') }}</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection


@section('footer_scripts')
    {!! htmlScriptTagJsApi() !!}
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endsection
