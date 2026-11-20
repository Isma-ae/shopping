$(function () {
    load_qty();

    function escapeHtml(str) {
        if (str === null || str === undefined) return "";
        return String(str)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    function formatBaht(amount) {
        var num = Number(amount || 0);
        if (isNaN(num)) num = 0;
        var hasFraction = Math.abs(num - Math.round(num)) > 0.001;
        return "฿" + num.toLocaleString("th-TH", {
            minimumFractionDigits: hasFraction ? 2 : 0,
            maximumFractionDigits: 2
        });
    }

    function getCheckedCartIds() {
        var ids = [];
        $('.cart_data .cart-item-check:checked:not(:disabled)').each(function () {
            ids.push(String($(this).val()));
        });
        return ids;
    }

    function select_cart(preserveChecked) {
        var previouslyChecked = preserveChecked ? getCheckedCartIds() : [];

        $.ajax({
            type: "post",
            url: "api/cart.php",
            data: {
                fn: "select_cart"
            },
            dataType: "json",
            success: function (res) {
                if (res.data == 'f') {
                    window.location.href = './login';
                    return;
                }

                var items = Array.isArray(res.data) ? res.data : [];
                var html = '';

                if (items.length === 0) {
                    $('#cartToolbar').addClass('d-none');
                    $('#cartHeadCount').addClass('d-none');
                    $('#checkAll').prop('checked', false);

                    html += '<div class="cart-empty-state">';
                    html += '  <div class="cart-empty-icon"><i class="zmdi zmdi-shopping-cart" aria-hidden="true"></i></div>';
                    html += '  <h4 class="cart-empty-title">ยังไม่มีสินค้าในตะกร้าของคุณ</h4>';
                    html += '  <p class="cart-empty-desc">เลือกชมเสื้อ ของที่ระลึก และสินค้าประจำสำนักวิทยบริการได้เลย</p>';
                    html += '  <a href="?page=product" class="cart-empty-btn"><i class="fa fa-shopping-bag mr-2" aria-hidden="true"></i>เลือกชมสินค้า</a>';
                    html += '</div>';

                    $('.cart_data').html(html);
                    calculateTotal();
                    return;
                }

                $('#cartToolbar').removeClass('d-none');
                $('#cartHeadCount').removeClass('d-none');
                $('#cartTotalItems').text(items.length);

                $.each(items, function (i, v) {
                    var cartId = String(v.cart_id);
                    var unitPrice = parseFloat(v.variant_sale || 0);
                    var qty = parseInt(v.cart_qty || 1, 10);
                    if (isNaN(qty) || qty < 1) qty = 1;
                    var lineTotal = unitPrice * qty;

                    var isReserve = Number(v.cart_type) === 2;
                    var remainingStock = v.remaining_stock !== undefined
                        ? parseInt(v.remaining_stock, 10)
                        : parseInt(v.variant_stock || 0, 10);
                    var isOut = !isReserve && remainingStock < 1;

                    var disabledAttr = isOut ? ' disabled' : '';
                    var isChecked = !isOut && preserveChecked && previouslyChecked.indexOf(cartId) !== -1;
                    var checkedAttr = isChecked ? ' checked' : '';

                    var itemClasses = 'cart-item';
                    if (isOut) itemClasses += ' product_out';
                    if (isChecked) itemClasses += ' is-selected';

                    // Match pages/detail logic: show color only when product has > 1 color; show size only when > 1 size
                    var colorCount = parseInt(v.color_count || 0, 10);
                    var sizeCount = parseInt(v.size_count || 0, 10);
                    var colorVal = $.trim(v.variant_color || '');
                    var sizeVal = $.trim(v.variant_size || '');
                    var sizeDetail = $.trim(v.size_detail || '');

                    var variantHtml = '';
                    if (colorCount > 1) {
                        var colorLabel = colorVal !== '' ? colorVal : 'มาตรฐาน';
                        variantHtml += '<span class="cart-variant-pill"><strong>สี:</strong>' + escapeHtml(colorLabel) + '</span>';
                    }
                    if (sizeCount > 1) {
                        var sizeLabel = sizeVal !== '' ? sizeVal : 'มาตรฐาน';
                        if (sizeDetail !== '') {
                            sizeLabel += ' (' + sizeDetail + ')';
                        }
                        variantHtml += '<span class="cart-variant-pill"><strong>ขนาด:</strong>' + escapeHtml(sizeLabel) + '</span>';
                    }

                    if (isReserve) {
                        variantHtml += '<span class="cart-status-pill cart-status-pill--reserve">สินค้าสั่งจอง</span>';
                    } else if (isOut) {
                        variantHtml += '<span class="cart-status-pill cart-status-pill--out">สินค้าหมด</span>';
                    } else if (remainingStock > 0 && remainingStock <= 5) {
                        variantHtml += '<span class="cart-status-pill cart-status-pill--low">เหลือ ' + remainingStock + ' ชิ้น</span>';
                    }

                    var imgPath = (v.img_name && $.trim(v.img_name) !== '')
                        ? 'file/product/' + encodeURIComponent(v.img_name)
                        : 'images/icons/OARShopping.png';
                    var detailUrl = v.product_hash
                        ? '?page=detail&product=' + encodeURIComponent(v.product_hash)
                        : '?page=product';

                    var qtyControlHtml = '';
                    if (isOut) {
                        qtyControlHtml = '<span class="cart-qty-static">จำนวน ' + qty + ' ชิ้น</span>';
                    } else {
                        qtyControlHtml =
                            '<div class="cart-qty-box">' +
                                '<button type="button" class="cart-qty-btn btn-num-product-down" cart-id="' + escapeHtml(cartId) + '" aria-label="ลดจำนวน">' +
                                    '<i class="zmdi zmdi-minus" aria-hidden="true"></i>' +
                                '</button>' +
                                '<input class="cart-qty-input num-product" type="number" min="1" name="num-product1" cart-id="' + escapeHtml(cartId) + '" value="' + qty + '">' +
                                '<button type="button" class="cart-qty-btn btn-num-product-up" cart-id="' + escapeHtml(cartId) + '" aria-label="เพิ่มจำนวน">' +
                                    '<i class="zmdi zmdi-plus" aria-hidden="true"></i>' +
                                '</button>' +
                            '</div>';
                    }

                    html += '<article class="' + itemClasses + '" data-cart-id="' + escapeHtml(cartId) + '" data-unit-price="' + unitPrice + '" data-qty="' + qty + '">';
                    html += '  <div class="cart-item-main">';
                    html += '    <input class="form-check-input cart-checkbox cart-item-check" type="checkbox" value="' + escapeHtml(cartId) + '"' + disabledAttr + checkedAttr + ' aria-label="เลือกสินค้า">';
                    html += '    <a href="' + detailUrl + '" class="cart-item-thumb">';
                    html += '      <img src="' + imgPath + '" alt="' + escapeHtml(v.product_name) + '" onerror="this.onerror=null;this.src=\'images/icons/OARShopping.png\';this.classList.add(\'is-fallback-img\');">';
                    html += '    </a>';
                    html += '    <div class="cart-item-info">';
                    html += '      <a href="' + detailUrl + '" class="cart-item-title">' + escapeHtml(v.product_name) + '</a>';
                    if (variantHtml !== '') {
                        html += '      <div class="cart-item-variants">' + variantHtml + '</div>';
                    }
                    html += '      <div class="cart-item-unit-price">ราคาต่อชิ้น: <strong>' + formatBaht(unitPrice) + '</strong></div>';
                    html += '    </div>';
                    html += '  </div>';
                    html += '  <div class="cart-item-controls">';
                    html +=      qtyControlHtml;
                    html += '    <div class="cart-item-subtotal">' + formatBaht(lineTotal) + '</div>';
                    html += '    <button type="button" class="cart-delete-btn product-delete" cart-id="' + escapeHtml(cartId) + '" title="ลบสินค้า" aria-label="ลบสินค้า">';
                    html += '      <i class="fa fa-trash-o" aria-hidden="true"></i>';
                    html += '    </button>';
                    html += '  </div>';
                    html += '</article>';
                });

                $('.cart_data').html(html);
                syncCheckAllState();
                calculateTotal();
            }
        });
    }

    function syncCheckAllState() {
        var total = $('.cart_data .cart-item-check:not(:disabled)').length;
        var checked = $('.cart_data .cart-item-check:not(:disabled):checked').length;
        $('#checkAll').prop('checked', total > 0 && total === checked);
    }

    function calculateTotal() {
        var totalPrice = 0;
        var selectedItems = 0;
        var selectedQty = 0;

        $('.cart_data .cart-item').each(function () {
            var $item = $(this);
            var $cb = $item.find('.cart-item-check');
            var checked = $cb.is(':checked') && !$cb.is(':disabled');
            $item.toggleClass('is-selected', checked);

            if (checked) {
                var price = parseFloat($item.attr('data-unit-price') || 0);
                var qty = parseInt($item.find('.num-product').val() || $item.attr('data-qty') || 0, 10);
                if (!isNaN(price) && !isNaN(qty) && qty > 0) {
                    totalPrice += price * qty;
                    selectedItems += 1;
                    selectedQty += qty;
                }
            }
        });

        $('#cartSelectedHint').text('(เลือกแล้ว ' + selectedItems + ' รายการ)');
        $('#cartSummarySelected').text(selectedItems + ' รายการ (' + selectedQty + ' ชิ้น)');
        $('#cartSubtotal').text(formatBaht(totalPrice));
        $('.cart-total').text(formatBaht(totalPrice));
    }

    function change_qty(cart_id, cart_qty) {
        $.ajax({
            type: "post",
            url: "api/cart.php",
            data: {
                fn: "change_qty",
                cart_id: cart_id,
                cart_qty: cart_qty
            },
            dataType: "json",
            success: function (res) {
                if (res.data == 'n') {
                    swal(res.title, res.message, res.icon);
                    select_cart(true);
                    load_qty();
                } else if (res.data == 't') {
                    select_cart(true);
                    load_qty();
                }
            }
        });
    }

    function delete_cart(cart_id) {
        swal({
            title: "แน่ใจหรือไม่ว่าจะลบสินค้านี้?",
            text: "หลังจากลบแล้วจะต้องเพิ่มสินค้าใหม่อีกครั้งหากต้องการ",
            icon: "warning",
            buttons: {
                cancel: {
                    text: "ยกเลิก",
                    visible: true,
                    className: "swal-button--cancel",
                    closeModal: true,
                },
                confirm: {
                    text: "ลบ",
                    visible: true,
                    className: "swal-button--confirm",
                    closeModal: true,
                },
            },
            dangerMode: true,
        }).then(function (willDelete) {
            if (willDelete) {
                $.ajax({
                    type: "post",
                    url: "api/cart.php",
                    data: {
                        fn: "delete_cart",
                        cart_id: cart_id
                    },
                    dataType: "json",
                    success: function (res) {
                        if (res.data == 't') {
                            select_cart(true);
                            load_qty();
                        }
                    }
                });
            }
        });
    }

    // Delegated item quantity & delete events
    $('.cart_data').on('click', '.btn-num-product-down', function () {
        var $input = $(this).siblings('.num-product');
        var numProduct = parseInt($input.val(), 10) || 1;
        if (numProduct > 1) {
            var newVal = numProduct - 1;
            $input.val(newVal);
            change_qty($(this).attr('cart-id'), newVal);
        }
    });

    $('.cart_data').on('click', '.btn-num-product-up', function () {
        var $input = $(this).siblings('.num-product');
        var numProduct = parseInt($input.val(), 10) || 1;
        var newVal = numProduct + 1;
        $input.val(newVal);
        change_qty($(this).attr('cart-id'), newVal);
    });

    $('.cart_data').on('change', '.num-product', function () {
        var val = parseInt($(this).val(), 10);
        if (isNaN(val) || val < 1) {
            val = 1;
            $(this).val(1);
        }
        change_qty($(this).attr('cart-id'), val);
    });

    $('.cart_data').on('click', '.product-delete', function (e) {
        e.preventDefault();
        delete_cart($(this).attr('cart-id'));
    });

    $('#checkAll').on('change', function () {
        var checked = $(this).is(':checked');
        $('.cart_data .cart-item-check:not(:disabled)').prop('checked', checked);
        calculateTotal();
    });

    $('.cart_data').on('change', '.cart-item-check', function () {
        syncCheckAllState();
        calculateTotal();
    });

    $('#continue-shopping').on('click', function (e) {
        e.preventDefault();
        window.location.href = "?page=product";
    });

    $('#empty-basket').on('click', function (e) {
        e.preventDefault();
        swal({
            title: "แน่ใจหรือไม่ว่าจะลบสินค้าทั้งหมดในตะกร้า?",
            text: "หลังจากลบแล้วจะต้องเพิ่มสินค้าใหม่อีกครั้งหากต้องการ",
            icon: "warning",
            buttons: {
                cancel: {
                    text: "ยกเลิก",
                    visible: true,
                    className: "swal-button--cancel",
                    closeModal: true,
                },
                confirm: {
                    text: "ลบทั้งหมด",
                    visible: true,
                    className: "swal-button--confirm",
                    closeModal: true,
                },
            },
            dangerMode: true,
        }).then(function (willDelete) {
            if (willDelete) {
                $.ajax({
                    type: "post",
                    url: "api/cart.php",
                    data: {
                        fn: "empty_basket"
                    },
                    dataType: "json",
                    success: function (res) {
                        if (res.data == 't') {
                            swal(res.title, res.message, res.icon);
                            select_cart(false);
                            load_qty();
                        }
                    }
                });
            }
        });
    });

    $('#btn-checkout').on('click', function (e) {
        e.preventDefault();
        var selected = getCheckedCartIds();

        if (selected.length === 0) {
            swal({
                title: "ยังไม่ได้เลือกสินค้า",
                text: "กรุณาเลือกสินค้าที่ต้องการสั่งซื้อ",
                icon: "warning",
            });
            return;
        }

        $.ajax({
            url: 'api/cart.php',
            type: 'post',
            data: {
                fn: "checkout",
                cart_ids: selected
            },
            dataType: "json",
            success: function (res) {
                if (res.success) {
                    window.location.href = './?page=checkout';
                } else {
                    swal("เกิดข้อผิดพลาด", res.message || "ไม่สามารถดำเนินการต่อได้", "error");
                }
            }
        });
    });

    select_cart(false);
});
