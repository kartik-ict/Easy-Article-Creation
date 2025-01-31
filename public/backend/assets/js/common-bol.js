// Declare the necessary variables
const manufacturerSearchUrlbol = $('#route-container').data('manufacturer-search');
const swManufacturerSearchUrl = $('#route-container-sw-manufacturer-search').data('sw-manufacturer-search');
const categorySearchUrlbol = $('#route-container-category').data('category-search');
const csrfToken = $('meta[name="csrf-token"]').attr('content');
let currentPageManufacturer = 1; // Start from the first page
let isLoadingManufacturer = false;
let isEndOfResultsManufacturer = false;

$('#manufacturer-sw-search').select2({
        placeholder: 'Fabrikant',
        ajax: {
            url: manufacturerSearchUrlbol,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            delay: 250,
            data: function(params) {
                // Prepare data for the API request
                if (params.term) {
                    currentPageManufacturer = 1; // Reset page to 1 when a term is typed
                }

                return {
                    page: currentPageManufacturer, // Send the current page
                    limit: 25, // Limit the results per page
                    term: params.term || '', // Search term entered by the user
                    'total-count-mode': 1 // Fetch total count if needed
                };
            },
            processResults: function(data) {
                // Check if we've reached the end of the results
                isEndOfResultsManufacturer = (data.manufacturers.length < 25);

                // Map results to Select2 format
                const results = data.manufacturers.map(function(manufacturer) {
                    return {
                        id: manufacturer.id,
                        text: manufacturer.attributes.translated.name
                    };
                });
                return {
                    results: results,
                    pagination: {
                        more: !isEndOfResultsManufacturer // Show 'more' if there are more results
                    }
                };
            },
            cache: true,
        },
        minimumInputLength: 0,
        allowClear: true,
        language: {
            searching: function() {
                return "Zoeken, even geduld..."; // Dutch translation for "searching"
            },
            loadingMore: function() {
                return "Meer resultaten laden..."; // Dutch translation for "loading more results"
            },
            noResults: function() {
                return "Geen resultaten gevonden."; // Dutch translation for "no results found"
            }
        },

        // comment due to popup modal
        // Add this to ensure Select2 works inside the modal
        // dropdownParent: $('#productEditModal'),
    });


// When dropdown is opened, reset the page number and flags
$('#manufacturer-sw-search').on('select2:open', function() {
    currentPageManufacturer = 1; // Start from page 1
    isLoadingManufacturer = false;
    isEndOfResultsManufacturer = false;

    const dropdown = $('.select2-results__options');

    // Scroll event handler to trigger the next API request when scrolling to the bottom
    dropdown.on('scroll', function() {
        const scrollTop = dropdown.scrollTop();
        const containerHeight = dropdown.innerHeight();
        const scrollHeight = dropdown[0].scrollHeight;

        // If we're at the bottom of the dropdown and more results are available
        if (scrollTop + containerHeight >= scrollHeight - 10 && !isEndOfResultsManufacturer) {
            isLoadingManufacturer = true; // Set loading flag to true to prevent multiple requests

            currentPageManufacturer++;

            // Trigger the next page load by opening the dropdown
            $('#manufacturer-sw-search').select2('open');
        }
    });
});

// Optionally, handle closing the dropdown manually if required
$('#manufacturer-sw-search').on('select2:close', function() {
    // Reset page when dropdown is closed, if needed
    currentPageManufacturer = 1;
    isLoadingManufacturer = false; // Reset loading flag when dropdown closes
    isEndOfResultsManufacturer = false; // Reset end of results flag
});

$('#searchSwManufacturer').on('click', function () {

    const productManufacturer = $('#manufacturerValue').text();

    // const productManufacturer = $('#productManufacturerName');
    // console.log(productManufacturer);
    // const productManufacturer = 'LEGO';
    // console.log(productManufacturer);
    // if (!productManufacturer) {
    //     // alert('{{ __('product.enter_ean_alert') }}');
    //     return;
    // }

    // Show loader while fetching product data
    // $('#productDetails').html('<div class="loader"></div>');

    // Fetch Manufacturer data

    $.ajax({
        url: swManufacturerSearchUrl,
        method: "POST",
        headers: { 'X-CSRF-TOKEN': csrfToken },
        data: { productManufacturer: productManufacturer },
        success: function (response) {
            // console.log('Success:', response);
            if(response.productManufacturer){
                const manufacturerData = response.productManufacturer;
                const manufacturerName = manufacturerData[0].attributes.translated.name;
                const manufacturerId = manufacturerData[0].id;
                const newOption = new Option(manufacturerName, manufacturerId, true, true);

                $('#manufacturer-sw-search').append(newOption).trigger('change');
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', error);
        }
    });
});


$('#searchSwCategory').on('click', function () {
    const productCategoriesElement = document.getElementById('bolCat');
    const productCategoriesText = productCategoriesElement.innerText;
// Split the string by commas and trim whitespace
    const categoriesArray = productCategoriesText.split(',').map(category => category.trim());

// Get the last value
    const lastCategory = categoriesArray[categoriesArray.length - 1];

    $.ajax({
        url: swManufacturerSearchUrl,
        method: "POST",
        headers: { 'X-CSRF-TOKEN': csrfToken },
        data: { lastCategory: lastCategory },
        success: function (response) {
            // console.log('Success:', response);
            if(response.productManufacturer){
                const manufacturerData = response.productManufacturer;
                const manufacturerName = manufacturerData[0].attributes.translated.name;
                const manufacturerId = manufacturerData[0].id;
                const newOption = new Option(manufacturerName, manufacturerId, true, true);

                $('#manufacturer-sw-search').append(newOption).trigger('change');
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error:', error);
        }
    });

});


//
// // Category API
// let currentPageCategory = 1; // Current page for pagination
// let isLoadingCategory = false; // Prevent multiple concurrent requests
// let isEndOfResultsCategory = false; // Flag to indicate end of results
//
// $('#category-select').select2({
//     placeholder: 'Categorie',
//     ajax: {
//         url: categorySearchUrlbol,
//         type: 'POST',
//         headers: {
//             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//         },
//         dataType: 'json',
//         delay: 250,
//         data: function(params) {
//             // Prepare data for the API request
//             if (params.term) {
//                 currentPageCategory = 1; // Reset page to 1 when a term is typed
//             }
//
//             return {
//                 page: currentPageCategory, // Send the current page
//                 limit: 25, // Limit the results per page
//                 term: params.term || '', // Search term entered by the user
//                 'total-count-mode': 1 // Fetch total count if needed
//             };
//         },
//         processResults: function(data) {
//             // Check if we've reached the end of the results
//             isEndOfResultsCategory = (data.categories.length < 25);
//
//             // Map results to Select2 format
//             const results = data.categories.map(function(category) {
//                 return {
//                     id: category.id,
//                     text: category.attributes.translated.name
//                 };
//             });
//
//             return {
//                 results: results,
//                 pagination: {
//                     more: !isEndOfResultsCategory // Show 'more' if there are more results
//                 }
//             };
//         },
//         cache: true,
//     },
//     minimumInputLength: 0,
//     allowClear: true,
//     multiple: true, // Enable multiple selection
//     language: {
//         searching: function() {
//             return "Zoeken, even geduld..."; // Dutch translation for "searching"
//         },
//         loadingMore: function() {
//             return "Meer resultaten laden..."; // Dutch translation for "loading more results"
//         },
//         noResults: function() {
//             return "Geen resultaten gevonden."; // Dutch translation for "no results found"
//         }
//     }
// });
//
// // When dropdown is opened, reset the page number and flags
// $('#category-select').on('select2:open', function() {
//     currentPageCategory = 1; // Start from page 1
//     isLoadingCategory = false;
//     isEndOfResultsCategory = false;
//
//     const dropdown = $('.select2-results__options');
//
//     // Scroll event handler to trigger the next API request when scrolling to the bottom
//     dropdown.on('scroll', function() {
//         const scrollTop = dropdown.scrollTop();
//         const containerHeight = dropdown.innerHeight();
//         const scrollHeight = dropdown[0].scrollHeight;
//
//         // If we're at the bottom of the dropdown and more results are available
//         if (scrollTop + containerHeight >= scrollHeight - 10 && !isEndOfResultsCategory && !isLoadingCategory) {
//             isLoadingCategory = true; // Set loading flag to true to prevent multiple requests
//
//             currentPageCategory++;
//
//             // Trigger the next page load
//             $.ajax({
//                 url: '{{ route("product.categorySearch") }}',
//                 type: 'POST',
//                 headers: {
//                     'X-CSRF-TOKEN': '{{ csrf_token() }}'
//                 },
//                 dataType: 'json',
//                 data: {
//                     page: currentPageCategory,
//                     limit: 25,
//                     term: $('.select2-search__field').val() || '',
//                     'total-count-mode': 1
//                 },
//                 success: function(data) {
//                     const results = data.categories.map(function(category) {
//                         return {
//                             id: category.id,
//                             text: category.attributes.translated.name
//                         };
//                     });
//
//                     results.forEach(function(result) {
//                         const option = new Option(result.text, result.id, false, false);
//                         $('#category-select').append(option).trigger('change');
//                     });
//
//                     isEndOfResultsCategory = (data.categories.length < 25);
//                 },
//                 complete: function() {
//                     isLoadingCategory = false; // Reset loading flag
//                 }
//             });
//         }
//     });
// });
//
// // Optionally, handle closing the dropdown manually if required
// $('#category-select').on('select2:close', function() {
//     // Reset page when dropdown is closed, if needed
//     currentPageCategory = 1;
//     isLoadingCategory = false; // Reset loading flag when dropdown closes
//     isEndOfResultsCategory = false; // Reset end of results flag
// });
