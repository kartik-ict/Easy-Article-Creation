@extends('backend.layouts.master')

@section('title')
    Produkt
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
                    <h4 class="page-title pull-left">Product</h4>
                    <ul class="breadcrumbs pull-left">
                        <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li><span>Product</span></li>
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

            <h2 class="mb-4">Search Product by EAN</h2>
            <div class="card p-4">
                <p class="float-right mb-2">
                    @if (Auth::guard('admin')->user()->can('role.create'))
                        <a class="btn btn-primary text-white" href="{{ route('product.create') }}"> Nieuw product maken </a>
                    @endif
                </p>
                <div class="clearfix"></div>
                <!-- Step 1 -->
                <div id="step1">
                    <div class="form-group">
                        <label for="ean">Enter EAN Number</label>
                        <input type="text" id="ean" class="form-control" placeholder="Enter EAN number">
                    </div>
                    <button id="searchBtn" class="btn btn-primary mt-3">Search</button>
                </div>

                <!-- Loader -->
                <div id="loader" style="display: none;" class="text-center mt-3">
                    <div class="spinner-border text-dark" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <!-- Table View -->
                <div id="result" style="display: none;" class="mt-4">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>EAN Number</th>
                            <th>Action</th>
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
                    alert('Please enter an EAN number!');
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
                                <td>${response.product.ean}</td>
                                <td>
                                    <a href="/product/edit/${response.product.id}" class="btn btn-sm btn-primary">Edit</a>
                                </td>
                            </tr>
                        `);
                        } else {
                            alert('No product found with the given EAN number.');
                        }
                    },
                    error: function () {
                        $('#loader').hide();
                        alert('An error occurred while searching for the product.');
                    }
                });
            });
        });
    </script>
@endsection
