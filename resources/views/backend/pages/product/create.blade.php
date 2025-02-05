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
                    @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                        @endforeach
                    </div>
                    @endif
                    <form action="{{ route('product.saveData') }}" method="POST">
                        @csrf
                        <div class="form-row d-flex flex-wrap mb-4">
                            <div class="form-group col-md-6 col-sm-12 px-2">
                                <label for="name">@lang('product.name'):</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="form-group col-md-6 col-sm-12 px-2">
                                <label for="stock">@lang('product.stock'):</label>
                                <input type="number" class="form-control" id="stock" name="stock" required>
                            </div>
                        </div>

                        <div class="form-row d-flex flex-wrap mb-4">
                            <div class="form-group col-md-6 col-sm-12 px-2">
                                <label for="category">@lang('product.category'):</label>
                                <select id="category-select" class="js-example-basic-single form-control" name="category[]" multiple required>
                                </select>
                            </div>
                            <div class="form-group col-md-6 col-sm-12 px-2">
                                <label for="manufacturer">@lang('product.manufacturer'):</label>
                                <select id="manufacturer-select" class="js-example-basic-single form-control" name="manufacturer" required>
                                </select>
                            </div>
                        </div>

                        <div class="form-row d-flex flex-wrap mb-4">
                            <div class="form-group col-md-6 col-sm-12 px-2">
                                <label for="taxId">@lang('product.tax_id'):</label>
                                <select id="tax-provider-select" class="js-example-basic-single form-control" name="taxId" required>
                                </select>
                            </div>
                            <div class="form-group col-md-6 col-sm-12 px-2">
                                <label for="productNumber">@lang('product.product_number'):</label>
                                <input type="text" class="form-control" id="productNumber" name="productNumber"
                                    required>
                            </div>
                        </div>

                        <div class="form-row d-flex flex-wrap mb-4">
                            <div class="form-group col-md-6 col-sm-12 px-2">
                                <label for="price_gross">{{ __('product.price_gross') }}</label>
                                <input
                                    type="number"
                                    name="priceGross"
                                    id="priceGross"
                                    class="form-control"
                                    step="any"
                                    required
                                    placeholder="{{ __('product.enter_price_gross') }}">
                            </div>
                            <div class="form-group col-md-6 col-sm-12 px-2">
                                <label for="price_net">{{ __('product.price_net') }}</label>
                                <input
                                        type="number"
                                        name="priceNet"
                                        id="priceNet"
                                        class="form-control"
                                        step="any"
                                        placeholder="{{ __('product.calculated_price_net') }}">
                            </div>
                        </div>

                        <div class="form-row d-flex flex-wrap mb-4">
                            <div class="form-group col-md-6 col-sm-12 px-2">
                                <label for="active_for_all">{{ __('product.active_for_all_label') }}</label>
                                <input type="hidden" name="active_for_all" value="0">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" name="active_for_all" id="active_for_all" value="1" {{ old('active_for_all') ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="form-group col-md-6 col-sm-12 px-2">
                                <label for="salesChannel">@lang('product.sales_channel'):</label>
                                <select id="sales-channel-select" class="js-example-basic-single form-control" name="salesChannel[]" multiple required>
                                </select>
                            </div>
                        </div>

                        <div class="form-row d-flex flex-wrap mb-4">
                            <div class="form-group col-md-6 col-sm-12 px-2">
                                <label for="ean">@lang('product.ean'):</label>
                                <input
                                    type="text"
                                    id="ean"
                                    class="form-control"
                                    name="ean"
                                    placeholder="@lang('product.enter_ean')"
                                    required>
                            </div>
                            <div class="form-group col-md-6 col-sm-12 px-2">
                                <label for="mediaUrl">@lang('product.media_url'):</label>
                                <input type="file" class="form-control" id="media" name="media">
                            </div>
                        </div>


                        <div class="form-row d-flex flex-wrap mb-4">
                            <div class="form-group col-md-6 col-sm-12 px-2">
                                <label for="width">@lang('product.width'):</label>
                                <input type="text" class="form-control" id="width" name="productWidth">
                            </div>
                            <div class="form-group col-md-6 col-sm-12 px-2">
                                <label for="Height">@lang('product.height'):</label>
                                <input type="text" class="form-control" id="height" name="productHeight">
                            </div>
                        </div>

                        <div class="form-row d-flex flex-wrap mb-4">
                            <div class="form-group col-md-6 col-sm-12 px-2">
                                <label for="Length">@lang('product.length'):</label>
                                <input type="text" class="form-control" id="length" name="productLength">
                            </div>
                            <div class="form-group col-md-6 col-sm-12 px-2">
                                <label for="Weight">@lang('product.weight'):</label>
                                <input type="text" class="form-control" id="weight" name="productWeight">
                            </div>
                        </div>

                        <div class="form-group col-md-12 col-sm-12 px-2">
                            <label for="description">@lang('product.description'):</label>
                            <textarea class="form-control" id="description" name="description"
                                      rows="5"></textarea>
                        </div>

                        <!-- Submit Button -->
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary w-100">@lang('product.submit')</button>
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

<script>
    $(document).ready(function() {
        let currentPage = 1; // Current page for pagination
        let isLoading = false; // Prevent multiple concurrent requests
        let isEndOfResults = false; // Flag to indicate end of results

        // Initialize the Select2 component
        $('#manufacturer-select').select2({
            placeholder: '@lang("product.manufacturer")',
            ajax: {
                url: '{{ route("product.manufacturerSearch") }}',
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
            placeholder: '@lang("product.sales_channel")',
            ajax: {
                url: '{{ route("product.salesChannelSearch") }}',
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
            dropdown.html('<li class="select2-results__option" style="text-align: center;">Aan het laden...</li>'); // Custom loader
        });

        // Handle scroll for infinite loading
        $('#sales-channel-select').on('select2:open', function() {
            const dropdown = $('.select2-results__options');

            dropdown.off('scroll').on('scroll', function() {
                const scrollTop = dropdown.scrollTop();
                const containerHeight = dropdown.innerHeight();
                const scrollHeight = dropdown[0].scrollHeight;

                // Load more data when scrolled to the bottom
                if (scrollTop + containerHeight >= scrollHeight - 10 && !isSalesChannelEnd && !isSalesChannelLoading) {
                    isSalesChannelLoading = true; // Prevent multiple triggers
                    salesChannelPage++; // Increment the page

                    $.ajax({
                        url: '{{ route("product.salesChannelSearch") }}',
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
                            const results = data.salesChannels.map(function(salesChannel) {
                                return {
                                    id: salesChannel.id,
                                    text: salesChannel.attributes.translated.name
                                };
                            });

                            // Append the results to the dropdown
                            results.forEach(function(result) {
                                const option = new Option(result.text, result.id, false, false);
                                $('#sales-channel-select').append(option);
                            });

                            isSalesChannelEnd = (data.salesChannels.length < 25); // Check if there are more results
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
            placeholder: '@lang("product.category")',
            ajax: {
                url: '{{ route("product.categorySearch") }}',
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
                            text: category.attributes.translated.name
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
                if (scrollTop + containerHeight >= scrollHeight - 10 && !isEndOfResultsCategory && !isLoadingCategory) {
                    isLoadingCategory = true; // Set loading flag to true to prevent multiple requests

                    currentPageCategory++;

                    // Trigger the next page load
                    $.ajax({
                        url: '{{ route("product.categorySearch") }}',
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
                                    text: category.attributes.translated.name
                                };
                            });

                            results.forEach(function(result) {
                                const option = new Option(result.text, result.id, false, false);
                                $('#category-select').append(option).trigger('change');
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


        // Tax Provider API

        $('#tax-provider-select').select2({
            ajax: {
                url: '{{ route("product.fetchTax") }}',
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
                                text: provider.attributes.name,
                                taxRate: provider.attributes.taxRate // Include tax rate
                            };
                        })
                    };
                }
            },
            placeholder: '@lang("product.taxRate")',
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
        });

        // Event listener for price gross input
        $('#priceGross').on('input', function() {
            const priceGross = parseFloat($(this).val()) || 0;
            const taxRate = parseFloat($(this).data('taxRate')) || 0;

            // Calculate net price
            const priceNet = priceGross / (1 + taxRate / 100);
            $('#priceNet').val(priceNet.toFixed(5));
        });


    });
</script>
@endsection