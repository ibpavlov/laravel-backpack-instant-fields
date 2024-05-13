<div id="deleteActions" class="form-group">

    <button type="submit" class="btn btn-danger">
        <span class="la la-trash" role="presentation" aria-hidden="true"></span> &nbsp;
        <span data-value="delete">{{ trans('backpack::crud.delete') }}</span>
    </button>

    <button class="btn btn-default" type="button" data-toggle="modal" data-target="#{{ $entity }}_delete_modal">
        <span class="la la-ban"></span> {{ trans('backpack::crud.cancel') }}
    </button>
</div>
