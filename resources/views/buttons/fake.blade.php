@isset($crud->instantCreateButtons[$button->name])
    <div class="modal fade"
         id="{{ $crud->instantCreateButtons[$button->name]['name'] }}_create_modal"
         role="dialog"
         aria-labelledby="{{ $crud->instantCreateButtons[$button->name]['name'] }}_create_modal"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content"></div>
        </div>
    </div>
@endisset
