@extends('backend.layouts.master')

@section('title')
    {{ trans('custom.bol_image_import') }}
@endsection

@section('admin-content')
<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">{{ trans('custom.bol_image_import') }}</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><span>{{ trans('custom.bol_image_import') }}</span></li>
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
                    <h4 class="header-title float-left">{{ trans('custom.bol_offer_mappings') }}</h4>
                    <div class="clearfix"></div>
                    <p class="text-muted">
                        Bol doesn't give us the offer ID for a product any other way — this pulls
                        Bol's own offer export and matches it to our products by EAN.
                    </p>
                    <button class="btn btn-secondary" type="button" id="btnSyncMappings">
                        <i class="fa fa-refresh"></i> {{ trans('custom.sync_offer_mappings') }}
                    </button>
                    <span id="syncMappingsResult" class="ml-3"></span>
                </div>
            </div>
        </div>

        <div class="col-12 mt-3">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title float-left">{{ trans('custom.bol_image_import') }}</h4>
                    <div class="clearfix"></div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label font-weight-bold">@lang('product.product_number'):</label>
                            <div class="input-group">
                                <input type="text" id="productNumberInput" class="form-control" placeholder="Product number" autocomplete="off">
                                <div class="input-group-append">
                                    <button class="btn btn-primary px-4" type="button" id="btnLookup">
                                        <i class="fa fa-search"></i> @lang('product.search')
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="lookupResult" style="display:none;">
                        <div class="alert alert-info" id="offerMappingInfo"></div>

                        <label class="form-label font-weight-bold">Photos (first = primary photo):</label>
                        <div id="imageUrlRows"></div>
                        <div class="mt-1">
                            <button class="btn btn-sm btn-outline-secondary" type="button" id="btnAddUrlRow">+ Add photo URL</button>
                            <label class="btn btn-sm btn-outline-secondary mb-0" style="cursor:pointer;">
                                <i class="fa fa-upload"></i> Upload photo
                                <input type="file" id="photoUploadInput" accept="image/jpeg,image/png,image/webp" style="display:none;">
                            </label>
                            <span id="photoUploadStatus" class="text-muted ml-1"></span>
                        </div>
                        <div class="mt-3">
                            <button class="btn btn-success" type="button" id="btnPushImages">
                                <i class="fa fa-upload"></i> {{ trans('custom.push_images') }}
                            </button>
                            <span id="pushImagesResult" class="ml-2"></span>
                        </div>

                        <h5 class="mt-4">Import history</h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Batch</th>
                                    <th>Status</th>
                                    <th>Assets</th>
                                    <th>Submitted</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="batchHistoryBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function () {
        let currentProductNumber = null;
        const checkStatusUrlTemplate = '{{ route("bol-image-import.check-status", ["batchId" => "__BATCH_ID__"]) }}';

        function addUrlRow(value) {
            const $row = $('<div class="input-group mb-1"></div>');
            $row.append('<input type="url" class="form-control image-url-input" placeholder="https://..." value="' + (value ? $('<div>').text(value).html() : '') + '">');
            $row.append('<div class="input-group-append"><button class="btn btn-outline-danger btn-remove-url" type="button"><i class="fa fa-times"></i></button></div>');
            $('#imageUrlRows').append($row);
        }

        $('#btnAddUrlRow').on('click', function () { addUrlRow(''); });

        $('#photoUploadInput').on('change', function () {
            const file = this.files[0];
            if (!file || !currentProductNumber) {
                return;
            }

            const formData = new FormData();
            formData.append('photo', file);
            formData.append('product_number', currentProductNumber);
            formData.append('_token', '{{ csrf_token() }}');

            $('#photoUploadStatus').text('Uploading…');

            $.ajax({
                url: '{{ route("bol-image-import.upload-photo") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    addUrlRow(response.url);
                    $('#photoUploadStatus').text('Uploaded.');
                },
                error: function (xhr) {
                    $('#photoUploadStatus').text('Upload failed: ' + (xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'unknown error'));
                },
                complete: function () {
                    $('#photoUploadInput').val('');
                },
            });
        });

        $('#imageUrlRows').on('click', '.btn-remove-url', function () {
            $(this).closest('.input-group').remove();
        });

        function renderBatches(batches) {
            const $body = $('#batchHistoryBody');
            $body.empty();

            (batches || []).forEach(function (batch) {
                const assetSummary = (batch.asset_results || [])
                    .map(function (a) { return a.status + (a.subStatusDescription ? ' (' + a.subStatusDescription + ')' : ''); })
                    .join(', ') || '-';

                $body.append(
                    '<tr>' +
                    '<td>' + (batch.batch_id || '-') + '</td>' +
                    '<td>' + batch.status + '</td>' +
                    '<td>' + $('<div>').text(assetSummary).html() + '</td>' +
                    '<td>' + (batch.created_at || '-') + '</td>' +
                    '<td><button class="btn btn-sm btn-outline-secondary btn-check-status" data-id="' + batch.id + '">Check status</button></td>' +
                    '</tr>'
                );
            });
        }

        function doLookup(productNumber) {
            $.ajax({
                url: '{{ route("bol-image-import.lookup") }}',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}', product_number: productNumber },
                success: function (response) {
                    currentProductNumber = productNumber;
                    $('#lookupResult').show();

                    const mapping = response.mapping;
                    if (mapping && mapping.offer_id) {
                        $('#offerMappingInfo').removeClass('alert-warning').addClass('alert-info')
                            .text('Bol offer ID: ' + mapping.offer_id + ' (last synced: ' + (mapping.last_synced_at || 'never') + ')');
                    } else {
                        $('#offerMappingInfo').removeClass('alert-info').addClass('alert-warning')
                            .text('No Bol offer mapping found for this product yet — run "Sync offer mappings" above first.');
                    }

                    $('#imageUrlRows').empty();
                    addUrlRow('');
                    renderBatches(response.batches);
                },
                error: function () {
                    alert('Lookup failed.');
                },
            });
        }

        $('#btnLookup').on('click', function () {
            const productNumber = $('#productNumberInput').val().trim();
            if (productNumber) {
                doLookup(productNumber);
            }
        });

        $('#btnSyncMappings').on('click', function () {
            const $btn = $(this);
            $btn.prop('disabled', true);
            $('#syncMappingsResult').text('Syncing… this can take up to 20s.');

            $.ajax({
                url: '{{ route("bol-image-import.sync-mappings") }}',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function (response) {
                    $('#syncMappingsResult').text('Synced ' + response.synced + ' offer mapping(s).');
                },
                error: function (xhr) {
                    $('#syncMappingsResult').text('Sync failed: ' + (xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'unknown error'));
                },
                complete: function () {
                    $btn.prop('disabled', false);
                },
            });
        });

        $('#btnPushImages').on('click', function () {
            if (!currentProductNumber) {
                return;
            }

            const urls = $('.image-url-input').map(function () { return $(this).val().trim(); }).get().filter(Boolean);
            if (urls.length === 0) {
                alert('Add at least one photo URL.');
                return;
            }

            $('#pushImagesResult').text('Submitting…');

            $.ajax({
                url: '{{ route("bol-image-import.push") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    product_number: currentProductNumber,
                    image_urls: urls,
                },
                success: function (response) {
                    $('#pushImagesResult').text('Submitted — batch ' + (response.batch.batch_id || response.batch.id) + '.');
                    doLookup(currentProductNumber);
                },
                error: function (xhr) {
                    $('#pushImagesResult').text('Failed: ' + (xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'unknown error'));
                },
            });
        });

        $('#batchHistoryBody').on('click', '.btn-check-status', function () {
            const id = $(this).data('id');
            const $btn = $(this);
            $btn.prop('disabled', true).text('Checking…');

            $.ajax({
                url: checkStatusUrlTemplate.replace('__BATCH_ID__', id),
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function () {
                    doLookup(currentProductNumber);
                },
                complete: function () {
                    $btn.prop('disabled', false).text('Check status');
                },
            });
        });
    });
</script>
@endsection
