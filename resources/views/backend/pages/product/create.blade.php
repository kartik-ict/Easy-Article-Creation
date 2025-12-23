@extends('backend.layouts.master')

@section('title')
    @lang('product.create_product')
@endsection

@section('admin-content')
    <!-- page title area start -->
    <div class="page-title-area">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <div class="breadcrumbs-area clearfix">
                    <h4 class="page-title pull-left">@lang('product.create_product')</h4>
                    <ul class="breadcrumbs pull-left">
                        <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li><span>@lang('product.create_product')</span></li>
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
                        <h4>@lang('product.create_product')</h4>
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif
                        <form action="{{ route('product.saveData') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row mb-2">
                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <label for="name">@lang('product.name') <span class="text-danger">*</span>:</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <label for="stock">@lang('product.stock') <span class="text-danger">*</span>:</label>
                                    <input type="hidden" class="form-control" id="stock" name="stock"
                                        value="0" />
                                    <label class="form-control text-muted bg-light" disabled id="stock"
                                        name="stock">0</label>
                                    @error('stock')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <label for="category">@lang('product.category') <span class="text-danger">*</span>:</label>
                                    <select id="category-select"
                                        class="js-example-basic-single form-control @error('category.*') is-invalid @enderror"
                                        name="category[]" multiple required>
                                    </select>
                                    @error('category.*')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <label for="manufacturer">@lang('product.manufacturer') <span class="text-danger">*</span>:</label>
                                    <select id="manufacturer-select"
                                        class="js-example-basic-single form-control @error('manufacturer') is-invalid @enderror"
                                        name="manufacturer" required>
                                    </select>
                                    @error('manufacturer')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <label for="taxId">@lang('product.tax_id') <span class="text-danger">*</span>:</label>
                                    <select id="tax-provider-select"
                                        class="js-example-basic-single form-control @error('taxId') is-invalid @enderror"
                                        name="taxId" required>
                                    </select>
                                    @error('taxId')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <input type="hidden" name="taxRate" id="taxRate" value="21" />
                                </div>
                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <label for="productNumber">@lang('product.product_number') <span
                                            class="text-danger">*</span>:</label>
                                    <input type="text" class="form-control @error('productNumber') is-invalid @enderror"
                                        id="productNumber" name="productNumber" value="{{ old('productNumber') }}" required>
                                    @error('productNumber')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <label for="price_gross">
                                        {{ __('product.price_gross') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" name="priceGross" id="priceGross"
                                        class="form-control @error('priceGross') is-invalid @enderror" step="any"
                                        required placeholder="{{ __('product.enter_price_gross') }}">
                                    @error('priceGross')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    <input type="hidden" name="priceNet" id="priceNet"
                                        class="form-control @error('priceNet') is-invalid @enderror" step="any" required
                                        placeholder="{{ __('product.calculated_price_net') }}" />
                                </div>
                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <label for="purchase_price">
                                        {{ __('product.purchase_price') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" name="purchasePriceNet" id="purchasePriceNet"
                                        class="form-control @error('purchasePriceNet') is-invalid @enderror" step="any"
                                        required placeholder="{{ __('product.enter_purchase_price') }}">
                                    @error('purchasePriceNet')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                    <input type="hidden" name="purchasePrice" id="purchasePrice"
                                        class="form-control" step="any" required/>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <label for="ean">@lang('product.ean'):</label>
                                    <input type="text" id="ean" class="form-control" name="ean"
                                        placeholder="@lang('product.enter_ean')">
                                </div>
                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <label for="salesChannel">@lang('product.sales_channel') <span class="text-danger">*</span>
                                        :</label>
                                    <select id="sales-channel-select" class="js-example-basic-single form-control"
                                        name="salesChannel[]" multiple required>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-2">

                                <div class="form-group col-md-12 col-sm-12 px-2">
                                    <label for="mediaUrl">@lang('product.media_url'):</label>
                                    <input type="file" class="form-control" id="media" name="media">
                                    <span id="uploadStatus" class="text-info"></span>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6 form-group mb-3">
                                    <label for="bolProductShortDescription">@lang('product.shortDescription'):</label>
                                    <textarea class="form-control" id="shortDescription" name="bolProductShortDescription" rows="1"></textarea>
                                </div>

                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <input type="hidden" name="active_for_all" value="0">
                                    <label for="active_for_all">{{ __('product.active_for_all_label') }}</label>
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" name="active_for_all"
                                            id="active_for_all" value="1"
                                            {{ old('active_for_all') ? 'checked' : '' }} checked>
                                    </div>
                                </div>
                                <div class="form-group col-md-12 col-sm-12 px-2">
                                    <label for="description">@lang('product.description'):</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                                        rows="5">{{ old('description') }}</textarea>
                                    @error('description')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror

                                    <!-- Hidden input to store mediaId dynamically -->
                                    <input type="hidden" name="media_id" id="media_id">
                                </div>
                            </div>
                            {{-- Product Marketplace Section --}}
                            {{-- <div class="row">
                                <div class="col-md-12">
                                    <h5 class="my-3">{{ __('product.product_marketplace_information') }}</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <input type="hidden" name="bolNlActive" value="0">
                                    <label for="bolNlActive"
                                        class="bolNlActive">{{ $customFields['migration_DMG_product_bol_nl_active']['label'] }}</label>
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" name="bolNlActive"
                                            id="bolNlActive" value="1" {{ old('bolNlActive') ? 'checked' : '' }}>
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <input type="hidden" name="bolBeActive" value="0">
                                    <label for="bolBeActive"
                                        class="bolBeActive">{{ $customFields['migration_DMG_product_bol_be_active']['label'] }}</label>
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" name="bolBeActive"
                                            id="bolBeActive" value="1" {{ old('bolBeActive') ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label for="bolNlPrice"
                                        class="bolNlPrice">{{ $customFields['migration_DMG_product_bol_price_nl']['label'] }}</label>
                                    <input type="text" name="bolNlPrice" id="bolNlPrice" class="form-control">
                                </div>

                                <div class="col-md-6 form-group mb-3">
                                    <label for="bolBePrice"
                                        class="bolBePrice">{{ $customFields['migration_DMG_product_bol_price_be']['label'] }}</label>
                                    <input type="text" name="bolBePrice" id="bolBePrice" class="form-control">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label for="bolNLDeliveryTime"
                                        class="bolNLDeliveryTime">{{ $customFields['migration_DMG_product_bol_nl_delivery_code']['label'] }}:</label>
                                    <select id="bolNLDeliveryTime"
                                        class="js-example-basic-single form-control bolNLDeliveryTimeSelect"
                                        name="bolNLDeliveryTime">
                                        @foreach ($customFields['migration_DMG_product_bol_nl_delivery_code']['options'] as $option)
                                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label for="bolBEDeliveryTime"
                                        class="bolBEDeliveryTime">{{ $customFields['migration_DMG_product_bol_be_delivery_code']['label'] }}:</label>
                                    <select id="bolBEDeliveryTime"
                                        class="js-example-basic-single form-control bolBEDeliveryTimeSelect"
                                        name="bolBEDeliveryTime">
                                        @foreach ($customFields['migration_DMG_product_bol_be_delivery_code']['options'] as $option)
                                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label for="bolCondition"
                                        class="bolCondition">{{ $customFields['migration_DMG_product_bol_condition']['label'] }}:</label>
                                    <select id="bolCondition"
                                        class="js-example-basic-single form-control bolConditionSelect"
                                        name="bolCondition">
                                        @foreach ($customFields['migration_DMG_product_bol_condition']['options'] as $option)
                                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label for="bolConditionDescription"
                                        class="bolConditionDescription">{{ $customFields['migration_DMG_product_bol_condition_desc']['label'] }}:</label>
                                    <input type="text" class="form-control" id="bolConditionDescription"
                                        name="bolConditionDescription">
                                </div>
                            </div> --}}
                            {{-- Shipping information DGM site --}}
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="my-3">{{ __('product.product_shipping_header') }}</h5>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <div class="form-check">
                                        <input type="hidden" name="bolOrderBeforeTomorrow" value="0">
                                        <input type="checkbox" class="form-check-input bolOrderBeforeTomorrow"
                                            id="bolOrderBeforeTomorrow" value="1" name="bolOrderBeforeTomorrow">
                                        <label class="form-check-label bolOrderBeforeTomorrow"
                                            for="bolOrderBeforeTomorrow">{{ $customFields['migration_DMG_product_proposition_1']['label'] }}</label>
                                    </div>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <div class="form-check">
                                        <input type="hidden" name="bolLetterboxPackage" value="0">
                                        <input type="checkbox" class="form-check-input bolLetterboxPackage"
                                            id="bolLetterboxPackage" value="1" name="bolLetterboxPackage">
                                        <label class="form-check-label bolLetterboxPackage"
                                            for="bolLetterboxPackage">{{ $customFields['migration_DMG_product_proposition_3']['label'] }}</label>
                                    </div>
                                </div>
                            </div>

                            {{-- <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <div class="form-check">
                                        <input type="hidden" name="bolOrderBefore" value="0">
                                        <input type="checkbox" class="form-check-input bolOrderBefore"
                                            id="bolOrderBefore" value="1" name="bolOrderBefore">
                                        <label class="form-check-label bolOrderBefore"
                                            for="bolOrderBefore">{{ $customFields['migration_DMG_product_proposition_2']['label'] }}</label>
                                    </div>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <div class="form-check">
                                        <input type="hidden" name="bolLetterboxPackageUp" value="0">
                                        <input type="checkbox" class="form-check-input bolLetterboxPackageUp"
                                            id="bolLetterboxPackageUp" value="1" name="bolLetterboxPackageUp">
                                        <label class="form-check-label bolLetterboxPackageUp"
                                            for="bolLetterboxPackageUp">{{ $customFields['migration_DMG_product_proposition_4']['label'] }}</label>
                                    </div>
                                </div>
                            </div> --}}
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <input type="hidden" name="bolPickUpOnly" value="0">
                                    <label for="bolPickUpOnly"
                                        class="bolPickUpOnly">{{ $customFields['migration_DMG_product_proposition_5']['label'] }}</label>
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" name="bolPickUpOnly"
                                            id="bolPickUpOnly" value="1"
                                            {{ old('bolPickUpOnly') ? 'checked' : '' }}>
                                    </div>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="binLocation">@lang('product.bin_location')</label>
                                    <select name="bin_location_id" id="binLocation" class="form-control bin-location-select">
                                        <option value=""></option>
                                        @foreach ($binLocationList as $location)
                                            <option value="{{ $location['id'] }}"> {{ $location['attributes']['code'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <input type="hidden" name="warehouse" value="{{ $admin->warehouse_id }}" />
                            </div>
                            <!-- Submit Button -->
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary">@lang('product.submit')</button>
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
    <!-- Route data containers for JS -->
    <div id="route-container" 
         data-manufacturer-search="{{ route('product.manufacturerSearch') }}"
         data-category-search="{{ route('product.categorySearch') }}"
         data-tax-search="{{ route('product.fetchTax') }}"
         style="display: none;"></div>
    
    <div id="route-container-sales" 
         data-sales-search="{{ route('product.salesChannelSearch') }}"
         style="display: none;"></div>
         
    <div id="route-container-category" 
         data-category-search="{{ route('product.categorySearch') }}"
         style="display: none;"></div>
         
    <div id="route-container-tax" 
         data-tax-search="{{ route('product.fetchTax') }}"
         style="display: none;"></div>
    
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('backend/assets/js/common-select2.js') }}"></script>
    <script src="{{ asset('backend/assets/js/common-bol.js') }}"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
    <script>
        // names you want preselected
        const preselectSalesChannelNames = ['DGMoutlet.nl', 'Bol NL', 'Bol BE'];

        $(document).ready(function() {
            $('#description').summernote({
                height: 200,
                toolbar: [
                    ['style', ['style']],
                    ['font', [
                        'fontname',
                        'fontsize',
                        'forecolor',
                        'backcolor',
                        'bold',
                        'italic',
                        'underline',
                        'clear'
                    ]],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['picture']],
                    ['view', ['undo', 'redo', 'codeview']]
                ],
                fontSizes: ['10', '12', '14', '16', '18', '20', '24', '28', '32']
            });

            // Initialize select2 for all delivery and condition dropdowns
            const selectFields = ['#bolNLDeliveryTime', '#bolBEDeliveryTime', '#bolCondition'];

            selectFields.forEach(field => {
                $(field).select2({
                    placeholder: $(field).prev('label').text(),
                    minimumInputLength: 0,
                    allowClear: true,
                    multiple: false,
                    language: {
                        searching: function() {
                            return "Zoeken, even geduld...";
                        },
                        loadingMore: function() {
                            return "Meer resultaten laden...";
                        },
                        noResults: function() {
                            return "Geen resultaten gevonden.";
                        }
                    }
                });

                $(field).val('').trigger('change');
                $(field).trigger("select2:close");
            });

            $('#binLocation').select2({
                placeholder: "{{ __('Select Bin Location') }}",
                allowClear: true,
                width: '100%'
            });

            // Media upload handler
            $('#media').on('change', function() {
                let file = this.files[0];
                if (file) {
                    let formData = new FormData();
                    formData.append('media', file);

                    $('#uploadStatus').text('Uploading...');

                    $.ajax({
                        url: '{{ route('media.upload') }}',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.mediaId && response.shopwareResponse) {
                                $('#uploadStatus').text('Upload Successful!');

                                if ($('#media_id').length === 0) {
                                    $('#media').after(
                                        '<input type="hidden" name="media_id" id="media_id" value="' +
                                        response.mediaId + '">');
                                } else {
                                    $('#media_id').val(response.mediaId);
                                }

                            } else {
                                $('#uploadStatus').text('Upload Failed!');
                            }
                        },
                        error: function() {
                            $('#uploadStatus').text('Error Uploading File!');
                        }
                    });
                }
            });
        });
    </script>
@endsection
