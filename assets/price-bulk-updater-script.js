(function($) {
    console.log("$",$)
    console.log("jquery", jQuery)

    var $element = $("#woocommerce-price-bulk-updater"),
        rowSelector = ".woocommerce-price-bulk-updater-row",
        disableClass = "woocommerce-price-bulk-updater-disabled"

    $element.on("change", "input[type='checkbox']", toggleDisableRow)
    $element.find("input[type='checkbox']:checked").removeAttr("checked")
    $element.find("input[type='text']").attr("disabled", "disabled")

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
    }

})(jQuery);