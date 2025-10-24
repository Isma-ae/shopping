$(function () {
    load_qty();

    select_cart();

    function select_cart() {
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
                var html = '';
                if (res.data.length === 0) {
                    html += '<tr class="table_row">';
                    html += '<td colspan="7" class="txt-center">ไม่มีสินค้าในตะกร้า<td>';
                    html += '</tr>';
                    $('.cart_data').html(html);
                } else {
                    var size_detail = '';
                    $.each(res.data, function (i, v) {
                        if (v.size_detail != "") {
                            size_detail = ' (' + v.size_detail + ')';
                        } else {
                            size_detail = '';
                        }

                        if (v.cart_type != 2) {
                            if (v.variant_stock < 1) {
                                var disabled = 'disabled';
                                var statusProduct = '<span class="product-style2">สินค้าหมด</span>';
                                var tr_class = 'product_out';
                                var cart_qty = v.cart_qty;
                            } else {
                                var disabled = '';
                                var statusProduct = '';
                                var tr_class = '';
                                var cart_qty = '<div class="wrap-num-product flex-w m-l-auto m-r-0">\
                                                    <div class="btn-num-product-down cl8 hov-btn3 trans-04 flex-c-m" cart-id="' + v.cart_id + '">\
                                                        <i class="fs-16 zmdi zmdi-minus"></i>\
                                                    </div>\
                                                    <input class="mtext-104 cl3 txt-center num-product" type="number" name="num-product1" value="' + v.cart_qty + '">\
                                                    <div class="btn-num-product-up cl8 hov-btn3 trans-04 flex-c-m" cart-id="' + v.cart_id + '">\
                                                        <i class="fs-16 zmdi zmdi-plus"></i>\
                                                    </div>\
                                                </div>';
                            }
                        } else {
                            var disabled = '';
                            var statusProduct = '<span class="product-style2">จอง</span>';
                            var tr_class = '';
                            var cart_qty = '<div class="wrap-num-product flex-w m-l-auto m-r-0">\
                                                <div class="btn-num-product-down cl8 hov-btn3 trans-04 flex-c-m" cart-id="' + v.cart_id + '">\
                                                    <i class="fs-16 zmdi zmdi-minus"></i>\
                                                </div>\
                                                <input class="mtext-104 cl3 txt-center num-product" type="number" name="num-product1" value="' + v.cart_qty + '">\
                                                <div class="btn-num-product-up cl8 hov-btn3 trans-04 flex-c-m" cart-id="' + v.cart_id + '">\
                                                    <i class="fs-16 zmdi zmdi-plus"></i>\
                                                </div>\
                                            </div>';
                        }

                        html += '<tr class="table_row ' + tr_class + '">';
                        html += '<td class="my-column-1">';
                        html += '<input class="form-check-input" type="checkbox" value="' + v.cart_id + '" ' + disabled + '>';
                        html += '</td>';
                        html += '<td class="my-column-2">';
                        html += '<div class="how-itemcart1">';
                        html += '<a href="file/product/' + v.img_name + ' target="_blank""><img src="file/product/' + v.img_name + '" alt=""></a>';
                        html += '</div>';
                        html += '</td>';
                        html += '<td class="my-column-3">' + v.product_name + '<br>';
                        html += '<span class="product-style">' + v.variant_color + '</span>';
                        html += '<span class="product-style">' + v.variant_size + size_detail + '</span>' + statusProduct;
                        html += '</td>';
                        html += '<td class="column-3">฿' + v.variant_sale + '</td>';
                        html += '<td class="column-4">' + cart_qty + '</td>';
                        html += '<td class="my-column-4">B' + parseFloat(v.variant_sale * v.cart_qty).toFixed(2) + '</td>';
                        html += '<td class="my-column-5"><a href="#" class="product-delete" cart-id="' + v.cart_id + '"><i class="fa fa-trash"></i></a></td>';
                        html += '</tr>';
                    });
                    $('.cart_data').html(html);
                    $('.btn-num-product-down').on('click', function () {
                        var numProduct = Number($(this).next().val());
                        var newVal = numProduct - 1;
                        if (numProduct > 1) {
                            $(this).next().val(newVal);
                            var cartId = $(this).attr('cart-id');
                            change_qty(cartId, newVal);
                        }
                    });

                    $('.btn-num-product-up').on('click', function () {
                        var numProduct = Number($(this).prev().val());
                        var newVal = numProduct + 1;
                        $(this).prev().val(newVal);
                        var cartId = $(this).attr('cart-id');
                        change_qty(cartId, newVal);
                    });


                    $('.product-delete').click(function (e) {
                        e.preventDefault();
                        var cart_id = $(this).attr('cart-id');
                        delete_cart(cart_id);
                    });
                }
            }
        });
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
                    select_cart();
                    load_qty();
                } else if (res.data == 't') {
                    select_cart();
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
        }).then((willDelete) => {
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
                            select_cart();
                            load_qty();
                        }
                    }
                });
            }
        });
    }

    $('#checkAll').change(function () {
        var checked = $(this).is(':checked');
        $('.cart_data .form-check-input:not(:disabled)').prop('checked', checked);
        calculateTotal();
    });

    $('.cart_data').on('change', '.form-check-input:not(#checkAll)', function () {
        var total = $('.cart_data .form-check-input:not(:disabled)').length;
        var checked = $('.cart_data .form-check-input:not(:disabled):checked').length;
        $('#checkAll').prop('checked', total === checked);

        calculateTotal();
    });

    function calculateTotal() {
        var totalPrice = 0;
        $('.cart_data .form-check-input:checked:not(:disabled)').each(function () {
            var row = $(this).closest('tr');

            var price = parseFloat(row.find('.column-3').text().replace('฿', '').trim());
            var qty = parseFloat(row.find('.num-product').val());

            if (!isNaN(price) && !isNaN(qty)) {
                totalPrice += price * qty;
            }
        });

        $('.cart-total').text(totalPrice.toFixed(2) + ' บาท');
    }


    $('#continue-shopping').click(function (e) {
        e.preventDefault();
        window.location.href = "?page=product";
    });

    $('#empty-basket').click(function (e) {
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
                    text: "ลบ",
                    visible: true,
                    className: "swal-button--confirm",
                    closeModal: true,
                },
            },
            dangerMode: true,
        }).then((willDelete) => {
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
                            select_cart();
                            load_qty();
                        }
                    }
                });
            }
        });
    });

    $('#btn-checkout').click(function (e) {
        e.preventDefault();
        var selected = [];
        $('.cart_data .form-check-input:checked').each(function () {
            selected.push($(this).val()); // value = cart_id
        });

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
                    alert('เกิดข้อผิดพลาด: ' + res.message);
                }
            }
        });
    });

})