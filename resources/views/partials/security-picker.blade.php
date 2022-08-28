<div class="row pb-3">
    <div class="col-12 d-flex flex-column flex-sm-row">
        <select id="select-securities" multiple="multiple" class="invisible"></select>
        @auth
            <button
                id="create-portfolio-button"
                class="btn btn-primary d-none"
                data-bs-toggle="modal"
                data-bs-target="#create-portfolio">
                Create Portfolio
            </button>
            @include('modals.create-portfolio')
            {!! Form::open([
                'id' => 'update-portfolio-form',
                'route' => [
                    'portfolios.update',
                    ':id',
                ],
                'method' => 'PUT',
                'role' => 'form',
                'class' => 'needs-validation',
            ]) !!}
            <button
                id="update-portfolio-button"
                class="btn btn-primary d-none h-100 w-100">
                Update Portfolio
            </button>
            {!! Form::close() !!}
        @endauth
    </div>
</div>
