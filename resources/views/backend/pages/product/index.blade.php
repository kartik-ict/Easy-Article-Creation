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
                <!-- Step 1 -->
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

                <!-- Table View -->
                <div id="result" style="display: none;" class="mt-4">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>{{ __('product.product_name') }}</th>
                            <th>{{ __('product.ean_number') }}</th>
                            <th>{{ __('product.action') }}</th>
                        </tr>
                        </thead>
                        <tbody id="resultBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('#searchBtn').on('click', function () {
                const ean = $('#ean').val();

                if (!ean) {
                    alert('{{ __('product.enter_ean_alert') }}');
                    return;
                }

                $('#loader').show(); // Show loader
                $('#result').hide(); // Hide table

                $.ajax({
                    url: "{{ route('product.search') }}",
                    method: "POST",
                    data: {ean, _token: "{{ csrf_token() }}"},
                    success: function (response) {
                        $('#loader').hide(); // Hide loader

                        if (response.product) {
                            $('#result').show();
                            $('#resultBody').html(`
                            <tr>
                                <td>${response.product.name}</td>
                                <td>${response.product.productNumber}</td>
                                <td>
                                    <a href="/product/edit/${response.product.id}" class="btn btn-sm btn-primary">{{ __('product.edit') }}</a>
                                </td>
                            </tr>
                        `);
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
        });
    </script>
@endsection