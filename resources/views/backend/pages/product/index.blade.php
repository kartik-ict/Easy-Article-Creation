@extends('backend.layouts.master')

@section('title')
    {{ __('product.title') }}
@endsection

@section('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.jqueryui.min.css">
    <style>
        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .step {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .step-header {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .form-control {
            border-radius: 6px;
        }

        .btn {
            border-radius: 6px;
        }

        .btn-back, .btn-next {
            margin-top: 10px;
        }

        /* Loader Style */
        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 2s linear infinite;
            margin: auto;
            display: block;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
@endsection

@section('admin-content')
    <div class="page-title-area">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <div class="breadcrumbs-area clearfix">
                    <h4 class="page-title pull-left">{{ __('product.title') }}</h4>
                    <ul class="breadcrumbs pull-left">
                        <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li><span>{{ __('product.title') }}</span></li>
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
            <div class="col-lg-12">
                <div class="card p-4">
                    <h2 class="mb-4">{{ __('product.search_product_ean') }}</h2>

                    <!-- Step 1: Search EAN -->
                    <div id="step1" class="step">
                        <div class="step-header">Step 1: {{ __('product.enter_ean') }}</div>
                        <div class="form-group col-6">
                            <input type="text" id="ean" class="form-control" placeholder="{{ __('product.enter_ean') }}">
                        </div>
                        <button id="searchBtn" class="btn btn-primary mt-2">{{ __('product.search') }}</button>
                    </div>

                    <!-- Step 2: Show Product Data (Initially hidden) -->
                    <div id="step2" style="display: none;" class="step">
                        <div class="step-header">Step 2: {{ __('product.product_details') }}</div>
                        <div id="productDetails">
                            <!-- Loading Spinner initially -->
                            <div class="loader"></div>
                        </div>

                        <!-- Ask about grade -->
                        <div class="form-group mt-4 col-6" style="display:none;" id="gradeSection">
                            <label>{{ __('product.is_different_grade') }}</label>
                            <select id="differentGrade" class="form-control">
                                <option value="" selected disabled>{{ __('product.select_grade_alert') }}</option>
                                <option value="no">{{ __('product.no') }}</option>
                                <option value="yes">{{ __('product.yes') }}</option>
                            </select>
                        </div>

                        <!-- Back and Next Buttons -->
                        <button id="backBtn" class="btn btn-secondary btn-back" style="display:none;">{{ __('product.previous') }}</button>
                        <button id="nextBtn" class="btn btn-primary btn-next" style="display:none;">{{ __('product.next') }}</button>
                    </div>

                    <!-- Step 3: Update Stock (Initially hidden) -->
                    <div id="step3" style="display: none;" class="step">
                        <div class="step-header">Step 3: {{ __('product.update_stock') }}</div>
                        <div id="step3Content">
                            <!-- Current Stock Displayed as Disabled Input -->
                            <div class="row mb-3">
                                <div class="col-6">
                                    <label>{{ __('product.current_stock') }}</label>
                                    <input type="number" id="currentStock" class="form-control" disabled>
                                </div>
                                <!-- Update Stock Field -->
                                <div id="newStockSection" style="display:none;" class="col-6">
                                    <label>{{ __('product.new_stock') }}</label>
                                    <input type="number" id="newStock" class="form-control" placeholder="{{ __('product.enter_new_stock') }}">
                                </div>
                            </div>

                            <!-- Back and Update Data Buttons -->
                            <button id="backStep2Btn" class="btn btn-secondary btn-back">{{ __('product.previous') }}</button>
                            <button id="updateDataBtn" class="btn btn-primary btn-next">{{ __('product.update_stock') }}</button>
                        </div>
                    </div>
                    <!-- Additional Section for Yes Selection (Initially hidden) -->

                    <div id="yesStepDetails" style="display:none;" class="step">
                        <div class="step-header">{{ __('product.step3_additional_product_details') }}</div>

                        <div class="form-group">
                            <label for="productName">{{ __('product.product_name') }}</label>
                            <span id="productName"></span> <!-- To display the product name -->
                        </div>

                        <div class="form-group">
                            <label for="productPrice">{{ __('product.product_price') }}</label>
                            <span id="productPrice"></span> <!-- To display the product price -->
                        </div>

                        <div class="form-group">
                            <label for="productDescription">{{ __('product.product_description') }}</label>
                            <span id="productDescription"></span> <!-- To display the product description -->
                        </div>

                        <!-- Property Group Selection -->
                        <div class="form-group">
                            <label for="propertyOptions">{{ __('product.property_group') }}</label>
                            <select id="propertyOptions" class="js-example-basic-single form-control">
                            </select>
                        </div>

                        <!-- Back and Next Buttons -->
                        <button id="back3YesStep" class="btn btn-secondary btn-back">{{ __('product.previous') }}</button>
                        <button id="next3YesStep" class="btn btn-primary btn-next">{{ __('product.next') }}</button>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function () {
            const apiUrl = "{{ url('/api/product') }}";
            let productDetails = {};
            let selectedGrade = "";
            let selectedPropertyGroup = "";

            // Step 1: Search product by EAN
            $('#searchBtn').on('click', function () {
                const ean = $('#ean').val();

                if (!ean) {
                    alert('{{ __('product.enter_ean_alert') }}');
                    return;
                }

                // Show loader while fetching product data
                $('#productDetails').html('<div class="loader"></div>');

                // Hide Step 1 and show Step 2 after the loader
                $('#step1').hide();
                $('#step2').show();

                // Fetch product data
                $.ajax({
                    url: "{{ route('product.search') }}",
                    method: "POST",
                    data: { ean, _token: "{{ csrf_token() }}" },
                    success: function (response) {
                        if (response.product && response.product.productData && response.product.productData.length > 0) {
                            let productTable = `
                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>{{ __('product.product_name') }}</th>
                            <th>{{ __('product.ean_number') }}</th>
                            <th>{{ __('product.stock') }}</th>
                            <th>{{ __('product.property_group_option') }}</th>
                        </tr>
                    </thead>
                    <tbody>`;

                            response.product.productData.forEach(product => {
                                // Map property group option names based on option IDs
                                let propertyGroupNames = '';
                                if (product.attributes.optionIds) {
                                    product.attributes.optionIds.forEach(optionId => {
                                        let matchingOption = response.product.productData.attributes.optionIds.find(option =>
                                            option.id === optionId && option.type === "property_group_option"
                                        );
                                        console.log("matchingOption",matchingOption);
                                        if (matchingOption) {
                                            propertyGroupNames += `${matchingOption.attributes.name || 'N/A'}<br>`;
                                        }
                                    });
                                }

                                productTable += `
                    <tr>
                        <td>${product.attributes.translated.name || '-'}</td>
                        <td>${product.attributes.ean || '-'}</td>
                        <td>${product.attributes.stock || '0'}</td>
                        <td>${propertyGroupNames || '-'}</td>
                    </tr>`;
                            });

                            productTable += `</tbody></table>`;

                            $('#productDetails').html(productTable);
                            $('#gradeSection').show();
                            $('#backBtn').show();
                            $('#nextBtn').show();
                        } else {
                            $('#productDetails').html('<p class="text-danger">{{ __('product.no_product_found') }}</p>');
                            $('#nextBtn').hide();
                        }
                    },
                    error: function () {
                        alert('{{ __('product.error_occurred') }}');
                        $('#productDetails').html('<p class="text-danger">{{ __('product.error_occurred') }}</p>');
                        $('#nextBtn').hide();
                    }
                });

            });

            // Handle grade selection
            $('#differentGrade').on('change', function () {
                selectedGrade = $(this).val();
                if (selectedGrade === "no") {
                    $('#newStockSectionnewStockSection').show();  // Show new stock input
                    $('#updateDataBtn').show();  // Show update button
                }else if (selectedGrade === "yes") {
                } else {
                    $('#newStockSection').hide();  // Hide new stock input
                    $('#updateDataBtn').hide();  // Hide update button
                }
            });

            // Handle Next button (Step 2 -> Step 3)
            $('#nextBtn').on('click', function () {
                if (selectedGrade === "no") {
                    // Transition to Step 3
                    $('#step2').hide();
                    $('#step3').show();
                    $('#currentStock').val(productDetails.stock);  // Show current stock
                } else if (selectedGrade === "yes") {
                    $('#step2').hide();
                    $('#yesStepDetails').show(); // Assuming #yesStepDetails is the container for Yes flow

                    // Populate product details
                    $('#productName').text(productDetails.name);
                }else {
                    alert('{{ __('product.select_grade_alert') }}');
                }
            });

            // Handle Back button (Step 2 -> Step 1)
            $('#backBtn').on('click', function () {
                $('#step2').hide();
                $('#step1').show();
                $('#gradeSection').hide();  // Hide grade selection section when going back
                $('#nextBtn').hide();  // Hide next button when going back
                $('#productDetails').html('<div class="loader"></div>');  // Show loader in Step 2 again
            });

            // Handle Back button (Step 3 -> Step 2)
            $('#backStep2Btn').on('click', function () {
                $('#step3').hide();
                $('#step2').show();
                $('#nextBtn').show();  // Show next button again
            });

            $('#back3YesStep').on('click', function () {
                $('#yesStepDetails').hide();  // Hide Step 3 (Yes Step Details)
                $('#step2').show();  // Show Step 2
            });

            // Handle Update Data button (Update stock)
            $('#updateDataBtn').on('click', function () {
                const newStock = $('#newStock').val();
                if (!newStock) {
                    alert('{{ __('product.enter_new_stock_alert') }}');
                    return;
                }
                $('#step3Content').html('<div class="loader"></div>');
                // Update stock logic
                $.ajax({
                    url: "{{ route('product.update_stock') }}",
                    method: 'POST',
                    data: {
                        product_id: productDetails.id,
                        new_stock: newStock,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        alert('{{ __('product.stock_updated') }}');
                        location.reload();
                        $('#step3').hide();
                        $('#step1').show();
                    },
                    error: function () {
                        alert('{{ __('product.error_updating_stock') }}');
                    }
                });
            });

            // API CALLING For The PropertyGroup

            let currentPage = 1;
            let isLoading = false;
            let isEndOfResults = false;

            $('#propertyOptions').select2({
                width: '50%',
                placeholder: '@lang("product.propertyGroup")',
                ajax: {
                    url: '{{ route("product.propertyGroupSearch") }}',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        if (params.term) {
                            currentPage = 1;
                        }

                        return {
                            page: currentPage,
                            limit: 25,
                            term: params.term || '',
                            'total-count-mode': 1
                        };
                    },
                    processResults: function (data) {
                        isEndOfResults = (data.propertyGroups.length < 25);

                        const results = data.propertyGroups.map(function (group) {
                            return {
                                id: group.id,
                                text: group.attributes.translated.name
                            };
                        });

                        return {
                            results: results,
                            pagination: {
                                more: !isEndOfResults
                            }
                        };
                    },
                    cache: true
                },
                minimumInputLength: 0,
                allowClear: true,
                language: {
                    searching: function () {
                        return "Zoeken, even geduld...";
                    },
                    loadingMore: function () {
                        return "Meer resultaten laden...";
                    },
                    noResults: function () {
                        return "Geen resultaten gevonden.";
                    }
                }
            });

// Reset flags and handle scrolling for pagination
            $('#propertyOptions').on('select2:open', function () {
                currentPage = 1;
                isLoading = false;
                isEndOfResults = false;

                const dropdown = $('.select2-results__options');

                dropdown.on('scroll', function () {
                    const scrollTop = dropdown.scrollTop();
                    const containerHeight = dropdown.innerHeight();
                    const scrollHeight = dropdown[0].scrollHeight;

                    if (scrollTop + containerHeight >= scrollHeight - 10 && !isEndOfResults) {
                        isLoading = true;
                        currentPage++;
                        $('#propertyOptions').select2('open');
                    }
                });
            });

            $('#propertyOptions').on('select2:close', function () {
                currentPage = 1;
                isLoading = false;
                isEndOfResults = false;
            });

        });
    </script>
@endsection
