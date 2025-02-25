<!-- Step 2: Show Product Data (Initially hidden) -->
<div id="step2" style="display: none;" class="step">
    <div class="step-header">Step 2: {{ __('product.product_details') }}</div>
    <div id="productDetails">
        <!-- Loading Spinner initially -->
        {{--                                    <div class="loader"></div>--}}
    </div>
    <!-- Ask about grade -->
    <div class="form-group mt-4 col-6" style="display:none;" id="gradeSection">
        <label>{{ __('product.is_different_grade') }}</label>
        <select id="differentGrade" class="form-control">
            <option value="" selected
                    disabled>{{ __('product.select_grade_alert') }}</option>
            <option value="no">{{ __('product.no') }}</option>
            <option value="yes">{{ __('product.yes') }}</option>
        </select>
    </div>

    <!-- Back and Next Buttons -->
    <button id="backBtn" class="btn btn-danger btn-back"
            style="display:none;">{{ __('product.previous') }}</button>
    <button id="nextBtn" class="btn btn-primary btn-next"
            style="display:none;">{{ __('product.next') }}</button>

    @include('backend.pages.product.partials.step2-bol-section')

</div>