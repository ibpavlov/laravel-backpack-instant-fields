<span class="input-group-append">
    <a
        href="#"
        type="button"
        class="btn btn-warning {{ isset($field['value']) ?: 'disabled'}}"
        style="border-radius: 0px"
        data-id="{{ $field['value'] ?? '' }}"
        data-url="{{ $field['on_the_fly']['crud_url'] ?? backpack_url($field['on_the_fly']['entity']) }}"
        data-target="#{{ $field['on_the_fly']['entity'] ?? 'ajax_entity' }}_edit_crud"
        >
    <i class="la la-pencil"></i>
    </a>
</span>

