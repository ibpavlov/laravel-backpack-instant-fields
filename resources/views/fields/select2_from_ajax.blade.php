<!-- select2 from ajax -->
@php
    $connected_entity = new $field['model'];
    $connected_entity_key_name = $connected_entity->getKeyName();
    $old_value = old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? false;
@endphp

@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    <?php $entity_model = $crud->model; ?>

    <div class="input-group">
        <select
            class="form-control"
            name="{{ $field['name'] }}"
            style="width: 100%"
            id="select2_ajax_{{ $field['name'] }}"
            @include('crud::inc.field_attributes', ['default_class' =>  'form-control'])
        >

            @if ($old_value)
                @php
                    $item = $connected_entity->find($old_value);
                @endphp
                @if ($item)

                    {{-- allow clear --}}
                    @if ($entity_model::isColumnNullable($field['name']))
                        <option value="" selected>
                            {{ $field['placeholder'] }}
                        </option>
                    @endif

                    <option value="{{ $item->getKey() }}" selected>
                        {{ $item->{isset($field['option_label']) ? $field['option_label'] : $field['attribute']} }}
                    </option>
                @endif
            @endif
        </select>

        @if ($field['on_the_fly']['create'] ?? true)
            @include('webfactor::fields.inc.button-add')
        @endif

        @if ($field['on_the_fly']['edit'] ?? true)
            @include('webfactor::fields.inc.button-edit')
        @endif

        @if ($field['on_the_fly']['crud'] ?? false)
            @include('webfactor::fields.inc.button-crud')
        @endif

        @if ($field['on_the_fly']['delete'] ?? true)
            @include('webfactor::fields.inc.button-delete')
        @endif
    </div>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')


{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
@push('crud_fields_styles')
    {{-- include select2 css --}}
    @basset('https://unpkg.com/select2@4.0.13/dist/css/select2.min.css')
    @basset('https://unpkg.com/select2-bootstrap-theme@0.1.0-beta.10/dist/select2-bootstrap.min.css')
    {{-- allow clear --}}
    @if($field['allows_null'])
        @bassetBlock('backpack/pro/fields/select2-from-ajax-field-'.app()->getLocale().'.css')
        <style type="text/css">
            .select2-selection__clear::after {
                content: ' {{ trans('backpack::crud.clear') }}';
            }
        </style>
        @endBassetBlock
    @endif
@endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
@push('crud_fields_scripts')
    {{-- include select2 js --}}
    @basset('https://unpkg.com/select2@4.0.13/dist/js/select2.full.min.js')
    @if (app()->getLocale() !== 'en')
        @basset('https://unpkg.com/select2@4.0.13/dist/js/i18n/' . str_replace('_', '-', app()->getLocale()) . '.js')
    @endif
@endpush

<!-- include field specific select2 js-->
@push('crud_fields_scripts')
    <script>
        jQuery(document).ready(function ($) {

            var searchTerm;

            // load create modal content
            $("#{{ $field['on_the_fly']['entity'] ?? 'ajax_entity' }}_create_modal").on('show.bs.modal', function (e) {
                var loadurl = $(e.relatedTarget).data('load-url');
                var form = $(e.relatedTarget).closest('form');

                var data = form.serializeArray().filter(function(index){
                    return $.inArray(index.name, <?php echo json_encode($field['on_the_fly']['serialize'] ?? []); ?>) >= 0;
                });

                $(this).find('.modal-content').load(loadurl + '&' + $.param(data) + '&' + $.param({'searchTerm': searchTerm}));
            });

            // load edit/delete modal content
            $(
                "#{{ $field['on_the_fly']['entity'] ?? 'ajax_entity' }}_edit_modal," +
                "#{{ $field['on_the_fly']['entity'] ?? 'ajax_entity' }}_delete_modal"
            ).on('show.bs.modal', function (e) {
                var button = e.relatedTarget;

                if ($(button).hasClass('disabled')) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                } else {
                    var loadurl = $(button).data('load-url');
                    var id = $(button).data('id');

                    $(this).find('.modal-content').load(loadurl + '&id=' + id);
                }
            });

            // update id for edit/delete modal url
            $("#select2_ajax_{{ $field['name'] }}").change(function (e) {
                var entry = $("#select2_ajax_{{ $field['name'] }}").select2('data')[0];
                var editCrud = $("[data-target='#{{ $field['on_the_fly']['entity'] ?? 'ajax_entity' }}_edit_crud']");
                var editButton = $("[data-target='#{{ $field['on_the_fly']['entity'] ?? 'ajax_entity' }}_edit_modal']");
                var deleteButton = $("[data-target='#{{ $field['on_the_fly']['entity'] ?? 'ajax_entity' }}_delete_modal']");

                if (entry) {
                    editCrud.data("id", entry.id).prop('href', editCrud.data('url') + '/' + entry.id + '/edit').removeClass('disabled');
                    editButton.data("id", entry.id).removeClass('disabled');
                    deleteButton.data("id", entry.id).removeClass('disabled');
                } else {
                    editCrud.data("id", "").prop('href', '#').addClass('disabled');
                    editButton.data("id", "").addClass('disabled');
                    deleteButton.data("id", "").addClass('disabled');
                }

            })

            // trigger select2 for each untriggered select2 box
            $("#select2_ajax_{{ $field['name'] }}").each(function (i, obj) {
                var form = $(obj).closest('form');

                if (!$(obj).hasClass("select2-hidden-accessible")) {

                    $(obj).select2({
                        theme: 'bootstrap',
                        multiple: false,
                        placeholder: "{{ $field['placeholder'] }}",
                        minimumInputLength: "{{ $field['minimum_input_length'] }}",

                        {{-- allow clear --}}
                            @if ($entity_model::isColumnNullable($field['name']))
                        allowClear: true,
                        @endif
                        ajax: {
                            url: "/{{ ltrim($field['data_source'] ?? $crud->getRoute().'/ajax', '/') }}",
                            type: '{{ $field['method'] ?? 'POST' }}',
                            dataType: 'json',
                            quietMillis: 250,
                            data: function (params) {
                                return {
                                    q: params.term, // search term
                                    field: "{{ $field['name'] }}",
                                    page: params.page,
                                    form: form.serializeArray()  // all other form inputs
                                };
                            },
                            processResults: function (data, params) {
                                searchTerm = params.term;
                                params.page = params.page || 1;

                                var result = {
                                    results: $.map(data.data, function (item) {
                                        return {
                                            text: item["{{ isset($field['option_label']) ? $field['option_label'] : $field['attribute'] }}"],
                                            id: item["{{ $connected_entity_key_name }}"]
                                        }
                                    }),
                                    more: data.current_page < data.last_page
                                };

                                return result;
                            },
                            cache: true
                        },
                    })
                    {{-- allow clear --}}
                    @if ($entity_model::isColumnNullable($field['name']))
                        .on('select2:unselecting', function (e) {
                            $(this).val('').trigger('change');
                            // console.log('cleared! '+$(this).val());
                            e.preventDefault();
                        })
                    @endif
                    ;

                }
            });

            @if (isset($field['dependencies']))
            @foreach (array_wrap($field['dependencies']) as $dependency)
            $('input[name={{ $dependency }}], select[name={{ $dependency }}], checkbox[name={{ $dependency }}], radio[name={{ $dependency }}], textarea[name={{ $dependency }}]').change(function () {
                $("#select2_ajax_{{ $field['name'] }}").val(null).trigger("change");
            });
            @endforeach
            @endif
        });
    </script>
@endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
