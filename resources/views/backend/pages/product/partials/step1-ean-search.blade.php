<!-- Step 1: Search EAN -->
<div id="step1" class="step">
    <div class="step-header">Step 1: {{ __('product.enter_ean') }}</div>
    <div class="form-group col-6">
        <input type="text" id="ean" class="form-control"
               placeholder="{{ __('product.enter_ean') }}">
    </div>
    <button id="searchBtn" class="btn btn-primary mt-2">{{ __('product.search') }}</button>
</div>
