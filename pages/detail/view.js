$(document).ready(function () {
    $('input[name="variant_color"]').on('change', function () {
        var imgSrc = $(this).attr('color-img').trim();
        var $wrap = $(this).closest('.sec-product-detail'); // wrapper
        var $main = $wrap.find('.slick3-main');
        var $nav = $wrap.find('.slick3-nav');

        var index = $main.find('img').index($main.find('img[src="' + imgSrc + '"]'));

        if (index >= 0) {
            // ❌ ปิด sync ชั่วคราว (ป้องกัน nav เลื่อน)
            $main.slick('slickSetOption', 'asNavFor', null, false);

            // ✅ เปลี่ยนภาพหลัก
            $main.slick('slickGoTo', index);

            // ✅ อัปเดต active ของ thumbnail เอง
            $nav.find('.slick-slide').removeClass('slick-current');
            $nav.find('[data-slick-index="' + index + '"]').addClass('slick-current');

            // ✅ เปิด sync กลับ (ให้เลื่อนได้ตามปกติเมื่อคลิก thumbnail)
            $main.slick('slickSetOption', 'asNavFor', '.slick3-nav', false);
        }
    });

    select_size();

    function select_size() {
        var product_id = $('#product_id').val();
        var variant_color = $('[name="variant_color"]:checked').val();
        var size_old = $('[name="variant_size"]:checked').val();
        if (size_old !== "" && typeof size_old !== "undefined") {
            var size_check = size_old;
        } else {
            var size_check = "";
        }
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
                var html = '';
                var checked = '';
                var size_detail = '';
                if (response.data.length > 1) {
                    html += '<div class="size-203 respon6 p-b-10">';
                    html += '   <strong>ขนาด</strong>';
                    html += '</div>';
                    html += '<div class="selectgroup selectgroup-pills">';
                    $.each(response.data, function (i, v) {
                        if (v.variant_size == size_check) {
                            checked = ' checked=""';
                        } else {
                            checked = '';
                        }
                        if (v.size_detail != "") {
                            size_detail = ' (' + v.size_detail + ')';
                        } else {
                            size_detail = '';
                        }
                        html += '<label class="selectgroup-item">';
                        html += '   <input type="radio" name="variant_size" value="' + v.variant_size + '" class="selectgroup-input"' + checked + ' />';
                        html += '   <span class="selectgroup-button">' + v.variant_size + size_detail + '</span>';
                        html += '</label>';
                    });
                    html += '</div>';
                } else {
                    var variant_size = "";
                    select_price(variant_size);
                }
                $('.size-data').html(html);
                $('[name="variant_size"]').change(function (e) {
                    e.preventDefault();
                    var variant_size = $(this).val();
                    select_price(variant_size);
                });
            }
        });
    }

    function select_price(size) {
        var product_id = $('#product_id').val();
        var variant_color = $('[name="variant_color"]:checked').val();
        var variant_size = size;
        $.ajax({
            type: "post",
            url: "api/detail.php",
            data: {
                fn: "select_price",
                product_id: product_id,
                variant_color: variant_color,
                variant_size: variant_size
            },
            dataType: "json",
            success: function (response) {
                $('.price-data').html('฿' + response.data[0].variant_sale)
            }
        });
    }

    $('[name="variant_color"]').change(function (e) {
        e.preventDefault();
        select_size();
        var size = $('[name="variant_size"]:checked').val();
        if (size !== "" && typeof size !== "undefined") {
            select_price(size);
        }
    });

    $('#add-cart').click(function (e) {
        e.preventDefault();
        var product_id = $('#product_id').val();
        var product_name = $('.product_name').text();
        var color = $('[name="variant_color"]:checked').val();
        if (color !== "" && typeof color !== "undefined") {
            var variant_color = color;
        } else {
            var variant_color = "";
        }

        var variant_color = "";
        if ($('input[name="variant_color"]').length > 0) {
            variant_color = $('input[name="variant_color"]:checked').val();
        } else {
            variant_color = "";
        }
        var variant_size = "";
        if ($('input[name="variant_size"]').length > 0) {
            var checked = $('input[name="variant_size"]:checked');
            if (checked.length > 0) {
                variant_size = checked.val();
            } else {
                swal("กรุณาเลือกขนาดสินค้า", "", "warning");
                return;
            }
        } else {
            variant_size = "";
        }
        var qty = parseInt($('[name="cart_qty"]').val());
        if (isNaN(qty) || qty <= 0) {
            var cart_qty = 1;
            $('[name="cart_qty"]').val(1);
        } else {
            var cart_qty = qty;
        }
        $.ajax({
            type: "post",
            url: "api/addcart.php",
            data: {
                fn: "add_cart",
                product_id: product_id,
                product_name: product_name,
                variant_color: variant_color,
                variant_size: variant_size,
                cart_qty: cart_qty
            },
            dataType: "json",
            success: function (res) {
                if (res.data == 'r') {
                    swal({
                        title: res.title,
                        text: res.message,
                        icon: res.icon,
                        buttons: {
                            cancel: {
                                text: "ไม่จอง",
                                visible: true,
                                className: "swal-button--cancel",
                                closeModal: true,
                            },
                            confirm: {
                                text: "จองสินค้า",
                                visible: true,
                                className: "swal-button--confirm",
                                closeModal: true,
                            },
                        },
                    }).then((willInsert) => {
                        if (willInsert) {
                            reserve_product();
                        }
                    });
                } else if (res.data == 'f') {
                    swal({
                        title: res.title,
                        text: res.message,
                        icon: res.icon,
                        buttons: {
                            cancel: {
                                text: "ยกเลิก",
                                visible: true,
                                className: "swal-button--cancel",
                                closeModal: true,
                            },
                            confirm: {
                                text: "เข้าสู่ระบบ",
                                visible: true,
                                className: "swal-button--confirm",
                                closeModal: true,
                            },
                        },
                    }).then((willInsert) => {
                        if (willInsert) {
                            window.location.href = "./login"
                        }
                    });
                } else if (res.data == 't') {
                    swal(res.title, res.message, res.icon);
                    load_qty();
                } else {
                    swal(res.title, res.message, res.icon);
                }
            }
        });
    });

    function reserve_product() {
        var product_id = $('#product_id').val();
        var product_name = $('.product_name').text();
        var color = $('[name="variant_color"]:checked').val();
        if (color !== "" && typeof color !== "undefined") {
            var variant_color = color;
        } else {
            var variant_color = "";
        }
        var size = $('[name="variant_size"]:checked').val();
        if (size !== "" && typeof size !== "undefined") {
            var variant_size = size;
        } else {
            var variant_size = "";
        }
        var cart_qty = $('[name="cart_qty"]').val();
        $.ajax({
            type: "post",
            url: "api/addcart.php",
            data: {
                fn: "reserve_product",
                product_id: product_id,
                product_name: product_name,
                variant_color: variant_color,
                variant_size: variant_size,
                cart_qty: cart_qty
            },
            dataType: "json",
            success: function (res) {
                if (res.data == 't') {
                    swal(res.title, res.message, res.icon);
                    load_qty();
                } else {
                    swal(res.title, res.message, res.icon);
                }
            }
        });
    }

    $('#buy-product').click(function (e) {
        e.preventDefault();
        $.ajax({
            type: "post",
            url: "api/addcart.php",
            data: {
                fn: "check_login"
            },
            dataType: "json",
            success: function (res) {
                if (res.data == 'f') {
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
                                closeModal: true,
                            },
                            login: {
                                text: "เข้าสู่ระบบก่อน",
                                value: "login",
                                visible: true,
                                className: "swal-button--login",
                                closeModal: true,
                            },
                            order: {
                                text: "สั่งซื้อทันที",
                                value: "order",
                                visible: true,
                                className: "swal-button--order",
                                closeModal: true,
                            },
                        },
                    }).then((value) => {
                        switch (value) {
                            case "order":
                                buy_product();
                                break;

                            case "login":
                                window.location.href = "./login";
                                break;

                            case "cancel":
                            default:
                                break;
                        }
                    });

                } else {
                    buy_product();
                }

            }
        });
    });

    function buy_product() {
        var product_id = $('#product_id').val();
        var product_name = $('.product_name').text();
        var color = $('[name="variant_color"]:checked').val();
        if (color !== "" && typeof color !== "undefined") {
            var variant_color = color;
        } else {
            var variant_color = "";
        }

        var variant_color = "";
        if ($('input[name="variant_color"]').length > 0) {
            variant_color = $('input[name="variant_color"]:checked').val();
        } else {
            variant_color = "";
        }
        var variant_size = "";
        if ($('input[name="variant_size"]').length > 0) {
            var checked = $('input[name="variant_size"]:checked');
            if (checked.length > 0) {
                variant_size = checked.val();
            } else {
                swal("กรุณาเลือกขนาดสินค้า", "", "warning");
                return;
            }
        } else {
            variant_size = "";
        }
        var cart_qty = $('[name="cart_qty"]').val();
        $.ajax({
            type: "post",
            url: "api/addcart.php",
            data: {
                fn: "buy_product",
                product_id: product_id,
                product_name: product_name,
                variant_color: variant_color,
                variant_size: variant_size,
                cart_qty: cart_qty
            },
            dataType: "json",
            success: function (res) {
                if (res.data == 't') {
                    window.location.href = "?page=order&variant=" + res.variant_id + "&qty=" + cart_qty;
                } else {
                    swal(res.title, res.message, res.icon);
                }
            }
        });
    }

    let offset = 0;
    const limit = 5;
    let totalReviews = 0;

    // โหลดรีวิวครั้งแรก
    loadReviews();

    function loadReviews() {
        const product_id = $('#product_id').val();
        $.ajax({
            type: "POST",
            url: "api/review.php",
            data: {
                fn: "select_review",
                product_id,
                limit,
                offset
            },
            dataType: "json",
            success: function (res) {
                totalReviews = res.total;

                let html = '';
                $.each(res.data, function (i, v) {
                    let rating = '';
                    for (let i = 1; i <= 5; i++) {
                        rating += `<i class="zmdi ${i <= v.review_rating ? 'zmdi-star' : 'zmdi-star-outline'}"></i>`;
                    }

                    html += `
                        <div class="flex-w flex-t p-tb-20 bor12">
                            <div class="wrap-pic-s size-109 bor0 of-hidden m-r-18 m-t-6">
                                <img src="images/feedback.png" alt="AVATAR">
                            </div>
                            <div class="size-207">
                                <div class="flex-w flex-sb-m p-b-10">
                                    <span class="mtext-107 cl2 p-r-20">${v.name}</span>
                                    <span class="fs-18 cl11">${rating}</span>
                                </div>
                                <p class="stext-102 cl6">${v.review_detail}</p>
                            </div>
                        </div>
                    `;
                });

                $('.review-data').append(html);

                offset += res.data.length;

                // ซ่อนปุ่มถ้าโหลดครบ
                if (offset >= totalReviews) {
                    $('#load-more').hide();
                } else {
                    $('#load-more').show();
                }
            }
        });
    }

    // เมื่อคลิกปุ่มโหลดเพิ่ม
    $('#load-more').on('click', function () {
        loadReviews();
    });

    $('.wrap-rating .item-rating').on('click', function () {
        // หาตำแหน่งของดาวที่คลิก (index เริ่มที่ 0)
        var index = $(this).index();

        // ลูปเปลี่ยนดาวทั้งหมด
        $('.wrap-rating .item-rating').each(function (i) {
            if (i <= index) {
                $(this).removeClass('zmdi-star-outline').addClass('zmdi-star');
            } else {
                $(this).removeClass('zmdi-star').addClass('zmdi-star-outline');
            }
        });

        // นับจำนวนดาวที่เปิดอยู่ (zmdi-star)
        var count = $('.wrap-rating .zmdi-star').length;
        $('[name="review_rating"]').val(count);
    });


    $('#insert-review').click(function (e) {
        e.preventDefault();
        var product_id = $('#product_id').val();
        var review_rating = $('[name="review_rating"]').val();
        var review_detail = $('[name="review_detail"]').val();
        $.ajax({
            type: "post",
            url: "api/review.php",
            data: {
                fn: "add_review",
                review_rating: review_rating,
                review_detail: review_detail,
                product_id: product_id,
            },
            dataType: "json",
            success: function (res) {
                if (res.data == 'f') {
                    swal({
                        title: res.title,
                        text: res.message,
                        icon: res.icon,
                        buttons: {
                            cancel: {
                                text: "ยกเลิก",
                                visible: true,
                                className: "swal-button--cancel",
                                closeModal: true,
                            },
                            confirm: {
                                text: "เข้าสู่ระบบ",
                                visible: true,
                                className: "swal-button--confirm",
                                closeModal: true,
                            },
                        },
                    }).then((willInsert) => {
                        if (willInsert) {
                            window.location.href = "./login"
                        }
                    });
                } else if (res.data == 't') {
                    swal(res.title, res.message, res.icon);
                    offset = 0;
                    $('.review-data').empty();
                    loadReviews();

                    // ✅ ล้างค่าฟอร์มหลังรีวิว
                    $('[name="review_rating"]').val('');
                    $('[name="review_detail"]').val('');
                } else {
                    swal(res.title, res.message, res.icon);
                }
            }
        });
    });

    load_qty();

});