@extends('backend.layouts.master')

@section('title')
    {{ __('product.title') }}
@endsection

@section('styles')
    <!-- Start datatable css -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" type="text/css"
          href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" type="text/css"
          href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.jqueryui.min.css">
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
            <h2 class="mb-4">{{ __('product.search_product_ean') }}</h2>
            <div class="card p-4">
                <p class="float-right mb-2">
                    @if (Auth::guard('admin')->user()->can('role.create'))
                        <a class="btn btn-primary text-white" href="{{ route('product.create') }}">
                            {{ __('product.create_new_product') }}
                        </a>
                    @endif
                </p>
                <div class="clearfix"></div>

                <!-- Step 1: Search EAN -->
                <div id="step1">
                    <div class="form-group">
                        <label for="ean">{{ __('product.enter_ean') }}</label>
                        <input type="text" id="ean" class="form-control" placeholder="{{ __('product.enter_ean') }}">
                    </div>
                    <button id="searchBtn" class="btn btn-primary mt-3">{{ __('product.search') }}</button>
                </div>

                <!-- Loader -->
                <div id="loader" style="display: none;" class="text-center mt-3">
                    <div class="spinner-border text-dark" role="status">
                        <span class="visually-hidden">{{ __('product.loading') }}</span>
                    </div>
                </div>

                <!-- Step 2: Show Product Data -->
                <div id="step2" style="display: none;" class="mt-4">
                    <h4>{{ __('product.product_details') }}</h4>
                    <div id="productDetails"></div>

                    <!-- Ask about grade -->
                    <div class="form-group mt-3">
                        <label>{{ __('product.is_different_grade') }}</label>
                        <select id="differentGrade" class="form-control">
                            <option value="" selected disabled>{{ __('product.select_grade_alert') }}</option>
                            <option value="no">{{ __('product.no') }}</option>
                            <option value="yes">{{ __('product.yes') }}</option>
                        </select>
                    </div>

                    <div id="stockUpdateSection" style="display: none;" class="mt-3">
                        <div class="form-group">
                            <label for="stock">{{ __('product.update_stock') }}</label>
                            <input type="number" id="stock" class="form-control" placeholder="{{ __('product.stock_quantity') }}">
                        </div>
                        <button id="updateBtn" class="btn btn-success mt-3">{{ __('product.update') }}</button>
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

            // Search product by EAN
            $('#searchBtn').on('click', function () {
                const ean = $('#ean').val();

                if (!ean) {
                    alert('{{ __('product.enter_ean_alert') }}');
                    return;
                }

                $('#loader').show(); // Show loader
                $('#step2').hide(); // Hide step 2

                $.ajax({
                    url: "{{ route('product.search') }}",
                    method: "POST",
                    data: { ean, _token: "{{ csrf_token() }}" },
                    success: function (response) {
                        $('#loader').hide(); // Hide loader

                        if (response.product) {
                            $('#step2').show();
                            $('#productDetails').html(`
                                <p><strong>{{ __('product.product_name') }}:</strong> ${response.product.name}</p>
                                <p><strong>{{ __('product.ean_number') }}:</strong> ${response.product.productNumber}</p>
                            `);
                            $('#stockUpdateSection').hide();
                        } else {
                            alert('{{ __('product.no_product_found') }}');
                        }
                    },
                    error: function () {
                        $('#loader').hide();
                        alert('{{ __('product.error_occurred') }}');
                    }
                });
            });

            // Handle grade selection
            $('#differentGrade').on('change', function () {
                const grade = $(this).val();

                if (!grade) {
        alert('{{ __('product.select_grade_alert') }}');
        return;
    }

                if (grade === 'no') {
                    $('#stockUpdateSection').show();
                } else {
                    $('#stockUpdateSection').hide();
                }
            });

            // Update stock via API
            $('#updateBtn').on('click', function () {
                const stock = $('#stock').val();
                const productId = "REPLACE_WITH_PRODUCT_ID"; // You should retrieve this dynamically from the response.product.id

                if (!stock) {
                    alert('{{ __('product.enter_stock_alert') }}');
                    return;
                }

                $.ajax({
                    url: `${apiUrl}/${productId}`,
                    method: "POST",
                    data: { stock, _token: "{{ csrf_token() }}" },
                    success: function (response) {
                        alert('{{ __('product.stock_updated_success') }}');
                    },
                    error: function () {
                        alert('{{ __('product.error_updating_stock') }}');
                    }
                });
            });
        });
    </script>
@endsection
