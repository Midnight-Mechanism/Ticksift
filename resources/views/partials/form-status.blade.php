@if (session('message'))
  <div class="alert alert-{{ Session::get('status') }} status-box alert-dismissable fade show" role="alert">
    {{ session('message') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="close"></button>
  </div>
@endif

@if (count($errors) > 0)
  <div class="alert alert-danger alert-dismissable fade show" role="alert">
    <h4 class="alert-heading">
      <i class="icon fa fa-warning fa-fw" aria-hidden="true"></i>
      {{ Lang::get('auth.someProblems') }}
    </h4>
    <ul>
      @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="close"></button>
  </div>
@endif
