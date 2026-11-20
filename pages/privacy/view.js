$(document).ready(function () {
    var $tocLinks = $(".oar-privacy-toc-list a");

    $tocLinks.on("click", function (e) {
        var href = $(this).attr("href");
        if (href && href.charAt(0) === "#") {
            var $target = $(href);
            if ($target.length) {
                e.preventDefault();
                $("html, body").animate({
                    scrollTop: $target.offset().top - 92
                }, 280);
                $tocLinks.removeClass("active");
                $(this).addClass("active");
            }
        }
    });

    $(window).on("scroll", function () {
        var scrollPos = $(window).scrollTop() + 130;
        $(".oar-privacy-card[id]").each(function () {
            var top = $(this).offset().top;
            var bottom = top + $(this).outerHeight();
            if (scrollPos >= top && scrollPos < bottom) {
                var id = $(this).attr("id");
                $tocLinks.removeClass("active");
                $tocLinks.filter('[href="#' + id + '"]').addClass("active");
            }
        });
    });
});
