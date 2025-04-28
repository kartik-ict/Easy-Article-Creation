@extends('backend.layouts.master')

@section('title')
    @lang('product.create_product')
@endsection

@section('admin-content')
    <!-- page title area start -->
    <div class="page-title-area">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <div class="breadcrumbs-area clearfix">
                    <h4 class="page-title pull-left">@lang('product.create_product')</h4>
                    <ul class="breadcrumbs pull-left">
                        <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li><span>@lang('product.create_product')</span></li>
                    </ul>
                </div>
            </div>
            <div class="col-sm-6 clearfix">
                @include('backend.layouts.partials.logout')
            </div>
        </div>
    </div>

    <div class="main-content-inner">
        <div class="row">
            <!-- data table start -->
            <div class="col-12 mt-5">
                <div class="card">
                    <div class="card-body">
                        <h4>@lang('product.create_product')</h4>
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif
                        <form action="{{ route('product.saveData') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row mb-2">
                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <label for="name">@lang('product.name') <span class="text-danger">*</span>:</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <label for="stock">@lang('product.stock') <span class="text-danger">*</span>:</label>
                                    <input type="number" class="form-control @error('stock') is-invalid @enderror"
                                        id="stock" name="stock" value="{{ old('stock') }}" required>
                                    @error('stock')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <label for="category">@lang('product.category') <span class="text-danger">*</span>:</label>
                                    <select id="category-select"
                                        class="js-example-basic-single form-control @error('category.*') is-invalid @enderror"
                                        name="category[]" multiple required>
                                    </select>
                                    @error('category.*')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <label for="manufacturer">@lang('product.manufacturer') <span class="text-danger">*</span>:</label>
                                    <select id="manufacturer-select"
                                        class="js-example-basic-single form-control @error('manufacturer') is-invalid @enderror"
                                        name="manufacturer" required>
                                    </select>
                                    @error('manufacturer')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <label for="taxId">@lang('product.tax_id') <span class="text-danger">*</span>:</label>
                                    <select id="tax-provider-select"
                                        class="js-example-basic-single form-control @error('taxId') is-invalid @enderror"
                                        name="taxId" required>
                                    </select>
                                    @error('taxId')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <label for="productNumber">@lang('product.product_number') <span
                                            class="text-danger">*</span>:</label>
                                    <input type="text" class="form-control @error('productNumber') is-invalid @enderror"
                                        id="productNumber" name="productNumber" value="{{ old('productNumber') }}"
                                        required>
                                    @error('productNumber')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <label for="price_gross">
                                        {{ __('product.price_gross') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" name="priceGross" id="priceGross"
                                        class="form-control @error('priceGross') is-invalid @enderror" step="any"
                                        required placeholder="{{ __('product.enter_price_gross') }}">
                                    @error('priceGross')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <label for="price_net">
                                        {{ __('product.price_net') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" name="priceNet" id="priceNet"
                                        class="form-control @error('priceNet') is-invalid @enderror" step="any" required
                                        placeholder="{{ __('product.calculated_price_net') }}">
                                    @error('priceNet')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <input type="hidden" name="active_for_all" value="0">
                                    <div class="form-check form-switch">
                                        <label for="active_for_all">{{ __('product.active_for_all_label') }}</label>
                                        <input type="checkbox" class="form-check-input" name="active_for_all"
                                            id="active_for_all" value="1"
                                            {{ old('active_for_all') ? 'checked' : '' }} checked>
                                    </div>
                                </div>
                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <label for="salesChannel">@lang('product.sales_channel') <span class="text-danger">*</span>
                                        :</label>
                                    <select id="sales-channel-select" class="js-example-basic-single form-control"
                                        name="salesChannel[]" multiple required>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <label for="ean">@lang('product.ean'):</label>
                                    <input type="text" id="ean" class="form-control" name="ean"
                                        placeholder="@lang('product.enter_ean')">
                                </div>
                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <label for="mediaUrl">@lang('product.media_url'):</label>
                                    <input type="file" class="form-control" id="media" name="media">
                                    <span id="uploadStatus" class="text-info"></span>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-12 form-group mb-3">
                                    <label for="bolProductShortDescription">@lang('product.shortDescription'):</label>
                                    <textarea class="form-control" id="shortDescription" name="bolProductShortDescription" rows="1"></textarea>
                                </div>
                                <div class="form-group col-md-12 col-sm-12 px-2">
                                    <label for="description">@lang('product.description'):</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                        rows="5">{{ old('description') }}</textarea>
                                    @error('description')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror

                                    <!-- Hidden input to store mediaId dynamically -->
                                    <input type="hidden" name="media_id" id="media_id">
                                </div>
                            </div>
                            {{-- Product Marketplace Section --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="my-3">{{ __('product.product_marketplace_information') }}</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <input type="hidden" name="bolNlActive" value="0">
                                    <label for="bolNlActive"
                                        class="bolNlActive">{{ $customFields['migration_DMG_product_bol_nl_active']['label'] }}</label>
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" name="bolNlActive"
                                            id="bolNlActive" value="1" {{ old('bolNlActive') ? 'checked' : '' }}>
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <input type="hidden" name="bolBeActive" value="0">
                                    <label for="bolBeActive"
                                        class="bolBeActive">{{ $customFields['migration_DMG_product_bol_be_active']['label'] }}</label>
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" name="bolBeActive"
                                            id="bolBeActive" value="1" {{ old('bolBeActive') ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label for="bolNlPrice"
                                        class="bolNlPrice">{{ $customFields['migration_DMG_product_bol_price_nl']['label'] }}</label>
                                    <input type="text" name="bolNlPrice" id="bolNlPrice" class="form-control">
                                </div>

                                <div class="col-md-6 form-group mb-3">
                                    <label for="bolBePrice"
                                        class="bolBePrice">{{ $customFields['migration_DMG_product_bol_price_be']['label'] }}</label>
                                    <input type="text" name="bolBePrice" id="bolBePrice" class="form-control">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label for="bolNLDeliveryTime"
                                        class="bolNLDeliveryTime">{{ $customFields['migration_DMG_product_bol_nl_delivery_code']['label'] }}:</label>
                                    <select id="bolNLDeliveryTime"
                                        class="js-example-basic-single form-control bolNLDeliveryTimeSelect"
                                        name="bolNLDeliveryTime">
                                        @foreach ($customFields['migration_DMG_product_bol_nl_delivery_code']['options'] as $option)
                                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label for="bolBEDeliveryTime"
                                        class="bolBEDeliveryTime">{{ $customFields['migration_DMG_product_bol_be_delivery_code']['label'] }}:</label>
                                    <select id="bolBEDeliveryTime"
                                        class="js-example-basic-single form-control bolBEDeliveryTimeSelect"
                                        name="bolBEDeliveryTime">
                                        @foreach ($customFields['migration_DMG_product_bol_be_delivery_code']['options'] as $option)
                                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label for="bolCondition"
                                        class="bolCondition">{{ $customFields['migration_DMG_product_bol_condition']['label'] }}:</label>
                                    <select id="bolCondition"
                                        class="js-example-basic-single form-control bolConditionSelect"
                                        name="bolCondition">
                                        @foreach ($customFields['migration_DMG_product_bol_condition']['options'] as $option)
                                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label for="bolConditionDescription"
                                        class="bolConditionDescription">{{ $customFields['migration_DMG_product_bol_condition_desc']['label'] }}:</label>
                                    <input type="text" class="form-control" id="bolConditionDescription"
                                        name="bolConditionDescription">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <div class="form-check">
                                        <input type="hidden" name="bolOrderBeforeTomorrow" value="0">
                                        <input type="checkbox" class="form-check-input bolOrderBeforeTomorrow"
                                            id="bolOrderBeforeTomorrow" value="1" name="bolOrderBeforeTomorrow">
                                        <label class="form-check-label bolOrderBeforeTomorrow"
                                            for="bolOrderBeforeTomorrow">{{ $customFields['migration_DMG_product_proposition_1']['label'] }}</label>
                                    </div>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <div class="form-check">
                                        <input type="hidden" name="bolOrderBefore" value="0">
                                        <input type="checkbox" class="form-check-input bolOrderBefore"
                                            id="bolOrderBefore" value="1" name="bolOrderBefore">
                                        <label class="form-check-label bolOrderBefore"
                                            for="bolOrderBefore">{{ $customFields['migration_DMG_product_proposition_2']['label'] }}</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <div class="form-check">
                                        <input type="hidden" name="bolLetterboxPackage" value="0">
                                        <input type="checkbox" class="form-check-input bolLetterboxPackage"
                                            id="bolLetterboxPackage" value="1" name="bolLetterboxPackage">
                                        <label class="form-check-label bolLetterboxPackage"
                                            for="bolLetterboxPackage">{{ $customFields['migration_DMG_product_proposition_3']['label'] }}</label>
                                    </div>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <div class="form-check">
                                        <input type="hidden" name="bolLetterboxPackageUp" value="0">
                                        <input type="checkbox" class="form-check-input bolLetterboxPackageUp"
                                            id="bolLetterboxPackageUp" value="1" name="bolLetterboxPackageUp">
                                        <label class="form-check-label bolLetterboxPackageUp"
                                            for="bolLetterboxPackageUp">{{ $customFields['migration_DMG_product_proposition_4']['label'] }}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <input type="hidden" name="bolPickUpOnly" value="0">
                                    <label for="bolPickUpOnly"
                                        class="bolPickUpOnly">{{ $customFields['migration_DMG_product_proposition_5']['label'] }}</label>
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" name="bolPickUpOnly"
                                            id="bolPickUpOnly" value="1"
                                            {{ old('bolPickUpOnly') ? 'checked' : '' }}>
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="binLocation">@lang('product.bin_location')</label>
                                    <select name="bin_location_id" id="binLocation" class="form-control">
                                        @foreach ($binLocationList as $location)
                                            <option value="{{ $location['id'] }}"> {{ $location['attributes']['code'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <input type="hidden" name="warehouse" value="{{ $admin->warehouse_id }}" />
                            </div>
                            <!-- Submit Button -->
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary">@lang('product.submit')</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- data table end -->

        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/23.0.0/classic/ckeditor.js"></script>
    <script>
        $(document).ready(function() {
            ClassicEditor.create(document.querySelector('#description'))
                .catch(error => {
                    console.error(error);
                });

            // Initialize select2 for all delivery and condition dropdowns
            const selectFields = ['#bolNLDeliveryTime', '#bolBEDeliveryTime', '#bolCondition'];

            selectFields.forEach(field => {
                $(field).select2({
                    placeholder: $(field).prev('label').text(),
                    minimumInputLength: 0,
                    allowClear: true,
                    multiple: false,
                    language: {
                        searching: function() {
                            return "Zoeken, even geduld...";
                        },
                        loadingMore: function() {
                            return "Meer resultaten laden...";
                        },
                        noResults: function() {
                            return "Geen resultaten gevonden.";
                        }
                    }
                });

                $(field).val('').trigger('change');
                $(field).trigger("select2:close");
            });
            let currentPage = 1; // Current page for pagination
            let isLoading = false; // Prevent multiple concurrent requests
            let isEndOfResults = false; // Flag to indicate end of results

            // Initialize the Select2 component
            $('#manufacturer-select').select2({
                placeholder: '@lang('product.manufacturer')',
                ajax: {
                    url: '{{ route('product.manufacturerSearch') }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        // Prepare data for the API request

                        if (params.term) {
                            currentPage = 1; // Reset page to 1 when a term is typed
                        }

                        return {
                            page: currentPage, // Send the current page
                            limit: 25, // Limit the results per page
                            term: params.term || '', // Search term entered by the user
                            'total-count-mode': 1 // Fetch total count if needed
                        };
                    },
                    processResults: function(data) {
                        // Check if we've reached the end of the results
                        isEndOfResults = (data.manufacturers.length < 25);

                        // Map results to Select2 format
                        const results = data.manufacturers.map(function(manufacturer) {
                            return {
                                id: manufacturer.id,
                                text: manufacturer.attributes.translated.name
                            };
                        });

                        return {
                            results: results,
                            pagination: {
                                more: !isEndOfResults // Show 'more' if there are more results
                            }
                        };
                    },
                    cache: true,
                },
                minimumInputLength: 0,
                allowClear: true,
                language: {
                    searching: function() {
                        return "Zoeken, even geduld..."; // Dutch translation for "searching"
                    },
                    loadingMore: function() {
                        return "Meer resultaten laden..."; // Dutch translation for "loading more results"
                    },
                    noResults: function() {
                        return "Geen resultaten gevonden."; // Dutch translation for "no results found"
                    }
                }
            });

            // When dropdown is opened, reset the page number and flags
            $('#manufacturer-select').on('select2:open', function() {
                currentPage = 1; // Start from page 1
                isLoading = false;
                isEndOfResults = false;

                const dropdown = $('.select2-results__options');

                // Scroll event handler to trigger the next API request when scrolling to the bottom
                dropdown.on('scroll', function() {
                    const scrollTop = dropdown.scrollTop();
                    const containerHeight = dropdown.innerHeight();
                    const scrollHeight = dropdown[0].scrollHeight;

                    // If we're at the bottom of the dropdown and more results are available
                    if (scrollTop + containerHeight >= scrollHeight - 10 && !isEndOfResults) {
                        isLoading = true; // Set loading flag to true to prevent multiple requests

                        currentPage++;

                        // Trigger the next page load by opening the dropdown
                        $('#manufacturer-select').select2('open');
                    }
                });
            });

            // Optionally, handle closing the dropdown manually if required
            $('#manufacturer-select').on('select2:close', function() {
                // Reset page when dropdown is closed, if needed
                currentPage = 1;
                isLoading = false; // Reset loading flag when dropdown closes
                isEndOfResults = false; // Reset end of results flag
            });


            // Sale Channel API

            let salesChannelPage = 1; // Current page for sales channel pagination
            let isSalesChannelEnd = false; // End of results flag
            let isSalesChannelLoading = false; // Prevent multiple requests

            // Initialize Select2 for Sales Channel with loader message
            $('#sales-channel-select').select2({
                placeholder: '@lang('product.sales_channel')',
                ajax: {
                    url: '{{ route('product.salesChannelSearch') }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        if (params.term) {
                            salesChannelPage = 1; // Reset page when search term changes
                            isSalesChannelEnd = false;
                        }
                        return {
                            page: salesChannelPage,
                            limit: 25,
                            term: params.term || '', // Search term
                            'total-count-mode': 1
                        };
                    },
                    processResults: function(data) {
                        isSalesChannelEnd = data.salesChannels.length < 25; // Check if it's the end

                        const results = data.salesChannels.map(function(salesChannel) {
                            return {
                                id: salesChannel.id,
                                text: salesChannel.attributes.translated.name
                            };
                        });

                        return {
                            results: results,
                            pagination: {
                                more: !isSalesChannelEnd
                            }
                        };
                    },
                    cache: true,
                },
                minimumInputLength: 0,
                allowClear: true,
                multiple: true, // Enable multiple selection
                language: {
                    loadingMore: function() {
                        return "@lang('product.loading_more')"; // Message when loading more results
                    },
                    searching: function() {
                        return "@lang('product.searching')"; // Message during search
                    },
                    noResults: function() {
                        return "@lang('product.no_results_found')"; // Message when no results found
                    }
                }
            });

            // Custom handling of the loader when dropdown opens
            $('#sales-channel-select').on('select2:open', function() {
                salesChannelPage = 1; // Reset pagination
                isSalesChannelEnd = false; // Reset end flag
                isSalesChannelLoading = false; // Reset loading flag

                // Add a custom loader or information text to the dropdown
                const dropdown = $('.select2-results__options');
                dropdown.html(
                    '<li class="select2-results__option" style="text-align: center;">Aan het laden...</li>'
                ); // Custom loader
            });

            // Handle scroll for infinite loading
            $('#sales-channel-select').on('select2:open', function() {
                const dropdown = $('.select2-results__options');

                dropdown.off('scroll').on('scroll', function() {
                    const scrollTop = dropdown.scrollTop();
                    const containerHeight = dropdown.innerHeight();
                    const scrollHeight = dropdown[0].scrollHeight;

                    // Load more data when scrolled to the bottom
                    if (scrollTop + containerHeight >= scrollHeight - 10 && !isSalesChannelEnd && !
                        isSalesChannelLoading) {
                        isSalesChannelLoading = true; // Prevent multiple triggers
                        salesChannelPage++; // Increment the page

                        $.ajax({
                            url: '{{ route('product.salesChannelSearch') }}',
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            dataType: 'json',
                            data: {
                                page: salesChannelPage,
                                limit: 25,
                                term: $('.select2-search__field').val() || '',
                                'total-count-mode': 1
                            },
                            success: function(data) {
                                const results = data.salesChannels.map(function(
                                    salesChannel) {
                                    return {
                                        id: salesChannel.id,
                                        text: salesChannel.attributes.translated
                                            .name
                                    };
                                });

                                // Append the results to the dropdown
                                results.forEach(function(result) {
                                    const option = new Option(result.text,
                                        result.id, false, false);
                                    $('#sales-channel-select').append(option);
                                });

                                isSalesChannelEnd = (data.salesChannels.length <
                                    25); // Check if there are more results
                            },
                            complete: function() {
                                isSalesChannelLoading = false; // Reset loading flag
                            }
                        });
                    }
                });
            });

            // Reset everything when dropdown closes
            $('#sales-channel-select').on('select2:close', function() {
                salesChannelPage = 1; // Reset page
                isSalesChannelLoading = false; // Reset loading flag
                isSalesChannelEnd = false; // Reset end flag
            });

            // Category API

            let currentPageCategory = 1; // Current page for pagination
            let isLoadingCategory = false; // Prevent multiple concurrent requests
            let isEndOfResultsCategory = false; // Flag to indicate end of results

            $('#category-select').select2({
                placeholder: '@lang('product.category')',
                ajax: {
                    url: '{{ route('product.categorySearch') }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        // Prepare data for the API request
                        if (params.term) {
                            currentPageCategory = 1; // Reset page to 1 when a term is typed
                        }

                        return {
                            page: currentPageCategory, // Send the current page
                            limit: 25, // Limit the results per page
                            term: params.term || '', // Search term entered by the user
                            'total-count-mode': 1 // Fetch total count if needed
                        };
                    },
                    processResults: function(data) {
                        // Check if we've reached the end of the results
                        isEndOfResultsCategory = (data.categories.length < 25);

                        // Map results to Select2 format
                        const results = data.categories.map(function(category) {
                            return {
                                id: category.id,
                                text: category.attributes.breadcrumb ?
                                    category.attributes.breadcrumb.filter(item => !["Default",
                                        "Shop"
                                    ].includes(item)).join(' > ') : category.attributes
                                    .translated.name
                            };
                        });

                        return {
                            results: results,
                            pagination: {
                                more: !isEndOfResultsCategory // Show 'more' if there are more results
                            }
                        };
                    },
                    cache: true,
                },
                minimumInputLength: 0,
                allowClear: true,
                multiple: true, // Enable multiple selection
                language: {
                    searching: function() {
                        return "Zoeken, even geduld..."; // Dutch translation for "searching"
                    },
                    loadingMore: function() {
                        return "Meer resultaten laden..."; // Dutch translation for "loading more results"
                    },
                    noResults: function() {
                        return "Geen resultaten gevonden."; // Dutch translation for "no results found"
                    }
                }
            });

            // When dropdown is opened, reset the page number and flags
            $('#category-select').on('select2:open', function() {
                currentPageCategory = 1; // Start from page 1
                isLoadingCategory = false;
                isEndOfResultsCategory = false;

                const dropdown = $('.select2-results__options');

                // Scroll event handler to trigger the next API request when scrolling to the bottom
                dropdown.on('scroll', function() {
                    const scrollTop = dropdown.scrollTop();
                    const containerHeight = dropdown.innerHeight();
                    const scrollHeight = dropdown[0].scrollHeight;

                    // If we're at the bottom of the dropdown and more results are available
                    if (scrollTop + containerHeight >= scrollHeight - 10 && !
                        isEndOfResultsCategory && !isLoadingCategory) {
                        isLoadingCategory =
                            true; // Set loading flag to true to prevent multiple requests

                        currentPageCategory++;

                        // Trigger the next page load
                        $.ajax({
                            url: '{{ route('product.categorySearch') }}',
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            dataType: 'json',
                            data: {
                                page: currentPageCategory,
                                limit: 25,
                                term: $('.select2-search__field').val() || '',
                                'total-count-mode': 1
                            },
                            success: function(data) {
                                const results = data.categories.map(function(category) {
                                    return {
                                        id: category.id,
                                        text: category.attributes.breadcrumb ?
                                            category.attributes.breadcrumb
                                            .filter(item => !["Default", "Shop"]
                                                .includes(item)).join(' > ') :
                                            category.attributes.translated.name
                                    };
                                });

                                results.forEach(function(result) {
                                    const option = new Option(result.text,
                                        result.id, false, false);
                                    $('#category-select').append(option)
                                        .trigger('change');
                                });

                                isEndOfResultsCategory = (data.categories.length < 25);
                            },
                            complete: function() {
                                isLoadingCategory = false; // Reset loading flag
                            }
                        });
                    }
                });
            });

            // Optionally, handle closing the dropdown manually if required
            $('#category-select').on('select2:close', function() {
                // Reset page when dropdown is closed, if needed
                currentPageCategory = 1;
                isLoadingCategory = false; // Reset loading flag when dropdown closes
                isEndOfResultsCategory = false; // Reset end of results flag
            });

            // Event listener for price gross input
            $('#priceGross').on('input', function() {
                const priceGross = parseFloat($(this).val()) || 0;
                const taxRate = parseFloat($(this).data('taxRate')) || 0;

                // Calculate net price
                const priceNet = priceGross / (1 + taxRate / 100);
                $('#priceNet').val(priceNet.toFixed(2));
            });
            // net price to gross price convert
            $('#priceNet').on('input', function() {
                const priceNet = parseFloat($(this).val()) || 0;
                const taxRate = parseFloat($('#priceGross').data('taxRate')) || 0;

                // Calculate gross price
                const priceGross = priceNet * (1 + taxRate / 100);
                $('#priceGross').val(priceGross.toFixed(2));
            });

            // Tax Provider API

            $('#tax-provider-select').select2({
                ajax: {
                    url: '{{ route('product.fetchTax') }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    dataType: 'json',
                    data: function(params) {
                        return {
                            term: params.term, // Search term
                            page: params.page || 1
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.taxProviders.map(function(provider) {
                                return {
                                    id: provider.id,
                                    text: `${provider.attributes.name} (${provider.attributes.taxRate}%)`,
                                    taxRate: provider.attributes.taxRate // Include tax rate
                                };
                            })
                        };
                    }
                },
                placeholder: '@lang('product.taxRate')',
                minimumResultsForSearch: Infinity,
                language: {
                    searching: function() {
                        return "Zoeken, even geduld..."; // Dutch translation for "searching"
                    },
                    loadingMore: function() {
                        return "Meer resultaten laden..."; // Dutch translation for "loading more results"
                    },
                    noResults: function() {
                        return "Geen resultaten gevonden."; // Dutch translation for "no results found"
                    }
                }
            }).on('select2:select', function(e) {
                const selectedTaxRate = e.params.data.taxRate || 0; // Get the selected tax rate

                // Update the tax rate for calculation
                $('#priceGross').data('taxRate', selectedTaxRate);
                $('#priceGross').trigger('input');
            });

            // Load tax rates and set default on page load
            $.ajax({
                url: '{{ route('product.fetchTax') }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function(data) {
                    const taxProviders = data.taxProviders.map(provider => ({
                        id: provider.id,
                        text: `${provider.attributes.name} (${provider.attributes.taxRate}%)`,
                        taxRate: provider.attributes.taxRate
                    }));

                    // Find 21% tax rate option
                    const defaultTax = taxProviders.find(provider => provider.taxRate === 21);

                    if (defaultTax) {
                        // Set default option
                        const newOption = new Option(defaultTax.text, defaultTax.id, true, true);
                        $('#tax-provider-select').append(newOption).trigger('change');

                        // Set tax rate and trigger calculation
                        $('#priceGross').data('taxRate', defaultTax.taxRate);
                        $('#priceGross').trigger('input');
                    }
                }
            });

            // Uploading the Media

            $('#media').on('change', function() {
                let file = this.files[0];
                if (file) {
                    let formData = new FormData();
                    formData.append('media', file);

                    $('#uploadStatus').text('Uploading...');

                    $.ajax({
                        url: '{{ route('media.upload') }}',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.mediaId && response.shopwareResponse) {
                                $('#uploadStatus').text('Upload Successful!');

                                if ($('#media_id').length === 0) {
                                    $('#media').after(
                                        '<input type="hidden" name="media_id" id="media_id" value="' +
                                        response.mediaId + '">');
                                } else {
                                    $('#media_id').val(response.mediaId);
                                }

                            } else {
                                $('#uploadStatus').text('Upload Failed!');
                            }
                        },
                        error: function() {
                            $('#uploadStatus').text('Error Uploading File!');
                        }
                    });
                }
            });
        });
    </script>
@endsection
