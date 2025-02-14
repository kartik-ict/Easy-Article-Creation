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
                    text: category.attributes.translated.name
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
                            text: category.attributes.translated.name
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


// Tax Provider API

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
                term: params.term, // Search term
                page: params.page || 1
            };
        },
        processResults: function (data) {
            return {
                results: data.taxProviders.map(function (provider) {
                    return {
                        id: provider.id,
                        text: provider.attributes.name,
                        taxRate: provider.attributes.taxRate // Include tax rate
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
    dropdownParent: $('#productEditModal'),

}).on('select2:select', function (e) {
    const selectedTaxRate = e.params.data.taxRate || 0; // Get the selected tax rate

    // Update the tax rate for calculation
    $('#priceGross').data('taxRate', selectedTaxRate);
});

$('#priceGross').on('input', function () {
    const priceGross = parseFloat($(this).val()) || 0;
    const taxRate = parseFloat($(this).data('taxRate')) || 0;

    // Calculate net price
    const priceNet = priceGross / (1 + taxRate / 100);
    $('#priceNet').val(priceNet.toFixed(5));
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


/*second*/

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
/*second*/


/*Third*/

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
/*Third*/


/*Four*/

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
/*Four*/


/*Five*/

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
/*Five*/



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

// Add Property Option button functionality
$('#addPropertyOptionBtn').on('click', function () {
    $('#full-page-preloader').show();
    const selectedGroupId = $('#propertyGroupSelect').val();
    const selectedGroupOption = $('#propertyGroupOptionSelect').val();
    const selectedGroupName = $('#propertyGroupSelect option:selected').text();
    const selectedPropertyOption = $('#propertyGroupOptionSelect option:selected').text();

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
            $('#description').val(product.attributes.translated.description);
            $('#stock').val(product.attributes.stock);
            $('#productEanNumber').val(product.attributes.ean);
            $('#productPackagingHeight').val(product.attributes.height);
            $('#productPackagingLength').val(product.attributes.length);
            $('#productPackagingWeight').val(product.attributes.weight);
            $('#productPackagingWidth').val(product.attributes.width);

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


$('#saveVariant').on('click', function (e) {
    $('#full-page-preloader').show();
    e.preventDefault(); // Prevent the default form submission (if any)

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
            showValidationErrors(xhr.responseJSON.errors);
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

