@extends('backend.layouts.master')

@section('title')
    {{ trans('custom.serial_numbers') }}
@endsection

@section('admin-content')
<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">{{ trans('custom.serial_numbers') }}</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><span>{{ trans('custom.serial_numbers') }}</span></li>
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
        <div class="col-12 mt-5">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title float-left">{{ trans('custom.serial_numbers') }}</h4>
                    <div class="clearfix"></div>

                    <!-- Search Section -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="serialSearchQuery" class="form-label font-weight-bold">Serial / @lang('product.product_number') / EAN:</label>
                            <div class="input-group">
                                <input type="text" id="serialSearchQuery" class="form-control" placeholder="Search by serial, product number or EAN" autocomplete="off">
                                <div class="input-group-append">
                                    <button class="btn btn-primary px-4" type="button" id="btnSerialSearch">
                                        <i class="fa fa-search"></i> @lang('product.search')
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="serialSearchLoading" class="text-muted" style="display:none;">Searching…</div>
                    <div id="serialSearchEmpty" class="text-muted" style="display:none;">No results.</div>

                    <table class="table table-bordered" id="serialResultsTable" style="display:none;">
                        <thead>
                            <tr>
                                <th>Serial</th>
                                <th>Status</th>
                                <th>@lang('product.product_number')</th>
                                <th>Product</th>
                                <th>Recorded</th>
                            </tr>
                        </thead>
                        <tbody id="serialResultsBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(function () {
        function renderResults(results) {
            const $body = $('#serialResultsBody');
            $body.empty();

            if (!results || results.length === 0) {
                $('#serialResultsTable').hide();
                $('#serialSearchEmpty').show();
                return;
            }

            $('#serialSearchEmpty').hide();
            results.forEach(function (row) {
                const statusLabel = {
                    in_stock: 'In stock',
                    picked: 'Picked',
                    shipped: 'Shipped',
                    returned: 'Returned',
                    mismatch: 'Mismatch',
                }[row.status] || row.status || '-';

                $body.append(
                    '<tr>' +
                    '<td>' + $('<div>').text(row.serial || '-').html() + '</td>' +
                    '<td>' + $('<div>').text(statusLabel).html() + '</td>' +
                    '<td>' + $('<div>').text(row.productNumber || '-').html() + '</td>' +
                    '<td>' + $('<div>').text(row.productName || '-').html() + '</td>' +
                    '<td>' + $('<div>').text(row.createdAt || '-').html() + '</td>' +
                    '</tr>'
                );
            });
            $('#serialResultsTable').show();
        }

        function doSearch() {
            const query = $('#serialSearchQuery').val().trim();
            if (!query) {
                return;
            }

            $('#serialSearchLoading').show();
            $('#serialResultsTable').hide();
            $('#serialSearchEmpty').hide();

            $.ajax({
                url: '{{ route("serial-numbers.search") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    query: query,
                },
                success: function (response) {
                    $('#serialSearchLoading').hide();
                    renderResults(response.results || []);
                },
                error: function () {
                    $('#serialSearchLoading').hide();
                    $('#serialSearchEmpty').text('Search failed — try again.').show();
                },
            });
        }

        $('#btnSerialSearch').on('click', doSearch);
        $('#serialSearchQuery').on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                doSearch();
            }
        });
    });
</script>
@endsection
