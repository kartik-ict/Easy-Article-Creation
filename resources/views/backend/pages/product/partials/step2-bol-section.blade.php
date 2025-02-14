
<div class="form-group mt-4 col-12" style="display:none;" id="bolSection">
    <div class="form-group row">
        <div class="form-group row col-12">
            <div class="col-12">
                <div id="productCategories" class="w-100">
                    <h5>{{ __('product.categories') }}</h5>
                </div>
                <div class="col-12 mb-4">
                    <a class="btn btn-xs btn-success"
                       id="searchSwCategory">{{ __('product.searchCategorySw') }}</a>
                </div>
            </div>
            <div class="form-group mb-3 col-12" id="bolCategorySelected">
                <label for="category">@lang('product.category'):</label>
                <select id="sw-category-select"
                        class="js-example-basic-single form-control"
                        name="category" required>
                </select>
            </div>

            <div class="form-group mb-3 col-12" style="display: none"
                 id="parentCategorySelect">
                <label for="category">@lang('product.categoryParent')</label>
                <select id="sw-parent-category-select"
                        class="js-example-basic-single form-control"
                        name="parentCategory" required>
                </select>

                <div>
                    <a class="btn btn-primary btn-next"
                       id="createSwCategory">{{ __('product.createCategorySw') }}</a>
                </div>
            </div>
        </div>

        <div class="form-group row col-12 mt-4">

            <div class="col-12">
                <div id="productManufacturer">
                    <p id="productManufacturerName">
                        <strong>{{ __('product.manufacturer') }}</strong>: <span
                                id="manufacturerValue">{{ $manufacturer ?? '' }}</span></p>
                </div>
                <div class="col-12 mb-4">
                    <a class="btn btn-success btn-xs" id="searchSwManufacturer">
                        {{ __('product.searchBrandSw') }}
                    </a>
                </div>
            </div>
            <div class="form-group mb-3 col-12">
                <label for="manufacturer">@lang('product.manufacturer'):</label>
                <select id="manufacturer-sw-search"
                        class="js-example-basic-single form-control"
                        name="manufacturer" required>
                </select>
            </div>
        </div>
    </div>
</div>


<!-- Back and Next Buttons -->
<button id="backBolBtn" class="btn btn-danger btn-back"
        style="display:none;">{{ __('product.previous') }}</button>
<button id="nextBolBtn" class="btn btn-primary btn-next"
        style="display:none;">{{ __('product.next') }}</button>