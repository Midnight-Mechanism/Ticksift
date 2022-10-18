@extends('layouts.app')

@section('template_title')
    Profile
@endsection

@section('content')
    <div class="container">

        <div class="row justify-content-center pb-3">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Update Info</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('update-profile') }}">
                            @csrf

                            <div class="form-group row">
                                <label for="first_name" class="col-sm-4 col-form-label text-md-end">First Name:</label>

                                <div class="col-md-8">
                                    <input id="first_name" class="form-control" name="first_name" value="{{ Auth::user()->first_name }}" required autofocus>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="last_name" class="col-sm-4 col-form-label text-md-end">Last Name:</label>

                                <div class="col-md-8">
                                    <input id="last_name" class="form-control" name="last_name" value="{{ Auth::user()->last_name }}" required autofocus>
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-success float-end">Update</button>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center pb-3">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Change Password</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('update-password') }}">
                            @csrf
                            <div class="form-group row">
                                <label for="password" class="col-md-4 col-form-label text-md-end">{{ __('Password') }}:</label>

                                <div class="col-md-8">
                                    <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="password-confirm" class="col-md-4 col-form-label text-md-end">{{ __('Confirm Password') }}:</label>
                                <div class="col-md-8">
                                    <input id="password-confirm" type="password" class="form-control{{ $errors->has('password_confirmation') ? ' is-invalid' : '' }}" name="password_confirmation" required>
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-success float-end">Change</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
