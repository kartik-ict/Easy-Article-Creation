// Declare the necessary variables
const manufacturerSearchUrl = $('#route-container').data('manufacturer-search');
const salesSearchUrl = $('#route-container-sales').data('sales-search');
const categorySearchUrl = $('#route-container-category').data('category-search');
const taxSearchUrl = $('#route-container-tax').data('tax-search');
const propertySearchUrl = $('#route-container-property').data('property-search');
const propertyOptionSearchUrl = $('#route-container-property-option').data('property-search-option');
const propertyOptionSave = $('#route-container-property-option-save').data('property-option-save');
const variantSave = $('#route-container-variant-save').data('variant-save');
const csrfToken_common = $('meta[name="csrf-token"]').attr('content');

let currentPage = 1; // Start from the first page
let isLoading = false;
let isEndOfResults = false;

$('#manufacturer-select').select2({
    placeholder: 'Fabrikant',
    ajax: {
        url: manufacturerSearchUrl,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        delay: 250,
        data: function (params) {
            // Prepare data for the API request
            if (params.term) {
                currentPage = 1; // Reset page to 1 when a term is typed
            }

            return {
                page: currentPage, // Send the current page
                limit: 25, // Limit the results per page
                term: params.term || '', // Search term entered by the user
                'total-count-mode': 1 // Fetch total count if needed
            };
        },
        processResults: function (data) {
            // Check if we've reached the end of the results
            isEndOfResults = (data.manufacturers.length < 25);

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
                    more: !isEndOfResults // Show 'more' if there are more results
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
    // Add this to ensure Select2 works inside the modal
    dropdownParent: $('#productEditModal'),
});


// When dropdown is opened, reset the page number and flags
$('#manufacturer-select').on('select2:open', function () {
    currentPage = 1; // Start from page 1
    isLoading = false;
    isEndOfResults = false;

    const dropdown = $('.select2-results__options');

    // Scroll event handler to trigger the next API request when scrolling to the bottom
    dropdown.on('scroll', function () {
        const scrollTop = dropdown.scrollTop();
        const containerHeight = dropdown.innerHeight();
        const scrollHeight = dropdown[0].scrollHeight;

        // If we're at the bottom of the dropdown and more results are available
        if (scrollTop + containerHeight >= scrollHeight - 10 && !isEndOfResults) {
            isLoading = true; // Set loading flag to true to prevent multiple requests

            currentPage++;

            // Trigger the next page load by opening the dropdown
            $('#manufacturer-select').select2('open');
        }
    });
});

// Optionally, handle closing the dropdown manually if required
$('#manufacturer-select').on('select2:close', function () {
    // Reset page when dropdown is closed, if needed
    currentPage = 1;
    isLoading = false; // Reset loading flag when dropdown closes
    isEndOfResults = false; // Reset end of results flag
});


let salesChannelPage = 1; // Current page for sales channel pagination
let isSalesChannelEnd = false; // End of results flag
let isSalesChannelLoading = false; // Prevent multiple requests

// Initialize Select2 for Sales Channel with loader message
$('#sales-channel-select').select2({
    placeholder: 'Verkoopkanaal',
    ajax: {
        url: salesSearchUrl,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        delay: 250,
        data: function (params) {
            if (params.term) {
                salesChannelPage = 1; // Reset page when search term changes
                isSalesChannelEnd = false;
            }
            return {
                page: salesChannelPage,
                limit: 25,
                term: params.term || '', // Search term
                'total-count-mode': 1
            };
        },
        processResults: function (data) {
            isSalesChannelEnd = data.salesChannels.length < 25; // Check if it's the end

            const results = data.salesChannels.map(function (salesChannel) {
                return {
                    id: salesChannel.id,
                    text: salesChannel.attributes.translated.name
                };
            });

            return {
                results: results,
                pagination: {
                    more: !isSalesChannelEnd
                }
            };
        },
        cache: true,
    },
    minimumInputLength: 0,
    allowClear: true,
    multiple: true, // Enable multiple selection
    language: {
        loadingMore: function () {
            return "@lang('product.loading_more')"; // Message when loading more results
        },
        searching: function () {
            return "@lang('product.searching')"; // Message during search
        },
        noResults: function () {
            return "@lang('product.no_results_found')"; // Message when no results found
        }
    }
});

// Custom handling of the loader when dropdown opens
$('#sales-channel-select').on('select2:open', function () {
    salesChannelPage = 1; // Reset pagination
    isSalesChannelEnd = false; // Reset end flag
    isSalesChannelLoading = false; // Reset loading flag

    // Add a custom loader or information text to the dropdown
    const dropdown = $('.select2-results__options');
    dropdown.html('<li class="select2-results__option" style="text-align: center;">Aan het laden...</li>'); // Custom loader
});

// Handle scroll for infinite loading
$('#sales-channel-select').on('select2:open', function () {
    const dropdown = $('.select2-results__options');

    dropdown.off('scroll').on('scroll', function () {
        const scrollTop = dropdown.scrollTop();
        const containerHeight = dropdown.innerHeight();
        const scrollHeight = dropdown[0].scrollHeight;

        // Load more data when scrolled to the bottom
        if (scrollTop + containerHeight >= scrollHeight - 10 && !isSalesChannelEnd && !isSalesChannelLoading) {
            isSalesChannelLoading = true; // Prevent multiple triggers
            salesChannelPage++; // Increment the page

            $.ajax({
                url: '{{ route("product.salesChannelSearch") }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                dataType: 'json',
                data: {
                    page: salesChannelPage,
                    limit: 25,
                    term: $('.select2-search__field').val() || '',
                    'total-count-mode': 1
                },
                success: function (data) {
                    const results = data.salesChannels.map(function (salesChannel) {
                        return {
                            id: salesChannel.id,
                            text: salesChannel.attributes.translated.name
                        };
                    });

                    // Append the results to the dropdown
                    results.forEach(function (result) {
                        const option = new Option(result.text, result.id, false, false);
                        $('#sales-channel-select').append(option);
                    });

                    isSalesChannelEnd = (data.salesChannels.length < 25); // Check if there are more results
                },
                complete: function () {
                    isSalesChannelLoading = false; // Reset loading flag
                }
            });
        }
    });
});

// Reset everything when dropdown closes
$('#sales-channel-select').on('select2:close', function () {
    salesChannelPage = 1; // Reset page
    isSalesChannelLoading = false; // Reset loading flag
    isSalesChannelEnd = false; // Reset end flag
});

// Category API

let currentPageCategory = 1; // Current page for pagination
let isLoadingCategory = false; // Prevent multiple concurrent requests
let isEndOfResultsCategory = false; // Flag to indicate end of results

$('#category-select-modal').select2({
    placeholder: 'Categorie',
    ajax: {
        url: categorySearchUrl,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        delay: 250,
        data: function (params) {
            // Prepare data for the API request
            if (params.term) {
                currentPageCategory = 1; // Reset page to 1 when a term is typed
            }

            return {
                page: currentPageCategory, // Send the current page
                limit: 25, // Limit the results per page
                term: params.term || '', // Search term entered by the user
                'total-count-mode': 1 // Fetch total count if needed
            };
        },
        processResults: function (data) {
            // Check if we've reached the end of the results
            isEndOfResultsCategory = (data.categories.length < 25);

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
                    more: !isEndOfResultsCategory // Show 'more' if there are more results
                }
            };
        },
        cache: true,
    },
    minimumInputLength: 0,
    allowClear: true,
    multiple: true, // Enable multiple selection
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
    dropdownParent: $('#productEditModal'),
});

// When dropdown is opened, reset the page number and flags
$('#category-select-modal').on('select2:open', function () {
    currentPageCategory = 1; // Start from page 1
    isLoadingCategory = false;
    isEndOfResultsCategory = false;

    const dropdown = $('.select2-results__options');

    // Scroll event handler to trigger the next API request when scrolling to the bottom
    dropdown.on('scroll', function () {
        const scrollTop = dropdown.scrollTop();
        const containerHeight = dropdown.innerHeight();
        const scrollHeight = dropdown[0].scrollHeight;

        // If we're at the bottom of the dropdown and more results are available
        if (scrollTop + containerHeight >= scrollHeight - 10 && !isEndOfResultsCategory && !isLoadingCategory) {
            isLoadingCategory = true; // Set loading flag to true to prevent multiple requests

            currentPageCategory++;

            // Trigger the next page load
            $.ajax({
                url: categorySearchUrl,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken_common
                },
                dataType: 'json',
                data: {
                    page: currentPageCategory,
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
                        $('#category-select-modal').append(option).trigger('change');
                    });

                    isEndOfResultsCategory = (data.categories.length < 25);
                },
                complete: function () {
                    isLoadingCategory = false; // Reset loading flag
                }
            });
        }
    });
});

// Optionally, handle closing the dropdown manually if required
$('#category-select-modal').on('select2:close', function () {
    // Reset page when dropdown is closed, if needed
    currentPageCategory = 1;
    isLoadingCategory = false; // Reset loading flag when dropdown closes
    isEndOfResultsCategory = false; // Reset end of results flag
});

// gross price to net price convert
$('#priceGross').on('input', function () {
    const priceGross = parseFloat($(this).val()) || 0;
    const taxRate = parseFloat($(this).data('taxRate')) || parseFloat($('#tax-provider-select').data('tax-rate')) || 21;
    // Calculate net price
    const priceNet = priceGross / (1 + taxRate / 100);
    $('#priceNet').val(priceNet.toFixed(2));
});

// net price to gross price convert
$('#priceNet').on('input', function () {
    const priceNet = parseFloat($(this).val()) || 0;
    const taxRate = parseFloat($('#priceGross').data('taxRate')) || 0;

    // Calculate gross price
    const priceGross = priceNet * (1 + taxRate / 100);
    $('#priceGross').val(priceGross.toFixed(2));
});

// net price to gross price convert for purchase price
$(document).on('input', '#swPurchasePriceNet, #purchasePriceNet', function() {
    const purchasePriceNet = parseFloat($(this).val()) || 0;
    const taxRate = parseFloat($(this).data('taxRate')) || parseFloat($('#tax-provider-select').data('tax-rate')) || 21;
    // Calculate gross price
    const purchasePrice = purchasePriceNet * (1 + taxRate / 100);
    $('#swPurchasePrice, #purchasePrice').val(purchasePrice.toFixed(2));
});
// gross price to net price convert
$(document).on('input', '#swPurchasePrice, #purchasePrice', function() {
    const purchasePrice = parseFloat($(this).val()) || 0;
    const taxRate = parseFloat($('#swPurchasePriceNet, #purchasePriceNet').data('taxRate')) || parseFloat($('#tax-provider-select').data('tax-rate')) || 21;

    // Calculate net price
    const purchasePriceNet = purchasePrice / (1 + taxRate / 100);
    $('#swPurchasePriceNet, #purchasePriceNet').val(purchasePriceNet.toFixed(2));
});

// gross price to net price convert
$('#listPriceGross').on('input', function () {
    const listPriceGross = parseFloat($(this).val()) || 0;
    const taxRate = parseFloat($(this).data('taxRate')) || parseFloat($('#tax-provider-select').data('tax-rate')) || 21;
    // Calculate net price
    const listPriceNet = listPriceGross / (1 + taxRate / 100);
    $('#listPriceNet').val(listPriceNet.toFixed(2));
});

// net price to gross price convert
$('#listPriceNet').on('input', function () {
    const listPriceNet = parseFloat($(this).val()) || 0;
    const taxRate = parseFloat($('#listPriceGross').data('taxRate')) || 0;

    // Calculate gross price
    const listPriceGross = listPriceNet * (1 + taxRate / 100);
    $('#listPriceGross').val(listPriceGross.toFixed(2));
});
// Tax Provider API

// $('#tax-provider-select').select2({
//     ajax: {
//         url: taxSearchUrl,
//         type: 'POST',
//         headers: {
//             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//         },
//         dataType: 'json',
//         data: function (params) {
//             return {
//                 term: params.term, // Search term
//                 page: params.page || 1
//             };
//         },
//         processResults: function (data) {
//             return {
//                 results: data.taxProviders.map(function (provider) {
//                     return {
//                         id: provider.id,
//                         text: `${provider.attributes.name} (${provider.attributes.taxRate}%)`,
//                         taxRate: provider.attributes.taxRate, // Include tax rate
//                     };
//                 })
//             };
//         }
//     },
//     placeholder: 'Selecteer een belastingtarief',
//     minimumResultsForSearch: Infinity,
//     language: {
//         searching: function () {
//             return "Zoeken, even geduld...";
//         },
//         loadingMore: function () {
//             return "Meer resultaten laden...";
//         },
//         noResults: function () {
//             return "Geen resultaten gevonden.";
//         }
//     },
//     dropdownParent: $('#productEditModal'),

// }).on('select2:select', function (e) {
//     const selectedTaxRate = e.params.data.taxRate || 0; // Get the selected tax rate

//     // Update the tax rate for calculation
//     $('#priceGross').data('taxRate', selectedTaxRate);
//     $('#priceGross').trigger('input');
// });
// Step 1: Fetch tax providers manually to prefill 21%
$.ajax({
    url: taxSearchUrl,
    type: 'POST',
    dataType: 'json',
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    data: {
        term: '',
        page: 1
    },
    success: function (data) {
        // Step 4: Init select2 with AJAX config
        $('#tax-provider-select').select2({
            ajax: {
                url: taxSearchUrl,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                data: function (params) {
                    return {
                        term: params.term,
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
            dropdownParent: $('#productEditModal'),
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
        }).on('select2:select', function (e) {
            const selectedTaxRate = e.params.data.taxRate || 0;
            $('#taxRate, #swTaxRate').val(selectedTaxRate); // Set the hidden input value
            // Set tax rate data attribute for both price fields
            $('#priceGross').data('taxRate', selectedTaxRate);
            $('#listPriceGross').data('taxRate', selectedTaxRate);
            $('#swPurchasePrice, #purchasePrice').data('taxRate', selectedTaxRate);

            // Store tax rate in a data attribute on the select element for easier access
            $(this).data('tax-rate', selectedTaxRate);

            // Trigger calculations
            $('#priceGross').trigger('input');
            $('#listPriceGross').trigger('input');
            $('#swPurchasePrice, #purchasePrice').trigger('input');
        });
        // Step 2: Find 21% tax rate
        const selectedProvider = data.taxProviders.find(provider => provider.attributes.taxRate === 21);

        if (selectedProvider) {
            const option = new Option(
                `${selectedProvider.attributes.name} (${selectedProvider.attributes.taxRate}%)`,
                selectedProvider.id,
                true,
                true
            );

            // Step 3: Add and trigger change
            $('#tax-provider-select').append(option).trigger('change');
            $('#taxRate, #swTaxRate').val(selectedProvider.attributes.taxRate); // Set the hidden input value
            // Set tax rate data attributes
            $('#priceGross').data('taxRate', selectedProvider.attributes.taxRate);
            $('#listPriceGross').data('taxRate', selectedProvider.attributes.taxRate);
            $('#swPurchasePrice,#purchasePrice').data('taxRate', selectedProvider.attributes.taxRate);
            $('#tax-provider-select').data('tax-rate', selectedProvider.attributes.taxRate);
            // Trigger calculations
            $('#priceGross').trigger('input');
            $('#listPriceGross').trigger('input');
            $('#swPurchasePrice,#purchasePrice').trigger('input');
        }

    }
});

$('#newVariantButton').click(function () {
    $('#propertyGroupSection').show();
});

// Fetch property groups
// Initialize Property Group Select
let currentPageOption = 1;
let isEndOfResultsOption = false;

function destroySelect2IfExists(selector) {
    if ($(selector).hasClass('select2-hidden-accessible')) {
        $(selector).select2('destroy');
    }
}

// Initialize Property Group Select
$('#propertyGroupSelect').select2({
    width: '50%',
    placeholder: 'Select Property Group',
    ajax: {
        url: propertySearchUrl,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        delay: 250,
        data: function (params) {
            if (params.term) {
                currentPage = 1;
            }
            return {
                page: currentPage,
                limit: 25,
                term: params.term || '',
                'total-count-mode': 1
            };
        },
        processResults: function (data) {
            isEndOfResults = data.propertyGroups.length < 25;
            const results = data.propertyGroups.map(group => ({
                id: group.id,
                text: group.attributes.translated.name
            }));
            return {
                results: results,
                pagination: {
                    more: !isEndOfResults
                }
            };
        },
        cache: true
    },
    minimumInputLength: 0,
    allowClear: true,
    language: {
        searching: () => 'Searching...',
        loadingMore: () => 'Loading more results...',
        noResults: () => 'No results found.'
    }
});

// Handle Property Group selection and reset Property Group Option
$('#propertyGroupSelect').change(function () {
    const groupId = $(this).val();

    // Clear previous options and disable the dropdown until options are loaded
    destroySelect2IfExists('#propertyGroupOptionSelect');
    $('#propertyGroupOptionSelect').empty().select2({
        width: '50%',
        placeholder: 'Select Property Group Option',
        allowClear: true
    });

    // Fetch new Property Group Options if a group is selected
    if (groupId) {
        fetchPropertyGroupOptions(groupId);
    }
});

// Fetch property group options and enable scroll API for pagination
function fetchPropertyGroupOptions(groupId) {
    currentPageOption = 1;
    isEndOfResultsOption = false;

    $('#propertyGroupOptionSelect').select2({
        width: '50%',
        placeholder: 'Select Property Group Option',
        ajax: {
            url: propertyOptionSearchUrl,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            contentType: 'application/json',
            delay: 250,
            data: function (params) {
                return JSON.stringify({
                    page: params.page || 1,
                    groupId : groupId,
                    limit: 25,
                    searchTerm: params.term
                });
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                isEndOfResultsOption = data.propertyGroups.length < 25;

                const results = data.propertyGroups.map(option => ({
                    id: option.id,
                    text: option.attributes.translated.name
                }));

                return {
                    results: results,
                    pagination: {
                        more: !isEndOfResultsOption
                    }
                };
            },
            cache: true
        },
        minimumInputLength: 0,
        allowClear: true,
        language: {
            noResults: () => 'No options found.'
        }
    });

    // Reset scrolling flags upon dropdown open and close
    $('#propertyGroupOptionSelect').on('select2:open', function () {
        currentPageOption = 1;
        isEndOfResultsOption = false;
    });
}

$('#propertyGroupSelect').change(function () {
    const selectedGroup = $(this).val();
    if (selectedGroup) {
        $('#propertyGroupOptionWrapper').show();
        $('#addPropertyOptionWrapper').css('visibility', 'visible');
    } else {
        $('#propertyGroupOptionWrapper').hide();
        $('#addPropertyOptionWrapper').css('visibility', 'hidden');
    }
});


/*second - COMMENTED OUT*/
/*
// Initialize Property Group Select
$('#propertyGroupSelectSecond').select2({
    width: '50%',
    placeholder: 'Select Property Group',
    ajax: {
        url: propertySearchUrl,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        delay: 250,
        data: function (params) {
            if (params.term) {
                currentPage = 1;
            }
            return {
                page: currentPage,
                limit: 25,
                term: params.term || '',
                'total-count-mode': 1
            };
        },
        processResults: function (data) {
            isEndOfResults = data.propertyGroups.length < 25;
            const results = data.propertyGroups.map(group => ({
                id: group.id,
                text: group.attributes.translated.name
            }));
            return {
                results: results,
                pagination: {
                    more: !isEndOfResults
                }
            };
        },
        cache: true
    },
    minimumInputLength: 0,
    allowClear: true,
    language: {
        searching: () => 'Searching...',
        loadingMore: () => 'Loading more results...',
        noResults: () => 'No results found.'
    }
});

// Handle Property Group selection and reset Property Group Option
$('#propertyGroupSelectSecond').change(function () {
    const groupId = $(this).val();

    // Clear previous options and disable the dropdown until options are loaded
    destroySelect2IfExists('#propertyGroupOptionSelectSecond');
    $('#propertyGroupOptionSelectSecond').empty().select2({
        width: '50%',
        placeholder: 'Select Property Group Option',
        allowClear: true
    });

    // Fetch new Property Group Options if a group is selected
    if (groupId) {
        fetchPropertyGroupOptionsSecond(groupId);
    }
});

// Fetch property group options and enable scroll API for pagination
function fetchPropertyGroupOptionsSecond(groupId) {
    currentPageOption = 1;
    isEndOfResultsOption = false;

    $('#propertyGroupOptionSelectSecond').select2({
        width: '50%',
        placeholder: 'Select Property Group Option',
        ajax: {
            url: propertyOptionSearchUrl,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            contentType: 'application/json',
            delay: 250,
            data: function (params) {
                return JSON.stringify({
                    page: params.page || 1,
                    groupId: groupId,
                    limit: 25
                });
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                isEndOfResultsOption = data.propertyGroups.length < 25;

                const results = data.propertyGroups.map(option => ({
                    id: option.id,
                    text: option.attributes.translated.name
                }));

                return {
                    results: results,
                    pagination: {
                        more: !isEndOfResultsOption
                    }
                };
            },
            cache: true
        },
        minimumInputLength: 0,
        allowClear: true,
        language: {
            noResults: () => 'No options found.'
        }
    });

    // Reset scrolling flags upon dropdown open and close
    $('#propertyGroupOptionSelectSecond').on('select2:open', function () {
        currentPageOption = 1;
        isEndOfResultsOption = false;
    });
}

$('#propertyGroupSelectSecond').change(function () {
    const selectedGroup = $(this).val();
    if (selectedGroup) {
        $('#propertyGroupOptionWrapperSecond').show();
        $('#addPropertyOptionWrapper').css('visibility', 'visible');
    } else {
        $('#propertyGroupOptionWrapperSecond').hide();
        $('#addPropertyOptionWrapper').css('visibility', 'hidden');
    }
});
*/
/*second - COMMENTED OUT*/


/*Third - COMMENTED OUT*/
/*
// Initialize Property Group Select
$('#propertyGroupSelectThird').select2({
    width: '50%',
    placeholder: 'Select Property Group',
    ajax: {
        url: propertySearchUrl,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        delay: 250,
        data: function (params) {
            if (params.term) {
                currentPage = 1;
            }
            return {
                page: currentPage,
                limit: 25,
                term: params.term || '',
                'total-count-mode': 1
            };
        },
        processResults: function (data) {
            isEndOfResults = data.propertyGroups.length < 25;
            const results = data.propertyGroups.map(group => ({
                id: group.id,
                text: group.attributes.translated.name
            }));
            return {
                results: results,
                pagination: {
                    more: !isEndOfResults
                }
            };
        },
        cache: true
    },
    minimumInputLength: 0,
    allowClear: true,
    language: {
        searching: () => 'Searching...',
        loadingMore: () => 'Loading more results...',
        noResults: () => 'No results found.'
    }
});

// Handle Property Group selection and reset Property Group Option
$('#propertyGroupSelectThird').change(function () {
    const groupId = $(this).val();

    // Clear previous options and disable the dropdown until options are loaded
    destroySelect2IfExists('#propertyGroupOptionSelectThird');
    $('#propertyGroupOptionSelectThird').empty().select2({
        width: '50%',
        placeholder: 'Select Property Group Option',
        allowClear: true
    });

    // Fetch new Property Group Options if a group is selected
    if (groupId) {
        fetchPropertyGroupOptionsThird(groupId);
    }
});

// Fetch property group options and enable scroll API for pagination
function fetchPropertyGroupOptionsThird(groupId) {
    currentPageOption = 1;
    isEndOfResultsOption = false;

    $('#propertyGroupOptionSelectThird').select2({
        width: '50%',
        placeholder: 'Select Property Group Option',
        ajax: {
            url: propertyOptionSearchUrl,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            contentType: 'application/json',
            delay: 250,
            data: function (params) {
                return JSON.stringify({
                    page: params.page || 1,
                    groupId: groupId,
                    limit: 25
                });
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                isEndOfResultsOption = data.propertyGroups.length < 25;

                const results = data.propertyGroups.map(option => ({
                    id: option.id,
                    text: option.attributes.translated.name
                }));

                return {
                    results: results,
                    pagination: {
                        more: !isEndOfResultsOption
                    }
                };
            },
            cache: true
        },
        minimumInputLength: 0,
        allowClear: true,
        language: {
            noResults: () => 'No options found.'
        }
    });

    // Reset scrolling flags upon dropdown open and close
    $('#propertyGroupOptionSelectThird').on('select2:open', function () {
        currentPageOption = 1;
        isEndOfResultsOption = false;
    });
}

$('#propertyGroupSelectThird').change(function () {
    const selectedGroup = $(this).val();
    if (selectedGroup) {
        $('#propertyGroupOptionWrapperThird').show();
        $('#addPropertyOptionWrapper').css('visibility', 'visible');
    } else {
        $('#propertyGroupOptionWrapperThird').hide();
        $('#addPropertyOptionWrapper').css('visibility', 'hidden');
    }
});
*/
/*Third - COMMENTED OUT*/


/*Four - COMMENTED OUT*/
/*
// Initialize Property Group Select
$('#propertyGroupSelectFour').select2({
    width: '50%',
    placeholder: 'Select Property Group',
    ajax: {
        url: propertySearchUrl,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        delay: 250,
        data: function (params) {
            if (params.term) {
                currentPage = 1;
            }
            return {
                page: currentPage,
                limit: 25,
                term: params.term || '',
                'total-count-mode': 1
            };
        },
        processResults: function (data) {
            isEndOfResults = data.propertyGroups.length < 25;
            const results = data.propertyGroups.map(group => ({
                id: group.id,
                text: group.attributes.translated.name
            }));
            return {
                results: results,
                pagination: {
                    more: !isEndOfResults
                }
            };
        },
        cache: true
    },
    minimumInputLength: 0,
    allowClear: true,
    language: {
        searching: () => 'Searching...',
        loadingMore: () => 'Loading more results...',
        noResults: () => 'No results found.'
    }
});

// Handle Property Group selection and reset Property Group Option
$('#propertyGroupSelectFour').change(function () {
    const groupId = $(this).val();

    // Clear previous options and disable the dropdown until options are loaded
    destroySelect2IfExists('#propertyGroupOptionSelectFour');
    $('#propertyGroupOptionSelectFour').empty().select2({
        width: '50%',
        placeholder: 'Select Property Group Option',
        allowClear: true
    });

    // Fetch new Property Group Options if a group is selected
    if (groupId) {
        fetchPropertyGroupOptionsFour(groupId);
    }
});

// Fetch property group options and enable scroll API for pagination
function fetchPropertyGroupOptionsFour(groupId) {
    currentPageOption = 1;
    isEndOfResultsOption = false;

    $('#propertyGroupOptionSelectFour').select2({
        width: '50%',
        placeholder: 'Select Property Group Option',
        ajax: {
            url: propertyOptionSearchUrl,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            contentType: 'application/json',
            delay: 250,
            data: function (params) {
                return JSON.stringify({
                    page: params.page || 1,
                    groupId: groupId,
                    limit: 25
                });
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                isEndOfResultsOption = data.propertyGroups.length < 25;

                const results = data.propertyGroups.map(option => ({
                    id: option.id,
                    text: option.attributes.translated.name
                }));

                return {
                    results: results,
                    pagination: {
                        more: !isEndOfResultsOption
                    }
                };
            },
            cache: true
        },
        minimumInputLength: 0,
        allowClear: true,
        language: {
            noResults: () => 'No options found.'
        }
    });

    // Reset scrolling flags upon dropdown open and close
    $('#propertyGroupOptionSelectFour').on('select2:open', function () {
        currentPageOption = 1;
        isEndOfResultsOption = false;
    });
}

$('#propertyGroupSelectFour').change(function () {
    const selectedGroup = $(this).val();
    if (selectedGroup) {
        $('#propertyGroupOptionWrapperFour').show();
        $('#addPropertyOptionWrapper').css('visibility', 'visible');
    } else {
        $('#propertyGroupOptionWrapperFour').hide();
        $('#addPropertyOptionWrapper').css('visibility', 'hidden');
    }
});
*/
/*Four - COMMENTED OUT*/


/*Five - COMMENTED OUT*/
/*
// Initialize Property Group Select
$('#propertyGroupSelectFive').select2({
    width: '50%',
    placeholder: 'Select Property Group',
    ajax: {
        url: propertySearchUrl,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        delay: 250,
        data: function (params) {
            if (params.term) {
                currentPage = 1;
            }
            return {
                page: currentPage,
                limit: 25,
                term: params.term || '',
                'total-count-mode': 1
            };
        },
        processResults: function (data) {
            isEndOfResults = data.propertyGroups.length < 25;
            const results = data.propertyGroups.map(group => ({
                id: group.id,
                text: group.attributes.translated.name
            }));
            return {
                results: results,
                pagination: {
                    more: !isEndOfResults
                }
            };
        },
        cache: true
    },
    minimumInputLength: 0,
    allowClear: true,
    language: {
        searching: () => 'Searching...',
        loadingMore: () => 'Loading more results...',
        noResults: () => 'No results found.'
    }
});

// Handle Property Group selection and reset Property Group Option
$('#propertyGroupSelectFive').change(function () {
    const groupId = $(this).val();

    // Clear previous options and disable the dropdown until options are loaded
    destroySelect2IfExists('#propertyGroupOptionSelectFive');
    $('#propertyGroupOptionSelectFive').empty().select2({
        width: '50%',
        placeholder: 'Select Property Group Option',
        allowClear: true
    });

    // Fetch new Property Group Options if a group is selected
    if (groupId) {
        fetchPropertyGroupOptionsFive(groupId);
    }
});

// Fetch property group options and enable scroll API for pagination
function fetchPropertyGroupOptionsFive(groupId) {
    currentPageOption = 1;
    isEndOfResultsOption = false;

    $('#propertyGroupOptionSelectFive').select2({
        width: '50%',
        placeholder: 'Select Property Group Option',
        ajax: {
            url: propertyOptionSearchUrl,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            contentType: 'application/json',
            delay: 250,
            data: function (params) {
                return JSON.stringify({
                    page: params.page || 1,
                    groupId: groupId,
                    limit: 25
                });
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                isEndOfResultsOption = data.propertyGroups.length < 25;

                const results = data.propertyGroups.map(option => ({
                    id: option.id,
                    text: option.attributes.translated.name
                }));

                return {
                    results: results,
                    pagination: {
                        more: !isEndOfResultsOption
                    }
                };
            },
            cache: true
        },
        minimumInputLength: 0,
        allowClear: true,
        language: {
            noResults: () => 'No options found.'
        }
    });

    // Reset scrolling flags upon dropdown open and close
    $('#propertyGroupOptionSelectFive').on('select2:open', function () {
        currentPageOption = 1;
        isEndOfResultsOption = false;
    });
}

$('#propertyGroupSelectFive').change(function () {
    const selectedGroup = $(this).val();
    if (selectedGroup) {
        $('#propertyGroupOptionWrapperFive').show();
        $('#addPropertyOptionWrapper').css('visibility', 'visible');
    } else {
        $('#propertyGroupOptionWrapperFive').hide();
        $('#addPropertyOptionWrapper').css('visibility', 'hidden');
    }
});
*/
/*Five - COMMENTED OUT*/



/*Six*/

// Initialize Property Group Select
$('#propertyGroupSelectSix').select2({
    width: '50%',
    placeholder: 'Select Property Group',
    ajax: {
        url: propertySearchUrl,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        delay: 250,
        data: function (params) {
            if (params.term) {
                currentPage = 1;
            }
            return {
                page: currentPage,
                limit: 25,
                term: params.term || '',
                'total-count-mode': 1
            };
        },
        processResults: function (data) {
            isEndOfResults = data.propertyGroups.length < 25;
            const results = data.propertyGroups.map(group => ({
                id: group.id,
                text: group.attributes.translated.name
            }));
            return {
                results: results,
                pagination: {
                    more: !isEndOfResults
                }
            };
        },
        cache: true
    },
    minimumInputLength: 0,
    allowClear: true,
    language: {
        searching: () => 'Searching...',
        loadingMore: () => 'Loading more results...',
        noResults: () => 'No results found.'
    }
});


// Fetch property group options and enable scroll API for pagination
function fetchPropertyGroupOptionsSix(groupId) {
    currentPageOption = 1;
    isEndOfResultsOption = false;

    $('#propertyGroupOptionSelectSix').select2({
        width: '50%',
        placeholder: 'Select Property Group Option',
        ajax: {
            url: propertyOptionSearchUrl,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            contentType: 'application/json',
            delay: 250,
            data: function (params) {
                return JSON.stringify({
                    page: params.page || 1,
                    groupId: groupId,
                    limit: 25
                });
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                isEndOfResultsOption = data.propertyGroups.length < 25;

                const results = data.propertyGroups.map(option => ({
                    id: option.id,
                    text: option.attributes.translated.name
                }));

                return {
                    results: results,
                    pagination: {
                        more: !isEndOfResultsOption
                    }
                };
            },
            cache: true
        },
        minimumInputLength: 0,
        allowClear: true,
        language: {
            noResults: () => 'No options found.'
        }
    });

    // Reset scrolling flags upon dropdown open and close
    $('#propertyGroupOptionSelectSix').on('select2:open', function () {
        currentPageOption = 1;
        isEndOfResultsOption = false;
    });
}

$('#propertyGroupSelectSix').change(function () {
    const selectedGroup = $(this).val();
    if (selectedGroup) {
        $('#propertyGroupOptionWrapperSix').show();
        $('#addPropertyOptionWrapper').css('visibility', 'visible');
    } else {
        $('#propertyGroupOptionWrapperSix').hide();
        $('#addPropertyOptionWrapper').css('visibility', 'hidden');
    }
});

/*six*/


// Handle click on 'Create New Property Group' button
$('#createPropertyGroupBtn').click(function () {
    alert("@lang('property.create_group_message')");
});

// Show the form for creating a new Property Option
$('#createPropertyGroupOptionBtn').click(function () {
    $('#newPropertyOptionForm').show();
    $('#newPropertyOptionInput').focus();
});

// Cancel the creation of a new Property Option
$('#cancelPropertyOptionBtn').click(function () {
    $('#newPropertyOptionInput').val('');
    $('#newPropertyOptionForm').hide();
});

$('#createPropertyGroupOptionBtn').on('click', function () {
    const selectedGroup = $('#propertyGroupSelect').val(); // Get the selected property group ID
    const selectedGroupName = $('#propertyGroupSelect option:selected').text(); // Get the selected property group name

    if (selectedGroup) {
        // Show the modal
        $('#createPropertyGroupOptionModal').modal('show');

        // Set the selected group name and ID in the modal
        $('#selectedPropertyGroupName').val(selectedGroupName); // Display the group name
        $('#selectedPropertyGroupId').val(selectedGroup); // Pass the group ID to the hidden input
    } else {
        alert("Selecteer Vastgoedgroep");
    }
});

$('#savePropertyGroupOptionBtn').on('click', function () {
    $('#full-page-preloader').show();
    const groupId = $('#propertyGroupSelectSix').val();
    const optionName = $('#newPropertyOptionName').val();

    if (optionName && groupId) {
        $.ajax({
            url: propertyOptionSave, // URL to save the property option
            method: 'POST',
            data: {
                groupId: groupId,
                optionName: optionName,
            },
            headers: {
                'X-CSRF-TOKEN': csrfToken_common
            },
            success: function (response) {
                // Handle the success (e.g., close the modal and display a success message)
                $('#createPropertyGroupOptionModal').modal('hide');
                $('#full-page-preloader').hide();
                alert(response.message); // Display a success message

                // Clear input fields
                $('#newPropertyOptionName').val(''); // Reset option name input
                $('#selectedPropertyGroupName').val(''); // Clear displayed group name
                $('#selectedPropertyGroupId').val(''); // Clear hidden group ID

            },
            error: function (error) {
                $('#full-page-preloader').hide();
                // Handle the error (e.g., display an error message)
                alert("@lang('product.error_occurred')");
            }
        });
    } else {
        $('#full-page-preloader').hide();
        alert("@lang('product.enter_property_option_name')");
    }
});
// $('#yesStepDetails').show();
// $('#productEditModal').modal('show');

// Add Property Option button functionality
$('#addPropertyOptionBtn').on('click', function () {
    $('#full-page-preloader').show();
    const selectedGroupId = $('#propertyGroupSelect').val();
    const selectedGroupOption = $('#propertyGroupOptionSelect').val();
    const selectedGroupName = $('#propertyGroupSelect option:selected').text();
    const selectedPropertyOption = $('#propertyGroupOptionSelect option:selected').text();

    // COMMENTED OUT - Fields 2-5 disabled
    /*
    const selectedGroupIdSecond = $('#propertyGroupSelectSecond').val();
    const selectedGroupOptionSecond = $('#propertyGroupOptionSelectSecond').val();
    const selectedGroupNameSecond = $('#propertyGroupSelectSecond option:selected').text();
    const selectedPropertyOptionSecond = $('#propertyGroupOptionSelectSecond option:selected').text();

    const selectedGroupIdThird = $('#propertyGroupSelectThird').val();
    const selectedGroupOptionThird = $('#propertyGroupOptionSelectThird').val();
    const selectedGroupNameThird = $('#propertyGroupSelectThird option:selected').text();
    const selectedPropertyOptionThird = $('#propertyGroupOptionSelectThird option:selected').text();

    const selectedGroupIdFour = $('#propertyGroupSelectFour').val();
    const selectedGroupOptionFour = $('#propertyGroupOptionSelectFour').val();
    const selectedGroupNameFour = $('#propertyGroupSelectFour option:selected').text();
    const selectedPropertyOptionFour = $('#propertyGroupOptionSelectFour option:selected').text();

    const selectedGroupIdFive = $('#propertyGroupSelectFive').val();
    const selectedGroupOptionFive = $('#propertyGroupOptionSelectFive').val();
    const selectedGroupNameFive = $('#propertyGroupSelectFive option:selected').text();
    const selectedPropertyOptionFive = $('#propertyGroupOptionSelectFive option:selected').text();
    */
    
    // Set disabled fields to null
    const selectedGroupIdSecond = null;
    const selectedGroupOptionSecond = null;
    const selectedGroupNameSecond = '';
    const selectedPropertyOptionSecond = '';
    
    const selectedGroupIdThird = null;
    const selectedGroupOptionThird = null;
    const selectedGroupNameThird = '';
    const selectedPropertyOptionThird = '';
    
    const selectedGroupIdFour = null;
    const selectedGroupOptionFour = null;
    const selectedGroupNameFour = '';
    const selectedPropertyOptionFour = '';
    
    const selectedGroupIdFive = null;
    const selectedGroupOptionFive = null;
    const selectedGroupNameFive = '';
    const selectedPropertyOptionFive = '';

    // Validate: If a group is selected, its corresponding option must be selected
    const validationFailed = [
        {group: selectedGroupId, option: selectedGroupId},
        {group: selectedGroupIdSecond, option: selectedGroupOptionSecond},
        {group: selectedGroupIdThird, option: selectedGroupOptionThird},
        {group: selectedGroupIdFour, option: selectedGroupOptionFour},
        {group: selectedGroupIdFive, option: selectedGroupOptionFive}
    ].some(item => item.group && !item.option);

    if (validationFailed) {
        $('#full-page-preloader').hide();
        alert("Als een extra vastgoedgroep is geselecteerd, moet ook een bijbehorende optie worden geselecteerd.");
        return;
    }


    if (selectedGroupId && selectedGroupOption || selectedGroupIdSecond && selectedGroupOptionSecond || selectedGroupIdThird && selectedGroupOptionThird || selectedGroupIdFour && selectedGroupOptionFour || selectedGroupIdFive && selectedGroupOptionFive) {
        // Show the modal if valid values are selected
        $('#productEditModal').modal('show');

        allProductData.productData.forEach(product => {
            $('#name').val(product.attributes.translated.name);
            if (ckEditors['description']) {
                const desc = product.attributes?.translated?.description || '';
                ckEditors['description'].summernote('code', desc);
            }
            $('#stock').val(product.attributes.stock || 1);
            $('#productEanNumber').val(product.attributes.ean);
            $('#productNumber').val(product.attributes.ean);
            $('#productPackagingHeight').val(product.attributes.height);
            $('#productPackagingLength').val(product.attributes.length);
            $('#productPackagingWeight').val(product.attributes.weight);
            $('#productPackagingWidth').val(product.attributes.width);
            // Set manufacturer ID from parent product if variant doesn't have one
            const manufacturerId = product.attributes.manufacturerId || (allProductData.parentData ? allProductData.parentData.attributes?.manufacturerId : '');
            $('#manufacturer').val(manufacturerId);
            
            // Prefill marketplace and shipping data from main product
            // Prefill prices from main product
            if (product.attributes?.price && product.attributes.price.length > 0) {
                $('#priceGross').val(product.attributes.price[0].gross || '');
                $('#priceNet').val(product.attributes.price[0].net || '');
                if (product.attributes.price[0].listPrice) {
                    $('#listPriceGross').val(product.attributes.price[0].listPrice.gross || '');
                    $('#listPriceNet').val(product.attributes.price[0].listPrice.net || '');
                }
            }
            if (product.attributes?.purchasePrices && product.attributes.purchasePrices.length > 0) {
                $('#swPurchasePriceNet').val(product.attributes.purchasePrices[0].net || '');
                $('#swPurchasePrice').val(product.attributes.purchasePrices[0].gross || '');
            }
            
            // Product data is available in global allProductData

        });
        $('#productConfiguratorSettingsIds').val(allProductData.optionsIds);

        // Populate fields if needed (set defaults here)

        $('#selectedPropertyGroupId').val(
            [selectedGroupId, selectedGroupIdSecond, selectedGroupIdThird, selectedGroupIdFour, selectedGroupIdFive]
                .filter(Boolean) // Removes empty, null, or undefined values
                .join(',')
        );

        $('#selectedPropertyGroupDisplay').text(
            [selectedGroupName, selectedGroupNameSecond, selectedGroupNameThird, selectedGroupNameFour, selectedGroupNameFive]
                .filter(Boolean) // Removes any empty or undefined values
                .join(', ')
        );

        $('#selectedPropertyOptionDisplay').text(
            [selectedPropertyOption, selectedPropertyOptionSecond, selectedPropertyOptionThird, selectedPropertyOptionFour, selectedPropertyOptionFive]
                .filter(Boolean) // Removes any empty or undefined values
                .join(', ')
        );

        // $('#selectedPropertyGroupDisplay').text(selectedGroupName);
        // $('#selectedPropertyOptionDisplay').text(selectedPropertyOption);

        // Iterate through the products in apiResponse
        apiResponse.product.productData.forEach((product) => {
            const {childCount, parentId, propertyId} = product.attributes || {};

            // Check the conditions for childCount and parentId
            if (childCount !== null && parentId === null) {
                // Create hidden fields and append them to the form
                createHiddenFields(product.id, selectedGroupOption, selectedGroupOptionSecond, selectedGroupOptionThird, selectedGroupOptionFour, selectedGroupOptionFive);
            }
        });
        $('#full-page-preloader').hide();
    } else {
        $('#full-page-preloader').hide();
        alert("Selecteer Vastgoedgroep");
    }
});

function createHiddenFields(parentId, selectedGroupOption, selectedGroupOptionSecond, selectedGroupOptionThird, selectedGroupOptionFour, selectedGroupOptionFive) {
    var form = $('#product-form');

    function ensureHiddenField(name, value) {
        var existingField = form.find('input[name="' + name + '"]');
        if (existingField.length) {
            existingField.val(value); // Update value if already exists
        } else {
            var hiddenField = $('<input>', {
                type: 'hidden',
                name: name,
                value: value
            });
            form.append(hiddenField);
        }
    }

    ensureHiddenField('parentId', parentId);
    ensureHiddenField('propertyOptionIdAll', [selectedGroupOption, selectedGroupOptionSecond, selectedGroupOptionThird, selectedGroupOptionFour, selectedGroupOptionFive].filter(Boolean).join(','));
    ensureHiddenField('propertyOptionId', selectedGroupOption);
    ensureHiddenField('propertyOptionIdSecond', selectedGroupOptionSecond);
    ensureHiddenField('propertyOptionIdThird', selectedGroupOptionThird);
    ensureHiddenField('propertyOptionIdFour', selectedGroupOptionFour);
    ensureHiddenField('propertyOptionIdFive', selectedGroupOptionFive);
}


// Function to set custom field data for dropdowns (used by both Step 3 and Step 4)
function setCustomFieldData(customFieldData) {
    customFieldData.forEach(item => {
        if (item.is_select_type && Array.isArray(item.options)) {
            let selectClass = '';
            if (item.name === 'migration_DMG_product_bol_nl_delivery_code') {
                selectClass = '.bolNLDeliveryTimeSelect';
            } else if (item.name === 'migration_DMG_product_bol_be_delivery_code') {
                selectClass = '.bolBEDeliveryTimeSelect';
            } else if (item.name === 'migration_DMG_product_bol_condition') {
                selectClass = '.bolConditionSelect';
            }
            
            if (selectClass) {
                const $select = $(selectClass);
                
                if ($select.length) {
                    $select.each(function() {
                        const $currentSelect = $(this);
                        $currentSelect.empty();
                        
                        item.options.forEach(opt => {
                            $currentSelect.append(`<option value="${opt.value}">${opt.label}</option>`);
                        });
                        
                        // Initialize select2 if not already initialized
                        if (!$currentSelect.hasClass('select2-hidden-accessible')) {
                            let dropdownParent = $('#productEditModal');
                            if ($currentSelect.closest('#productUpdateModal').length) {
                                dropdownParent = $('#productUpdateModal');
                            }
                            
                            $currentSelect.select2({
                                placeholder: item.label,
                                minimumInputLength: 0,
                                allowClear: true,
                                multiple: false,
                                dropdownParent: dropdownParent,
                                language: {
                                    searching: function() {
                                        return "Zoeken, even geduld...";
                                    },
                                    loadingMore: function() {
                                        return "Meer resultaten laden...";
                                    },
                                    noResults: function() {
                                        return "Geen resultaten gevonden.";
                                    }
                                }
                            });
                        }
                        
                        $currentSelect.val('').trigger('change');
                    });
                }
            }
        }
    });
}

// Step 4 modal prefilling
$('#productEditModal').on('shown.bs.modal', function() {
    if (customFieldData && customFieldData.length > 0) {
        setCustomFieldData(customFieldData);
    }
    
    if (allProductData && allProductData.productData && allProductData.productData.length > 0) {
        const product = allProductData.productData[0];
        const productFields = product.attributes?.customFields || {};
        
        // Get manufacturer ID from included data or product relationships
        let manufacturerId = null;
        
        // Check if manufacturer is in the included data
        if (allProductData.included) {
            const manufacturerData = allProductData.included.find(item => item.type === 'product_manufacturer');
            if (manufacturerData) {
                manufacturerId = manufacturerData.id;
            }
        }
        
        // If no manufacturer found in included data, check product relationships
        if (!manufacturerId && product.relationships && product.relationships.manufacturer) {
            manufacturerId = product.relationships.manufacturer.data?.id;
        }
        
        // Set manufacturer ID if found
        if (manufacturerId) {
            $('#manufacturer').val(manufacturerId);
        }
        
        // Set tax ID - use product's tax or fallback to parent
        let taxId = product.attributes?.taxId;
        if (!taxId && allProductData.parentData) {
            taxId = allProductData.parentData.attributes?.taxId;
        }
        if (taxId) {
            $('#swTaxRate').data('taxId', taxId);
        }
        
        // Set packaging dimensions from parent data
        if (allProductData.parentData) {
            const parentAttrs = allProductData.parentData.attributes;
            $('#productPackagingWidth').val(parentAttrs?.width || 0);
            $('#productPackagingHeight').val(parentAttrs?.height || 0);
            $('#productPackagingLength').val(parentAttrs?.length || 0);
            $('#productPackagingWeight').val(parentAttrs?.weight || 0);
        }
        
        // Marketplace fields
        $('#productEditModal #bolNlActive').prop('checked', !!productFields.migration_DMG_product_bol_nl_active);
        $('#productEditModal #bolBeActive').prop('checked', !!productFields.migration_DMG_product_bol_be_active);
        $('#productEditModal #bolNlPrice').val(productFields.migration_DMG_product_bol_price_nl || '');
        $('#productEditModal #bolBePrice').val(productFields.migration_DMG_product_bol_price_be || '');
        $('#productEditModal #shortDescription').val(productFields.custom_product_message_ || '');
        
        // Shipping information fields
        $('#productEditModal #bolConditionDescription').val(productFields.migration_DMG_product_bol_condition_desc || '');
        $('#productEditModal #bolOrderBeforeTomorrow').prop('checked', !!productFields.migration_DMG_product_proposition_1);
        $('#productEditModal #bolOrderBefore').prop('checked', !!productFields.migration_DMG_product_proposition_2);
        $('#productEditModal #bolLetterboxPackage').prop('checked', !!productFields.migration_DMG_product_proposition_3);
        $('#productEditModal #bolLetterboxPackageUp').prop('checked', !!productFields.migration_DMG_product_proposition_4);
        $('#productEditModal #bolPickUpOnly').prop('checked', !!productFields.migration_DMG_product_proposition_5);
        
        // Set dropdown values
        setTimeout(() => {
            if (productFields.migration_DMG_product_bol_nl_delivery_code) {
                $('#bolNLDeliveryTime').val(productFields.migration_DMG_product_bol_nl_delivery_code).trigger('change');
            }
            if (productFields.migration_DMG_product_bol_be_delivery_code) {
                $('#bolBEDeliveryTime').val(productFields.migration_DMG_product_bol_be_delivery_code).trigger('change');
            }
            if (productFields.migration_DMG_product_bol_condition) {
                $('#bolCondition').val(productFields.migration_DMG_product_bol_condition).trigger('change');
            }
        }, 1000);
    }
});

$('#saveVariant').on('click', function (e) {
    $('#full-page-preloader').show();
    e.preventDefault(); // Prevent the default form submission (if any)

    if (ckEditors['description']) {
        $('#description').val(ckEditors['description'].summernote('code'));
    }
    
    // Ensure taxId is set from the stored value
    const taxId = $('#swTaxRate').data('taxId');
    if (taxId && !$('#product-form input[name="taxId"]').length) {
        $('#product-form').append(`<input type="hidden" name="taxId" value="${taxId}" />`);
    }
    
    // Collect properties data
    const properties = [];
    $('.property-group-row').each(function() {
        const optionId = $(this).find('.property-option-select').val();
        if (optionId) {
            properties.push({ "id": optionId });
        }
    });
    
    // Add properties to form data
    if (properties.length > 0) {
        $('#product-form').append(`<input type="hidden" name="properties" value='${JSON.stringify(properties)}' />`);
    }

    const formData = $('#product-form').serialize(); // Serialize the entire form data
    const button = $(this);
    const originalButtonText = button.text(); // Save original button text

    // Show loader on the button
    // button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verwerking...');

    $.ajax({
        url: variantSave,
        method: 'POST',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': csrfToken_common
        },
        dataType: 'json',
        success: function (response) {
            alert(response.message);
            $('#full-page-preloader').hide();
            $('#productEditModal').modal('hide');
            location.reload();
        },
        error: function (xhr) {
            $('#full-page-preloader').hide();
            if(xhr.status == 400){
                alert(xhr.responseJSON.errors);
            }else{
                showValidationErrors(xhr.responseJSON.errors);
            }
        }
    });
});

function showValidationErrors(errors) {
    // Remove previous error highlights and messages
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').remove();

    $.each(errors, function (field, messages) {
        let input = $('[name="' + field + '"]');

        if (input.length) {
            input.addClass('is-invalid'); // Highlight the field in red

            // Create and append error message
            let errorDiv = $('<div class="invalid-feedback">' + messages[0] + '</div>');
            input.after(errorDiv);
        }
    });
}


// Dynamic Property Groups for Step 4 Modal
let propertyGroupCounter = 0;

// Reset properties when modal is hidden
$('#productEditModal').on('hidden.bs.modal', function() {
    resetPropertyGroups();
});

// Initialize property dropdowns when modal is shown
$('#productEditModal').on('shown.bs.modal', function() {
    initializePropertyDropdowns();
});

function resetPropertyGroups() {
    // Remove all property rows except the first one
    $('.property-group-row:not(:first)').remove();
    
    // Reset the first row
    const firstRow = $('.property-group-row:first');
    firstRow.find('.property-group-select').val('').trigger('change');
    firstRow.find('.property-option-select').val('').prop('disabled', true);
    
    // Reset counter
    propertyGroupCounter = 0;
    
    // Hide remove button on first row
    firstRow.find('.remove-property-btn').hide();
}

function initializePropertyDropdowns() {
    // Initialize existing property group dropdowns
    $('.property-group-select').each(function() {
        const index = $(this).data('index');
        if (!$(this).hasClass('select2-hidden-accessible')) {
            initPropertyGroupSelect(index);
        }
    });
}

function initPropertyGroupSelect(index) {
    $(`select[data-index="${index}"].property-group-select`).select2({
        placeholder: 'Selecteer eigenschappengroep',
        ajax: {
            url: propertySearchUrl,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken_common
            },
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    page: 1,
                    limit: 25,
                    term: params.term || '',
                    'total-count-mode': 1
                };
            },
            processResults: function(data) {
                const results = data.propertyGroups.map(group => ({
                    id: group.id,
                    text: group.attributes.translated.name
                }));
                return { results: results };
            },
            cache: true
        },
        minimumInputLength: 0,
        allowClear: true,
        dropdownParent: $('#productEditModal'),
        language: {
            searching: () => 'Zoeken...',
            noResults: () => 'Geen resultaten gevonden.'
        }
    });
}

function initPropertyOptionSelect(index, groupId) {
    $(`select[data-index="${index}"].property-option-select`).select2({
        placeholder: 'Selecteer eigenschapoptie',
        ajax: {
            url: propertyOptionSearchUrl,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken_common
            },
            dataType: 'json',
            contentType: 'application/json',
            delay: 250,
            data: function(params) {
                return JSON.stringify({
                    page: 1,
                    groupId: groupId,
                    limit: 25,
                    searchTerm: params.term
                });
            },
            processResults: function(data) {
                const results = data.propertyGroups.map(option => ({
                    id: option.id,
                    text: option.attributes.translated.name
                }));
                return { results: results };
            },
            cache: true
        },
        minimumInputLength: 0,
        allowClear: true,
        dropdownParent: $('#productEditModal'),
        language: {
            noResults: () => 'Geen opties gevonden.'
        }
    });
}

// Handle property group selection
$(document).on('change', '.property-group-select', function() {
    const index = $(this).data('index');
    const groupId = $(this).val();
    const optionSelect = $(`.property-option-select[data-index="${index}"]`);
    
    // Reset and enable/disable option select
    if (optionSelect.hasClass('select2-hidden-accessible')) {
        optionSelect.select2('destroy');
    }
    
    optionSelect.empty().prop('disabled', !groupId);
    
    if (groupId) {
        initPropertyOptionSelect(index, groupId);
    } else {
        optionSelect.select2({
            placeholder: 'Selecteer eerst een eigenschappengroep',
            dropdownParent: $('#productEditModal')
        });
    }
});

// Add new property group row
$(document).on('click', '.add-property-btn', function() {
    propertyGroupCounter++;
    const newRow = `
        <div class="property-group-row" data-index="${propertyGroupCounter}">
            <div class="row align-items-end mb-3">
                <div class="col-md-5">
                    <label class="form-label">Selecteer eigenschappengroep</label>
                    <select class="form-control property-group-select" name="propertyGroups[${propertyGroupCounter}][groupId]" data-index="${propertyGroupCounter}">
                        <option value="">Selecteer eigenschappengroep</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Selecteer eigenschapoptie</label>
                    <select class="form-control property-option-select" name="propertyGroups[${propertyGroupCounter}][optionId]" data-index="${propertyGroupCounter}" disabled>
                        <option value="">Selecteer eerst een optie</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-success add-property-btn" title="Add Property">
                        <i class="fa fa-plus"></i>
                    </button>
                    <button type="button" class="btn btn-danger remove-property-btn" title="Remove Property">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    $('#propertiesContainer').append(newRow);
    initPropertyGroupSelect(propertyGroupCounter);
    
    // Show remove button for all rows except the first one
    updateRemoveButtons();
});

// Remove property group row
$(document).on('click', '.remove-property-btn', function() {
    $(this).closest('.property-group-row').remove();
    updateRemoveButtons();
});

function updateRemoveButtons() {
    const rows = $('.property-group-row');
    rows.each(function(index) {
        const removeBtn = $(this).find('.remove-property-btn');
        if (rows.length > 1 && index > 0) {
            removeBtn.show();
        } else {
            removeBtn.hide();
        }
    });
}
// Step 3 Property Groups functionality
let updatePropertyGroupCounter = 0;

// Reset properties when Step 3 modal is hidden
$('#productUpdateModal').on('hidden.bs.modal', function() {
    resetUpdatePropertyGroups();
});

function resetUpdatePropertyGroups() {
    // Remove all property rows except the first one
    $('.property-group-row-update:not(:first)').remove();
    
    // Reset the first row
    const firstRow = $('.property-group-row-update:first');
    firstRow.find('.property-group-select-update').val('').trigger('change');
    firstRow.find('.property-option-select-update').val('').prop('disabled', true);
    
    // Reset counter
    updatePropertyGroupCounter = 0;
    
    // Hide remove button on first row
    firstRow.find('.remove-property-btn-update').hide();
}

function initUpdatePropertyDropdowns() {
    // Initialize existing property group dropdowns
    $('.property-group-select-update').each(function() {
        const index = $(this).data('index');
        if (!$(this).hasClass('select2-hidden-accessible')) {
            initUpdatePropertyGroupSelect(index);
        }
    });
}

function initUpdatePropertyGroupSelect(index) {
    $(`select[data-index="${index}"].property-group-select-update`).select2({
        placeholder: 'Selecteer eigenschappengroep',
        ajax: {
            url: propertySearchUrl,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken_common
            },
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    page: 1,
                    limit: 25,
                    term: params.term || '',
                    'total-count-mode': 1
                };
            },
            processResults: function(data) {
                const results = data.propertyGroups.map(group => ({
                    id: group.id,
                    text: group.attributes.translated.name
                }));
                return { results: results };
            },
            cache: true
        },
        minimumInputLength: 0,
        allowClear: true,
        dropdownParent: $('#productUpdateModal'),
        language: {
            searching: () => 'Zoeken...',
            noResults: () => 'Geen resultaten gevonden.'
        }
    });
}

function initUpdatePropertyOptionSelect(index, groupId) {
    $(`select[data-index="${index}"].property-option-select-update`).select2({
        placeholder: 'Selecteer eigenschapoptie',
        ajax: {
            url: propertyOptionSearchUrl,
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken_common
            },
            dataType: 'json',
            contentType: 'application/json',
            delay: 250,
            data: function(params) {
                return JSON.stringify({
                    page: 1,
                    groupId: groupId,
                    limit: 25,
                    searchTerm: params.term
                });
            },
            processResults: function(data) {
                const results = data.propertyGroups.map(option => ({
                    id: option.id,
                    text: option.attributes.translated.name
                }));
                return { results: results };
            },
            cache: true
        },
        minimumInputLength: 0,
        allowClear: true,
        dropdownParent: $('#productUpdateModal'),
        language: {
            noResults: () => 'Geen opties gevonden.'
        }
    });
}

// Handle property group selection for Step 3
$(document).on('change', '.property-group-select-update', function() {
    const index = $(this).data('index');
    const groupId = $(this).val();
    const optionSelect = $(`.property-option-select-update[data-index="${index}"]`);
    
    // Reset and enable/disable option select
    if (optionSelect.hasClass('select2-hidden-accessible')) {
        optionSelect.select2('destroy');
    }
    
    optionSelect.empty().prop('disabled', !groupId);
    
    if (groupId) {
        initUpdatePropertyOptionSelect(index, groupId);
    } else {
        optionSelect.select2({
            placeholder: 'Selecteer eerst een eigenschappengroep',
            dropdownParent: $('#productUpdateModal')
        });
    }
});

// Add new property group row for Step 3
$(document).on('click', '.add-property-btn-update', function() {
    updatePropertyGroupCounter++;
    const newRow = `
        <div class="property-group-row-update" data-index="${updatePropertyGroupCounter}">
            <div class="row align-items-end mb-3">
                <div class="col-md-5">
                    <label class="form-label">Selecteer eigenschappengroep</label>
                    <select class="form-control property-group-select-update" name="propertyGroups[${updatePropertyGroupCounter}][groupId]" data-index="${updatePropertyGroupCounter}">
                        <option value="">Selecteer eigenschappengroep</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Selecteer eigenschapoptie</label>
                    <select class="form-control property-option-select-update" name="propertyGroups[${updatePropertyGroupCounter}][optionId]" data-index="${updatePropertyGroupCounter}" disabled>
                        <option value="">Selecteer eerst een optie</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-success add-property-btn-update" title="Add Property">
                        <i class="fa fa-plus"></i>
                    </button>
                    <button type="button" class="btn btn-danger remove-property-btn-update" title="Remove Property">
                        <i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    $('#updatePropertiesContainer').append(newRow);
    initUpdatePropertyGroupSelect(updatePropertyGroupCounter);
    
    // Show remove button for all rows except the first one
    updateUpdateRemoveButtons();
});

// Remove property group row for Step 3
$(document).on('click', '.remove-property-btn-update', function() {
    $(this).closest('.property-group-row-update').remove();
    updateUpdateRemoveButtons();
    
    // Clear any cached property data to ensure fresh collection on save
    $('#product-update-form input[name="properties"]').remove();
});

function updateUpdateRemoveButtons() {
    const rows = $('.property-group-row-update');
    rows.each(function(index) {
        const removeBtn = $(this).find('.remove-property-btn-update');
        if (rows.length > 1 && index > 0) {
            removeBtn.show();
        } else {
            removeBtn.hide();
        }
    });
}

// Prefill properties when Step 3 modal is shown
$('#productUpdateModal').on('shown.bs.modal', function() {
    initUpdatePropertyDropdowns();
    
    // Get product ID and prefill properties
    const productId = $('#updateProductId').val();
    if (productId && allProductData && allProductData.productData) {
        prefillProductProperties(productId);
    }
});

function prefillProductProperties(productId) {
    if (!allProductData || !allProductData.productData) return;
    
    const product = allProductData.productData.find(p => p.id === productId);
    if (!product || !product.relationships) return;
    
    let propertyOptionIds = [];
    
    // Check if product has properties (for parent products)
    if (product.relationships.properties && product.relationships.properties.data) {
        propertyOptionIds = product.relationships.properties.data.map(prop => prop.id);
    }
    // Check if product has configuratorSettings (for variants)
    else if (product.relationships.configuratorSettings && product.relationships.configuratorSettings.data) {
        const configuratorSettings = product.relationships.configuratorSettings.data;
        configuratorSettings.forEach(setting => {
            const settingData = allProductData.included.find(item => 
                item.id === setting.id && item.type === 'product_configurator_setting'
            );
            
            if (settingData && settingData.relationships && settingData.relationships.option) {
                propertyOptionIds.push(settingData.relationships.option.data.id);
            }
        });
    }
    
    if (propertyOptionIds.length === 0) return;
    
    // Add additional rows if needed
    while ($('.property-group-row-update').length < propertyOptionIds.length) {
        $('.add-property-btn-update:first').click();
    }
    
    // Prefill each property option
    propertyOptionIds.forEach((optionId, index) => {
        if (index < $('.property-group-row-update').length) {
            // Find the property option in included data
            const optionData = allProductData.included.find(item => 
                item.id === optionId && item.type === 'property_group_option'
            );
            
            if (optionData && optionData.relationships && optionData.relationships.group) {
                const groupId = optionData.relationships.group.data.id;
                const groupData = allProductData.included.find(item => 
                    item.id === groupId && item.type === 'property_group'
                );
                
                if (groupData) {
                    const row = $(`.property-group-row-update[data-index="${index}"]`);
                    const groupSelect = row.find('.property-group-select-update');
                    const optionSelect = row.find('.property-option-select-update');
                    
                    // Set group first
                    setTimeout(() => {
                        const groupOption = new Option(groupData.attributes.translated.name, groupId, true, true);
                        groupSelect.append(groupOption).trigger('change');
                        
                        // Then set option after group is selected
                        setTimeout(() => {
                            const optionOption = new Option(optionData.attributes.translated.name, optionId, true, true);
                            optionSelect.append(optionOption).trigger('change');
                        }, 300);
                    }, 200 * (index + 1));
                }
            }
        }
    });
}

// Update Step 3 form submission to include properties
$(document).off('submit', '#product-update-form').off('click', '#saveProductUpdate').on('submit', '#product-update-form', function(e) {
    e.preventDefault();
    e.stopImmediatePropagation();
    
    // Multiple safeguards against double submission
    if ($(this).data('submitting') || $('#saveProductUpdate').prop('disabled')) {
        return false;
    }
    
    // Set multiple flags
    $(this).data('submitting', true);
    $('#saveProductUpdate').prop('disabled', true).text('Verwerking...');
    
    $('#full-page-preloader').show();
    
    // Remove any existing properties input to avoid duplicates
    $(this).find('input[name="properties"]').remove();
    
    // Collect current properties data from DOM
    const properties = [];
    $('.property-group-row-update').each(function() {
        const optionId = $(this).find('.property-option-select-update').val();
        if (optionId) {
            properties.push({ "id": optionId });
        }
    });
    
    // Always add properties input (empty array if no properties)
    $(this).append(`<input type="hidden" name="properties" value='${JSON.stringify(properties)}' />`);
    
    const formData = new FormData(this);
    const form = this;
    
    $.ajax({
        url: '/admin/product/update',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            $('#productUpdateModal').modal('hide');
            
            if (response.success) {
                alert('Product succesvol bijgewerkt');
                location.reload();
            }
        },
        error: function(xhr) {
            let errorMessage = 'Het is niet gelukt om het product bij te werken';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            alert(errorMessage);
        },
        complete: function() {
            $('#full-page-preloader').hide();
            $('#saveProductUpdate').prop('disabled', false).text('Opslaan');
            $('#product-update-form').data('submitting', false);
        }
    });
});

// Also prevent button click events
$(document).off('click', '#saveProductUpdate').on('click', '#saveProductUpdate', function(e) {
    if ($(this).prop('disabled') || $('#product-update-form').data('submitting')) {
        e.preventDefault();
        e.stopImmediatePropagation();
        return false;
    }
});