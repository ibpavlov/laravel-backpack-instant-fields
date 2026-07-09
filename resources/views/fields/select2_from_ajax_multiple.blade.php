<!-- select2 from ajax multiple -->
@php
    $connected_entity = new $field['model'];
    $connected_entity_key_name = isset($field['key_name']) ? $field['key_name'] : $connected_entity->getKeyName();
    $old_value = old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? false;
@endphp

@include('crud::fields.inc.wrapper_start')
<label>{!! $field['label'] !!}</label>
<?php $entity_model = $crud->model; ?>

    <div class="input-group select2-ajax-group">
        <select
            class="form-control"
            data-init-function="bpFieldInitSelect2FromAjaxInstantMultiple"
        name="{{ $field['name'] }}[]"
        styleOld="width: 100%"  style=""
        id="select2_ajax_multiple_{{ $field['name'] }}"
        @include('crud::inc.field_attributes', ['default_class' =>  'form-control'])
        multiple>

        @if ($old_value)
            @foreach ($old_value as $item)
                @if (!is_object($item))
                    @php
                        $item = $connected_entity->find($item);
                    @endphp
                @endif
                <option value="{{ $item->getKey() }}" selected>
                    {{ $item->{isset($field['option_label']) ? $field['option_label'] : $field['attribute']} }}
                </option>
            @endforeach
        @endif
        </select>

        @if ($field['on_the_fly']['create'] ?? true)
            @include('webfactor::fields.inc.button-add')
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
@if ($crud->checkIfFieldIsFirstOfItsType($field))

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
        {{-- include select2 css --}}
        @basset('https://unpkg.com/select2@4.0.13/dist/css/select2.min.css')
        @basset('https://unpkg.com/select2-bootstrap-theme@0.1.0-beta.10/dist/select2-bootstrap.min.css')
        {{-- allow clear --}}
        @if($field['allows_null'] ?? $entity_model::isColumnNullable($field['name']))
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

@endif


<!-- include field specific select2 js-->
@push('crud_fields_scripts')
    @bassetBlock('backpack/fields/webfactor-select2-from-ajax-instant-multiple.js')
    <script>
        function bpFieldInitSelect2FromAjaxInstantMultiple(element) {
            var searchTerm;
            var form = $(element).closest('form');

            // load create modal content
            $("#{{ $field['on_the_fly']['entity'] ?? 'ajax_entity' }}_create_modal", form).on('show.bs.modal', function (e) {
                var loadurl = $(e.relatedTarget).data('load-url');
                var form2 = $(e.relatedTarget).closest('form');

                var data = form2.serializeArray().filter(function (index) {
                    return $.inArray(index.name, <?php echo json_encode($field['on_the_fly']['serialize'] ?? []); ?>) >= 0;
                });

                $(this).find('.modal-content').load(loadurl + '&' + $.param(data) + '&' + $.param({'searchTerm': searchTerm}));
            }).on('hidden.bs.modal', function () {
                // TODO for testing
                if($(".modal:visible").length > 0) {
                    //Slap the class on it (wait a moment for things to settle)
                    setTimeout(function() {
                        $('body').addClass('modal-open');
                    },100)
                }
            });

            // trigger select2 for each untriggered select2 box
            $("#select2_ajax_multiple_{{ $field['name'] }}", form).each(function (i, obj) {
                var form2 = $(obj).closest('form');

                if (!$(obj).hasClass("select2-hidden-accessible")) {
                    $(obj).select2({
                        theme: 'bootstrap',
                        multiple: true,
                        width: null,
                        containerCssClass: ':all:',
                        placeholder: "{{ $field['placeholder'] }}",
                        minimumInputLength: "{{ $field['minimum_input_length'] }}",
                        {{-- allow clear --}}
                            @if ($entity_model::isColumnNullable($field['name']) ?? $entity_model::isColumnNullable($field['name']))
                        allowClear: true,
                        @endif
                        data: [
                                @if ($old_value)
                                @php
                                    if(isset($field['key_name'])) {
                                        $item = $connected_entity->where($field['key_name'], $old_value)->first();
                                    } else {
                                        $item = $connected_entity->find($old_value);
                                    }
                                @endphp
                                @if ($item)

                                {{-- allow clear --}}
                                @if ($entity_model::isColumnNullable($field['name']))
                            {
                                "id": null,
                                "text": @json($field['placeholder'] ?? '-'),
                                "details": {}
                            },
                                @endif
                            {
                                "id": "{{ isset($field['key_name']) ? $item->{$field['key_name']} : $item->getKey() }}",
                                "text": @json($item->{isset($field['option_label']) ? $field['option_label'] : $field['attribute']} ?? '-'),
                                "selected": true,
                                "details": @json(isset($field['transform_logic']) ? $field['transform_logic']($item): $item)
                            }
                            @endif
                            @endif
                        ],
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
                                    form: form2.serializeArray() // all other form inputs
                                };
                            },
                            processResults: function (data, params) {
                                searchTerm = params.term;
                                params.page = params.page || 1;

                                return {
                                    results: $.map(data.data, function (item) {
                                        if (typeof item['extras'] === "string") {
                                            item['extras'] = JSON.parse(item['extras']);
                                        }
                                        //console.log(item);
                                        return {
                                            text: item["{{ isset($field['option_label']) ? $field['option_label'] : $field['attribute'] }}"],
                                            id: item["{{ $connected_entity_key_name }}"],
                                            details: item
                                        }
                                    }),
                                    more: data.current_page < data.last_page
                                };
                            },
                            cache: true
                        },
                    });
                }
            })
            {{-- allow clear TODO test for multiple --}}
            @if ($entity_model::isColumnNullable($field['name']))
                .on('select2:unselecting', function (e) {
                    $(this).val(null).trigger('change');
                })
            @endif
            ;

            @if (isset($field['dependencies']))
                @foreach (\Illuminate\Support\Arr::wrap($field['dependencies']) as $dependency)
                    $('input[name={{ $dependency }}], select[name={{ $dependency }}], checkbox[name={{ $dependency }}], radio[name={{ $dependency }}], textarea[name={{ $dependency }}]', form).change(function () {
                        $("#select2_ajax_multiple_{{ $field['name'] }}", form).val(null).trigger("change");
                    });
                @endforeach
            @endif
        });
    </script>
@endpush
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
