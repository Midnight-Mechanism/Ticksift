<div class="modal fade modal-danger" id="confirmArchival" role="dialog" aria-labelledby="confirmArchiveLabel" aria-hidden="true" tabindex="-1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    <span class="sr-only">close</span>
                </button>
            </div>
            <div class="modal-body">
                <p></p>
            </div>
            <div class="modal-footer">
                {!! Form::button('Cancel', [
                    'class' => 'btn btn-link pull-left',
                    'type' => 'button',
                    'data-dismiss' => 'modal'
                ]) !!}
                {!! Form::button('<i class="fas fa-fw fa-trash-alt" aria-hidden="true"></i> Confirm', [
                    'class' => 'btn btn-danger float-right',
                    'type' => 'button',
                    'id' => 'confirm'
                ]) !!}
            </div>
        </div>
    </div>
</div>
