$(document).ready(function () {
    function escapeHtml(str) {
        if (str === null || str === undefined) return "";
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    function getSelectedColor() {
        var $checkedRadio = $('input[type="radio"][name="variant_color"]:checked');
        if ($checkedRadio.length > 0) {
            return $checkedRadio.val();
        }
        var $hidden = $('input[type="hidden"][name="variant_color"]');
        if ($hidden.length > 0) {
            return $hidden.val();
        }
        return "";
    }

    function getSelectedSize(requireSelection) {
        var $sizeRadios = $('input[type="radio"][name="variant_size"]');
        if ($sizeRadios.length > 0) {
            var $checked = $sizeRadios.filter(":checked");
            if ($checked.length > 0) {
                return { ok: true, value: $checked.val() };
            }
            if (requireSelection) {
                return { ok: false, value: "" };
            }
            return { ok: true, value: "" };
        }
        var $hiddenSize = $('input[type="hidden"][name="variant_size"]');
        if ($hiddenSize.length > 0) {
            return { ok: true, value: $hiddenSize.val() };
        }
        return { ok: true, value: "" };
    }

    function getCartQty() {
        var qty = parseInt($('[name="cart_qty"]').val(), 10);
        if (isNaN(qty) || qty < 1) {
            qty = 1;
            $('[name="cart_qty"]').val(1);
        }
        return qty;
    }

    function flyToCart(onComplete) {
        var done = typeof onComplete === "function" ? onComplete : function () {};
        var prefersReduced = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
        var $cartIcon = $(".js-show-cart:visible").first();
        var $sourceImg = $(".slick3-main .slick-current img:visible").first();
        if (!$sourceImg.length) {
            $sourceImg = $(".detail-main-slide img:visible").first();
        }

        if (prefersReduced || !$cartIcon.length || !$sourceImg.length) {
            done();
            return;
        }

        var imgRect = $sourceImg.get(0).getBoundingClientRect();
        var cartRect = $cartIcon.get(0).getBoundingClientRect();

        var startSize = Math.min(110, Math.max(72, imgRect.width * 0.28));
        var endSize = 22;

        var startX = imgRect.left + imgRect.width / 2;
        var startY = imgRect.top + imgRect.height / 2;
        var endX = cartRect.left + cartRect.width / 2;
        var endY = cartRect.top + cartRect.height / 2;

        // Control point lifted slightly upward for a smooth parabolic arc
        var ctrlX = (startX + endX) / 2;
        var ctrlY = Math.min(startY, endY) - Math.min(140, Math.max(60, Math.abs(startX - endX) * 0.22));

        var $ghost = $("<img>", {
            src: $sourceImg.attr("src"),
            alt: "",
            "class": "fly-to-cart-ghost"
        }).css({
            left: (startX - startSize / 2) + "px",
            top: (startY - startSize / 2) + "px",
            width: startSize + "px",
            height: startSize + "px",
            opacity: 0.96
        }).appendTo("body");

        var duration = 700;
        var startTime = null;

        function step(timestamp) {
            if (!startTime) startTime = timestamp;
            var elapsed = timestamp - startTime;
            var t = Math.min(1, elapsed / duration);
            // Smooth cubic ease-in-out
            var ease = t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;

            var inv = 1 - ease;
            var curX = inv * inv * startX + 2 * inv * ease * ctrlX + ease * ease * endX;
            var curY = inv * inv * startY + 2 * inv * ease * ctrlY + ease * ease * endY;
            var curSize = startSize + (endSize - startSize) * ease;
            var curOpacity = t > 0.78 ? Math.max(0.15, 1 - (t - 0.78) / 0.22) : 0.96;

            $ghost.css({
                left: (curX - curSize / 2) + "px",
                top: (curY - curSize / 2) + "px",
                width: curSize + "px",
                height: curSize + "px",
                opacity: curOpacity,
                transform: "rotate(" + (ease * 12) + "deg)"
            });

            if (t < 1) {
                window.requestAnimationFrame(step);
            } else {
                $ghost.remove();
                $cartIcon.removeClass("cart-icon-bump");
                // Force reflow so repeat clicks re-trigger bump animation
                void $cartIcon.get(0).offsetWidth;
                $cartIcon.addClass("cart-icon-bump");
                setTimeout(function () {
                    $cartIcon.removeClass("cart-icon-bump");
                }, 480);
                done();
            }
        }

        window.requestAnimationFrame(step);
    }

    // Prevent quantity counter from dropping to 0 via js/main.js click handler
    $("#qty-down").on("click", function () {
        setTimeout(function () {
            getCartQty();
        }, 0);
    });

    function updateStockBadge(item) {
        var $badge = $("#detail-stock-badge");
        if (!item) {
            $badge.addClass("d-none");
            return;
        }
        var rem = parseInt(item.remaining_stock, 10);
        var reserveId = parseInt(item.reserve_id, 10);
        $badge.removeClass("d-none is-in-stock is-low-stock is-reserve is-out");

        if (isNaN(rem)) {
            $badge.addClass("d-none");
        } else if (rem <= 0 && reserveId === 1) {
            $badge.addClass("is-reserve").text("สินค้าหมด · เปิดให้จอง");
        } else if (rem <= 0) {
            $badge.addClass("is-out").text("สินค้าหมดชั่วคราว");
        } else if (rem <= 5) {
            $badge.addClass("is-low-stock").text("เหลือ " + rem + " ชิ้น");
        } else {
            $badge.addClass("is-in-stock").text("มีสินค้าพร้อมส่ง (" + rem + " ชิ้น)");
        }
    }

    function select_price(size) {
        var product_id = $("#product_id").val();
        var variant_color = getSelectedColor();
        $.ajax({
            type: "post",
            url: "api/detail.php",
            data: {
                fn: "select_price",
                product_id: product_id,
                variant_color: variant_color,
                variant_size: size
            },
            dataType: "json",
            success: function (response) {
                if (response && response.data && response.data.length > 0) {
                    var item = response.data[0];
                    var saleVal = Number(item.variant_sale || 0);
                    if (saleVal > 0) {
                        $(".price-data").text("฿" + saleVal.toLocaleString("th-TH", { maximumFractionDigits: 0 }));
                    }
                    updateStockBadge(item);
                }
            }
        });
    }

    function select_size() {
        var product_id = $("#product_id").val();
        var variant_color = getSelectedColor();
        var prevSize = $('input[type="radio"][name="variant_size"]:checked').val() || "";

        $.ajax({
            type: "post",
            url: "api/detail.php",
            data: {
                fn: "select_size",
                product_id: product_id,
                variant_color: variant_color
            },
            dataType: "json",
            success: function (response) {
                var rows = (response && response.data) ? response.data : [];
                var $sizeBox = $(".size-data");

                if (rows.length > 1) {
                    var html = '<div class="detail-option-label">ขนาด</div>';
                    html += '<div class="selectgroup selectgroup-pills">';
                    var matchedPrev = false;

                    $.each(rows, function (i, v) {
                        var isChecked = prevSize !== "" && v.variant_size === prevSize;
                        if (isChecked) matchedPrev = true;
                        var detailText = (v.size_detail && $.trim(v.size_detail) !== "")
                            ? " (" + escapeHtml(v.size_detail) + ")"
                            : "";
                        var sizeText = (v.variant_size && $.trim(v.variant_size) !== "")
                            ? escapeHtml(v.variant_size)
                            : "มาตรฐาน";

                        html += '<label class="selectgroup-item">';
                        html += '   <input type="radio" name="variant_size" value="' + escapeHtml(v.variant_size) + '" class="selectgroup-input"' + (isChecked ? ' checked="checked"' : "") + ' />';
                        html += '   <span class="selectgroup-button">' + sizeText + detailText + "</span>";
                        html += "</label>";
                    });

                    html += "</div>";
                    $sizeBox.removeClass("d-none").html(html);

                    if (matchedPrev) {
                        select_price(prevSize);
                    }
                } else {
                    // Single size or no size option: hide UI completely, keep exact value in hidden input
                    var singleSize = rows.length === 1 ? (rows[0].variant_size || "") : "";
                    $sizeBox
                        .addClass("d-none")
                        .html('<input type="hidden" name="variant_size" value="' + escapeHtml(singleSize) + '">');
                    select_price(singleSize);
                }

                $('input[type="radio"][name="variant_size"]').off("change").on("change", function () {
                    select_price($(this).val());
                });
            }
        });
    }

    // Color radio change -> sync gallery image + reload sizes
    $('input[type="radio"][name="variant_color"]').on("change", function () {
        var imgSrc = ($(this).attr("color-img") || "").trim();
        if (imgSrc !== "") {
            var $wrap = $(this).closest(".sec-product-detail");
            var $main = $wrap.find(".slick3-main");
            var $nav = $wrap.find(".slick3-nav");
            var index = $main.find("img").index($main.find('img[src="' + imgSrc + '"]'));

            if (index >= 0 && $main.hasClass("slick-initialized")) {
                $main.slick("slickSetOption", "asNavFor", null, false);
                $main.slick("slickGoTo", index);
                $nav.find(".slick-slide").removeClass("slick-current");
                $nav.find('[data-slick-index="' + index + '"]').addClass("slick-current");
                if ($nav.length > 0) {
                    $main.slick("slickSetOption", "asNavFor", ".slick3-nav", false);
                }
            }
        }
        select_size();
    });

    select_size();

    // Add to Cart
    $("#add-cart").on("click", function (e) {
        e.preventDefault();
        var sizeRes = getSelectedSize(true);
        if (!sizeRes.ok) {
            swal("กรุณาเลือกขนาดสินค้า", "", "warning");
            return;
        }

        $.ajax({
            type: "post",
            url: "api/addcart.php",
            data: {
                fn: "add_cart",
                product_id: $("#product_id").val(),
                product_name: $.trim($(".product_name").text()),
                variant_color: getSelectedColor(),
                variant_size: sizeRes.value,
                cart_qty: getCartQty()
            },
            dataType: "json",
            success: function (res) {
                if (res.data === "r") {
                    swal({
                        title: res.title,
                        text: res.message,
                        icon: res.icon,
                        buttons: {
                            cancel: {
                                text: "ไม่จอง",
                                visible: true,
                                className: "swal-button--cancel",
                                closeModal: true
                            },
                            confirm: {
                                text: "จองสินค้า",
                                visible: true,
                                className: "swal-button--confirm",
                                closeModal: true
                            }
                        }
                    }).then(function (willInsert) {
                        if (willInsert) {
                            reserve_product();
                        }
                    });
                } else if (res.data === "f") {
                    swal({
                        title: res.title,
                        text: res.message,
                        icon: res.icon,
                        buttons: {
                            cancel: {
                                text: "ยกเลิก",
                                visible: true,
                                className: "swal-button--cancel",
                                closeModal: true
                            },
                            confirm: {
                                text: "เข้าสู่ระบบ",
                                visible: true,
                                className: "swal-button--confirm",
                                closeModal: true
                            }
                        }
                    }).then(function (willLogin) {
                        if (willLogin) {
                            window.location.href = "./login";
                        }
                    });
                } else if (res.data === "t") {
                    flyToCart(function () {
                        load_qty();
                        swal({
                            title: res.title,
                            text: res.message,
                            icon: res.icon,
                            timer: 1800,
                            buttons: false
                        });
                    });
                } else {
                    swal(res.title, res.message, res.icon);
                }
            }
        });
    });

    function reserve_product() {
        var sizeRes = getSelectedSize(false);
        $.ajax({
            type: "post",
            url: "api/addcart.php",
            data: {
                fn: "reserve_product",
                product_id: $("#product_id").val(),
                product_name: $.trim($(".product_name").text()),
                variant_color: getSelectedColor(),
                variant_size: sizeRes.value,
                cart_qty: getCartQty()
            },
            dataType: "json",
            success: function (res) {
                if (res.data === "t") {
                    flyToCart(function () {
                        load_qty();
                        swal({
                            title: res.title,
                            text: res.message,
                            icon: res.icon,
                            timer: 1800,
                            buttons: false
                        });
                    });
                } else {
                    swal(res.title, res.message, res.icon);
                }
            }
        });
    }

    // Buy Now
    $("#buy-product").on("click", function (e) {
        e.preventDefault();
        var sizeRes = getSelectedSize(true);
        if (!sizeRes.ok) {
            swal("กรุณาเลือกขนาดสินค้า", "", "warning");
            return;
        }

        $.ajax({
            type: "post",
            url: "api/addcart.php",
            data: { fn: "check_login" },
            dataType: "json",
            success: function (res) {
                if (res.data === "f") {
                    swal({
                        title: res.title,
                        text: res.message,
                        icon: res.icon,
                        buttons: {
                            cancel: {
                                text: "ยกเลิก",
                                value: "cancel",
                                visible: true,
                                className: "swal-button--cancel",
                                closeModal: true
                            },
                            login: {
                                text: "เข้าสู่ระบบก่อน",
                                value: "login",
                                visible: true,
                                className: "swal-button--login",
                                closeModal: true
                            },
                            order: {
                                text: "สั่งซื้อทันที",
                                value: "order",
                                visible: true,
                                className: "swal-button--order",
                                closeModal: true
                            }
                        }
                    }).then(function (value) {
                        if (value === "order") {
                            swal("หากไม่เข้าสู่ระบบให้บันทึกเลขที่ใบสั่งซื้อ เพื่อติดตามสถานะสินค้า")
                                .then(function () {
                                    buy_product();
                                });
                        } else if (value === "login") {
                            window.location.href = "./login";
                        }
                    });
                } else {
                    buy_product();
                }
            }
        });
    });

    function buy_product() {
        var sizeRes = getSelectedSize(true);
        if (!sizeRes.ok) {
            swal("กรุณาเลือกขนาดสินค้า", "", "warning");
            return;
        }
        var cartQty = getCartQty();
        $.ajax({
            type: "post",
            url: "api/addcart.php",
            data: {
                fn: "buy_product",
                product_id: $("#product_id").val(),
                product_name: $.trim($(".product_name").text()),
                variant_color: getSelectedColor(),
                variant_size: sizeRes.value,
                cart_qty: cartQty
            },
            dataType: "json",
            success: function (res) {
                if (res.data === "t") {
                    window.location.href = "?page=order&variant=" + encodeURIComponent(res.variant_id) + "&qty=" + cartQty;
                } else {
                    swal(res.title, res.message, res.icon);
                }
            }
        });
    }

    // Reviews
    var offset = 0;
    var limit = 5;
    var totalReviews = 0;

    function loadReviews() {
        var product_id = $("#product_id").val();
        $.ajax({
            type: "POST",
            url: "api/review.php",
            data: {
                fn: "select_review",
                product_id: product_id,
                limit: limit,
                offset: offset
            },
            dataType: "json",
            success: function (res) {
                totalReviews = Number(res.total || 0);
                if (totalReviews > 0) {
                    $("#review-count-badge").text("(" + totalReviews + ")");
                } else {
                    $("#review-count-badge").text("");
                }

                var rows = (res && res.data) ? res.data : [];
                if (offset === 0 && rows.length === 0) {
                    $(".review-data").html('<p class="text-muted mb-0">ยังไม่มีรีวิวสำหรับสินค้านี้ เป็นคนแรกที่รีวิวเลย!</p>');
                    $("#load-more").hide();
                    return;
                }

                var html = "";
                $.each(rows, function (i, v) {
                    var score = Math.max(0, Math.min(5, parseInt(v.review_rating, 10) || 0));
                    var rating = "";
                    for (var s = 1; s <= 5; s++) {
                        rating += '<i class="zmdi ' + (s <= score ? "zmdi-star" : "zmdi-star-outline") + '"></i>';
                    }

                    html +=
                        '<div class="detail-review-item">' +
                            '<div class="detail-review-avatar">' +
                                '<img src="images/feedback.png" alt="AVATAR">' +
                            "</div>" +
                            '<div class="detail-review-body">' +
                                '<div class="detail-review-head">' +
                                    '<span class="detail-review-name">' + escapeHtml(v.name || "สมาชิก") + "</span>" +
                                    '<span class="fs-18 cl11">' + rating + "</span>" +
                                "</div>" +
                                '<p class="detail-review-text">' + escapeHtml(v.review_detail || "") + "</p>" +
                            "</div>" +
                        "</div>";
                });

                $(".review-data").append(html);
                offset += rows.length;

                if (offset >= totalReviews) {
                    $("#load-more").hide();
                } else {
                    $("#load-more").show();
                }
            }
        });
    }

    loadReviews();

    $("#load-more").on("click", function () {
        loadReviews();
    });

    $(".wrap-rating .item-rating").on("click", function () {
        var index = $(this).index();
        $(".wrap-rating .item-rating").each(function (i) {
            if (i <= index) {
                $(this).removeClass("zmdi-star-outline").addClass("zmdi-star");
            } else {
                $(this).removeClass("zmdi-star").addClass("zmdi-star-outline");
            }
        });
        $('[name="review_rating"]').val($(".wrap-rating .zmdi-star").length);
    });

    $("#insert-review").on("click", function (e) {
        e.preventDefault();
        $.ajax({
            type: "post",
            url: "api/review.php",
            data: {
                fn: "add_review",
                review_rating: $('[name="review_rating"]').val(),
                review_detail: $('[name="review_detail"]').val(),
                product_id: $("#product_id").val()
            },
            dataType: "json",
            success: function (res) {
                if (res.data === "f") {
                    swal({
                        title: res.title,
                        text: res.message,
                        icon: res.icon,
                        buttons: {
                            cancel: {
                                text: "ยกเลิก",
                                visible: true,
                                className: "swal-button--cancel",
                                closeModal: true
                            },
                            confirm: {
                                text: "เข้าสู่ระบบ",
                                visible: true,
                                className: "swal-button--confirm",
                                closeModal: true
                            }
                        }
                    }).then(function (willLogin) {
                        if (willLogin) {
                            window.location.href = "./login";
                        }
                    });
                } else if (res.data === "t") {
                    swal(res.title, res.message, res.icon);
                    offset = 0;
                    $(".review-data").empty();
                    loadReviews();
                    $('[name="review_rating"]').val(0);
                    $('[name="review_detail"]').val("");
                    $(".wrap-rating .item-rating").removeClass("zmdi-star").addClass("zmdi-star-outline");
                } else {
                    swal(res.title, res.message, res.icon);
                }
            }
        });
    });

    load_qty();
});
