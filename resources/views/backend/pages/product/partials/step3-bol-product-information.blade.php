<div id="stepBol3" style="display: none;" class="step">
    <div class="step-header">Step 3: {{ __('product.update_stock') }}</div>
    <div id="step3Content">
        <!-- Product Table with Stock Information -->
        <form id="bol-product-form" method="POST">
            @csrf
            <div class="row">
                <!-- Left Column -->
                <div class="col-md-12 p-0 d-flex flex-wrap">
                    <h5 class="mb-3 px-3 w-100">{{ __('product.general_information') }}</h5>

                    <div class="form-group mb-3 col-sm-6 col-xs-12 px-3 w-100">
                        <label for="bolProductName">@lang('product.name'):</label>
                        <input type="text" class="form-control" id="bolProductName"
                               name="bolProductName" required>
                    </div>
                    <div class="form-group mb-3 col-sm-6 col-xs-12 px-3 d-none">
                        <label
                                for="bolProductEanNumber">@lang('product.bolProductEanNumber')
                            :</label>
                        <input type="text" class="form-control" id="bolProductEanNumber"
                               name="bolProductEanNumber" required>
                    </div>
                    <div class="form-group mb-3 col-sm-6 col-xs-12 px-3">
                        <label for="bolProductSku">@lang('product.bolProductSku'):</label>
                        <input type="text" class="form-control" id="bolProductSku"
                        name="bolProductSku" required>
                    </div>
                    <div class="form-group mb-3 col-sm-6 col-xs-12 px-3">
                        <label for="bolTaxId">@lang('product.tax_id'):</label>
                        <select id="tax-provider-select-bol"
                                class="js-example-basic-single form-control" name="bolTaxId"
                                required>
                        </select>
                    </div>
                    <div class="form-group mb-3 col-sm-6 col-xs-12 px-3">
                        <label
                                for="bolProductDescription">{{ __('product.bolProductDescription') }}</label>
                        <textarea class="form-control" id="bolProductDescription"
                                  name="bolProductDescription" rows="5"></textarea>
                    </div>
                    <div class="form-group mb-3 col-sm-6 col-xs-12 px-3">
                        <label
                                for="bolProductShortDescription">{{ __('product.bolProductShortDescription') }}</label>
                        <textarea class="form-control" id="bolProductShortDescription"
                                  name="bolProductShortDescription" rows="2"></textarea>
                    </div>

                    <div class="form-group mb-3 col-sm-6 col-xs-12 px-3">
                        <label
                                for="bolProductPrice">{{ __('product.productPrice') }}</label>
                        <input type="text" name="bolProductPrice" id="bolProductPrice"
                               class="form-control" required>
                    </div>

                    <div class="form-group mb-3 col-sm-6 col-xs-12 px-3">
                        <label
                                for="bolTotalPrice">{{ __('product.bolNetPrice') }}</label>
                        <input type="text" name="bolTotalPrice" id="bolTotalPrice"
                               class="form-control" required>
                    </div>

                    <div class="form-group mb-3 col-sm-6 col-xs-12 px-3">
                        <label
                                for="bolProductListPriceGross">{{ __('product.bolListGrossPrice') }}</label>
                        <input type="text" name="bolProductListPriceGross" id="bolProductListPriceGross"
                               class="form-control" required>
                    </div>
                    <div class="form-group mb-3 col-sm-6 col-xs-12 px-3">
                        <label
                                for="bolProductListPriceNet">{{ __('product.bolListNetPrice') }}</label>
                        <input type="text" name="bolProductListPriceNet" id="bolProductListPriceNet"
                               class="form-control" required>
                    </div>
                    <div class="form-group mb-3 col-sm-6 col-xs-12 px-3">
                        <label for="salesChannel">@lang('product.sales_channel'):</label>
                        <select id="sales-channel-select"
                                class="js-example-basic-single form-control"
                                name="salesChannelBol[]" multiple required>
                        </select>
                    </div>
                    <div class="form-group mb-3 col-sm-6 col-xs-12 px-3">
                        <label
                                for="bolStock">{{ __('product.bolStock') }}</label>
                        <input type="text" name="bolStock" id="bolStock"
                               class="form-control" required>
                    </div>

                    <div class="form-group mb-3 col-sm-6 col-xs-12 px-3">
                        <label
                                for="bolProductManufacturer">@lang('product.bolProductManufacturer')
                            :</label>
                        <input type="text" class="form-control" id="bolProductManufacturer"
                               name="bolProductManufacturer" disabled required>
                        <input type="hidden" id="bolProductManufacturerId"
                               name="bolProductManufacturerId">
                    </div>

                    <div class="form-group mb-3 col-sm-6 col-xs-12 px-3">
                        <label for="bolProductCategories">@lang('product.categories')
                            :</label>
                        <input type="text" class="form-control" disabled
                               id="bolProductCategories"
                               name="bolProductCategories" required>
                        <input type="hidden" id="bolProductCategoriesId"
                               name="bolProductCategoriesId">
                    </div>

                    <div class="form-group mb-3 col-sm-6 col-xs-12 px-3 d-none">
                        <label
                                for="bolPackagingWidth">{{ __('product.PackagingWidth') }}</label>
                        <input type="text" name="bolPackagingWidth" id="bolPackagingWidth"
                               class="form-control" required>
                    </div>

                    <div class="form-group mb-3 col-sm-6 col-xs-12 px-3 d-none">
                        <label
                                for="bolPackagingHeight">{{ __('product.PackagingHeight') }}</label>
                        <input type="text" name="bolPackagingHeight" id="bolPackagingHeight"
                               class="form-control" required>
                    </div>

                    <div class="form-group mb-3 col-sm-6 col-xs-12 px-3 d-none">
                        <label
                                for="bolPackagingLength">{{ __('product.PackagingLength') }}</label>
                        <input type="text" name="bolPackagingLength" id="bolPackagingLength"
                               class="form-control" required>
                    </div>

                    <div class="form-group mb-3 col-sm-6 col-xs-12 px-3 d-none">
                        <label
                                for="bolPackagingWeight">{{ __('product.PackagingWeight') }}</label>
                        <input type="text" name="bolPackagingWeight" id="bolPackagingWeight"
                               class="form-control" required>
                    </div>

                    <div class="form-group mb-3 col-sm-6 col-xs-12 px-3">
                        <label
                                for="bolProductThumbnail">@lang('product.bolProductThumbnail')
                            :</label>
                        <div class="col-md-12">
                            <img src="" name="bolProductThumbnail" id="bolProductThumbnail"
                                 alt="" style="width: 200px">
                        </div>
                    </div>
                    <div class="form-group mb-3 col-sm-6 col-xs-12 px-3">
                        <label
                                for="active_for_all">{{ __('product.active_for_all_label') }}</label>
                        <input type="hidden" name="active_for_allBol" value="0">



                        <div class="form-check form-switch">
                            <input type="checkbox" class="form-check-input"
                                   name="active_for_allBol" id="active_for_all"
                                   value="1" {{ old('active_for_all') ? 'checked' : '' }}>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="d-flex justify-content-between">
                    <div>
                        <button type="button" id="backBolBtnsetp3" class="btn btn-danger float-start">{{ __('product.previous') }}</button>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary float-end" id="saveBolProductData">{{ __('product.submit') }}</button>            </div>
                    </div>
                </div>
        </form>
    </div>
</div>
