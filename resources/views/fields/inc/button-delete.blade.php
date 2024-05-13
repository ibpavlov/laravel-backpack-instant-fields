<span class="input-group-append">
    <button
        href="#"
        type="button"
        class="btn btn-danger {{ isset($field['value']) ?: 'disabled'}}"
        style="border-radius: 0px"
        data-toggle="modal"
        data-id="{{ $field['value'] ?? '' }}"
        data-target="#{{ $field['on_the_fly']['entity'] ?? 'ajax_entity' }}_delete_modal"
        data-load-url="{{ $field['on_the_fly']['delete_modal'] ?? backpack_url($field['on_the_fly']['entity']).'/ajax/delete?field_name='.$field['name'].'&delete_modal_view='.($field['on_the_fly']['delete_modal_view'] ?? 'webfactor::modal.delete').'&attribute='.($field['on_the_fly']['attribute'] ?? 'name') }}">
    <i class="la la-trash"></i>
    </button>
</span>
<div class="modal fade"
     id="{{ $field['on_the_fly']['entity'] ?? 'ajax_entity' }}_delete_modal"
     role="dialog"
     aria-labelledby="{{ $field['on_the_fly']['entity'] ?? 'ajax_entity' }}_delete_modal"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content"></div>
    </div>
</div>

