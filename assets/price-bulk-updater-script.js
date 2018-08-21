(function($) {

    var $element = $("#woocommerce-price-bulk-updater"),

        rowSelector = ".woocommerce-price-bulk-updater-row",
        disableClass = "woocommerce-price-bulk-updater-disabled",
        matchesClass = "has-matches",

        $fieldsMatch = $("#woocommerce-price-bulk-updater-match"),
        $fieldsPrices = $("#woocommerce-price-bulk-updater-prices"),

        $inputPrice = $("input[name='current_price']"),
        $inputSale = $("input[name='current_sale']"),
        $inputSearch = $("input[name='product_name']"),
        $inputNewPrice = $("input[name='new_price']"),
        $inputNewSale = $("input[name='new_sale']"),

        $matches = $("#woocommerce-price-bulk-updater-matches"),
        $matchesWrapper = $("#woocommerce-price-bulk-updater-matches-wrapper"),
        $submit = $("#submit")

    // on load remove all possibly selected values
    $element.find("input[type='checkbox']:checked").removeAttr("checked")
    $element.find("input[type='text']").attr("disabled", "disabled").attr("value", "")

    /**
     * Register event listeners
     */

    // enable price rows when checkbox clicked
    $fieldsPrices.on("change", "input[type='checkbox']", toggleDisableRow)

    // all inputs that take prices undergo formatting
    $.each([$inputPrice, $inputSale, $inputNewPrice, $inputNewSale], function(index, $input) {
        $input.on("change keyup", formatNumber)
    })

    // perform ajax search for matches
    $fieldsMatch.on("change keyup", "input", getMatches)


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
        $matchesWrapper.find("code").addClass("loading").html("&nbsp;")
        $submit.attr("disabled", "disabled")

        var data = {
            "action": "bulk_price_updater_match_products",
            "nonce": price_bulk_updater.nonce,
        }

        $.each([$inputPrice, $inputSale, $inputSearch], function(index, $input) {
            if (!$input.is(":disabled")) {
                data[$input.data("param")] = $input.val()
            }
        })

        if (typeof data.search !== "undefined" && data.search === "") {
            delete data.search
        }

        $.post(ajaxurl, data, updateMatches)
    }

    function updateMatches(result) {
        if (result) {
            $matches.children().remove()
            try {
                result = JSON.parse(result)
                if (result && result.length > 0) {
                    var append = "<ul>"

                    setTotalMatches(result.length)

                    $.each(result, function(index, result) {
                        append += "<li><span>" + result.post_title + "</span> <span>Price: <pre>" +
                            result.price + "</pre></span> <span>Sales price: <pre>" + result.sale +
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

    function setTotalMatches(num) {
        $matchesWrapper.removeClass("single multiple").addClass(num !== 1 ? "multiple" : "single")
            .find("code").removeClass("loading").html(num)
    }

    function formatNumber() {
        var val = $(this).val()

        // only allow single . as decimal separator
        val = val.replace(/[,;:]|\.{2,}/gi, ".")

        // everything (including whitespace within or around) that's not a 
        // digit or a . gets tossed
        val = val.replace(/[^\d\.]/gi, "")

        $(this).attr("value", val)
    }

    function checkSubmitAllowed() {
        if ($fieldsMatch.has("input:checked").length > 0 && $fieldsPrices.has("input:checked").length > 0) {
            $submit.removeAttr("disabled")
        } else {
            $submit.attr("disabled", "disabled")
        }
    }

})(jQuery)