$(document).ready(function () {
    // 1. Auto-submit when sort order changes
    $("#productSortSelect").on("change", function () {
        $("#productFilterForm").submit();
    });

    // 2. Keep visible count in sync when switching category pills
    $("#productCategoryFilters").on("click", ".oar-filter-pill", function () {
        var count = $(this).data("count");
        if (count !== undefined) {
            $("#productVisibleCount").text(count);
        }
    });

    // 3. Apply initial category filter if ?category=<id> is provided in URL
    var initialCat = Number($(".product-catalog-section").data("initial-category") || 0);
    if (initialCat > 0) {
        $(window).on("load", function () {
            var $targetBtn = $('#productCategoryFilters .oar-filter-pill[data-cat-id="' + initialCat + '"]');
            if ($targetBtn.length) {
                $targetBtn.trigger("click");
            }
        });
    }
});
