<div class="modal fade" id="saveSim" role="dialog" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Save Simulation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">close</span>
                </button>
            </div>
            {!! Form::open([
                'route' => [
                    'simulations.update',
                    $simulation->id,
                ],
                'method' => 'PUT',
                'role' => 'form',
                'class' => 'needs-validation',
            ]) !!}

            <div class="modal-body">

                {!! csrf_field() !!}

                {!! Form::label('name', 'Name', [
                    'class' => 'control-label'
                ]); !!}
                {!! Form::text('name', $simulation->name, [
                    'id' => 'name',
                    'class' => 'form-control',
                ]) !!}

                {!! Form::label('description', 'Description', [
                    'class' => 'control-label'
                ]); !!}
                {!! Form::textarea('description', $simulation->description, [
                    'id' => 'description',
                    'class' => 'form-control',
                ]) !!}

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
