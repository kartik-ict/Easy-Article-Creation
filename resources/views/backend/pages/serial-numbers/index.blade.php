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
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="serialResultsBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- DGM-307 follow-up: currently in-stock summary, grouped by product -->
    <div class="row">
        <div class="col-12 mt-3">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title float-left">Currently in stock</h4>
                    <div class="clearfix"></div>

                    <div id="inStockLoading" class="text-muted" style="display:none;">Loading…</div>
                    <div id="inStockEmpty" class="text-muted" style="display:none;">Nothing currently in stock.</div>

                    <table class="table table-bordered" id="inStockTable" style="display:none;">
                        <thead>
                            <tr>
                                <th>@lang('product.product_number')</th>
                                <th>Product</th>
                                <th>In stock serials</th>
                            </tr>
                        </thead>
                        <tbody id="inStockBody"></tbody>
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
                    stale: 'Stale',
                }[row.status] || row.status || '-';

                const actionCell = (row.status === 'in_stock' && row.id)
                    ? '<button type="button" class="btn btn-sm btn-outline-danger btn-mark-stale" data-serial-id="' + row.id + '">Mark as stale</button>'
                    : '-';

                $body.append(
                    '<tr data-serial-id="' + (row.id || '') + '">' +
                    '<td>' + $('<div>').text(row.serial || '-').html() + '</td>' +
                    '<td>' + $('<div>').text(statusLabel).html() + '</td>' +
                    '<td>' + $('<div>').text(row.productNumber || '-').html() + '</td>' +
                    '<td>' + $('<div>').text(row.productName || '-').html() + '</td>' +
                    '<td>' + $('<div>').text(row.createdAt || '-').html() + '</td>' +
                    '<td>' + actionCell + '</td>' +
                    '</tr>'
                );
            });
            $('#serialResultsTable').show();
        }

        function doSearch() {
            const query = $('#serialSearchQuery').val().trim();

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

        // DGM-307 follow-up — currently in-stock summary, grouped by product.
        function renderInStock(products) {
            const $body = $('#inStockBody');
            $body.empty();

            if (!products || products.length === 0) {
                $('#inStockTable').hide();
                $('#inStockEmpty').show();
                return;
            }

            $('#inStockEmpty').hide();
            products.forEach(function (product) {
                const serialRows = product.serials.map(function (s) {
                    return '<div class="d-flex align-items-center justify-content-between mb-1" data-serial-id="' + s.id + '" style="border-bottom:1px solid #eee;padding:3px 0;">' +
                        '<span>' + $('<div>').text(s.serial || '-').html() + '</span>' +
                        '<button type="button" class="btn btn-sm btn-outline-danger btn-mark-stale-inline ml-2" data-serial-id="' + s.id + '">Mark as stale</button>' +
                        '</div>';
                }).join('');

                $body.append(
                    '<tr data-product-number="' + $('<div>').text(product.productNumber || '').html() + '">' +
                    '<td>' + $('<div>').text(product.productNumber || '-').html() + '</td>' +
                    '<td>' + $('<div>').text(product.productName || '-').html() + '</td>' +
                    '<td>' + serialRows + '</td>' +
                    '</tr>'
                );
            });
            $('#inStockTable').show();
        }

        function loadInStockSummary() {
            $('#inStockLoading').show();
            $('#inStockTable').hide();
            $('#inStockEmpty').hide();

            $.ajax({
                url: '{{ route("serial-numbers.in-stock") }}',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function (response) {
                    $('#inStockLoading').hide();
                    renderInStock(response.products || []);
                },
                error: function () {
                    $('#inStockLoading').hide();
                    $('#inStockEmpty').text('Failed to load — try again.').show();
                },
            });
        }

        // DGM-307 follow-up — mark a serial as stale (non-destructive), then refresh both
        // the search results table and the in-stock summary so they stay in sync.
        function markStale(serialId, $trigger) {
            if (!serialId) {
                return;
            }

            const originalHtml = $trigger.html();
            $trigger.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

            $.ajax({
                url: '{{ route("serial-numbers.mark-stale") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    serialId: serialId,
                },
                success: function () {
                    doSearch();
                    loadInStockSummary();
                },
                error: function () {
                    $trigger.prop('disabled', false).html(originalHtml);
                    alert('Could not mark this serial as stale — try again.');
                },
            });
        }

        $(document).on('click', '.btn-mark-stale', function () {
            markStale($(this).data('serial-id'), $(this));
        });
        $(document).on('click', '.btn-mark-stale-inline', function (e) {
            e.preventDefault();
            markStale($(this).data('serial-id'), $(this));
        });

        // Default list on page load — same render path, just an empty query.
        doSearch();
        loadInStockSummary();
    });
</script>
@endsection
