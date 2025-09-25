<div id="stepBol3" style="display: none;" class="step">
    <div class="step-header">Step 3: {{ __('product.update_stock') }}</div>
    <div id="step3Content">
        <!-- Product Table with Stock Information -->
        <form id="bol-product-form" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-12">
                    <h5 class="mb-3 px-3 w-100">{{ __('product.general_information') }}</h5>
                </div>
            </div>
            <div class="row">
                <div class="form-group mb-3 col-sm-6 col-xs-12 px-3 w-100">
                    <label for="bolProductName">@lang('product.name'):</label>
                    <input type="text" class="form-control" id="bolProductName" name="bolProductName" required>
                </div>
            </div>
            <div class="row">
                <div class="form-group mb-3 col-sm-6 col-xs-12 px-3">
                    <label for="bolProductSku">@lang('product.bolProductSku'):</label>
                    <input type="text" class="form-control" id="bolProductSku" name="bolProductSku" required>
                </div>
                <div class="form-group mb-3 col-sm-6 col-xs-12 px-3">
                    <label for="bolTaxId">@lang('product.tax_id'):</label>
                    <select id="tax-provider-select-bol" class="js-example-basic-single form-control" name="bolTaxId"
                        required>
                    </select>
                    <input type="hidden" name="taxRate" id="taxRate" value="21" />
                </div>
            </div>
            <div class="row">
                <div class="form-group mb-3 col-sm-6 col-xs-12 px-3">
                    <label for="bolProductShortDescription">{{ __('product.bolProductShortDescription') }}</label>
                    <textarea class="form-control" id="bolProductShortDescription" name="bolProductShortDescription" rows="2"></textarea>
                </div>
                <div class="form-group mb-3 col-sm-6 col-xs-12 px-3">
                    <label for="bolProductDescription">{{ __('product.bolProductDescription') }}</label>
                    <textarea class="form-control description-editor" id="bolProductDescription" name="bolProductDescription"
                        rows="3"></textarea>
                </div>
            </div>
            <div class="row">
                <div class="form-group mb-3 col-sm-6 col-xs-12 px-3">
                    <label for="bolProductPrice">{{ __('product.productPrice') }}</label>
                    <input type="text" name="bolProductPrice" id="bolProductPrice" class="form-control" required>
                    <input type="hidden" name="bolTotalPrice" id="bolTotalPrice" class="form-control" required>
                </div>
                <div class="form-group mb-3 col-sm-6 col-xs-12 px-3">
                    <label for="bolProductListPriceGross">{{ __('product.bolListGrossPrice') }}</label>
                    <input type="text" name="bolProductListPriceGross" id="bolProductListPriceGross"
                        class="form-control" required>
                    <input type="hidden" name="bolProductListPriceNet" id="bolProductListPriceNet" class="form-control"
                        required>
                </div>
            </div>
            <div class="row">
                <div class="form-group mb-3 col-sm-6 col-xs-12 px-3">
                    <label for="purchase_price">
                        {{ __('product.purchase_price') }} <span class="text-danger">*</span>
                    </label>
                    <input type="number" name="purchasePriceNet" id="purchasePriceNet"
                        class="form-control @error('purchasePriceNet') is-invalid @enderror" step="any" required
                        placeholder="{{ __('product.enter_purchase_price') }}">
                    <input type="hidden" name="purchasePrice" id="purchasePrice" class="form-control"
                        step="any" required />
                </div>
                <div class="form-group mb-3 col-sm-6 col-xs-12 px-3">
                    <label for="salesChannel">@lang('product.sales_channel'):</label>
                    <select id="sales-channel-select" class="js-example-basic-single form-control"
                        name="salesChannelBol[]" multiple required>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="form-group mb-3 col-sm-6 col-xs-12 px-3">
                    <label for="bolStock">{{ __('product.bolStock') }}<span class="text-danger">*</span></label>
                    <input type="text" name="bolStock" id="bolStock" class="form-control" value="0" required>
                </div>
                <div class="col-md-6 form-group">
                    <label for="active_for_all">{{ __('product.active_for_all_label') }}</label>
                    <input type="hidden" name="active_for_allBol" value="0">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" name="active_for_allBol" id="active_for_all"
                            value="1" checked {{ old('active_for_all') ? 'checked' : '' }}>
                    </div>
                </div>
                <div class="form-group mb-3 col-sm-6 col-xs-12 px-3">
                    <label for="bolProductManufacturer">@lang('product.bolProductManufacturer')
                        :</label>
                    <input type="text" class="form-control" id="bolProductManufacturer"
                        name="bolProductManufacturer" disabled required>
                    <input type="hidden" id="bolProductManufacturerId" name="bolProductManufacturerId">
                </div>

                <div class="form-group mb-3 col-sm-6 col-xs-12 px-3">
                    <label for="bolProductCategories">@lang('product.categories')
                        :</label>
                    <input type="text" class="form-control" disabled id="bolProductCategories"
                        name="bolProductCategories" required>
                    <input type="hidden" id="bolProductCategoriesId" name="bolProductCategoriesId">
                </div>
            </div>

            {{-- Product Marketplace Section --}}
            <div class="row">
                <div class="col-md-12">
                    <h5 class="my-3">{{ __('product.product_marketplace_information') }}</h5>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 form-group">
                    <input type="hidden" name="bolNlActive" value="0">
                    <label for="bolNlActive" class="bolNlActive">{{ __('product.active_for_bol_nl') }}</label>
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" name="bolNlActive" id="bolNlActive"
                            value="1" {{ old('bolNlActive') ? 'checked' : '' }}>
                    </div>
                </div>
                <div class="col-md-6 form-group">
                    <input type="hidden" name="bolBeActive" value="0">
                    <label for="bolBeActive" class="bolBeActive">{{ __('product.active_for_bol_be') }}</label>
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" name="bolBeActive" id="bolBeActive"
                            value="1" {{ old('bolBeActive') ? 'checked' : '' }}>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <label for="bolNlPrice" class="bolNlPrice">{{ __('product.bol_nl_price') }}</label>
                    <input type="text" name="bolNlPrice" id="bolNlPrice" class="form-control" required>
                </div>

                <div class="col-md-6 form-group mb-3">
                    <label for="bolBePrice" class="bolBePrice">{{ __('product.bol_be_price') }}</label>
                    <input type="text" name="bolBePrice" id="bolBePrice" class="form-control" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <label for="bolNLDeliveryTime" class="bolNLDeliveryTime">@lang('product.bol_nl_delivery_time'):</label>
                    <select class="js-example-basic-single form-control bolNLDeliveryTimeSelect"
                        name="bolNLDeliveryTime" required>
                    </select>
                </div>
                <div class="col-md-6 form-group mb-3">
                    <label for="bolBEDeliveryTime" class="bolBEDeliveryTime">@lang('product.bol_be_delivery_time'):</label>
                    <select class="js-example-basic-single form-control bolBEDeliveryTimeSelect"
                        name="bolBEDeliveryTime" required>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <label for="bolCondition" class="bolCondition">@lang('product.bol_condition'):</label>
                    <select class="js-example-basic-single form-control bolConditionSelect" name="bolCondition"
                        required>
                    </select>
                </div>
                <div class="col-md-6 form-group mb-3">
                    <label for="bolConditionDescription" class="bolConditionDescription">@lang('product.bol_condition_description'):</label>
                    <input type="text" class="form-control" id="bolConditionDescription"
                        name="bolConditionDescription" required>
                </div>
            </div>
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
                            for="bolOrderBeforeTomorrow">@lang('product.bol_ordered_tomorrow')</label>
                    </div>
                </div>
                <div class="col-md-6 form-group mb-3">
                    <div class="form-check">
                        <input type="hidden" name="bolOrderBefore" value="0">
                        <input type="checkbox" class="form-check-input bolOrderBefore" id="bolOrderBefore"
                            value="1" name="bolOrderBefore">
                        <label class="form-check-label bolOrderBefore" for="bolOrderBefore">@lang('product.bol_ordered_before')</label>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 form-group mb-3">
                    <div class="form-check">
                        <input type="hidden" name="bolLetterboxPackage" value="0">
                        <input type="checkbox" class="form-check-input bolLetterboxPackage" id="bolLetterboxPackage"
                            value="1" name="bolLetterboxPackage">
                        <label class="form-check-label bolLetterboxPackage"
                            for="bolLetterboxPackage">@lang('product.bol_letterbox_package')</label>
                    </div>
                </div>
                <div class="col-md-6 form-group mb-3">
                    <div class="form-check">
                        <input type="hidden" name="bolLetterboxPackageUp" value="0">
                        <input type="checkbox" class="form-check-input bolLetterboxPackageUp"
                            id="bolLetterboxPackageUp" value="1" name="bolLetterboxPackageUp">
                        <label class="form-check-label bolLetterboxPackageUp"
                            for="bolLetterboxPackageUp">@lang('product.bol_letterbox_package_up')</label>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 form-group">
                    <input type="hidden" name="bolPickUpOnly" value="0">
                    <label for="bolPickUpOnly" class="bolPickUpOnly">{{ __('product.bol_pick_up_only') }}</label>
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" name="bolPickUpOnly" id="bolPickUpOnly"
                            value="1" {{ old('bolPickUpOnly') ? 'checked' : '' }}>
                    </div>
                </div>

                <div class="col-md-6 form-group">
                    <label for="binLocation">@lang('product.bin_location')<span class="text-danger">*</span></label>
                    <select name="bin_location_id" id="binLocation" class="form-control bin-location-select">
                        @foreach ($binLocationList as $location)
                            <option value="{{ $location['id'] }}"> {{ $location['attributes']['code'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="form-group mb-3 col-sm-6 col-xs-12 px-3">
                    <label for="bolProductThumbnail">@lang('product.bolProductThumbnail')
                        :</label>
                    <div class="col-md-12">
                        <img src="" name="bolProductThumbnail" id="bolProductThumbnail" alt=""
                            style="width: 200px">
                    </div>
                </div>

            </div>
            <input type="hidden" name="warehouse" value="{{ $admin->warehouse_id }}" />
            <input type="hidden" class="form-control" id="bolProductEanNumber" name="bolProductEanNumber" required>
            <input type="hidden" name="bolPackagingWidth" id="bolPackagingWidth" class="form-control" required>
            <input type="hidden" name="bolPackagingHeight" id="bolPackagingHeight" class="form-control" required>
            <input type="hidden" name="bolPackagingLength" id="bolPackagingLength" class="form-control" required>
            <input type="hidden" name="bolPackagingWeight" id="bolPackagingWeight" class="form-control" required>
        </form>
    </div>
    <div class="col-md-12 mt-5">
        <div class="d-flex justify-content-between">
            <div>
                <button type="button" id="backBolBtnsetp3"
                    class="btn btn-danger float-start">{{ __('product.previous') }}</button>
            </div>
            <div>
                <button type="submit" class="btn btn-primary float-end"
                    id="saveBolProductData">{{ __('product.submit') }}</button>
            </div>
        </div>
    </div>
</div>
