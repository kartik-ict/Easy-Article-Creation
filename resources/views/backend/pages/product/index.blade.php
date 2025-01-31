@extends('backend.layouts.master')

@section('title')
    {{ __('product.title') }}
@endsection

@section('styles')
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" type="text/css"
          href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" type="text/css"
          href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.jqueryui.min.css">
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

        .btn-back, .btn-next, .btn-new-variant {
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
                    <div id="route-container"
                         data-manufacturer-search="{{ route('product.manufacturerSearch') }}"></div>

                    <div id="route-container-sw-manufacturer-search"
                         data-sw-manufacturer-search="{{ route('sw.manufacturers.search') }}"></div>

                    <div id="route-container-sales" data-sales-search="{{ route('product.salesChannelSearch') }}"></div>
                    <div id="route-container-category"
                         data-category-search="{{ route('product.categorySearch') }}"></div>
                    <div id="route-container-tax" data-tax-search="{{ route('product.fetchTax') }}"></div>
                    <div id="route-container-property" data-property-search="{{ route('product.propertyGroupSearch') }}"></div>
                    <div id="route-container-property-option" data-property-search-option="{{ route('product.propertyGroupOptionSearch') }}"></div>
                    <div id="route-container-property-option-save" data-property-option-save="{{ route('product.savePropertyOption') }}"></div>

                    <!-- Step 1: Search EAN -->
                    <div id="step1" class="step">
                        <div class="step-header">Step 1: {{ __('product.enter_ean') }}</div>
                        <div class="form-group col-6">
                            <input type="text" id="ean" class="form-control"
                                   placeholder="{{ __('product.enter_ean') }}">
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
                        <div class="form-group mt-4 col-12" style="display:none;" id="bolSection">
                            <div class="form-group row">
                                <div class="form-group row col-12">
                                    <div class="col-6">
                                        <div id="productCategories">
                                            <p>{{ __('product.categories') }}</p>
                                        </div>
                                        <div>
                                            <a class="btn btn-primary btn-next" id="searchSwCategory">Search categories in Shopware </a>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3 col-6">
                                        <label for="category">@lang('product.category'):</label>
                                        <select id="category-select" class="js-example-basic-single form-control"
                                                name="category[]" multiple required>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row col-12">

                                    <div class="col-6">
                                        <div id="productManufacturer">
                                            <p id="productManufacturerName">
                                                <strong>{{ __('product.manufacturer') }}</strong>: <span
                                                    id="manufacturerValue">{{ $manufacturer ?? '' }}</span></p>
                                        </div>
                                        <div>
                                            <a class="btn btn-primary btn-next" id="searchSwManufacturer">Search
                                                Manufacturer in Shopware </a>
                                        </div>
                                    </div>
                                    <div class="form-group mb-3 col-6">
                                        <label for="manufacturer">@lang('product.manufacturer'):</label>
                                        <select id="manufacturer-sw-search"
                                                class="js-example-basic-single form-control"
                                                name="manufacturer" required>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- Back and Next Buttons -->
                        <button id="backBtn" class="btn btn-secondary btn-back"
                                style="display:none;">{{ __('product.previous') }}</button>
                        <button id="nextBtn" class="btn btn-primary btn-next"
                                style="display:none;">{{ __('product.next') }}</button>
                    </div>

                    <!-- Step 3: Update Stock (Initially hidden) -->
                    <div id="step3" style="display: none;" class="step">
                        <div class="step-header">Step 3: {{ __('product.update_stock') }}</div>
                        <div id="step3Content">
                            <!-- Product Table with Stock Information -->
                            <table class="table table-bordered mt-3" id="productTable">
                                <thead>
                                <tr>
                                    <th>{{ __('product.product_name') }}</th>
                                    <th>{{ __('product.ean_number') }}</th>
                                    <th>{{ __('product.current_stock') }}</th>
                                    <th>{{ __('product.new_stock') }}</th>
                                    <th>{{ __('product.action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <!-- Dynamic rows will be inserted here -->
                                </tbody>
                            </table>

                            <!-- Back and Update Data Buttons -->
                            <button id="backStep2Btn"
                                    class="btn btn-secondary btn-back">{{ __('product.previous') }}</button>
                            {{--                            <button id="updateDataBtn" class="btn btn-primary btn-next">{{ __('product.update_stock') }}</button>--}}
                        </div>
                    </div>

                    <!-- Additional Section for Yes Selection (Initially hidden) -->

                    <div id="yesStepDetails" style="display:none;" class="step">
                        <div class="step-header">{{ __('product.step3_show_product_details') }}</div>

                        <table class="table table-bordered mt-3" id="productTable-update">
                            <thead>
                            <tr>
                                <th>{{ __('product.product_name') }}</th>
                                <th>{{ __('product.ean_number') }}</th>
                                <th>{{ __('product.current_stock') }}</th>
                                <th>{{ __('product.action') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <!-- Dynamic rows will be inserted here -->
                            </tbody>
                        </table>


                        <!-- Product Edit Modal -->
                        <div class="modal fade" id="productEditModal" tabindex="-1" role="dialog"
                             aria-labelledby="editModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{ __('product.step4') }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="product-form" action="{{ route('product.saveData') }}"
                                              method="POST">
                                            @csrf
                                            <div class="row">
                                                <!-- Left Column -->
                                                <div class="col-md-6">
                                                    <h5 class="mb-3">{{ __('product.general_information') }}</h5>

                                                    <div class="form-group mb-3">
                                                        <label for="name">@lang('product.name'):</label>
                                                        <input type="text" class="form-control" id="name"
                                                               name="name"
                                                               required>
                                                    </div>

                                                    <div class="form-group mb-3">
                                                        <label for="stock">@lang('product.stock'):</label>
                                                        <input type="number" class="form-control" id="stock"
                                                               name="stock" required>
                                                    </div>

                                                    <div class="form-group mb-3">
                                                        <label for="manufacturer">@lang('product.manufacturer')
                                                            :</label>
                                                        <select id="manufacturer-select"
                                                                class="js-example-basic-single form-control"
                                                                name="manufacturer" required>
                                                        </select>
                                                    </div>

                                                    <div class="form-group mb-3">
                                                        <label for="taxId">@lang('product.tax_id'):</label>
                                                        <select id="tax-provider-select"
                                                                class="js-example-basic-single form-control"
                                                                name="taxId" required>
                                                        </select>
                                                    </div>

                                                    <div class="form-group mb-3">
                                                        <label for="productNumber">@lang('product.product_number')
                                                            :</label>
                                                        <input type="text" class="form-control" id="productNumber"
                                                               name="productNumber" required>
                                                    </div>

                                                    <div class="form-group mb-3">
                                                        <label
                                                            for="price_gross">{{ __('product.price_gross') }}</label>
                                                        <input type="number" name="priceGross" id="priceGross"
                                                               class="form-control" step="any" required
                                                               placeholder="{{ __('product.enter_price_gross') }}">
                                                    </div>

                                                    <div class="form-check form-switch mb-3">
                                                        <input type="checkbox" class="form-check-input"
                                                               name="active_for_all" id="active_for_all" value="1">
                                                        <label class="form-check-label"
                                                               for="active_for_all">{{ __('product.active_for_all_label') }}</label>
                                                    </div>
                                                </div>

                                                <!-- Right Column -->
                                                <div class="col-md-6">
                                                    <h5 class="mb-3">{{ __('product.additional_information') }}</h5>

                                                    <div class="form-group mb-3">
                                                        <label for="description">@lang('product.description')
                                                            :</label>
                                                        <textarea class="form-control" id="description"
                                                                  name="description" rows="5"></textarea>
                                                    </div>

                                                    <div class="form-group mb-3">
                                                        <label for="salesChannel">@lang('product.sales_channel')
                                                            :</label>
                                                        <select id="sales-channel-select"
                                                                class="js-example-basic-single form-control"
                                                                name="salesChannel[]" multiple required>
                                                        </select>
                                                    </div>

                                                    <div class="form-group mb-3">
                                                        <label for="category">@lang('product.category'):</label>
                                                        <select id="category-select"
                                                                class="js-example-basic-single form-control"
                                                                name="category[]" multiple required>
                                                        </select>
                                                    </div>

                                                    <div class="form-group mb-3">
                                                        <label for="ean">@lang('product.ean'):</label>
                                                        <input type="text" id="eanForm" class="form-control"
                                                               name="eanForm"
                                                               placeholder="@lang('product.enter_ean')"
                                                               required>
                                                    </div>

                                                    <div class="form-group mb-3">
                                                        <label for="mediaUrl">@lang('product.media_url'):</label>
                                                        <input type="url" class="form-control" id="mediaUrl"
                                                               name="mediaUrl">
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="price_net">{{ __('product.price_net') }}</label>
                                                        <input type="number" name="priceNet" id="priceNet"
                                                               class="form-control" step="any"
                                                               placeholder="{{ __('product.calculated_price_net') }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="submit"
                                                    class="btn btn-success w-100">{{ __('product.submit') }}</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Back and Next Buttons -->
                        <button id="back3YesStep"
                                class="btn btn-secondary btn-back">{{ __('product.previous') }}</button>
                        <button id="next3YesStep" class="btn btn-primary btn-next">{{ __('product.next') }}</button>
                        <button id="newVariantButton" class="btn btn-primary btn-new-variant">
                            {{ __('product.create_new_variant') }}
                        </button>


                        <div id="propertyGroupSection" style="display: none;" class="container mt-5">
                            <h5 class="text-primary mb-4">@lang('product.select_required_info')</h5>

                            <div class="row">
                                <!-- Property Group Select -->
                                <div class="col-md-6 mb-4">
                                    <div class="card p-3">
                                        <label for="propertyGroupSelect" class="font-weight-bold">@lang('product.property_select_group')</label>
                                        <div class="d-flex align-items-center mt-2">
                                            <select id="propertyGroupSelect" class="form-control me-3 w-75"></select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Property Group Option Select -->
                                <div id="propertyGroupOptionWrapper" class="col-md-6 mb-4" style="display: none;">
                                    <div class="card p-3">
                                        <label for="propertyGroupOptionSelect" class="font-weight-bold">@lang('product.property_select_group_option')</label>
                                        <div class="d-flex align-items-center mt-2">
                                            <select id="propertyGroupOptionSelect" class="form-control me-3 w-75"></select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="addPropertyOptionWrapper" class="mt-4 d-flex align-items-center gap-2" style="visibility : hidden;">
                                <div class="col-md-3">
                                    <button id="addPropertyOptionBtn" class="btn btn-success w-100">@lang('product.add_option')</button>
                                </div>
                                <span><strong>OR</strong></span>
                                <div class="col-md-3">
                                    <button id="createPropertyGroupOptionBtn" class="btn btn-primary w-100">@lang('product.create_new_property')</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal for creating new Property Group Option -->
                    <div class="modal fade" id="createPropertyGroupOptionModal" tabindex="-1" aria-labelledby="createPropertyGroupOptionModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="createPropertyGroupOptionModalLabel">@lang('product.create_new_property_option')</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <!-- Display selected Property Group Name -->
                                    <div class="mb-3">
                                        <label for="selectedPropertyGroupName" class="form-label">@lang('product.selected_property_group')</label>
                                        <input type="text" id="selectedPropertyGroupName" class="form-control" disabled>
                                        <input type="hidden" id="selectedPropertyGroupId" />
                                    </div>

                                    <!-- New Property Option Name -->
                                    <div class="mb-3">
                                        <label for="newPropertyOptionName" class="form-label">@lang('product.new_property_option_name')</label>
                                        <input type="text" id="newPropertyOptionName" class="form-control">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('product.cancel')</button>
                                    <button type="button" class="btn btn-primary" id="savePropertyGroupOptionBtn">@lang('product.save')</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('backend/assets/js/common-select2.js') }}"></script>
    <script src="{{ asset('backend/assets/js/common-bol.js') }}"></script>

    <script>
        $(document).ready(function () {
            const apiUrl = "{{ url('/api/product') }}";
            let productDetails = {};
            let allProductData = [];
            let apiResponse = [];
            let selectedGrade = "";
            let productRow = "";
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
                    data: {ean, _token: "{{ csrf_token() }}"},
                    success: function (response) {

                        if (response.product.bol === true) {
                            $('#gradeSection').hide();
                            $('#bolSection').show();
                        } else {
                            $('#gradeSection').show();
                            $('#bolSection').hide();
                        }
                        const categories = response.product.productData[0].categories || '-';
                        const manufacturer = response.product.productData[0].brand || response.product.productData[0].manufacturer || '-';

                        let productCategories = `
                            <p><strong>{{ __('Categories') }}:</strong></p>
                            <ul id="bolCat">
                                ${categories.map(category => `<li>${category}</li>`).join(',')}
                            </ul>
                        `;
                        $('#productCategories').html(productCategories);

                        let productManufacturer = `
                        <p><strong>{{ __('product.manufacturer') }}:</strong><span id="manufacturerValue">${manufacturer}</span></p>
                        `;
                        $('#productManufacturer').html(productManufacturer);

                        if (response.product) {
                            allProductData = response.product;
                            apiResponse = response;
                            const productName = response.product.name || '-';
                            const eanNumber = response.product.ean || '-';
                            // const categories = response.product.productData[0].categories || '-';
                            const categories = response.product.productData[0].categories || '-';
                            // const eanNumber = response.product.ean || '-';

                            let productTable = `
                    <p><strong>{{ __('product.product_name') }}:</strong> ${productName}</p>
                    <p><strong>{{ __('product.ean_number') }}:</strong> ${eanNumber}</p>
                `;
                            $('#productDetails').html(productTable);

                            //$('#gradeSection').show();
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
                } else if (selectedGrade === "yes") {
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
                    $('#productTable tbody').empty();

                    if (Array.isArray(allProductData.productData)) {
                        allProductData.productData.forEach(product => {
                            let propertyGroupNames = '';

                            // Handling product attributes like options (property group)
                            if (product.attributes?.optionIds?.length) {
                                product.attributes.optionIds.forEach(optionId => {

                                    // Find matching property group options from the included data
                                    const matchingOption = allProductData.included.find(data => {
                                        return data.id === optionId && data.type === "property_group_option";
                                    });

                                    if (matchingOption && matchingOption.attributes?.name) {
                                        propertyGroupNames += `${matchingOption.attributes.name}<br>`;
                                    } else {
                                        propertyGroupNames += 'N/A<br>';
                                    }
                                });
                            }

                            // Create a new row for the table
                            const productRow = `
            <tr data-product-id="${product.id}">
                <td>${product.attributes.translated?.name + "(" + propertyGroupNames + ")" || '-'}</td>
                <td>${product.attributes.ean || '-'}</td>
                <td><input type="number" class="form-control" value="${product.attributes.stock || '0'}" disabled></td>
                <td><input type="number" class="form-control new-stock" placeholder="{{ __('product.enter_new_stock') }}"></td>
                {{--<td><button class="btn btn-primary update-stock-btn" data-product-id="${product.id}" data-product-ean="${product.attributes.ean}">{{ __('product.update_stock') }}</button></td>--}}
                            </tr>`;

                            // Append the new row to the table
                            $('#productTable tbody').append(productRow);
                        });
                    }


                } else if (selectedGrade === "yes") {
                    $('#step2').hide();
                    $('#yesStepDetails').show();
                    $('#productTable-update tbody').empty();

                    if (Array.isArray(allProductData.productData)) {
                        allProductData.productData.forEach(product => {
                            let propertyGroupNames = '';

                            // Handling product attributes like options (property group)
                            if (product.attributes?.optionIds?.length) {
                                product.attributes.optionIds.forEach(optionId => {

                                    // Find matching property group options from the included data
                                    const matchingOption = allProductData.included.find(data => {
                                        return data.id === optionId && data.type === "property_group_option";
                                    });

                                    if (matchingOption && matchingOption.attributes?.name) {
                                        propertyGroupNames += `${matchingOption.attributes.name}<br>`;
                                    } else {
                                        propertyGroupNames += 'N/A<br>';
                                    }
                                });
                            }

                            // Create a new row for the table
                            const productRow = `
            <tr data-product-id="${product.id}">
                <td>${product.attributes.translated?.name + "(" + propertyGroupNames + ")" || '-'}</td>
                <td>${product.attributes.ean || '-'}</td>
                <td><input type="number" class="form-control" value="${product.attributes.stock || '0'}" disabled></td>
                {{--<td><button class="btn btn-primary edit-details-btn" data-product-id="${product.id}">{{ __('product.edit') }}</button></td></tr>--}}
                `;

                            $('#productTable-update tbody').append(productRow);
                        });
                    }
                } else {
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
            $(document).on('click', '.update-stock-btn', function () {
                const row = $(this).closest('tr'); // Get the clicked row
                const productId = $(this).data('product-id'); // Get the product ID from the clicked button
                const currentStock = row.find('.current-stock').text(); // Get the current stock value from the clicked row
                const newStockInput = row.find('.new-stock'); // Get the new stock input for this row

                const newStock = newStockInput.val(); // Get the new stock value from input

                // Ensure that new stock value is entered
                if (!newStock) {
                    alert('{{ __('product.enter_new_stock_alert') }}'); // Show alert if new stock value is not entered
                    return;
                }

                // Show loader while updating stock
                $('#step3Content').html('<div class="loader"></div>');

                // AJAX request to update stock
                $.ajax({
                    url: "{{ route('product.update_stock') }}",
                    method: 'POST',
                    data: {
                        product_id: productId, // Pass product ID dynamically
                        new_stock: newStock,    // Pass new stock value
                        _token: "{{ csrf_token() }}" // CSRF token
                    },
                    success: function (response) {
                        alert('{{ __('product.stock_updated') }}'); // Show success alert
                        location.reload(); // Reload the page to see updated stock values
                        $('#step3').hide();
                        $('#step1').show();
                    },
                    error: function () {
                        alert('{{ __('product.error_updating_stock') }}'); // Show error alert if update fails
                    }
                });
            });

            // Handle the Yes Flow
            $(document).on('click', '.edit-details-btn', function () {
                const productId = $(this).data('product-id');

                // Find the product details from allProductData

                const productData = allProductData.productData.find(product => product.id === productId);

                const manufacturerId = productData.attributes.manufacturerId;
                const categoryselectIds = productData.attributes.categoryIds;


                const categoriesId = allProductData.included.find(
                    item => item.id === manufacturerId && item.type === "category"
                );

                const manufacturerData = allProductData.included.find(
                    item => item.id === manufacturerId && item.type === "product_manufacturer"
                );

                if (manufacturerData) {
                    const manufacturerName = manufacturerData.attributes.translated.name;
                    const newOption = new Option(manufacturerName, manufacturerId, true, true);

                    $('#manufacturer-select').append(newOption).trigger('change');
                }

                function findIncludedData(id, type) {
                    return allProductData.included.find(
                        item => item.id === id && item.type === type
                    );
                }

                const taxId = productData.attributes.taxId;
                const taxData = findIncludedData(taxId, "tax");
                if (taxData) {
                    const taxName = taxData.attributes.translated.name;
                    const newTaxOption = new Option(taxName, taxId, true, true);
                    $('#tax-select').append(newTaxOption).trigger('change');
                }

                function findIncludedData(id, type) {
                    return allProductData.included.find(
                        item => item.id === id && item.type === type
                    );
                }

                // Handle Category
                const categoryId = productData.attributes.categoryId;
                const categoryData = findIncludedData(categoryId, "category");
                if (categoryData) {
                    const categoryName = categoryData.attributes.translated.name;
                    const newCategoryOption = new Option(categoryName, categoryId, true, true);
                    $('#category-select').append(newCategoryOption).trigger('change');
                }

                // Handle Sales Channel
                const salesChannelId = productData.attributes.salesChannelId;
                const salesChannelData = findIncludedData(salesChannelId, "sales_channel");
                if (salesChannelData) {
                    const salesChannelName = salesChannelData.attributes.translated.name;
                    const newSalesChannelOption = new Option(salesChannelName, salesChannelId, true, true);
                    $('#sales-channel-select').append(newSalesChannelOption).trigger('change');
                }


                if (productData) {
                    // Populate the modal form with product data
                    $('#name').val(productData.attributes.translated.name || '');
                    $('#eanForm').val(productData.attributes.ean || '');
                    $('#stock').val(productData.attributes.stock || '');
                    $('#description').val(productData.attributes.description || '');

                    // For the `productNumber` field, if there's a `productNumber` field in your data:
                    $('#productNumber').val(productData.attributes.productNumber || '');

                    // For the `priceGross` and `priceNet` fields, if they are part of the `price` array:
                    if (productData.attributes.price && productData.attributes.price.length > 0) {
                        $('#priceGross').val(productData.attributes.price[0].gross || ''); // Assuming gross is the first value in the price object
                        $('#priceNet').val(productData.attributes.price[0].net || ''); // Assuming net is the first value in the price object
                    }

                    // For the tax provider, if you have the taxId:
                    $('#tax-provider-select').val(productData.attributes.taxId || '').trigger('change');

                    // For the salesChannel, assuming it’s an array:
                    $('#sales-channel-select').val(productData.attributes.sales || []).trigger('change');

                    // For the category, assuming it’s an array of categoryIds:
                    $('#category-select').val(productData.attributes.categoryIds || []).trigger('change');

                    // For the `active_for_all` checkbox, if `available` is true, set it to checked:
                    $('#active_for_all').prop('checked', productData.attributes.available || false);

                    // Show the modal
                    $('#productEditModal').modal('show');
                } else {
                    alert("Product details not found.");
                }
            });


        });
    </script>
@endsection
