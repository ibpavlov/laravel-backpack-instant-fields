@extends('webfactor::modal.layout', ['action' => $delete ])

@section('header')
    <h5 class="box-title">{{ trans('backpack::crud.delete') }} {{ $crud->entity_name }}</h5>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
        @include('crud::inc.grouped_errors')

            {{ trans('backpack::crud.delete_confirm') }}<br><br>

            {{ $entry->{$request->input('attribute')} }}

        </div>
    </div>
@endsection

@section('footer')
    @include('webfactor::modal.inc.delete_form_buttons')
@endsection

@push('crud_fields_scripts')
    <script>
        $("#delete_{{ $entity }}").submit(function (e) {

            $.ajax({
                type: "DELETE",
                url: "{{ url($crud->route . '/ajax', '/') }}",
                data: { id: "{{ $id }}"},
                success: function (data) {
                    new PNotify({
                        type: "success",
                        title: "{{ trans('backpack::base.success') }}",
                        text: "{{ trans('backpack::crud.delete_success') }}"
                    });

                    $("#{{ $entity }}_delete_modal").modal('hide');

                    // Clear select
                    $("#select2_ajax_{{ $request->input('field_name') }}").val(null).trigger('change');
                },
                error: function (data) {
                    new PNotify({
                        type: "error",
                        title: "{{ trans('backpack::base.error') }}",
                        text: "{{ trans('backpack::base.error') }}: " + data.responseJSON
                    });
                }
            });

            e.preventDefault(); // avoid to execute the actual submit of the form.
        });
    </script>

@endpush
