@extends('backend.layouts.master')

@section('title')
    @lang('product_logs.product_logs')
@endsection

@section('styles')
<!-- Start datatable css -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.jqueryui.min.css">
@endsection

@section('admin-content')

<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">@lang('product_logs.product_logs')</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><span>@lang('product_logs.product_logs')</span></li>
                </ul>
            </div>
        </div>
        <div class="col-sm-6 clearfix">
            @include('backend.layouts.partials.logout')
        </div>
    </div>
</div>
<!-- page title area end -->

<div class="main-content-inner">
    <div class="row">
        <!-- data table start -->
        <div class="col-12 mt-5">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title float-left">@lang('product_logs.product_logs')</h4>
                    <div class="clearfix"></div>

                    <!-- Filters -->
                    <div class="mb-4" id="filters-section">
                        <div class="row">
                            <div class="col-md-2">
                                <input type="text" id="filter-product-id" class="form-control" placeholder="@lang('product_logs.product_number')">
                            </div>
                            <div class="col-md-2">
                                <select id="filter-user-id" class="form-control">
                                    <option value="">@lang('product_logs.all_users')</option>
                                    @foreach(\App\Models\Admin::all() as $admin)
                                        <option value="{{ $admin->id }}" {{ auth()->user()->id == $admin->id ? 'selected' : '' }}>{{ $admin->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" id="filter-start-date" class="form-control" placeholder="Start Date">
                            </div>
                            <div class="col-md-2">
                                <input type="date" id="filter-end-date" class="form-control" placeholder="End Date">
                            </div>
                            <div class="col-md-3">
                                <button type="button" id="apply-filters" class="btn btn-primary">@lang('product_logs.filter')</button>
                                <button type="button" id="clear-filters" class="btn btn-secondary">@lang('product_logs.clear')</button>
                            </div>
                        </div>
                    </div>

                    <div class="data-tables">
                        @include('backend.layouts.partials.messages')
                        <table id="dataTable" class="text-center table table-striped table-bordered">
                            <thead class="bg-light text-capitalize">
                                <tr>
                                    <th>@lang('product_logs.product_number')</th>
                                    <th>@lang('product_logs.user')</th>
                                    <th>@lang('product_logs.changes')</th>
                                    <th>@lang('product_logs.date')</th>
                                    <th>@lang('product_logs.action')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- data table end -->
    </div>
</div>
@endsection

@section('scripts')
<!-- Start datatable js -->
<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>
<script src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.18/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.3/js/responsive.bootstrap.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable with server-side processing
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: '{{ route('product-logs.index') }}',
            data: function(d) {
                d.product_id = $('#filter-product-id').val();
                d.user_id = $('#filter-user-id').val();
                d.start_date = $('#filter-start-date').val();
                d.end_date = $('#filter-end-date').val();
            }
        },
        columns: [
            { data: 'product_number', name: 'product_number', orderable: false },
            { data: 'user_name', name: 'user.name', orderable: false },
            { data: 'message', name: 'message', orderable: false },
            { data: 'formatted_date', name: 'created_at', orderable: true },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[3, 'desc']], // Order by date column descending
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.10.19/i18n/Dutch.json"
        },
        pageLength: 10,
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]]
    });

    // Apply filters
    $('#apply-filters').on('click', function() {
        table.ajax.reload();
    });

    // Clear filters
    $('#clear-filters').on('click', function() {
        $('#filter-product-id').val('');
        $('#filter-user-id').val('');
        $('#filter-start-date').val('');
        $('#filter-end-date').val('');
        table.ajax.reload();
    });

    // Apply filters on Enter key
    $('#filters-section input').on('keypress', function(e) {
        if (e.which === 13) {
            table.ajax.reload();
        }
    });

    // Date range validation
    $('#filter-start-date').on('change', function() {
        $('#filter-end-date').attr('min', $(this).val());
    });

    $('#filter-end-date').on('change', function() {
        $('#filter-start-date').attr('max', $(this).val());
    });
});
</script>
@endsection
