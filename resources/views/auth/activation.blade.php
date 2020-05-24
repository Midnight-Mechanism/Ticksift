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
                        <p>An email has been sent to <b>{{ $email }}</b>.</p>
                        <p>Please click the link in the email to activate your account.</p>
						<p><a href='/activation' class="btn btn-success float-right">Resend Email</a></p>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
