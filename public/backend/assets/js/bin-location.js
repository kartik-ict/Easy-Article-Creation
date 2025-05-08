jQuery(function () {
    let isPreselecting = true;

    $(".select2").select2();
    $("#ip_address").on("input", function () {
        // Replace any character that is not a digit or dot
        this.value = this.value.replace(/[^0-9.]/g, "");
    });
    // ------------------------------------------------  Warehouse Selection -------------------------------------------

    const wareHouseSearchUrl = $("#route-container-warehouse").data(
        "warehouse-search"
    );
    const csrfToken = $('meta[name="csrf-token"]').attr("content");

    let currentPageWarehouse = 1; // Start from the first page
    let isLoadingWarehouse = false;
    let isEndOfResultsWarehouse = false;

    // Preselect Warehouse
    if (window.selectedWarehouseId) {
        $.ajax({
            type: "POST",
            url: wareHouseSearchUrl,
            headers: {
                "X-CSRF-TOKEN": csrfToken,
            },
            data: {
                term: "",
                page: 1,
                limit: 1,
                "total-count-mode": 1,
            },
            success: function (data) {
                const warehouses = data.warehouses || [];
                const selectedWarehouse = warehouses.find(
                    (w) => w.id == window.selectedWarehouseId
                );

                if (selectedWarehouse) {
                    const option = new Option(
                        selectedWarehouse.attributes.name,
                        selectedWarehouse.id,
                        true,
                        true
                    );
                    $("#warehouse")
                        .append(option)
                        .trigger("change", { suppress: true });
                }

                // Start preselecting bin locations after warehouse is set
                preselectBinLocations();
            },
        });
    } else {
        // If no warehouse is preselected, just allow regular behavior
        isPreselecting = false;
    }

    $("#warehouse").select2({
        placeholder: "magazijn",
        ajax: {
            url: wareHouseSearchUrl,
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            dataType: "json",
            delay: 250,
            data: function (params) {
                // Prepare data for the API request
                if (params.term) {
                    currentPageWarehouse = 1; // Reset page to 1 when a term is typed
                }

                return {
                    page: currentPageWarehouse, // Send the current page
                    limit: 25, // Limit the results per page
                    term: params.term || "", // Search term entered by the user
                    "total-count-mode": 1, // Fetch total count if needed
                };
            },
            processResults: function (data) {
                // Check if we've reached the end of the results
                isEndOfResultsWarehouse = data.warehouses.length < 25;

                // Map results to Select2 format
                const results = data.warehouses.map(function (warehouse) {
                    return {
                        id: warehouse.id,
                        text: warehouse.attributes.name,
                    };
                });
                return {
                    results: results,
                    pagination: {
                        more: !isEndOfResultsWarehouse, // Show 'more' if there are more results
                    },
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
            },
        },
    });

    // When dropdown is opened, reset the page number and flags
    $("#warehouse").on("select2:open", function () {
        currentPageWarehouse = 1; // Start from page 1
        isLoadingWarehouse = false;
        isEndOfResultsWarehouse = false;

        const dropdown = $(".select2-results__options");

        // Scroll event handler to trigger the next API request when scrolling to the bottom
        dropdown.on("scroll", function () {
            const scrollTop = dropdown.scrollTop();
            const containerHeight = dropdown.innerHeight();
            const scrollHeight = dropdown[0].scrollHeight;

            // If we're at the bottom of the dropdown and more results are available
            if (
                scrollTop + containerHeight >= scrollHeight - 10 &&
                !isEndOfResultsWarehouse
            ) {
                isLoadingWarehouse = true; // Set loading flag to true to prevent multiple requests

                currentPageWarehouse++;

                // Trigger the next page load by opening the dropdown
                $("#warehouse").select2("open");
            }
        });
    });

    // Optionally, handle closing the dropdown manually if required
    $("#warehouse").on("select2:close", function () {
        // Reset page when dropdown is closed, if needed
        currentPageWarehouse = 1;
        isLoadingWarehouse = false; // Reset loading flag when dropdown closes
        isEndOfResultsWarehouse = false; // Reset end of results flag
    });

    $("#warehouse").on("change", function () {
        if (isPreselecting) return; // Skip reset if preselecting

        $("#binLocation").val(null).empty().trigger("change");
    });

    // ------------------------------------------------  Bin Location Selection -------------------------------------------

    const binLocationSearchUrl = $("#route-container-bin-location").data(
        "bin-location-search"
    );

    let currentPageBinLocation = 1; // Start from the first page
    let isLoadingBinLocation = false;
    let isEndOfResultsBinLocation = false;

    function preselectBinLocations() {
        if (
            window.selectedBinLocationIds &&
            window.selectedBinLocationIds.length > 0
        ) {
            $("#full-page-preloader").show();
            $.ajax({
                type: "POST",
                url: binLocationSearchUrl,
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                },
                data: {
                    term: "",
                    page: 1,
                    limit: 1,
                    "total-count-mode": 1,
                    warehouseId: window.selectedWarehouseId,
                    filter: [
                        {
                            type: "equalsAny",
                            field: "id",
                            value: window.selectedBinLocationIds,
                        },
                    ],
                },
                success: function (data) {
                    const binLocations = data.binLocations || [];
                    binLocations.forEach((bin) => {
                        const option = new Option(
                            bin.attributes.code,
                            bin.id,
                            true,
                            true
                        );
                        $("#binLocation").append(option);
                    });
                    $("#binLocation").trigger("change");
                    $("#full-page-preloader").hide();
                    isPreselecting = false;
                },
                error: function () {
                    isPreselecting = false;
                },
            });
        } else {
            isPreselecting = false;
        }
    }

    $("#binLocation").select2({
        placeholder: "locaties van bakken",
        ajax: {
            url: binLocationSearchUrl,
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            dataType: "json",
            delay: 250,
            data: function (params) {
                // Prepare data for the API request
                if (params.term) {
                    currentPageBinLocation = 1; // Reset page to 1 when a term is typed
                }

                return {
                    page: currentPageBinLocation, // Send the current page
                    limit: 25, // Limit the results per page
                    term: params.term || "", // Search term entered by the user
                    "total-count-mode": 1, // Fetch total count if needed
                    warehouseId: $("#warehouse").val() || "",
                };
            },
            processResults: function (data) {
                // Check if we've reached the end of the results
                isEndOfResultsBinLocation = data.binLocations.length < 25;

                // Map results to Select2 format
                const results = data.binLocations.map(function (binLocation) {
                    // Check if this bin location is in the selected IDs
                    const isSelected =
                        window.selectedBinLocationIds &&
                        window.selectedBinLocationIds.includes(binLocation.id);

                    return {
                        id: binLocation.id,
                        text: binLocation.attributes.code,
                        selected: isSelected,
                    };
                });
                return {
                    results: results,
                    pagination: {
                        more: !isEndOfResultsBinLocation, // Show 'more' if there are more results
                    },
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
            },
        },
    });

    // When dropdown is opened, reset the page number and flags
    $("#binLocation").on("select2:open", function () {
        currentPageBinLocation = 1; // Start from page 1
        isLoadingBinLocation = false;
        isEndOfResultsBinLocation = false;

        const dropdown = $(".select2-results__options");

        // Scroll event handler to trigger the next API request when scrolling to the bottom
        dropdown.on("scroll", function () {
            const scrollTop = dropdown.scrollTop();
            const containerHeight = dropdown.innerHeight();
            const scrollHeight = dropdown[0].scrollHeight;

            // If we're at the bottom of the dropdown and more results are available
            if (
                scrollTop + containerHeight >= scrollHeight - 10 &&
                !isEndOfResultsBinLocation
            ) {
                isLoadingBinLocation = true; // Set loading flag to true to prevent multiple requests

                currentPageBinLocation++;

                // Trigger the next page load b y opening the dropdown
                $("#binLocation").select2("open");
            }
        });
    });

    // Optionally, handle closing the dropdown manually if required
    $("#binLocation").on("select2:close", function () {
        // Reset page when dropdown is closed, if needed
        currentPageBinLocation = 1;
        isLoadingBinLocation = false; // Reset loading flag when dropdown closes
        isEndOfResultsBinLocation = false; // Reset end of results flag
    });
});
