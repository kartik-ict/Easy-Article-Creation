<!-- Step 3: Update Stock (Initially hidden) -->
<div id="step3" style="display: none;" class="step">
    <div class="step-header">Step 3: {{ __('product.update_stock') }}</div>
    <div id="step3Content">
        <!-- Product Table with Stock Information -->
        <table class="table table-bordered mt-3" id="productTable">
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

        <!-- Back and Update Data Buttons -->
        <button id="backStep2Btn"
                class="btn btn-danger btn-back">{{ __('product.previous') }}</button>
        {{--                            <button id="updateDataBtn" class="btn btn-primary btn-next">{{ __('product.update_stock') }}</button>--}}
    </div>
</div>