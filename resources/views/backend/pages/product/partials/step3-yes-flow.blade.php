<!-- Additional Section for Yes Selection (Initially hidden) -->

<div id="yesStepDetails" style="display:none;" class="step">
    <div class="step-header">{{ __('product.step3_show_product_details') }}</div>

    <table class="table table-bordered mt-3" id="productTable-update">
        <thead>
        <tr>
            <th>{{ __('product.product_name') }}</th>
            <th>{{ __('product.ean_number') }}</th>
            <th>{{ __('product.current_stock') }}</th>
            <th>{{ __('product.new_stock') }}</th>
            <th>{{ __('product.action') }}</th>
        </tr>
        </thead>
        <tbody>
        <!-- Dynamic rows will be inserted here -->
        </tbody>
    </table>


    <!-- Product Edit Modal -->
    <div class="modal fade" id="productEditModal" tabindex="-1" role="dialog"
         aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('product.step4') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="product-form" method="POST">
                        @csrf
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <h5 class="mb-3">{{ __('product.general_information') }}</h5>

                                <div class="form-group mb-3">
                                    <label for="name">@lang('product.name'):</label>
                                    <input type="text" class="form-control" id="name"
                                           name="name"
                                           required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="stock">@lang('product.stock'):</label>
                                    <input type="number" class="form-control" id="stock"
                                           name="stock" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label
                                            for="productEanNumber">@lang('product.bolProductEanNumber')
                                        :</label>
                                    <input type="text" class="form-control"
                                           id="productEanNumber"
                                           name="productEanNumber" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="manufacturer">@lang('product.manufacturer')
                                        :</label>
                                    <select id="manufacturer-select"
                                            class="js-example-basic-single form-control"
                                            name="manufacturer" required>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="taxId">@lang('product.tax_id'):</label>
                                    <select id="tax-provider-select"
                                            class="js-example-basic-single form-control"
                                            name="taxId" required>
                                    </select>
                                </div>

                                <div class="form-group mb-3">
                                    <label
                                            for="price_gross">{{ __('product.price_gross') }}</label>
                                    <input type="number" name="priceGross" id="priceGross"
                                           class="form-control" step="any" required
                                           placeholder="{{ __('product.enter_price_gross') }}">
                                </div>

                                <div class="form-group">
                                    <label
                                            for="price_net">{{ __('product.price_net') }}</label>
                                    <input type="number" name="priceNet" id="priceNet"
                                           class="form-control" step="any"
                                           placeholder="{{ __('product.calculated_price_net') }}">
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">
                                <h5 class="mb-3">{{ __('product.additional_information') }}</h5>

                                <div class="form-group mb-3">
                                    <label for="description">@lang('product.description')
                                        :</label>
                                    <textarea class="form-control" id="description"
                                              name="description" rows="5"></textarea>
                                </div>

                                <div class="form-group mb-3">
                                    <label
                                            for="productNumber">@lang('product.product_number')
                                        :</label>
                                    <input type="text" class="form-control"
                                           id="productNumber"
                                           name="productNumber" required>
                                </div>
                                <div class="form-group mb-3">
                                    <label
                                            for="productPackagingWidth">{{ __('product.productPackagingWidth') }}</label>
                                    <input type="text" name="productPackagingWidth"
                                           id="productPackagingWidth"
                                           class="form-control" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label
                                            for="productPackagingHeight">{{ __('product.productPackagingHeight') }}</label>
                                    <input type="text" name="productPackagingHeight"
                                           id="productPackagingHeight"
                                           class="form-control" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label
                                            for="productPackagingLength">{{ __('product.productPackagingLength') }}</label>
                                    <input type="text" name="productPackagingLength"
                                           id="productPackagingLength"
                                           class="form-control" required>
                                </div>

                                <div class="form-group mb-3">
                                    <label
                                            for="productPackagingWeight">{{ __('product.productPackagingWeight') }}</label>
                                    <input type="text" name="productPackagingWeight"
                                           id="productPackagingWeight"
                                           class="form-control" required>
                                </div>
                                <input type="hidden" id="productConfiguratorSettingsIds"
                                       name="productConfiguratorSettingsIds"/>
                                <div class="form-group mb-3">
                                    <strong>@lang('product.selectedGroup'):</strong> <span
                                            id="selectedPropertyGroupDisplay"></span><br>
                                    <strong>@lang('product.selectedPropertyGroup'):</strong>
                                    <span id="selectedPropertyOptionDisplay"></span>
                                </div>
                            </div>
                        </div>
                        <button type="submit"
                                class="btn btn-success w-100"
                                id="saveVariant">{{ __('product.submit') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Back and Next Buttons -->
    <button id="back3YesStep"
            class="btn btn-danger btn-back">{{ __('product.previous') }}</button>
    <button id="newVariantButton" class="btn btn-success btn-new-variant">
        {{ __('product.create_new_variant') }}
    </button>


    <div id="propertyGroupSection" style="display: none;" class="container mt-5">
        <h5 class="mb-4">@lang('product.select_required_info')</h5>

        <div class="row">
            <!-- Property Group Select -->
            <div class="col-md-6 mb-4">
                <div class="card p-3">
                    <label for="propertyGroupSelect"
                           class="font-weight-bold">@lang('product.property_select_group')</label>
                    <div class="d-flex align-items-center mt-2">
                        <select id="propertyGroupSelect"
                                class="form-control me-3 w-75"></select>
                    </div>
                </div>
            </div>

            <!-- Property Group Option Select -->
            <div id="propertyGroupOptionWrapper" class="col-md-6 mb-4"
                 style="display: none;">
                <div class="card p-3">
                    <label for="propertyGroupOptionSelect"
                           class="font-weight-bold">@lang('product.property_select_group_option')</label>
                    <div class="d-flex align-items-center mt-2">
                        <select id="propertyGroupOptionSelect"
                                class="form-control me-3 w-75"></select>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Property Group Select -->
            <div class="col-md-6 mb-4">
                <div class="card p-3">
                    <label for="propertyGroupSelectSecond"
                           class="font-weight-bold">@lang('product.property_select_group')</label>
                    <div class="d-flex align-items-center mt-2">
                        <select id="propertyGroupSelectSecond"
                                class="form-control me-3 w-75"></select>
                    </div>
                </div>
            </div>

            <!-- Property Group Option Select -->
            <div id="propertyGroupOptionWrapperSecond" class="col-md-6 mb-4"
                 style="display: none;">
                <div class="card p-3">
                    <label for="propertyGroupOptionSelectSecond"
                           class="font-weight-bold">@lang('product.property_select_group_option')</label>
                    <div class="d-flex align-items-center mt-2">
                        <select id="propertyGroupOptionSelectSecond"
                                class="form-control me-3 w-75"></select>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Property Group Select -->
            <div class="col-md-6 mb-4">
                <div class="card p-3">
                    <label for="propertyGroupSelectThird"
                           class="font-weight-bold">@lang('product.property_select_group')</label>
                    <div class="d-flex align-items-center mt-2">
                        <select id="propertyGroupSelectThird"
                                class="form-control me-3 w-75"></select>
                    </div>
                </div>
            </div>

            <!-- Property Group Option Select -->
            <div id="propertyGroupOptionWrapperThird" class="col-md-6 mb-4"
                 style="display: none;">
                <div class="card p-3">
                    <label for="propertyGroupOptionSelectThird"
                           class="font-weight-bold">@lang('product.property_select_group_option')</label>
                    <div class="d-flex align-items-center mt-2">
                        <select id="propertyGroupOptionSelectThird"
                                class="form-control me-3 w-75"></select>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Property Group Select -->
            <div class="col-md-6 mb-4">
                <div class="card p-3">
                    <label for="propertyGroupSelectFour"
                           class="font-weight-bold">@lang('product.property_select_group')</label>
                    <div class="d-flex align-items-center mt-2">
                        <select id="propertyGroupSelectFour"
                                class="form-control me-3 w-75"></select>
                    </div>
                </div>
            </div>

            <!-- Property Group Option Select -->
            <div id="propertyGroupOptionWrapperFour" class="col-md-6 mb-4"
                 style="display: none;">
                <div class="card p-3">
                    <label for="propertyGroupOptionSelectFour"
                           class="font-weight-bold">@lang('product.property_select_group_option')</label>
                    <div class="d-flex align-items-center mt-2">
                        <select id="propertyGroupOptionSelectFour"
                                class="form-control me-3 w-75"></select>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Property Group Select -->
            <div class="col-md-6 mb-4">
                <div class="card p-3">
                    <label for="propertyGroupSelectFive"
                           class="font-weight-bold">@lang('product.property_select_group')</label>
                    <div class="d-flex align-items-center mt-2">
                        <select id="propertyGroupSelectFive"
                                class="form-control me-3 w-75"></select>
                    </div>
                </div>
            </div>

            <!-- Property Group Option Select -->
            <div id="propertyGroupOptionWrapperFive" class="col-md-6 mb-4"
                 style="display: none;">
                <div class="card p-3">
                    <label for="propertyGroupOptionSelectFive"
                           class="font-weight-bold">@lang('product.property_select_group_option')</label>
                    <div class="d-flex align-items-center mt-2">
                        <select id="propertyGroupOptionSelectFive"
                                class="form-control me-3 w-75"></select>
                    </div>
                </div>
            </div>
        </div>
        <!--...................-->
        <div id="addPropertyOptionWrapper" class="mt-4 d-flex align-items-center gap-2"
             style="visibility : hidden;">
            <div class="col-md-3">
                <button id="addPropertyOptionBtn"
                        class="btn btn-success w-100">@lang('product.add_option')</button>
            </div>
            <div class="col-md-3">
                <button id="createPropertyGroupOptionBtn"
                        class="btn btn-primary w-100">@lang('product.create_new_property')</button>
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
                <h5 class="modal-title"
                    id="createPropertyGroupOptionModalLabel">@lang('product.create_new_property_option')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Display selected Property Group Name -->
                <div class="mb-3">
                    <div class="card p-3">
                        <label for="propertyGroupSelectSix"
                               class="font-weight-bold">@lang('product.property_select_group')</label>
                        <div class="d-flex align-items-center mt-2">
                            <select id="propertyGroupSelectSix"
                                    class="form-control me-3 w-75"></select>
                        </div>
                    </div>

                    <!-- New Property Option Name -->
                    <div class="card p-3">
                        <label for="newPropertyOptionName"
                               class="form-label">@lang('product.new_property_option_name')</label>
                        <input type="text" id="newPropertyOptionName" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger"
                        data-bs-dismiss="modal">@lang('product.cancel')</button>
                <button type="button" class="btn btn-primary"
                        id="savePropertyGroupOptionBtn">@lang('product.save')</button>
            </div>
        </div>
    </div>
</div>