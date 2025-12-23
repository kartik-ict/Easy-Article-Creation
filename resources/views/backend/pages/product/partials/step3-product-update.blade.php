<!-- Step 3: Product Update Modal -->
<div class="modal fade" id="productUpdateModal" tabindex="-1" role="dialog" aria-labelledby="productUpdateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('product.step3_product_update') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="max-height: 85vh; overflow-y: auto; overflow-x: hidden;">
                <form id="product-update-form" method="POST">
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
                            <label for="updateName">@lang('product.name'):</label>
                            <input type="text" class="form-control" id="updateName" name="name" required>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="updateShortDescription">@lang('product.shortDescription'):</label>
                            <textarea class="form-control" id="updateShortDescription" name="bolProductShortDescription" rows="1"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="updateCurrentStock">@lang('product.current_stock'):</label>
                            <input type="number" class="form-control" id="updateCurrentStock" name="current_stock" readonly>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="updateNewStock">@lang('product.enter_new_stock'):</label>
                            <input type="number" class="form-control" id="updateNewStock" name="new_stock" placeholder="@lang('product.enter_new_stock')" min="0">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="updateBinLocation">@lang('product.bin_location') <span class="text-danger">*</span></label>
                            <select name="bin_location_id" id="updateBinLocation" class="form-control bin-location-select-update" required>
                                <option value="">{{ __('product.select_bin_location') }}</option>
                                @if(isset($binLocationList))
                                    @foreach ($binLocationList as $location)
                                        <option value="{{ $location['id'] }}" {{ strtolower($location['attributes']['code']) == 'main bin location' ? 'selected' : '' }}> {{ $location['attributes']['code'] }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="updateEanNumber">@lang('product.bolProductEanNumber'):</label>
                            <input type="text" class="form-control" id="updateEanNumber" name="productEanNumber" required>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="updateProductNumber">@lang('product.product_number'):</label>
                            <input type="text" class="form-control" id="updateProductNumber" name="productNumber" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 form-group mb-3">
                            <label for="updateDescription">@lang('product.description'):</label>
                            <textarea class="form-control description-editor" id="updateDescription" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="updatePriceGross">{{ __('product.price_gross') }}</label>
                            <input type="number" name="priceGross" id="updatePriceGross" class="form-control" step="any" required placeholder="{{ __('product.enter_price_gross') }}">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="updatePurchasePrice">{{ __('product.purchase_price') }}</label>
                            <input type="number" name="purchasePriceNet" id="updatePurchasePrice" class="form-control" step="any" required placeholder="{{ __('product.enter_purchase_price') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="updateListPrice">{{ __('product.list_price') }}</label>
                            <input type="number" name="listPriceGross" id="updateListPrice" class="form-control" step="any" required placeholder="{{ __('product.enter_list_price') }}">
                        </div>
                    </div>
                    
                    {{-- Configure Properties Section --}}
                    <div class="row">
                        <div class="col-md-12">
                            <h5 class="my-3">{{ __('product.configure_properties') }}</h5>
                        </div>
                    </div>
                    <div id="updatePropertiesContainer">
                        <div class="property-group-row-update" data-index="0">
                            <div class="row align-items-end mb-3">
                                <div class="col-md-5">
                                    <label class="form-label">@lang('product.property_select_group')</label>
                                    <select class="form-control property-group-select-update" name="propertyGroups[0][groupId]" data-index="0">
                                        <option value="">@lang('product.select_property_group_first')</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label">@lang('product.property_select_group_option')</label>
                                    <select class="form-control property-option-select-update" name="propertyGroups[0][optionId]" data-index="0" disabled>
                                        <option value="">@lang('product.select_option_first')</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-success add-property-btn-update" title="Add Property">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger remove-property-btn-update" title="Remove Property" style="display: none;">
                                        <i class="fa fa-minus"></i>
                                    </button>
                                </div>
                            </div>
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
                            <label for="updateBolNlActive" class="bolNlActive">{{ __('product.active_for_bol_nl') }}</label>
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="bolNlActive" id="updateBolNlActive" value="1">
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <input type="hidden" name="bolBeActive" value="0">
                            <label for="updateBolBeActive" class="bolBeActive">{{ __('product.active_for_bol_be') }}</label>
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="bolBeActive" id="updateBolBeActive" value="1">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="updateBolNlPrice" class="bolNlPrice">{{ __('product.bol_nl_price') }}</label>
                            <input type="text" name="bolNlPrice" id="updateBolNlPrice" class="form-control">
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="updateBolBePrice" class="bolBePrice">{{ __('product.bol_be_price') }}</label>
                            <input type="text" name="bolBePrice" id="updateBolBePrice" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="updateBolNLDeliveryTime" class="bolNLDeliveryTime">@lang('product.bol_nl_delivery_time'):</label>
                            <select id="updateBolNLDeliveryTime" class="js-example-basic-single form-control bolNLDeliveryTimeSelect" name="bolNLDeliveryTime">
                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="updateBolBEDeliveryTime" class="bolBEDeliveryTime">@lang('product.bol_be_delivery_time'):</label>
                            <select id="updateBolBEDeliveryTime" class="js-example-basic-single form-control bolBEDeliveryTimeSelect" name="bolBEDeliveryTime">
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="updateBolCondition" class="bolCondition">@lang('product.bol_condition'):</label>
                            <select id="updateBolCondition" class="js-example-basic-single form-control bolConditionSelect" name="bolCondition">
                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="updateBolConditionDescription" class="bolConditionDescription">@lang('product.bol_condition_description'):</label>
                            <input type="text" class="form-control" id="updateBolConditionDescription" name="bolConditionDescription">
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
                                <input type="checkbox" class="form-check-input bolOrderBeforeTomorrow" id="updateBolOrderBeforeTomorrow" value="1" name="bolOrderBeforeTomorrow">
                                <label class="form-check-label bolOrderBeforeTomorrow" for="updateBolOrderBeforeTomorrow">@lang('product.bol_ordered_tomorrow')</label>
                            </div>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <div class="form-check">
                                <input type="hidden" name="bolOrderBefore" value="0">
                                <input type="checkbox" class="form-check-input bolOrderBefore" id="updateBolOrderBefore" value="1" name="bolOrderBefore">
                                <label class="form-check-label bolOrderBefore" for="updateBolOrderBefore">@lang('product.bol_ordered_before')</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <div class="form-check">
                                <input type="hidden" name="bolLetterboxPackage" value="0">
                                <input type="checkbox" class="form-check-input bolLetterboxPackage" id="updateBolLetterboxPackage" value="1" name="bolLetterboxPackage">
                                <label class="form-check-label bolLetterboxPackage" for="updateBolLetterboxPackage">@lang('product.bol_letterbox_package')</label>
                            </div>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <div class="form-check">
                                <input type="hidden" name="bolLetterboxPackageUp" value="0">
                                <input type="checkbox" class="form-check-input bolLetterboxPackageUp" id="updateBolLetterboxPackageUp" value="1" name="bolLetterboxPackageUp">
                                <label class="form-check-label bolLetterboxPackageUp" for="updateBolLetterboxPackageUp">@lang('product.bol_letterbox_package_up')</label>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6 form-group">
                            <input type="hidden" name="bolPickUpOnly" value="0">
                            <label for="updateBolPickUpOnly" class="bolPickUpOnly">{{ __('product.bol_pick_up_only') }}</label>
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" name="bolPickUpOnly" id="updateBolPickUpOnly" value="1">
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                        </div>
                    </div>

                    {{-- Hidden Fields --}}
                    <input type="hidden" id="updateProductId" name="product_id" value="" />
                    <input type="hidden" name="priceNet" id="updatePriceNet" class="form-control" step="any">
                    <input type="hidden" name="purchasePrice" id="updatePurchasePriceGross" class="form-control" step="any">
                    <input type="hidden" name="listPriceNet" id="updateListPriceNet" class="form-control" step="any">

                    <button type="submit" class="btn btn-success w-100 mt-2" id="saveProductUpdate">{{ __('product.submit') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>