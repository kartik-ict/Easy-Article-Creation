@extends('backend.layouts.master')

@section('title')
    Product maken
@endsection

@section('admin-content')

    <!-- page title area start -->
    <div class="page-title-area">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <div class="breadcrumbs-area clearfix">
                    <h4 class="page-title pull-left">Product maken</h4>
                    <ul class="breadcrumbs pull-left">
                        <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li><a href="{{ route('admin.roles.index') }}">All Roles</a></li>
                        <li><span>Create Role</span></li>
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
                        <h1>Create Product</h1>
                        <form id="createProductForm" method="POST" action="{{ route('product.store') }}">
                            @csrf
                            <div class="row">
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="name">Name:</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="stock">Stock:</label>
                                        <input type="number" class="form-control" id="stock" name="stock" required>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="manufacturer">Manufacturer:</label>
                                        <select id="manufacturer" name="manufacturer" class="form-control" required></select>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="taxId">Tax ID:</label>
                                        <input type="number" class="form-control" id="taxId" name="taxId" required>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="productNumber">Product Number:</label>
                                        <input type="text" class="form-control" id="productNumber" name="productNumber"
                                               required>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="description">Description:</label>
                                        <textarea class="form-control" id="description" name="description"
                                                  rows="5"></textarea>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="salesChannel">Sales Channel:</label>
                                        <input type="text" class="form-control" id="salesChannel" name="salesChannel"
                                               required>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="category">Category:</label>
                                        <input type="text" class="form-control" id="category" name="category" required>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="mediaUrl">Media URL:</label>
                                        <input type="url" class="form-control" id="mediaUrl" name="mediaUrl">
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary w-100">Submit</button>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#manufacturer').select2({
                placeholder: 'Search Manufacturer',
                ajax: {
                    url: '{{ route("api.manufacturer.search") }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term // search term
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(item => ({
                                id: item.id,
                                text: item.name
                            }))
                        };
                    },
                    cache: true
                }
            });
        });
    </script>
@endsection