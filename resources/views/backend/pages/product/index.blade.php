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

                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            const apiUrl = "{{ url('/api/product') }}";
            let productDetails = {};
            let selectedGrade = "";

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
                        if (response.product) {
                            productDetails = response.product;
                            $('#productDetails').html(`
                        <p><strong>{{ __('product.product_name') }}:</strong> ${response.product.name}</p>
                        <p><strong>{{ __('product.ean_number') }}:</strong> ${response.product.productNumber}</p>
                        <p><strong>{{ __('product.stock') }}:</strong> ${response.product.stock || '0'}</p>
                    `);
                            $('#gradeSection').show();  // Show grade selection section
                            $('#backBtn').show();  // Show back button
                            $('#nextBtn').show();  // Show next button
                        } else {
                            alert('{{ __('product.no_product_found') }}');
                            $('#productDetails').html('{{ __('product.no_product_found') }}');
                            $('#nextBtn').hide();  // Hide next button if no product is found
                        }
                    },
                    error: function () {
                        alert('{{ __('product.error_occurred') }}');
                        $('#productDetails').html('{{ __('product.error_occurred') }}');
                        $('#nextBtn').hide();  // Hide next button on error
                    }
                });
            });

            // Handle grade selection
            $('#differentGrade').on('change', function () {
                selectedGrade = $(this).val();
                if (selectedGrade === "no") {
                    $('#newStockSection').show();  // Show new stock input
                    $('#updateDataBtn').show();  // Show update button
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
        });
    </script>
@endsection
