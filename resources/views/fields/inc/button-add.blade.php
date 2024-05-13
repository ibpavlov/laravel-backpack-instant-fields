@php($autofill_attributes = json_encode($field['on_the_fly']['autofill_attributes'] ?? ['name']))
<span class="input-group-btn">
    <button
            href="#"
            type="button"
            class="btn btn-primary"
            style="border-radius: 0px"
            data-toggle="modal"
            data-target="#{{ $field['on_the_fly']['entity'] ?? 'ajax_entity' }}_create_modal"
            data-load-url="{{ $field['on_the_fly']['create_modal'] ?? backpack_url($field['on_the_fly']['entity']).'/ajax/create?field_name='.$field['name'].'&create_modal_view='.($field['on_the_fly']['create_modal_view'] ?? 'webfactor::modal.create').'&autofill_attributes='.$autofill_attributes }}">
    <i class="fa fa-plus"></i>
    </button>
</span>

{{-- Extend @section from parent --}}
@section('after_scripts')
    @parent
    <div class="modal fade"
         id="{{ $field['on_the_fly']['entity'] ?? 'ajax_entity' }}_create_modal"
         role="dialog"
         tabindex="-1"
         aria-labelledby="{{ $field['on_the_fly']['entity'] ?? 'ajax_entity' }}_create_modal"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content"></div>
        </div>
    </div>
@stop

