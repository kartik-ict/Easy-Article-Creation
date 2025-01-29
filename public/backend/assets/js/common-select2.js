// Declare the necessary variables

const manufacturerSearchUrl = $('#route-container').data('manufacturer-search');

console.log("ddd",manufacturerSearchUrl)
let currentPage = 1; // Start from the first page
let isLoading = false;
let isEndOfResults = false;

// Initialize the Select2 component
$('#manufacturer-select').select2({
    placeholder: '@lang("product.manufacturer")',
    ajax: {
        url: manufacturerSearchUrl,
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        delay: 250,
        data: function(params) {
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
        processResults: function(data) {
            // Check if we've reached the end of the results
            isEndOfResults = (data.manufacturers.length < 25);

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
                    more: !isEndOfResults // Show 'more' if there are more results
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
    }
});

// When dropdown is opened, reset the page number and flags
$('#manufacturer-select').on('select2:open', function() {
    currentPage = 1; // Start from page 1
    isLoading = false;
    isEndOfResults = false;

    const dropdown = $('.select2-results__options');

    // Scroll event handler to trigger the next API request when scrolling to the bottom
    dropdown.on('scroll', function() {
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
$('#manufacturer-select').on('select2:close', function() {
    // Reset page when dropdown is closed, if needed
    currentPage = 1;
    isLoading = false; // Reset loading flag when dropdown closes
    isEndOfResults = false; // Reset end of results flag
});