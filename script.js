$(document).ready(function () {
    load_qty();

    $('#logout').click(function (e) {
        e.preventDefault();
        $.ajax({
            type: "post",
            url: "login/action.php",
            data: {
                fn: "logout"
            },
            dataType: "json",
            success: function (response) {
                if (response.data == 't') {
                    location.reload();
                } else {
                    alert('error');
                }
            }
        });
    });

    update_stock();

    function update_stock() {
        $.ajax({
            type: "post",
            url: "api/update_stock.php",
            dataType: "json",
            success: function (response) {

            }
        });
    }

    function updateMobileSearchWidth($searchWrap) {
        var $mobileBar = $searchWrap.closest('.wrap-header-mobile');
        if (!$mobileBar.length) return;
        var barRect = $mobileBar.get(0).getBoundingClientRect();
        var wrapRect = $searchWrap.get(0).getBoundingClientRect();
        var availW = Math.floor(wrapRect.right - barRect.left - 12);
        if (availW < 170) availW = 170;
        $searchWrap.css('--oar-mobile-search-w', availW + 'px');
    }

    function openInlineSearch($searchWrap) {
        closeInlineSearch($('.oar-inline-search.is-open').not($searchWrap));
        if ($searchWrap.hasClass('oar-inline-search--mobile')) {
            updateMobileSearchWidth($searchWrap);
            $searchWrap.closest('.wrap-header-mobile').addClass('is-search-open');
        }
        $searchWrap.addClass('is-open');
        setTimeout(function () {
            $searchWrap.find('.oar-inline-search-input').trigger('focus');
        }, 120);
    }

    function closeInlineSearch($searchWrap) {
        if (!$searchWrap || !$searchWrap.length) return;
        $searchWrap.each(function () {
            var $wrap = $(this);
            $wrap.removeClass('is-open');
            $wrap.find('.oar-inline-search-input').val('').trigger('blur');
            if ($wrap.hasClass('oar-inline-search--mobile')) {
                $wrap.closest('.wrap-header-mobile').removeClass('is-search-open');
            }
        });
    }

    function submitInlineSearch($form) {
        var search = $.trim($form.find('.oar-inline-search-input').val());
        if (search.length > 0) {
            window.location.href = "?page=product&search=" + encodeURIComponent(search);
        } else {
            window.location.href = "?page=product";
        }
    }

    $(document).on('click', '.js-oar-search-toggle', function (e) {
        e.preventDefault();
        var $searchWrap = $(this).closest('.oar-inline-search');
        if (!$searchWrap.hasClass('is-open')) {
            openInlineSearch($searchWrap);
        } else {
            var val = $.trim($searchWrap.find('.oar-inline-search-input').val());
            if (val.length > 0) {
                submitInlineSearch($searchWrap.find('.oar-inline-search-form'));
            } else {
                $searchWrap.find('.oar-inline-search-input').trigger('focus');
            }
        }
    });

    $(document).on('click', '.js-oar-search-close', function (e) {
        e.preventDefault();
        closeInlineSearch($(this).closest('.oar-inline-search'));
    });

    $(document).on('submit', '.oar-inline-search-form', function (e) {
        e.preventDefault();
        submitInlineSearch($(this));
    });

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && $('.oar-inline-search.is-open').length) {
            closeInlineSearch($('.oar-inline-search.is-open'));
        }
    });

    $(document).on('click', function (e) {
        var $open = $('.oar-inline-search.is-open');
        if ($open.length && !$(e.target).closest('.oar-inline-search').length) {
            closeInlineSearch($open);
        }
    });

    $(window).on('resize', function () {
        var $mobileOpen = $('.oar-inline-search--mobile.is-open');
        if ($mobileOpen.length) {
            if ($(window).width() >= 992) {
                closeInlineSearch($mobileOpen);
            } else {
                updateMobileSearchWidth($mobileOpen);
            }
        }
    });
});

function load_qty() {
    $.ajax({
        type: "post",
        url: "api/load_qty.php",
        data: {
            user_id: $('#user_id').val()
        },
        dataType: "json",
        success: function (res) {
            if (res.data == 'f') {
                $('.js-show-cart').removeClass('icon-header-noti');
            } else {
                if (res.data[0].cart_qty > 0) {
                    $('.js-show-cart').addClass('icon-header-noti');
                    $('.js-show-cart').attr('data-notify', res.data[0].cart_qty);
                } else {
                    $('.js-show-cart').removeClass('icon-header-noti');
                }
            }
        }
    });
}