<div class="modal fade" id="delete-portfolio-{{ $portfolio['id'] }}" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Portfolio</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">close</span>
                </button>
            </div>
            {!! Form::open([
                'route' => [
                    'portfolios.destroy',
                    $portfolio['id'],
                ],
                'method' => 'DELETE',
                'role' => 'form',
                'class' => 'needs-validation',
            ]) !!}

            <div class="modal-body">

                {!! csrf_field() !!}
                <p>Are you sure you want to delete this portfolio?</p>

            </div>
            <div class="modal-footer">
                <div class="d-flex justify-content-around">
                    <span class="float-right mb-1">
                        {!! Form::button('Save', [
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
