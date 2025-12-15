<!-- Additional Section for Yes Selection (Initially hidden) -->

<div id="yesStepDetails" style="display:none;" class="step">
    <div class="step-header">{{ __('product.step2_yes_flow_show_product_details') }}</div>
    <div id="step3productDetails">
        <!-- Loading Spinner initially -->
        {{-- <div class="loader"></div> --}}
    </div>
    <table class="table table-bordered mt-3" id="productTable-update">
        <thead>
            <tr>
                <th>{{ __('product.product_name') }}</th>
                <th class="d-none">{{ __('product.ean_number') }}</th>
                <th>{{ __('product.product_number') }}</th>
                <th>{{ __('product.current_stock') }}</th>
                <th>{{ __('product.dgm_price') }}</th>
                <th>{{ __('product.bol_price_nl') }}</th>
                <th>{{ __('product.bol_price_be') }}</th>
                <th>{{ __('product.action') }}</th>
            </tr>
        </thead>
        <tbody>
            <!-- Dynamic rows will be inserted here -->
        </tbody>
    </table>


    <!-- Product Edit Modal -->
    <div class="modal fade" id="productEditModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('product.step4') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height: 85vh; overflow-y: auto; overflow-x: hidden;">
                    <form id="product-form" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="mb-3">{{ __('product.general_information') }}</h5>
                            </div>
                            <div class="col-md-6">
                                <h5 class="mb-3">{{ __('product.additional_information') }}</h5>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="name">@lang('product.name'):</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label for="bolProductShortDescription">@lang('product.shortDescription'):</label>
                                <textarea class="form-control" id="shortDescription" name="bolProductShortDescription" rows="1"></textarea>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="stock">@lang('product.stock'):</label>
                                <input type="number" class="form-control" id="stock" name="stock" required>
                            </div>


                            <div class="col-md-6 form-group mb-3">
                                <label for="description">@lang('product.description'):</label>
                                <textarea class="form-control description-editor" id="description" name="description" rows="1"></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="productEanNumber">@lang('product.bolProductEanNumber')
                                    :</label>
                                <input type="text" class="form-control" id="productEanNumber" name="productEanNumber"
                                    required>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label for="productNumber">@lang('product.product_number'):</label>
                                <input type="text" class="form-control" id="productNumber" name="productNumber"
                                    required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="taxId">@lang('product.tax_id'):</label>
                                <select id="tax-provider-select" class="js-example-basic-single form-control"
                                    name="taxId" required>
                                </select>
                                <input type="hidden" name="taxRate" id="swTaxRate" value="21" />
                            </div>
                            <div class="col-md-6 form-group mt-3">
                                <div class="d-flex flex-column">
                                    <div>
                                        <strong>@lang('product.selectedGroup'):</strong>
                                        <span id="selectedPropertyGroupDisplay"></span>
                                    </div>
                                    <div>
                                        <strong>@lang('product.selectedPropertyGroup'):</strong>
                                        <span id="selectedPropertyOptionDisplay"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="price_gross">{{ __('product.price_gross') }}</label>
                                <input type="number" name="priceGross" id="priceGross" class="form-control"
                                    step="any" required placeholder="{{ __('product.enter_price_gross') }}">
                                <input type="hidden" name="priceNet" id="priceNet" class="form-control" step="any"
                                    placeholder="{{ __('product.calculated_price_net') }}">
                            </div>
                            <div class="form-group col-md-6 col-sm-12 px-2">
                                <label for="sw_purchase_price">
                                    {{ __('product.purchase_price') }} <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="purchasePriceNet" id="swPurchasePriceNet"
                                    class="form-control @error('purchasePriceNet') is-invalid @enderror" step="any"
                                    required placeholder="{{ __('product.enter_purchase_price') }}">
                                <input type="hidden" name="purchasePrice" id="swPurchasePrice"
                                    class="form-control" step="any" required />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="list_price">
                                    {{ __('product.list_price') }} <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="listPriceGross" id="listPriceGross"
                                    class="form-control @error('listPriceGross') is-invalid @enderror" step="any"
                                    required placeholder="{{ __('product.enter_list_price') }}">
                                <input type="hidden" name="listPriceNet" id="listPriceNet"
                                    class="form-control" step="any" required />
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
                                <label for="bolNlActive"
                                    class="bolNlActive">{{ __('product.active_for_bol_nl') }}</label>
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" name="bolNlActive"
                                        id="bolNlActive" value="1" {{ old('bolNlActive') ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <input type="hidden" name="bolBeActive" value="0">
                                <label for="bolBeActive"
                                    class="bolBeActive">{{ __('product.active_for_bol_be') }}</label>
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" name="bolBeActive"
                                        id="bolBeActive" value="1" {{ old('bolBeActive') ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="bolNlPrice" class="bolNlPrice">{{ __('product.bol_nl_price') }}</label>
                                <input type="text" name="bolNlPrice" id="bolNlPrice" class="form-control"
                                    required>
                            </div>

                            <div class="col-md-6 form-group mb-3">
                                <label for="bolBePrice" class="bolBePrice">{{ __('product.bol_be_price') }}</label>
                                <input type="text" name="bolBePrice" id="bolBePrice" class="form-control"
                                    required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="bolNLDeliveryTime" class="bolNLDeliveryTime">@lang('product.bol_nl_delivery_time'):</label>
                                <select id="bolNLDeliveryTime"
                                    class="js-example-basic-single form-control bolNLDeliveryTimeSelect"
                                    name="bolNLDeliveryTime" required>
                                </select>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label for="bolBEDeliveryTime" class="bolBEDeliveryTime">@lang('product.bol_be_delivery_time'):</label>
                                <select id="bolBEDeliveryTime"
                                    class="js-example-basic-single form-control bolBEDeliveryTimeSelect"
                                    name="bolBEDeliveryTime" required>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <label for="bolCondition" class="bolCondition">@lang('product.bol_condition'):</label>
                                <select id="bolCondition"
                                    class="js-example-basic-single form-control bolConditionSelect"
                                    name="bolCondition" required>
                                </select>
                            </div>
                            <div class="col-md-6 form-group mb-3">
                                <label for="bolConditionDescription"
                                    class="bolConditionDescription">@lang('product.bol_condition_description'):</label>
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
                                    <input type="checkbox" class="form-check-input bolOrderBefore"
                                        id="bolOrderBefore" value="1" name="bolOrderBefore">
                                    <label class="form-check-label bolOrderBefore"
                                        for="bolOrderBefore">@lang('product.bol_ordered_before')</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group mb-3">
                                <div class="form-check">
                                    <input type="hidden" name="bolLetterboxPackage" value="0">
                                    <input type="checkbox" class="form-check-input bolLetterboxPackage"
                                        id="bolLetterboxPackage" value="1" name="bolLetterboxPackage">
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
                        <div class="row mb-3">
                            <div class="col-md-6 form-group">
                                <input type="hidden" name="bolPickUpOnly" value="0">
                                <label for="bolPickUpOnly"
                                    class="bolPickUpOnly">{{ __('product.bol_pick_up_only') }}</label>
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" name="bolPickUpOnly"
                                        id="bolPickUpOnly" value="1"
                                        {{ old('bolPickUpOnly') ? 'checked' : '' }}>
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <label for="binLocation">@lang('product.bin_location')</label>
                                <select name="bin_location_id" id="binLocation"
                                    class="form-control bin-location-select">
                                    @foreach ($binLocationList as $location)
                                        <option value="{{ $location['id'] }}"> {{ $location['attributes']['code'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="hidden" name="warehouse" value="{{ $admin->warehouse_id }}" />
                        </div>

                        {{-- Hidden Fields Start --}}

                        <input type="hidden" id="manufacturer" name="manufacturer" value="" />

                        <input type="hidden" name="productPackagingWidth" id="productPackagingWidth"
                            class="form-control" required>

                        <input type="hidden" name="productPackagingHeight" id="productPackagingHeight"
                            class="form-control" required>

                        <input type="hidden" name="productPackagingLength" id="productPackagingLength"
                            class="form-control" required>

                        <input type="hidden" name="productPackagingWeight" id="productPackagingWeight"
                            class="form-control" required>
                        <input type="hidden" id="productConfiguratorSettingsIds"
                            name="productConfiguratorSettingsIds" />
                        {{-- Hidden Fields End --}}
                        <button type="submit" class="btn btn-success w-100 mt-2"
                            id="saveVariant">{{ __('product.submit') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Bin Location Selection Modal -->
    <div class="modal fade" id="binLocationSelectionModal" tabindex="-1" role="dialog"
        aria-labelledby="binLocationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="binLocationModalLabel">{{ __('product.select_bin_location') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="col-md-12 form-group">
                        <label for="binLocation">@lang('product.bin_location')</label>
                        <select name="bin_location_id" id="modalBinLocation" class="form-control">
                            @foreach ($binLocationList as $location)
                                <option value="{{ $location['id'] }}"> {{ $location['attributes']['code'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger"
                        data-bs-dismiss="modal">{{ __('product.cancel') }}</button>
                    <button type="button" class="btn btn-success"
                        id="updateBinLocation">{{ __('product.update') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Back and Next Buttons -->
    <button type="button" id="back3YesStep" class="btn btn-danger btn-back">{{ __('product.previous') }}</button>
    <button type="button" id="newVariantButton" class="btn btn-success btn-new-variant">
        {{ __('product.create_new_variant') }}
    </button>


    <div id="propertyGroupSection" style="display: none;" class="container mt-5">
        <h5 class="mb-4">@lang('product.select_required_info')</h5>

        <div class="row">
            <!-- Property Group Select -->
            <div class="col-md-6 mb-4">
                <div class="card p-3">
                    <label for="propertyGroupSelect" class="font-weight-bold">@lang('product.property_select_group')<span class="text-danger">*</span></label>
                    <div class="d-flex align-items-center mt-2">
                        <select id="propertyGroupSelect" class="form-control me-3 w-75"></select>
                    </div>
                </div>
            </div>

            <!-- Property Group Option Select -->
            <div id="propertyGroupOptionWrapper" class="col-md-6 mb-4" style="display: none;">
                <div class="card p-3">
                    <label for="propertyGroupOptionSelect" class="font-weight-bold">@lang('product.property_select_group_option') <span class="text-danger">*</span></label>
                    <div class="d-flex align-items-center mt-2">
                        <select id="propertyGroupOptionSelect" class="form-control me-3 w-75"></select>
                    </div>
                </div>
            </div>
        </div>
        {{-- <div class="row">
            <!-- Property Group Select -->
            <div class="col-md-6 mb-4">
                <div class="card p-3">
                    <label for="propertyGroupSelectSecond" class="font-weight-bold">@lang('product.property_select_group')</label>
                    <div class="d-flex align-items-center mt-2">
                        <select id="propertyGroupSelectSecond" class="form-control me-3 w-75"></select>
                    </div>
                </div>
            </div>

            <!-- Property Group Option Select -->
            <div id="propertyGroupOptionWrapperSecond" class="col-md-6 mb-4" style="display: none;">
                <div class="card p-3">
                    <label for="propertyGroupOptionSelectSecond" class="font-weight-bold">@lang('product.property_select_group_option')</label>
                    <div class="d-flex align-items-center mt-2">
                        <select id="propertyGroupOptionSelectSecond" class="form-control me-3 w-75"></select>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Property Group Select -->
            <div class="col-md-6 mb-4">
                <div class="card p-3">
                    <label for="propertyGroupSelectThird" class="font-weight-bold">@lang('product.property_select_group')</label>
                    <div class="d-flex align-items-center mt-2">
                        <select id="propertyGroupSelectThird" class="form-control me-3 w-75"></select>
                    </div>
                </div>
            </div>

            <!-- Property Group Option Select -->
            <div id="propertyGroupOptionWrapperThird" class="col-md-6 mb-4" style="display: none;">
                <div class="card p-3">
                    <label for="propertyGroupOptionSelectThird" class="font-weight-bold">@lang('product.property_select_group_option')</label>
                    <div class="d-flex align-items-center mt-2">
                        <select id="propertyGroupOptionSelectThird" class="form-control me-3 w-75"></select>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Property Group Select -->
            <div class="col-md-6 mb-4">
                <div class="card p-3">
                    <label for="propertyGroupSelectFour" class="font-weight-bold">@lang('product.property_select_group')</label>
                    <div class="d-flex align-items-center mt-2">
                        <select id="propertyGroupSelectFour" class="form-control me-3 w-75"></select>
                    </div>
                </div>
            </div>

            <!-- Property Group Option Select -->
            <div id="propertyGroupOptionWrapperFour" class="col-md-6 mb-4" style="display: none;">
                <div class="card p-3">
                    <label for="propertyGroupOptionSelectFour" class="font-weight-bold">@lang('product.property_select_group_option')</label>
                    <div class="d-flex align-items-center mt-2">
                        <select id="propertyGroupOptionSelectFour" class="form-control me-3 w-75"></select>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Property Group Select -->
            <div class="col-md-6 mb-4">
                <div class="card p-3">
                    <label for="propertyGroupSelectFive" class="font-weight-bold">@lang('product.property_select_group')</label>
                    <div class="d-flex align-items-center mt-2">
                        <select id="propertyGroupSelectFive" class="form-control me-3 w-75"></select>
                    </div>
                </div>
            </div>

            <!-- Property Group Option Select -->
            <div id="propertyGroupOptionWrapperFive" class="col-md-6 mb-4" style="display: none;">
                <div class="card p-3">
                    <label for="propertyGroupOptionSelectFive" class="font-weight-bold">@lang('product.property_select_group_option')</label>
                    <div class="d-flex align-items-center mt-2">
                        <select id="propertyGroupOptionSelectFive" class="form-control me-3 w-75"></select>
                    </div>
                </div>
            </div>
        </div> --}}
        <!--...................-->
        <div id="addPropertyOptionWrapper" class="mt-4 d-flex align-items-center gap-2" style="visibility : hidden;">
            <div class="col-md-3">
                <button type="button" id="createPropertyGroupOptionBtn"
                    class="btn btn-primary w-100">@lang('product.create_new_property')</button>
            </div>
            <div class="col-md-3">
                <button type="button" id="addPropertyOptionBtn"
                    class="btn btn-success w-100">@lang('product.add_option')</button>
            </div>
        </div>
        <!--...................-->

    </div>
</div>

<!-- Modal for creating new Property Group Option -->
<div class="modal fade" id="createPropertyGroupOptionModal" tabindex="-1"
    aria-labelledby="createPropertyGroupOptionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createPropertyGroupOptionModalLabel">@lang('product.create_new_property_option')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Display selected Property Group Name -->
                <div class="mb-3">
                    <div class="card p-3">
                        <label for="propertyGroupSelectSix" class="font-weight-bold">@lang('product.property_select_group')</label>
                        <div class="d-flex align-items-center mt-2">
                            <select id="propertyGroupSelectSix" class="form-control me-3 w-75"></select>
                        </div>
                    </div>

                    <!-- New Property Option Name -->
                    <div class="card p-3">
                        <label for="newPropertyOptionName" class="form-label">@lang('product.new_property_option_name')</label>
                        <input type="text" id="newPropertyOptionName" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">@lang('product.cancel')</button>
                <button type="button" class="btn btn-primary"
                    id="savePropertyGroupOptionBtn">@lang('product.save')</button>
            </div>
        </div>
    </div>
</div>
