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
                    html += '<input type="hidden" name="variant_size" value="' + response.data[0].variant_size + '">';
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
                $('.price-data').html(response.data[0].variant_sale)
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
            var variant_color = $('[name="variant_color"]').val();
        }
        var size = $('[name="variant_size"]:checked').val();
        if (size !== "" && typeof size !== "undefined") {
            var variant_size = size;
        } else {
            var variant_size = $('[name="variant_size"]').val();
        }
        var cart_qty = $('[name="cart_qty"]').val();
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
        var variant_color = $('[name="variant_color"]:checked').val();
        if (color !== "" && typeof color !== "undefined") {
            var variant_color = color;
        } else {
            var variant_color = $('[name="variant_color"]').val();
        }
        var size = $('[name="variant_size"]:checked').val();
        if (size !== "" && typeof size !== "undefined") {
            var variant_size = size;
        } else {
            var variant_size = $('[name="variant_size"]').val();
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

    load_qty();

});