$(function () {
    var grandTotal = 0;
    var totalQty = 0;

    $('.form-in').hide();
    $('.form-out').hide();
    $('.receipt_form').hide();

    $('.js-example-basic-single').select2({
        width: '100%'
    });

    select_order();

    function select_order() {
        var variant = $('#variant').val();
        var qty = $('#qty').val();
        $.ajax({
            type: "post",
            url: "api/order.php",
            data: {
                fn: "select_order",
                variant_id: variant
            },
            dataType: "json",
            success: function (res) {
                var html = '';

                $.each(res.data, function (i, v) {
                    if (v.size_detail != "") {
                        size_detail = ' (' + v.size_detail + ')';
                    } else {
                        size_detail = '';
                    }
                    var itemTotal = parseFloat(v.variant_sale * qty);
                    grandTotal += itemTotal;

                    html += '<tr class="table_row">';
                    html += '<td class="column-1">';
                    html += '<div class="how-itemcart1">';
                    html += '<a href="file/product/' + v.img_name + ' target="_blank""><img src="file/product/' + v.img_name + '" alt=""></a>';
                    html += '</div>';
                    html += '</td>';
                    html += '<td class="column-2">' + v.product_name + '<br>';
                    html += '<span class="product-style">' + v.variant_color + '</span>';
                    html += '<span class="product-style">' + v.variant_size + size_detail + '</span>';
                    html += '</td>';
                    html += '<td class="column-3">฿' + v.variant_sale + '</td>';
                    html += '<td class="column-4">' + qty + '</td>';
                    html += '<td class="column-5">฿' + parseFloat(v.variant_sale * qty).toFixed(2) + '</td>';
                    html += '</tr>';
                });

                $('.cart_data').html(html);
                $('.cart-total').text(grandTotal.toFixed(2) + ' บาท');
                shipping_price(grandTotal);
            }
        });
    }

    function shipping_price(totalPrice) {
        var qty = $('#qty').val();
        var shipping_main = 0;
        var price_shipping = 0;

        if ($('#shipping-type-3').is(':checked')) {
            shipping_main = 50;
            var qty_add = parseInt(qty - 1);
            var price_add = parseFloat(10 * qty_add);
            price_shipping = parseFloat(price_add + shipping_main);
        }

        var cart_total = parseFloat(totalPrice);
        var total_price = parseFloat(cart_total + price_shipping);

        $('.shipping-price').text(price_shipping.toFixed(2) + ' บาท');
        $('.total-price').text(total_price.toFixed(2) + ' บาท');
    }

    $('[name="receipt_id"]').on('change', function (e) {
        e.preventDefault();
        if ($(this).is(':checked')) {
            $('.receipt_form').show();
        } else {
            $('.receipt_form').hide();
        }
    });

    // เมื่อเปลี่ยนประเภทการจัดส่ง
    $('[name="shipping_type"]').on('change', function (e) {
        e.preventDefault();
        if ($('#shipping-type-1').is(':checked')) {
            $('.form-front').show();
            $('.form-in').hide();
            $('.form-out').hide();
        }
        if ($('#shipping-type-2').is(':checked')) {
            $('.form-front').hide();
            $('.form-in').show();
            $('.form-out').hide();
        }
        if ($('#shipping-type-3').is(':checked')) {
            $('.form-front').hide();
            $('.form-in').hide();
            $('.form-out').show();
            select_provinces();
        }
        shipping_price(grandTotal);
    });

    function select_provinces() {
        $.ajax({
            type: "post",
            url: "api/address.php",
            data: {
                fn: "select_provinces"
            },
            dataType: "json",
            success: function (res) {
                var html = '<option value="">-- เลือกจังหวัด --</option>';
                $.each(res.data, function (i, v) {
                    html += '<option value="' + v.province_id + '">' + v.province_name_in_thai + '</option>';
                });
                $('#province_id').html(html);
                $('#district_id').html('<option value="">-- เลือกอำเภอ --</option>').prop('disabled', true);
                $('#subdistrict_id').html('<option value="">-- เลือกตำบล --</option>').prop('disabled', true);
                $('#zip_code').val('');
                $('#province_id').select2({
                    width: '100%'
                });
            }
        });
    }

    $('#province_id').change(function () {
        var province_id = $(this).val();

        if (province_id) {
            $('#district_id').prop('disabled', false);
            select_districts(province_id);
        } else {
            // เคลียร์และปิด dropdown ทั้ง district และ subdistrict
            $('#district_id')
                .html('<option value="">-- เลือกอำเภอ --</option>')
                .prop('disabled', true);
            $('#subdistrict_id')
                .html('<option value="">-- เลือกตำบล --</option>')
                .prop('disabled', true);
            $('#zip_code').val('');
        }
    });

    // เมื่อ district เปลี่ยน
    $('#district_id').change(function () {
        var district_id = $(this).val();

        if (district_id) {
            $('#subdistrict_id').prop('disabled', false);
            select_subdistricts(district_id);
        } else {
            $('#subdistrict_id')
                .html('<option value="">-- เลือกตำบล --</option>')
                .prop('disabled', true);
            $('#zip_code').val('');
        }
    });

    // เมื่อ subdistrict เปลี่ยน
    $('#subdistrict_id').change(function () {
        var subdistrict_id = $(this).val();

        if (subdistrict_id) {
            select_zip_code(subdistrict_id);
        } else {
            $('#zip_code').val('');
        }
    });

    // โหลดข้อมูลอำเภอ
    function select_districts(province_id, selected_id = null) {
        $.ajax({
            type: "post",
            url: "api/address.php",
            data: {
                fn: "select_districts",
                province_id: province_id
            },
            dataType: "json",
            success: function (res) {

                var html = '<option value="">-- เลือกอำเภอ --</option>';
                $.each(res.data, function (i, v) {
                    var selected = (selected_id == v.district_id) ? 'selected' : '';
                    html += '<option value="' + v.district_id + '" ' + selected + '>' + v.district_name_in_thai + '</option>';
                });
                $('#district_id').html(html).prop('disabled', false).trigger('change.select2');
            }
        });
    }

    // โหลดข้อมูลตำบล
    function select_subdistricts(district_id, selected_id = null) {
        $.ajax({
            type: "post",
            url: "api/address.php",
            data: {
                fn: "select_subdistricts",
                district_id: district_id
            },
            dataType: "json",
            success: function (res) {
                var html = '<option value="">-- เลือกตำบล --</option>';
                $.each(res.data, function (i, v) {
                    var selected = (selected_id == v.subdistrict_id) ? 'selected' : '';
                    html += '<option value="' + v.subdistrict_id + '" ' + selected + '>' + v.subdistrict_name_in_thai + '</option>';
                });
                $('#subdistrict_id').html(html).prop('disabled', false).trigger('change.select2');
            }
        });
    }

    function select_zip_code(subdistrict_id) {
        $.ajax({
            type: "post",
            url: "api/address.php",
            data: {
                fn: "select_zip_code",
                subdistrict_id: subdistrict_id
            },
            dataType: "json",
            success: function (res) {
                if (res.data && res.data.length > 0) {
                    $('#zip_code').val(res.data[0].zip_code);
                } else {
                    $('#zip_code').val('');
                }
            }
        });
    }

    $('#check-out').click(function (e) {
        e.preventDefault();
        if ($('#receipt_id').is(':checked')) {
            var receipt_id = $('input[name="receipt_id"]:checked').val();
            var receipt_name = $('#receipt_name').val();
            var receipt_number = $('#receipt_number').val();
            var receipt_address = $('#receipt_address').val();
        } else {
            var receipt_id = 0;
            var receipt_name = '';
            var receipt_number = '';
            var receipt_address = '';
        }
        var shipping_type = $('input[name="shipping_type"]:checked').val();
        if ($('#shipping-type-1').is(':checked')) {
            var shipping_name = $('[name="shipping_name"]').val();
            var shipping_phone = $('[name="shipping_phone"]').val();
            var shipping_department = $('[name="shipping_department"]').val();
            var address_id = 0;
        } else if ($('#shipping-type-2').is(':checked')) {
            var shipping_name = $('[name="shipping_name"]').val();
            var shipping_phone = $('[name="shipping_phone"]').val();
            var shipping_department = $('[name="shipping_department"]').val();
            if (shipping_name == "" || shipping_phone == "" || shipping_department == "") {
                swal("กรุณากรอกข้อมูลให้ครบ", "กรุณากรอกชื่อ หมายเลขโทรศัพท์ และหน่วยงานให้ครบก่อนยืนยันสั่งซื้อ", "warning");
                return;
            }
            var address_id = 0;
        } else if ($('#shipping-type-3').is(':checked')) {
            var shipping_name = $('#name').val();
            var shipping_phone = $('#phone').val();
            var address_at = $('#address_at').val();
            var subdistrict_id = $('#subdistrict_id').val();
            var district_id = $('#district_id').val();
            var province_id = $('#province_id').val();
            var zip_code = $('#zip_code').val();
            var shipping_department = address_at + ' ตำบล';
            var address_id = $('input[name="address_id"]:checked').val();
            if (!address_id) {
                swal("กรุณาเลือกที่อยู่จัดส่ง", "คุณยังไม่ได้เลือกที่อยู่สำหรับการจัดส่งสินค้า", "warning");
                return;
            }
        }
        var shipping_price = parseFloat($('.shipping-price').text().replace(/[^\d.]/g, ''));
        var total_price = parseFloat($('.total-price').text().replace(/[^\d.]/g, ''));

        $.ajax({
            type: "POST",
            url: "api/checkout.php",
            data: {
                fn: "create_order",
                receipt_id: receipt_id,
                receipt_name: receipt_name,
                receipt_number: receipt_number,
                receipt_address: receipt_address,
                shipping_type: shipping_type,
                shipping_name: shipping_name,
                shipping_phone: shipping_phone,
                shipping_department: shipping_department,
                address_id: address_id,
                shipping_price: shipping_price,
                total_price: total_price
            },
            dataType: "json",
            success: function (res) {
                if (res.status == "success") {
                    window.location.href = "payment/?order_no=" + res.order_no;
                } else {
                    alert("เกิดข้อผิดพลาด: " + res.msg);
                }
            }
        });
    });
})