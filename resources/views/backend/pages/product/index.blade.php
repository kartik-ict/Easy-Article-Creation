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
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
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
            <div class="col-lg-12 mt-5">
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
                    <div id="route-container-property"
                         data-property-search="{{ route('product.propertyGroupSearch') }}"></div>
                    <div id="route-container-property-option"
                         data-property-search-option="{{ route('product.propertyGroupOptionSearch') }}"></div>
                    <div id="route-container-property-option-save"
                         data-property-option-save="{{ route('product.savePropertyOption') }}"></div>
                    <div id="route-container-variant-save"
                         data-variant-save="{{ route('product.saveVariantProduct') }}"></div>

                    <div id="route-container-sw-category-search"
                         data-sw-category-search="{{ route('sw.category.search') }}"></div>

                    <div id="route-container-sw-create-category"
                         data-sw-create-category="{{ route('sw.create.category') }}">

                        <div id="route-container-save-bol-data"
                             data-save-bol-data="{{ route('product.SaveBolData') }}">

                            <div id="route-container-tax" data-tax-search="{{ route('product.fetchTax') }}"></div>
                            <div id="route-container-property"
                                 data-property-search="{{ route('product.propertyGroupSearch') }}"></div>
                            <div id="route-container-property-option"
                                 data-property-search-option="{{ route('product.propertyGroupOptionSearch') }}"></div>
                            <div id="route-container-property-option-save"
                                 data-property-option-save="{{ route('product.savePropertyOption') }}"></div>

                            {{-- Include Step 1 Partial --}}
                            @include('backend.pages.product.partials.step1-ean-search')

                            {{-- Include Step 2 Partial --}}
                            @include('backend.pages.product.partials.step2-show-product-data')

                            {{-- Include Step 3 Partial --}}
                            @include('backend.pages.product.partials.step3')

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        window.selectGradeAlert = @json(__('product.product_created_successfully'));
        window.selectGradeAlertError = @json(__('product.failed_to_create_product'));
        window.selectCategoryAlertError = @json(__('product.selectCategoryAlertError'));
        let allProductData = [];
        let apiResponse = [];
        let bolApiResponse = []
        $(document).ready(function () {
            const apiUrl = "{{ url('/api/product') }}";
            let productDetails = {};
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
                $('#full-page-preloader').show();

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
                            bolApiResponse = response;
                            $('#gradeSection').hide();
                            $('#bolSection').show();
                            const categories = response.product.productData[0].categories;
                            const manufacturer = response.product.productData[0].brand || response.product.productData[0].manufacturer;

                            if (categories.length != 0) {
                                let productCategories = `
                                    <h5 class="mb-2">{{ __('Categories') }}:
                                    <span class="p-1 fw-normal fs-6" id="bolCat">
                                        ${categories.map(category => `${category}`).join(',')}
                                    </span>
                                    </h5>`;

                                $('#productCategories').html(productCategories);

                            } else {
                                let productCategories = `
                            <h5 class="mb-2">{{ __('Categories') }}:
                                <span class="p-1 fw-normal fs-6" id="bolCat">{{ __('product.productCategoriesErrorMessage') }}</span></h5>
                            `;
                                $('#productCategories').html(productCategories);
                            }
                            if (manufacturer) {
                                let productManufacturer = `
                            <h5 class="mb-2">{{ __('product.manufacturer') }}:
                                <span class="p-1 fw-normal fs-6" id="manufacturerValue">${manufacturer}</span></h5>
                            `;
                                $('#productManufacturer').html(productManufacturer);
                            } else {
                                let productManufacturer = `
                            <h5 class="mb-2">{{ __('product.manufacturer') }}:
                                <span class="p-1 fw-normal fs-6" id="manufacturerValue">{{ __('product.manufacturerErrorMessage') }}</span></h5>
                            `;
                                $('#productManufacturer').html(productManufacturer);
                                $('#searchSwManufacturer').hide();
                            }


                            $('#backBolBtn').show();
                            $('#nextBolBtn').show();
                            $('#productDetails').html(' ');
                            $('#backBtn').hide();
                            $('#full-page-preloader').hide();
                        } else {
                            $('#gradeSection').show();
                            $('#bolSection').hide();
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
                                $('#full-page-preloader').hide();
                            } else {
                                $('#productDetails').html('<p class="text-danger">{{ __('product.error_notfound') }}</p><p class="text-success">{{ __('product.create_product_message') }}</p><a href="{{ route('product.create') }}" class="btn btn-xs btn-success">{{ __('product.create_product') }}</a>');
                                $('#nextBtn').hide();
                                $('#backBtn').show();
                                $('#full-page-preloader').hide();
                            }
                        }
                    },
                    error: function () {
                        $('#productDetails').html('<p class="text-danger">{{ __('product.error_notfound') }}</p><p class="text-success">{{ __('product.create_product_message') }}</p><a href="{{ route('product.create') }}" class="btn btn-xs btn-success">{{ __('product.create_product') }}</a>');
                        $('#nextBtn').hide();
                        $('#backBtn').show();
                        $('#full-page-preloader').hide();
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
                $('#full-page-preloader').show();
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
                <td><button class="btn btn-primary update-stock-btn" data-product-id="${product.id}" data-product-ean="${product.attributes.ean}">{{ __('product.update_stock') }}</button></td>
                            </tr>`;

                            // Append the new row to the table
                            $('#productTable tbody').append(productRow);
                        });
                    }

                    $('#full-page-preloader').hide();
                } else if (selectedGrade === "yes") {
                    $('#step2').hide();
                    $('#yesStepDetails').show();
                    $('#productTable-update tbody').empty();
                    const productLength = allProductData.productData.length;

                    if (Array.isArray(allProductData.productData)) {
                        allProductData.productData.forEach(product => {
                            let propertyGroupNames = '';
                            if (productLength == 1 && product.attributes.parentId) {
                                $('#newVariantButton').hide();
                            } else {
                                $('#newVariantButton').show();
                            }
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

                            if (propertyGroupNames == '') {
                                propertyGroupNames = 'N/A';
                            }

                            // Create a new row for the table
                            const productRow = `
            <tr data-product-id="${product.id}">
                <td>${product.attributes.translated?.name + "(" + propertyGroupNames + ")" || '-'}</td>
                <td>${product.attributes.ean || '-'}</td>
                <td><input type="number" class="form-control" value="${product.attributes.stock || '0'}" disabled></td>
                <td><input type="number" class="form-control new-stock" placeholder="{{ __('product.enter_new_stock') }}"></td>
                <td><button class="btn btn-primary update-stock-btn" data-product-id="${product.id}" data-product-ean="${product.attributes.ean}">{{ __('product.update_stock') }}</button></td>
                {{--<td><button class="btn btn-primary edit-details-btn" data-product-id="${product.id}">{{ __('product.edit') }}</button></td></tr>--}}
                            `;

                            $('#productTable-update tbody').append(productRow);
                        });
                    }
                    $('#full-page-preloader').hide();
                } else {
                    $('#full-page-preloader').hide();
                    alert('{{ __('product.select_grade_alert') }}');
                }
            });

            // Handle Back button (Step 2 -> Step 1)
            $('#backBtn').on('click', function () {
                $('#full-page-preloader').show();
                $('#step2').hide();
                $('#step1').show();
                $('#gradeSection').hide();  // Hide grade selection section when going back
                $('#bolSection').hide();  // Hide grade selection section when going back
                $('#nextBtn').hide();  // Hide next button when going back
                $('#full-page-preloader').hide();
            });

            // Handle Back button (Step 2 -> Step 1)
            $('#backBolBtn').on('click', function () {
                $('#full-page-preloader').show();
                $('#step2').hide();
                $('#step1').show();
                $('#gradeSection').hide();  // Hide grade selection section when going back
                $('#bolSection').hide();  // Hide grade selection section when going back
                $('#nextBtn').hide();  // Hide next button when going back
                $('#full-page-preloader').hide();
            });

            $('#backBolBtnsetp3').on('click', function () {
                $('#full-page-preloader').show();
                $('#stepBol3').hide();
                $('#step2').show();
                $('#gradeSection').hide();  // Hide grade selection section when going back
                $('#bolSection').show();  // Hide grade selection section when going back
                $('#nextBtn').hide();  // Hide next button when going back
                $('#full-page-preloader').hide();
            });

            // Handle Back button (Step 3 -> Step 2)
            $('#backStep2Btn').on('click', function () {
                $('#full-page-preloader').show();
                $('#step3').hide();
                $('#step2').show();
                $('#nextBtn').show();  // Show next button again
                $('#full-page-preloader').hide();
            });

            $('#back3YesStep').on('click', function () {
                $('#full-page-preloader').show();
                $('#yesStepDetails').hide();  // Hide Step 3 (Yes Step Details)
                $('#step2').show();  // Show Step 2
                $('#full-page-preloader').hide();
            });

            // Handle Update Data button (Update stock)
            $(document).on('click', '.update-stock-btn', function () {
                $('#full-page-preloader').show();
                const row = $(this).closest('tr'); // Get the clicked row
                const productId = $(this).data('product-id'); // Get the product ID from the clicked button
                const currentStock = row.find('.current-stock').text(); // Get the current stock value from the clicked row
                const newStockInput = row.find('.new-stock'); // Get the new stock input for this row

                const newStock = newStockInput.val(); // Get the new stock value from input

                // Ensure that new stock value is entered
                if (!newStock) {
                    alert('{{ __('product.enter_new_stock_alert') }}'); // Show alert if new stock value is not entered
                    $('#full-page-preloader').hide();
                    return;
                }

                // Show loader while updating stock
                // $('#step3Content').html('<div class="loader"></div>');

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
                        $('#full-page-preloader').hide();
                    },
                    error: function () {
                        $('#full-page-preloader').hide();
                        alert('{{ __('product.error_updating_stock') }}'); // Show error alert if update fails
                    }
                });
            });

            // Handle the Yes Flow
            $(document).on('click', '.edit-details-btn', function () {
                $('#full-page-preloader').show();
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
                    $('#full-page-preloader').hide();
                } else {
                    $('#full-page-preloader').hide();
                    alert("Product details not found.");
                }
            });
        });
    </script>
    <script src="{{ asset('backend/assets/js/common-select2.js') }}"></script>
    <script src="{{ asset('backend/assets/js/common-bol.js') }}"></script>
@endsection
