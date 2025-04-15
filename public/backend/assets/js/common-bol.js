// Declare the necessary variables
const manufacturerSearchUrlbol = $('#route-container').data('manufacturer-search');
const swManufacturerSearchUrl = $('#route-container-sw-manufacturer-search').data('sw-manufacturer-search');
const categorySearchUrlbol = $('#route-container-category').data('category-search');
const categoryCreateUrlbol = $('#route-container-sw-create-category').data('sw-create-category');
const swCategorySearchUrl = $('#route-container-sw-category-search').data('sw-category-search');
const saveBolData = $('#route-container-save-bol-data').data('save-bol-data');
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
        data: function (params) {
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
        processResults: function (data) {
            // Check if we've reached the end of the results
            isEndOfResultsManufacturer = (data.manufacturers.length < 25);

            // Map results to Select2 format
            const results = data.manufacturers.map(function (manufacturer) {
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
        searching: function () {
            return "Zoeken, even geduld..."; // Dutch translation for "searching"
        },
        loadingMore: function () {
            return "Meer resultaten laden..."; // Dutch translation for "loading more results"
        },
        noResults: function () {
            return "Geen resultaten gevonden."; // Dutch translation for "no results found"
        }
    },

    // comment due to popup modal
    // Add this to ensure Select2 works inside the modal
    // dropdownParent: $('#productEditModal'),
});

// When dropdown is opened, reset the page number and flags
$('#manufacturer-sw-search').on('select2:open', function () {
    currentPageManufacturer = 1; // Start from page 1
    isLoadingManufacturer = false;
    isEndOfResultsManufacturer = false;

    const dropdown = $('.select2-results__options');

    // Scroll event handler to trigger the next API request when scrolling to the bottom
    dropdown.on('scroll', function () {
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
$('#manufacturer-sw-search').on('select2:close', function () {
    // Reset page when dropdown is closed, if needed
    currentPageManufacturer = 1;
    isLoadingManufacturer = false; // Reset loading flag when dropdown closes
    isEndOfResultsManufacturer = false; // Reset end of results flag
});

$('#searchSwManufacturer').on('click', function () {
    $('#full-page-preloader').show();
    const productManufacturer = $('#manufacturerValue').text();
    console.log(productManufacturer);
    // Show loader while fetching product data
    // $('#productDetails').html('<div class="loader"></div>');

    // Fetch Manufacturer data

    $.ajax({
        url: swManufacturerSearchUrl,
        method: "POST",
        headers: {'X-CSRF-TOKEN': csrfToken},
        data: {productManufacturer: productManufacturer},
        success: function (response) {
            if (response.productManufacturer) {
                const manufacturerData = response.productManufacturer;
                const manufacturerName = manufacturerData[0].attributes.translated.name;
                const manufacturerId = manufacturerData[0].id;
                const newOption = new Option(manufacturerName, manufacturerId, true, true);
                $('#full-page-preloader').hide();
                $('#manufacturer-sw-search').append(newOption).trigger('change');

            }
        },
        error: function (xhr, status, error) {
            $('#full-page-preloader').hide();
            console.error('AJAX Error:', error);
        }
    });
});


/* Category Dropdown */
// Category API
let currentPageBolCategory = 1; // Current page for pagination
let isLoadingBolCategory = false; // Prevent multiple concurrent requests
let isEndOfResultsBolCategory = false; // Flag to indicate end of results

$('#sw-category-select').select2({
    placeholder: 'Categorie',
    ajax: {
        url: categorySearchUrlbol,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        delay: 250,
        data: function (params) {
            // Prepare data for the API request
            if (params.term) {
                currentPageBolCategory = 1; // Reset page to 1 when a term is typed
            }

            return {
                page: currentPageBolCategory, // Send the current page
                limit: 25, // Limit the results per page
                term: params.term || '', // Search term entered by the user
                'total-count-mode': 1 // Fetch total count if needed
            };
        },
        processResults: function (data) {
            // Check if we've reached the end of the results
            isEndOfResultsBolCategory = (data.categories.length < 25);

            // Map results to Select2 format
            const results = data.categories.map(function (category) {
                return {
                    id: category.id,
                    text: category.attributes.breadcrumb
                        ? category.attributes.breadcrumb.filter(item => !["Default", "Shop"].includes(item)).join(' > ')
                        : category.attributes.translated.name
                };
            });

            return {
                results: results,
                pagination: {
                    more: !isEndOfResultsBolCategory // Show 'more' if there are more results
                }
            };
        },
        cache: true,
    },
    minimumInputLength: 0,
    allowClear: true,
    multiple: false, // Enable multiple selection
    language: {
        searching: function () {
            return "Zoeken, even geduld..."; // Dutch translation for "searching"
        },
        loadingMore: function () {
            return "Meer resultaten laden..."; // Dutch translation for "loading more results"
        },
        noResults: function () {
            return "Geen resultaten gevonden."; // Dutch translation for "no results found"
        }
    }
});

// When dropdown is opened, reset the page number and flags
$('#sw-category-select').on('select2:open', function () {
    currentPageBolCategory = 1; // Start from page 1
    isLoadingBolCategory = false;
    isEndOfResultsBolCategory = false;

    const dropdown = $('.select2-results__options');

    // Scroll event handler to trigger the next API request when scrolling to the bottom
    dropdown.on('scroll', function () {
        const scrollTop = dropdown.scrollTop();
        const containerHeight = dropdown.innerHeight();
        const scrollHeight = dropdown[0].scrollHeight;

        // If we're at the bottom of the dropdown and more results are available
        if (scrollTop + containerHeight >= scrollHeight - 10 && !isEndOfResultsBolCategory && !isLoadingBolCategory) {
            isLoadingBolCategory = true; // Set loading flag to true to prevent multiple requests

            currentPageBolCategory++;

            // Trigger the next page load
            $.ajax({
                url: '{{ route("product.categorySearch") }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                dataType: 'json',
                data: {
                    page: currentPageBolCategory,
                    limit: 25,
                    term: $('.select2-search__field').val() || '',
                    'total-count-mode': 1
                },
                success: function (data) {
                    const results = data.categories.map(function (category) {
                        return {
                            id: category.id,
                            text: category.attributes.breadcrumb
                                ? category.attributes.breadcrumb.filter(item => !["Default", "Shop"].includes(item)).join(' > ')
                                : category.attributes.translated.name
                        };
                    });

                    results.forEach(function (result) {
                        const option = new Option(result.text, result.id, false, false);
                        $('#sw-category-select').append(option);
                    });

                    isEndOfResultsBolCategory = (data.categories.length < 25);
                },
                complete: function () {
                    isLoadingBolCategory = false; // Reset loading flag
                }
            });
        }
    });
});

// Optionally, handle closing the dropdown manually if required
$('#sw-category-select').on('select2:close', function () {
    // Reset page when dropdown is closed, if needed
    currentPageBolCategory = 1;
    isLoadingBolCategory = false; // Reset loading flag when dropdown closes
    isEndOfResultsBolCategory = false; // Reset end of results flag
});
/* Category Dropdown */


/* parent Category Dropdown */
// Category API
$('#sw-parent-category-select').select2({
    placeholder: 'Categorie',
    ajax: {
        url: categorySearchUrlbol,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        delay: 250,
        data: function (params) {
            if (params.term) {
                currentPageBolCategoryParent = 1; // Reset page when searching
            }
            return {
                page: currentPageBolCategoryParent,
                limit: 25,
                term: params.term || '',
                'total-count-mode': 1
            };
        },
        processResults: function (data) {
            isEndOfResultsBolCategoryParent = (data.categories.length < 25);
            const results = data.categories.map(category => ({
                id: category.id,
                text: category.attributes.breadcrumb
                    ? category.attributes.breadcrumb.filter(item => !["Default", "Shop"].includes(item)).join(' > ')
                    : category.attributes.translated.name
            }));

            return {
                results: results,
                pagination: {
                    more: !isEndOfResultsBolCategoryParent
                }
            };
        },
        cache: true,
    },
    minimumInputLength: 0,
    allowClear: true,
    multiple: false, // Ensure single selection
    language: {
        searching: function () {
            return "Zoeken, even geduld...";
        },
        loadingMore: function () {
            return "Meer resultaten laden...";
        },
        noResults: function () {
            return "Geen resultaten gevonden.";
        }
    }
});

// Reset pagination when opening the dropdown
$('#sw-parent-category-select').on('select2:open', function () {
    currentPageBolCategoryParent = 1;
    isLoadingBolCategoryParent = false;
    isEndOfResultsBolCategoryParent = false;
});

// Reset flags on dropdown close
$('#sw-parent-category-select').on('select2:close', function () {
    currentPageBolCategoryParent = 1;
    isLoadingBolCategoryParent = false;
    isEndOfResultsBolCategoryParent = false;
});
/* parent Category Dropdown */

/* Category button click */
$('#searchSwCategory').on('click', function () {
    $('#full-page-preloader').show();
    const productCategoriesElement = document.getElementById('bolCat');
    const productCategoriesText = productCategoriesElement.innerText;
// Split the string by commas and trim whitespace
    const categoriesArray = productCategoriesText.split(',').map(category => category.trim());

// Get the last value
    const lastCategory = categoriesArray[categoriesArray.length - 1];

    $.ajax({
        url: swCategorySearchUrl,
        method: "POST",
        headers: {'X-CSRF-TOKEN': csrfToken},
        data: {lastCategory: lastCategory},
        success: function (response, status, xhr) {

            if (response.message == "createNew") {
                $("#bolCategorySelected").hide(); // Hide bolCategorySelected
                $("#parentCategorySelect").show(); // Show parentCategorySelect
            }
            // Check if response is actually empty
            if (!response) {
                console.error('Empty response received from server');
                return;
            }

            // if (response.productCategory && Array.isArray(response.productCategory) && response.productCategory.length > 0) {
            const categoryName = response.productCategory[0]?.attributes?.translated?.name;
            const categoryId = response.productCategory[0]?.id;

            if (categoryName && categoryId) {
                const newOption = new Option(categoryName, categoryId, true, true);
                $('#sw-category-select').append(newOption).trigger('change');
            }
            $('#full-page-preloader').hide();
        },
        error: function (xhr, status, error) {
            $('#full-page-preloader').hide();
            alert('Category not found');
        }
    });


});
/* Category  button click */

$('#createSwCategory').on('click', function () {
    $('#full-page-preloader').show();
    const productCategoriesElement = document.getElementById('sw-parent-category-select');
    const parentCategoriesValue = productCategoriesElement.value; // Get the selected category ID
    const productCategoriesElements = document.getElementById('bolCat');
    const productCategoriesText = productCategoriesElements.innerText;
    // Split the string by commas and trim whitespace
    const categoriesArray = productCategoriesText.split(',').map(category => category.trim());
    // Get the last value
    const lastCategory = categoriesArray[categoriesArray.length - 1];

    $.ajax({
        url: categoryCreateUrlbol,
        method: "POST",
        headers: {'X-CSRF-TOKEN': csrfToken},
        data: {parentCategoriesValue: parentCategoriesValue, lastCategory: lastCategory},
        success: function (response, status, xhr) {

            // Check if response is actually empty
            if (!response) {
                // console.error('Empty response received from server');
                return;
            }
            $("#bolCategorySelected").show(); // Hide bolCategorySelected
            $("#parentCategorySelect").hide(); // Show parentCategorySelect
            // if (response.productCategory && Array.isArray(response.productCategory) && response.productCategory.length > 0) {
            const categoryName = response.productCategory[0]?.attributes?.translated?.name;
            const categoryId = response.productCategory[0]?.id;
            if (categoryName && categoryId) {
                const newOption = new Option(categoryName, categoryId, true, true);
                $('#sw-category-select').append(newOption).trigger('change');
                $('#full-page-preloader').hide();
            } else {
                $('#full-page-preloader').hide();
                alert('Category Name or ID missing');
            }
            // }
        },
        error: function (xhr, status, error) {
            $('#full-page-preloader').hide();
            alert('Category Name or ID missing');

        }
    });
});


// Handle Next button (Step 2 -> Step 3)
$('#nextBolBtn').on('click', function () {

    const selectedManufacturer = document.getElementById('manufacturer-sw-search').value;
    const selectedManufacturerName = $('#manufacturer-sw-search option:selected').text();

    const selectedCategory = document.getElementById('sw-category-select').value;
    const ean = document.getElementById('ean').value;
    const selectedCategoryName = $('#sw-category-select option:selected').text();

    if (!selectedManufacturer || !selectedCategory) {
        alert(window.selectCategoryAlertError);
        return;
    }
    $('#full-page-preloader').show();
    // Transition to Step 3
    $('#step2').hide();
    $('#stepBol3').show();


    if (Array.isArray(bolApiResponse.product.productData)) {
        bolApiResponse.product.productData.forEach(product => {
            $('#bolProductName').val(product.title);
            $('#bolProductEanNumber').val(ean);
            $('#bolProductSku').val(product.sku);
            $('#bolProductManufacturer').val(selectedManufacturerName);
            $('#bolProductManufacturerId').val(selectedManufacturer);
            $('#bolProductCategories').val(selectedCategoryName);
            $('#bolProductCategoriesId').val(selectedCategory);
            $('#bolProductDescription').val(product.description);
            if (product.specs && typeof product.specs["Verpakking breedte"] === "string") {
                $('#bolPackagingWidth').val(product.specs["Verpakking breedte"].match(/\d+/) ? parseInt(product.specs["Verpakking breedte"].match(/\d+/)[0], 10) : 0);
            } else {
                $('#bolPackagingWidth').val(0);
            }

            if (product.specs && typeof product.specs["Verpakking hoogte"] === "string") {
                $('#bolPackagingHeight').val(product.specs["Verpakking hoogte"].match(/\d+/) ? parseInt(product.specs["Verpakking hoogte"].match(/\d+/)[0], 10) : 0);
            } else {
                $('#bolPackagingHeight').val(0);
            }

            if (product.specs && typeof product.specs["Verpakking lengte"] === "string") {
                $('#bolPackagingLength').val(product.specs["Verpakking lengte"].match(/\d+/) ? parseInt(product.specs["Verpakking lengte"].match(/\d+/)[0], 10) : 0);
            } else {
                $('#bolPackagingLength').val(0);
            }

            if (product.specs && typeof product.specs["Verpakkingsgewicht"] === "string") {
                $('#bolPackagingWeight').val(product.specs["Verpakkingsgewicht"].match(/\d+/) ? parseInt(product.specs["Verpakkingsgewicht"].match(/\d+/)[0], 10) : 0);
            } else {
                $('#bolPackagingWeight').val(0);
            }
            $('#bolProductThumbnail').attr('src', product.thumbnail);
        });

        const taxRate = 21;//bolApiResponse.product.taxData.attributes.taxRate;
        const bolOffers = bolApiResponse.product.productPriceData
            .map(product => product.offers)
            .flat()
            .filter(offer => offer.sellerName === "Bol");


        bolOffers.forEach(productPrice => {
            const priceNet = productPrice.price / (1 + taxRate / 100);
            $('#bolProductPrice').val(productPrice.price);
            $('#bolTotalPrice').val(priceNet.toFixed(2));
            if (productPrice.availability === 'InStock') {
                $('#bolAvailable').prop('checked', true);
                $('#bolAvailable').val('1');
            }
            $("#bolProductListPriceGross").val(productPrice.price);
            $("#bolProductListPriceNet").val(priceNet.toFixed(2));
        });
        $('#full-page-preloader').hide();
    }
});

$('#saveBolProductData').on('click', function (e) {
    $('#full-page-preloader').show();
    e.preventDefault(); // Prevent default form submission

    const thumbnailUrl = $('#bolProductThumbnail').attr('src'); // Assuming this is the correct image URL
    const formData = $('#bol-product-form').serialize() + '&bolProductThumbnail=' + encodeURIComponent(thumbnailUrl);
    // const formData = $('#bol-product-form').serialize(); // Serialize the entire form data

    $.ajax({
        url: saveBolData,
        method: 'POST',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': csrfToken_common
        },
        dataType: 'json',
        success: function (response) {

            if (response.success == true) {
                location.reload();
                $('#full-page-preloader').hide();
                alert(window.selectGradeAlert);
            }
        },
        error: function (error) {
            $('#full-page-preloader').hide();
            alert(window.selectGradeAlertError);
            // Handle error (e.g., show an error message)
            // alert('An error occurred while saving the product variant.');
        }
    });
});

// gross price to net price convert
$('#bolProductPrice').on('input', function () {
    const priceGross = parseFloat($(this).val()) || 0;
    const taxRate = parseFloat($(this).data('taxRate'));
    // Calculate net price
    const priceNet = priceGross / (1 + taxRate / 100);
    $('#bolTotalPrice').val(priceNet.toFixed(2));
});

// net price to gross price convert
$('#bolTotalPrice').on('input', function() {
    const priceNet = parseFloat($(this).val()) || 0;
    const taxRate = parseFloat($('#bolProductPrice').data('taxRate'));
    // Calculate gross price
    const priceGross = priceNet * (1 + taxRate / 100);
    $('#bolProductPrice').val(priceGross.toFixed(2));
});

// gross price to net price convert
$('#bolProductListPriceGross').on('input', function () {
    const priceGross = parseFloat($(this).val()) || 0;
    const taxRate = parseFloat($(this).data('taxRate'));

    // Calculate net price
    const priceNet = priceGross / (1 + taxRate / 100);
    $('#bolProductListPriceNet').val(priceNet.toFixed(2));
});

// net price to gross price convert
$('#bolProductListPriceNet').on('input', function() {
    const priceNet = parseFloat($(this).val()) || 0;
    const taxRate = parseFloat($('#bolProductListPriceGross').data('taxRate'));

    // Calculate gross price
    const priceGross = priceNet * (1 + taxRate / 100);
    $('#bolProductListPriceGross').val(priceGross.toFixed(2));
});

// Tax Provider API
$('#tax-provider-select-bol').select2({
    ajax: {
        url: taxSearchUrl,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        data: function (params) {
            return {
                term: params.term, // Search term
                page: params.page || 1
            };
        },
        processResults: function (data) {
            return {
                results: data.taxProviders.map(function (provider) {
                    return {
                        id: provider.id,
                        text: `${provider.attributes.name} (${provider.attributes.taxRate}%)`,
                        taxRate: provider.attributes.taxRate
                    };
                })
            };
        }
    },
    placeholder: 'Selecteer een belastingtarief',
    minimumResultsForSearch: Infinity,
    language: {
        searching: function () {
            return "Zoeken, even geduld...";
        },
        loadingMore: function () {
            return "Meer resultaten laden...";
        },
        noResults: function () {
            return "Geen resultaten gevonden.";
        }
    },
}).on('select2:select', function (e) {
    const selectedTaxRate = e.params.data.taxRate || 0;
    $('#bolProductPrice').data('taxRate', selectedTaxRate);
    $('#bolProductListPriceGross').data('taxRate', selectedTaxRate);
    $('#bolProductPrice').trigger('input');
    $('#bolProductListPriceGross').trigger('input');
});

// Load tax rates and set default on page load
$.ajax({
    url: taxSearchUrl,
    type: 'POST',
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    dataType: 'json',
    success: function(data) {
        const taxProviders = data.taxProviders.map(provider => ({
            id: provider.id,
            text: `${provider.attributes.name} (${provider.attributes.taxRate}%)`,
            taxRate: provider.attributes.taxRate
        }));

        // Find 21% tax rate option
        const defaultTax = taxProviders.find(provider => provider.taxRate === 21);

        if(defaultTax) {
            // Set default option
            const newOption = new Option(defaultTax.text, defaultTax.id, true, true);
            $('#tax-provider-select-bol').append(newOption).trigger('change');

            // Set tax rate and trigger calculation
            $('#bolProductPrice').data('taxRate', defaultTax.taxRate);
            $('#bolProductListPriceGross').data('taxRate', defaultTax.taxRate);
            $('#bolProductPrice').trigger('input');
        }
    }
});



