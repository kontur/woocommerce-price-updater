/**
 * Encapuslated interface interactions and AJAX handling for the Price Bulk Updater
 */
(function($) {

    /** 
     * Declare all recuring variables only once, including $-prefixed jQuery
     * collections
     */
    var $element = $("#woocommerce-price-updater"),

        rowSelector = ".woocommerce-price-updater-row",
        disableClass = "woocommerce-price-updater-disabled",
        matchesClass = "has-matches",
        hiddenClass = "hidden-initially",

        $fieldsMatch = $("#woocommerce-price-updater-match"),
        $fieldsPrices = $("#woocommerce-price-updater-prices"),

        $methodSelect = $("select[name='method']"),

        $inputPrice = $("input[name='price']"),
        $inputRegular = $("input[name='regular']"),
        $inputSale = $("input[name='sale']"),
        $inputSearch = $("input[name='search']"),
        $inputCategory = $("input[name='category']"),
        $inputNewPrice = $("input[name='new_price']"),
        $inputNewRegular = $("input[name='new_regular']"),
        $inputNewSale = $("input[name='new_sale']"),

        $matches = $("#woocommerce-price-updater-matches"),
        $matchesWrapper = $("#woocommerce-price-updater-matches-wrapper"),
        $submit = $("#submit")

    // on init remove all possibly selected values
    $element.find("input[type='checkbox']:checked").removeAttr("checked")
    $element.find("input[type='text']").attr("disabled", "disabled").attr("value", "")
    checkSubmitAllowed()

    /**
     * Register event listeners
     */

    // enable price rows when checkbox clicked
    $fieldsPrices.on("change", "input[type='checkbox']", toggleDisableRow)

    // all inputs that take prices undergo formatting
    $.each([$inputPrice, $inputRegular, $inputSale, $inputNewPrice, $inputNewRegular, $inputNewSale], function(index, $input) {
        $input.on("change keyup", formatNumber)
    })

    // perform ajax search for matches
    $fieldsMatch.on("change keyup", "input, select", getMatches)


    /**
     * Callback to enable or disable a row with inputs
     */
    function toggleDisableRow() {
        var checked = $(this).is(":checked"),
            $row = $(this).closest(rowSelector),
            $input = $row.find("input[type='text']")

        if (checked) {
            $input.removeAttr("disabled")
            $row.removeClass(disableClass)
        } else {
            $input.attr("disabled", "disabled")
            $row.addClass(disableClass)
        }
        checkSubmitAllowed()
    }

    /**
     * Serialize the current form data and fetch a list of matched products
     * which will be affected by the submit
     * 
     * @param event event 
     */
    function getMatches(event) {
        if (event) {
            // if update was triggered by click on a checkbox first update the row status
            if ($(this).is("[type='checkbox']")) {
                toggleDisableRow.apply(this)
            }

            // if the update was triggered by a text input make sure the actual value has changed
            if ($(this).is("[type='text']") && $(this).data("value") && $(this).data("value") === $(this).val()) {
                return
            }
            $(this).data("value", $(this).val())
        }

        $matches.removeClass(matchesClass).children().remove()
        $matchesWrapper.removeClass(hiddenClass)
            .find("code").addClass("loading").html("&nbsp;")
        $submit.attr("disabled", "disabled")

        var data = {
            "action": "woocommerce_price_updater_match_products",
            "nonce": woocommerce_price_updater.nonce,
            "method": $methodSelect.val()
        }

        $.each([$inputPrice, $inputRegular, $inputSale, $inputSearch, $inputCategory], function(index, $input) {
            if (!$input.is(":disabled")) {
                data[$input.attr("name")] = $input.val()
            }
        })

        // delete empty string for active search term
        if (typeof data.search !== "undefined" && data.search === "") {
            delete data.search
        }

        // delete empty string for active category
        if (typeof data.category !== "undefined" && data.category === "") {
            delete data.category
        }

        $.post(ajaxurl, data, updateMatches)
        checkSubmitAllowed()
    }

    /**
     * Callback to handle the JSON string result containing an array of matches
     * 
     * @param string(JSON) result 
     */
    function updateMatches(result) {
        if (result) {
            $matches.children().remove()
            try {
                result = JSON.parse(result)
                if (result && result.length > 0) {
                    var append = "<ul>"

                    setTotalMatches(result.length)

                    $.each(result, function(index, result) {
                        append += "<li><span>" + result.post_title + "</span> <span>Current price: <pre>" +
                            result.price + "</pre></span> <span>Regular price: <pre>" + result.regular +
                            "</pre></span> <span>Sales price: <pre>" + result.sale +
                            "</pre></span> <span>Categories: <pre>" + result.categories +
                            "</pre></span> <span class='" + result.post_status + "'>Status: " +
                            result.post_status + "</span></li>";
                    })
                    append += "</ul>"
                    $matches.append(append).addClass(matchesClass)
                } else {
                    setTotalMatches(0)
                }
            } catch (error) {
                console.warn(error)
            }
        }
    }

    /**
     * Update the match counter
     * 
     * @param int num 
     */
    function setTotalMatches(num) {
        num = parseInt(num)
        $matchesWrapper.removeClass("single multiple").addClass(num !== 1 ? "multiple" : "single")
            .find("code").removeClass("loading").html(num)
    }

    /**
     * Callback to transform number input to valid floats
     */
    function formatNumber() {
        var val = $(this).val()

        // only allow single . as decimal separator
        val = val.replace(/[,;:]|\.{2,}/gi, ".")

        // everything (including whitespace within or around) that's not a 
        // digit or a . gets tossed
        val = val.replace(/[^\d\.]/gi, "")

        $(this).attr("value", val)
    }

    /** 
     * Check if enough info is provided to allow a submit. Validation is performed
     * server side as well, but this is a bare minimum sanity check we have some
     * data to work with
     */
    function checkSubmitAllowed() {
        if ($fieldsMatch.has("input:checked").length > 0 && $fieldsPrices.has("input:checked").length > 0) {
            $submit.removeAttr("disabled")
        } else {
            $submit.attr("disabled", "disabled")
        }
    }

})(jQuery)