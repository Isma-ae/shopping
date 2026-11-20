$(document).ready(function () {
    // 1. Custom pill dots synced with Slick1 hero banner
    var $heroSlider = $(".home-hero .slick1");
    var $dotsWrap = $("#homeHeroDots");

    if ($heroSlider.length && $dotsWrap.length) {
        var slideCount = $heroSlider.find(".item-slick1:not(.slick-cloned)").length;

        if (slideCount > 1) {
            var dotsHtml = "";
            for (var i = 0; i < slideCount; i++) {
                var activeCls = i === 0 ? " is-active" : "";
                dotsHtml +=
                    '<button type="button" class="home-hero-dot' + activeCls +
                    '" data-slide-index="' + i +
                    '" aria-label="ไปยังสไลด์ที่ ' + (i + 1) + '"></button>';
            }
            $dotsWrap.html(dotsHtml);

            $dotsWrap.on("click", ".home-hero-dot", function () {
                var idx = Number($(this).data("slide-index"));
                if ($heroSlider.hasClass("slick-initialized")) {
                    $heroSlider.slick("slickGoTo", idx);
                }
            });

            $heroSlider.on("afterChange", function (event, slick, currentSlide) {
                $dotsWrap.find(".home-hero-dot").removeClass("is-active");
                $dotsWrap.find('.home-hero-dot[data-slide-index="' + currentSlide + '"]').addClass("is-active");
            });
        } else {
            $dotsWrap.hide();
        }
    }

    // 2. Smooth scroll helper accounting for fixed desktop navbar
    function scrollToSection(selector) {
        var $target = $(selector);
        if (!$target.length) return;
        var offsetTop = Math.max(0, $target.offset().top - 90);
        $("html, body").animate({ scrollTop: offsetTop }, 450);
    }

    // 3. Category showcase card -> filter product grid & scroll
    $(".js-home-cat-trigger").on("click", function () {
        var targetFilter = $(this).attr("data-target-filter");
        if (targetFilter) {
            var $pill = $('.home-products .filter-tope-group button[data-filter="' + targetFilter + '"]');
            if ($pill.length) {
                $pill.trigger("click");
            }
        }
        scrollToSection("#home-products");
    });

    // 4. Smooth scroll when clicking banner links pointing to #home-products
    $('.home-hero a.item-slick1[href="#home-products"]').on("click", function (e) {
        e.preventDefault();
        scrollToSection("#home-products");
    });
});
