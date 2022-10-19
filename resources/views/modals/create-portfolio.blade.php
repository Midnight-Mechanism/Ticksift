<div class="modal fade" id="create-portfolio" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('forms.create_portfolio') }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {!! Form::open([
                'id' => 'create-portfolio-form',
                'route' => [
                    'portfolios.store',
                ],
                'method' => 'POST',
                'role' => 'form',
                'class' => 'needs-validation',
            ]) !!}

            <div class="modal-body">

                {!! csrf_field() !!}

                {!! Form::label('name', __('forms.name'), [
                    'class' => 'control-label'
                ]); !!}
                {!! Form::text('name', NULL, [
                    'id' => 'name',
                    'class' => 'form-control',
                ]) !!}

            </div>
            <div class="modal-footer">
                <div class="d-flex justify-content-around">
                    <span class="float-right mb-1">
                        {!! Form::button(__('forms.create'), [
                            'class' => 'btn btn-success mx-2',
                            'type' => 'submit',
                        ]) !!}
                    </span>
                </div>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
