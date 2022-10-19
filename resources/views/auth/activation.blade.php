@extends('layouts.app')

@section('template_title')
	{{ trans('titles.activation') }}
@endsection

@section('content')
	<div class="container">
		<div class="row">
			<div class="col-md-10 offset-md-1">
				<div class="card card-default">
                    <div class="card-header"><h3>Activation Required</h3></div>
					<div class="card-body">
                        <p>Thank you for registering!</p>
                        <p>An email has been sent to <b>{{ Auth::user()->email }}</b>.</p>
                        <p>Please click the link in the email to activate your account.</p>
                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button type="submit" class="btn btn-success float-end">Resend Email</button>
                        </form>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
