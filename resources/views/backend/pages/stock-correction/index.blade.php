@extends('backend.layouts.master')

@section('title')
    {{ trans('custom.stock_correction') }}
@endsection

@section('styles')
<style>
    .btn-flex-center {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        gap: 8px !important;
    }
</style>
@endsection

@section('admin-content')
<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">{{ trans('custom.stock_correction') }}</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><span>{{ trans('custom.stock_correction') }}</span></li>
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
                    <h4 class="header-title float-left">{{ trans('custom.stock_correction') }}</h4>
                    <div class="clearfix"></div>

                    <!-- Search Section -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="searchQuery" class="form-label font-weight-bold">@lang('product.product_number') / EAN:</label>
                            <div class="input-group">
                                <input type="text" id="searchQuery" class="form-control" placeholder="@lang('product.search_product_placeholder')" autocomplete="off">
                                <div class="input-group-append">
                                    <button class="btn btn-primary px-4 btn-flex-center" type="button" id="btnSearch">
                                        <i class="fa fa-search"></i><span>@lang('product.search')</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alert message area -->
                    <div id="alertMessage" style="display: none;" class="mt-3"></div>

                    <!-- Results Section (Initially hidden) -->
                    <div id="resultsSection" style="display: none;" class="mt-4">
                        <hr>
                        <div class="mb-3">
                            <h5 id="productTitleHeader" class="text-primary font-weight-bold"></h5>
                            <p id="productEanSubheader" class="text-secondary mb-0"></p>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover" id="variantTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th>@lang('product.product_details')</th>
                                        <th>@lang('product.product_number')</th>
                                        <th class="text-center" style="width: 130px;">@lang('product.current_stock')</th>
                                        <th class="text-center" style="width: 140px;">@lang('product.decrease_by')</th>
                                        <th class="text-center" style="width: 140px;">Actie</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Reduction Confirmation Modal -->
<div class="modal fade" id="decreaseStockModal" tabindex="-1" role="dialog" aria-labelledby="decreaseStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 10px; border: 0; box-shadow: 0 5px 25px rgba(0,0,0,0.15);">
            <div class="modal-header bg-primary text-white d-flex align-items-center justify-content-between px-4 py-3" style="border-top-left-radius: 10px; border-top-right-radius: 10px;">
                <h5 class="modal-title font-weight-bold text-white d-flex align-items-center mb-0" id="decreaseStockModalLabel" style="font-size: 18px;">
                    <i class="fa fa-minus-circle" style="margin-right: 12px !important; font-size: 20px;"></i>
                    <span>{{ trans('custom.stock_correction') }}</span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close" style="opacity: 0.9; font-size: 28px; font-weight: 300; background: transparent; border: 0; padding: 0; margin: 0; line-height: 1; outline: none; cursor: pointer;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <!-- Product summary badge -->
                <div class="card p-3 mb-3 bg-light border-0" style="border-radius: 8px;">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="font-weight-bold text-dark" id="modalProductName" style="font-size: 15px;"></span>
                        <span class="badge badge-danger px-2 py-1" style="font-size: 13px;" id="modalDecreaseBadge"></span>
                    </div>
                    <div class="text-muted small">
                        <span>SKU: <strong id="modalProductNum"></strong></span> |
                        <span>Huidige voorraad: <strong id="modalCurrentStock"></strong></span>
                    </div>
                </div>

                <div id="modalAlertMessage" style="display: none;" class="mb-3"></div>

                <form id="decreaseModalForm">
                    <input type="hidden" id="modalProductId">
                    <input type="hidden" id="modalDecreaseQty">

                    <div class="form-group mb-3">
                        <label for="modalSourceWarehouse" class="font-weight-bold">@lang('product.source_warehouse'):</label>
                        <select id="modalSourceWarehouse" class="form-control" style="height: 42px; border-radius: 6px;">
                            @if(isset($warehouseList) && count($warehouseList) > 0)
                                @foreach($warehouseList as $wh)
                                    <option value="{{ $wh['id'] }}">{{ $wh['attributes']['name'] ?? $wh['attributes']['code'] ?? 'Hauptlager' }}</option>
                                @endforeach
                            @else
                                <option value="">@lang('product.select_warehouse')</option>
                            @endif
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label for="modalSourceBinLocation" class="font-weight-bold">@lang('product.source_bin_location'):</label>
                        <select id="modalSourceBinLocation" class="form-control" style="height: 42px; border-radius: 6px;">
                            <option value="">@lang('product.select_bin_location')</option>
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <label for="modalCorrectionComment" class="font-weight-bold">@lang('product.comment'):</label>
                        <input type="text" id="modalCorrectionComment" class="form-control" placeholder="@lang('product.comment_placeholder')" maxlength="50" style="height: 42px; border-radius: 6px;" autocomplete="off">
                        <small class="form-text text-muted mt-1" style="font-size: 12px;">Max. 50 tekens (wordt getoond in Product Logs).</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light" style="border-bottom-left-radius: 10px; border-bottom-right-radius: 10px;">
                <button type="button" class="btn btn-secondary px-4" data-dismiss="modal" onclick="$('#decreaseStockModal').modal('hide')">@lang('product.cancel')</button>
                <button type="button" class="btn btn-primary px-4 btn-flex-center" id="btnConfirmDecrease">
                    <i class="fa fa-check"></i><span>@lang('product.save')</span>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
var allBinLocations = @json($binLocationList ?? []);

$(document).ready(function() {
    $(document).on('keydown', '.decrease-input', function(e) {
        if (e.key === '-' || e.key === 'e' || e.key === 'E') {
            e.preventDefault();
        }
    });

    // DGM-312 follow-up — new function, not an edit to updateModalBinLocations below,
    // per Kartik's instruction. Populates the bin dropdown with only the locations where
    // this specific product actually has physical stock (Rory's feedback: staff need to
    // correct stock at a location even if they aren't personally assigned to it, as long
    // as the product is really there).
    function populateBinLocationsWithStock(productId) {
        var binSelect = $('#modalSourceBinLocation');
        binSelect.empty().append('<option value="">@lang("product.select_bin_location")</option>');

        $.ajax({
            url: "{{ route('stock.correction.bin-locations-with-stock') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                product_id: productId
            },
            success: function(res) {
                var bins = res.binLocations || [];

                if (bins.length === 0) {
                    binSelect.append('<option value="">Geen stellinglocaties met voorraad</option>');
                    return;
                }

                bins.sort(function(a, b) {
                    if (a.code === 'Main Bin Location') return -1;
                    if (b.code === 'Main Bin Location') return 1;
                    return a.code.localeCompare(b.code);
                });

                bins.forEach(function(bin) {
                    binSelect.append('<option value="' + bin.id + '" data-warehouse-id="' + bin.warehouseId + '">' +
                        $('<div>').text(bin.code + ' (' + bin.physicalStock + ' op voorraad)').html() + '</option>');
                });
            }
        });
    }

    function updateModalBinLocations() {
        var selectedWhId = $('#modalSourceWarehouse').val();
        var binSelect = $('#modalSourceBinLocation');
        binSelect.empty();

        if (!selectedWhId) {
            binSelect.append('<option value="">@lang("product.select_bin_location")</option>');
            return;
        }

        var filteredBins = allBinLocations.filter(function(bin) {
            var whId = bin.attributes ? bin.attributes.warehouseId : null;
            if (!whId && bin.relationships && bin.relationships.warehouse && bin.relationships.warehouse.data) {
                whId = bin.relationships.warehouse.data.id;
            }
            return whId === selectedWhId;
        });

        if (filteredBins.length === 0) {
            binSelect.append('<option value="">Geen stellinglocaties beschikbaar</option>');
        } else {
            // Sort: "Main Bin Location" first, then alphabetically
            filteredBins.sort(function(a, b) {
                var codeA = (a.attributes && a.attributes.code) ? a.attributes.code : '';
                var codeB = (b.attributes && b.attributes.code) ? b.attributes.code : '';
                if (codeA === 'Main Bin Location') return -1;
                if (codeB === 'Main Bin Location') return 1;
                return codeA.localeCompare(codeB);
            });

            filteredBins.forEach(function(bin) {
                var binCode = (bin.attributes && bin.attributes.code) ? bin.attributes.code : bin.id;
                binSelect.append('<option value="' + bin.id + '">' + $('<div>').text(binCode).html() + '</option>');
            });
        }
    }

    $('#modalSourceWarehouse').on('change', function() {
        updateModalBinLocations();
    });

    // Handle Enter key on search input
    $('#searchQuery').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            performSearch();
        }
    });

    $('#btnSearch').on('click', function() {
        performSearch();
    });

    function showAlert(type, message) {
        var iconMap = {
            'danger': 'fa-exclamation-circle',
            'warning': 'fa-exclamation-triangle',
            'success': 'fa-check-circle',
            'info': 'fa-info-circle'
        };
        var icon = iconMap[type] || 'fa-info-circle';

        var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show my-3 d-flex align-items-center justify-content-between shadow-sm" role="alert" style="border-radius: 8px; font-weight: 500; font-size: 14px; padding: 12px 18px;">' +
            '<div class="d-flex align-items-center"><i class="fa ' + icon + '" style="margin-right: 10px !important; font-size: 16px;"></i><span>' + message + '</span></div>' +
            '<button type="button" class="close" data-dismiss="alert" aria-label="Close" style="background: transparent !important; border: 0 !important; outline: none !important; font-size: 22px; line-height: 1; opacity: 0.6; cursor: pointer; padding: 0 0 0 15px; margin: 0; text-shadow: none; float: none;">' +
                '<span aria-hidden="true">&times;</span>' +
            '</button>' +
        '</div>';
        $('#alertMessage').html(alertHtml).show();
    }

    function showModalAlert(type, message) {
        var alertHtml = '<div class="alert alert-' + type + ' p-2 mb-2 style="font-size: 13px; border-radius: 6px;">' + message + '</div>';
        $('#modalAlertMessage').html(alertHtml).show();
    }

    function hideAlert() {
        $('#alertMessage').hide().html('');
        $('#modalAlertMessage').hide().html('');
    }

    function performSearch() {
        var query = $.trim($('#searchQuery').val());
        if (!query) {
            showAlert('warning', 'Voer a.u.b. een productnummer of EAN in.');
            return;
        }

        hideAlert();
        $('#resultsSection').hide();
        $('#variantTable tbody').empty();
        $('#full-page-preloader').show();

        $.ajax({
            url: "{{ route('product.search') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                ean: query
            },
            success: function(response) {
                $('#full-page-preloader').hide();

                if (!response || !response.product || !response.product.productData || response.product.productData.length === 0) {
                    showAlert('danger', '@lang("product.no_variants_found")');
                    return;
                }

                var pData = response.product;
                var mainName = pData.name || 'Product';
                var mainEan = pData.ean || '-';

                $('#productTitleHeader').text(mainName);
                $('#productEanSubheader').text('EAN: ' + mainEan);

                var products = pData.productData;
                var included = pData.included || [];

                products.forEach(function(prod) {
                    var prodId = prod.id;
                    var attrs = prod.attributes || {};
                    var prodNum = attrs.productNumber || '-';
                    var currentStock = parseInt(attrs.stock || 0);

                    var variantLabel = attrs.translated?.name || attrs.name || mainName;
                    var optionNames = [];

                    var optIds = attrs.optionIds || [];
                    if ((!optIds || optIds.length === 0) && prod.relationships && prod.relationships.options && prod.relationships.options.data) {
                        optIds = prod.relationships.options.data.map(function(o) { return o.id; });
                    }

                    if (optIds && optIds.length > 0) {
                        optIds.forEach(function(optId) {
                            var matchOpt = included.find(function(item) {
                                return item.id === optId && (item.type === "property_group_option" || item.type === "product_option");
                            });
                            if (matchOpt && matchOpt.attributes && matchOpt.attributes.name) {
                                optionNames.push(matchOpt.attributes.name);
                            }
                        });
                    }

                    var displayName = variantLabel;
                    if (optionNames.length > 0) {
                        displayName += ' (' + optionNames.join(', ') + ')';
                    } else if (attrs.parentId == null) {
                        displayName += ' (Hoofdproduct)';
                    }

                    var stockDisplayHtml = '<input type="number" class="form-control text-center font-weight-bold current-stock-input" value="' + currentStock + '" disabled style="background-color: #e9ecef; color: #495057; font-size: 15px; width: 90px; margin: 0 auto;">';

                    var rowHtml = '<tr data-product-id="' + prodId + '" data-product-name="' + $('<div>').text(displayName).html() + '" data-product-num="' + $('<div>').text(prodNum).html() + '">' +
                        '<td class="align-middle font-weight-bold text-dark">' + $('<div>').text(displayName).html() + '</td>' +
                        '<td class="align-middle text-dark font-weight-bold">' + $('<div>').text(prodNum).html() + '</td>' +
                        '<td class="align-middle text-center">' + stockDisplayHtml + '</td>' +
                        '<td class="align-middle"><input type="number" class="form-control decrease-input" min="1" max="' + currentStock + '" placeholder="0"></td>' +
                        '<td class="align-middle text-center"><button type="button" class="btn btn-primary px-3 btn-decrease btn-flex-center" style="background-color: #007bff; border-color: #007bff; color: #ffffff;"><i class="fa fa-minus-circle"></i><span>@lang("product.save")</span></button></td>' +
                    '</tr>';

                    $('#variantTable tbody').append(rowHtml);
                });

                $('#resultsSection').show();
            },
            error: function(xhr) {
                $('#full-page-preloader').hide();
                var msg = '@lang("product.no_variants_found")';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    msg = xhr.responseJSON.error;
                }
                showAlert('danger', msg);
            }
        });
    }

    // Open Confirmation Modal on clicking Save/Decrease row button
    $(document).on('click', '.btn-decrease', function() {
        var row = $(this).closest('tr');
        var productId = row.data('product-id');
        var productName = row.data('product-name');
        var productNum = row.data('product-num');
        var decreaseInput = row.find('.decrease-input');
        var decreaseQty = parseInt(decreaseInput.val());

        if (isNaN(decreaseQty) || decreaseQty <= 0) {
            showAlert('warning', 'Voer a.u.b. een geldig aantal (minimaal 1) in om te verlagen.');
            return;
        }

        var currentStockVal = parseInt(row.find('.current-stock-input').val() || 0);

        if (currentStockVal <= 0) {
            showAlert('danger', 'Huidige voorraad is 0. Kan niet verder verlagen.');
            return;
        }

        if (decreaseQty > currentStockVal) {
            showAlert('danger', 'Niet genoeg voorraad om te verlagen (Huidige voorraad: ' + currentStockVal + ').');
            return;
        }

        // Fill modal fields
        $('#modalProductId').val(productId);
        $('#modalDecreaseQty').val(decreaseQty);
        $('#modalProductName').text(productName);
        $('#modalProductNum').text(productNum);
        $('#modalCurrentStock').text(currentStockVal);
        $('#modalDecreaseBadge').text('-' + decreaseQty + ' stuks');
        $('#modalCorrectionComment').val('');
        $('#modalAlertMessage').hide();

        populateBinLocationsWithStock(productId);

        $('#decreaseStockModal').modal('show');
    });

    // Execute Stock Reduction inside Modal
    $('#btnConfirmDecrease').on('click', function() {
        var productId = $('#modalProductId').val();
        var decreaseQty = parseInt($('#modalDecreaseQty').val());
        var warehouseId = $('#modalSourceWarehouse').val();
        var binLocationId = $('#modalSourceBinLocation').val();
        var comment = $.trim($('#modalCorrectionComment').val());

        if (!warehouseId) {
            showModalAlert('warning', '@lang("product.please_select_warehouse")');
            return;
        }

        if (!binLocationId) {
            showModalAlert('warning', '@lang("product.please_select_bin_location")');
            return;
        }

        var btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-2"></i>Verwerken...');
        $('#modalAlertMessage').hide();

        $.ajax({
            url: "{{ route('stock.correction.decrease-at-bin-location') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                product_id: productId,
                decrease_by: decreaseQty,
                warehouse_id: warehouseId,
                bin_location_id: binLocationId,
                comment: comment
            },
            success: function(res) {
                btn.prop('disabled', false).html('<i class="fa fa-check mr-2"></i><span>@lang("product.save")</span>');

                if (res.success) {
                    $('#decreaseStockModal').modal('hide');

                    var targetRow = $('#variantTable tr[data-product-id="' + productId + '"]');
                    if (targetRow.length > 0) {
                        targetRow.find('.current-stock-input').val(res.new_stock);
                        targetRow.find('.decrease-input').val('');
                    }

                    showAlert('success', '@lang("product.stock_decreased") Nieuwe voorraad: ' + res.new_stock);
                } else {
                    showModalAlert('danger', res.error || '@lang("product.stock_decrease_failed")');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fa fa-check mr-2"></i><span>@lang("product.save")</span>');
                var errMsg = '@lang("product.stock_decrease_failed")';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    errMsg = xhr.responseJSON.error;
                }
                showModalAlert('danger', errMsg);
            }
        });
    });

});
</script>
@endsection
