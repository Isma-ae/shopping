$(function () {
    var grandTotal = 0;
    var totalQty = 0;

    $('.form-in').hide();
    $('.form-out').hide();
    $('.receipt_form').hide();

    $('.js-example-basic-single').select2({
        dropdownParent: $('#addModal'),
        width: '100%'
    });

    select_cart();

    function select_cart() {
        $.ajax({
            type: "post",
            url: "api/checkout.php",
            data: {
                fn: "select_cart"
            },
            dataType: "json",
            success: function (res) {
                if (res.data == 'f') {
                    window.location.href = './';
                    return;
                }
                if (res.data.length === 0) {
                    window.location.href = '?page=shipping_list';
                    return;
                }
                var html = '';
                grandTotal = 0;
                totalQty = 0;

                $.each(res.data, function (i, v) {
                    if (v.size_detail != "") {
                        size_detail = ' (' + v.size_detail + ')';
                    } else {
                        size_detail = '';
                    }
                    var itemTotal = parseFloat(v.variant_sale * v.cart_qty);
                    grandTotal += itemTotal;
                    totalQty += parseInt(v.cart_qty);

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
                    html += '<td class="column-4">' + v.cart_qty + '</td>';
                    html += '<td class="column-5">฿' + parseFloat(v.variant_sale * v.cart_qty).toFixed(2) + '</td>';
                    html += '</tr>';
                });

                $('.cart_data').html(html);
                $('.cart-total').text(grandTotal.toFixed(2) + ' บาท');

                // คำนวณค่าขนส่งตอนโหลดตะกร้า
                shipping_price(grandTotal, totalQty);
            }
        });
    }

    function shipping_price(totalPrice, totalQty) {
        var shipping_main = 0;
        var price_shipping = 0;

        if ($('#shipping-type-3').is(':checked')) {
            shipping_main = 50;
            var qty_add = parseInt(totalQty - 1);
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
            select_address();
        }
        shipping_price(grandTotal, totalQty);
    });

    function select_address() {
        $.ajax({
            type: "post",
            url: "api/address.php",
            data: {
                fn: "select_address"
            },
            dataType: "json",
            success: function (res) {
                var html = '';
                if (res.data.length === 0) {
                    html += '<div class="flex-w m-r--5">';
                    html += '<a href="#" class="flex-c-m stext-107 cl6 size-301 bor7 p-lr-15 hov-tag1 trans-04 m-r-5 m-b-5 add-address">เพิ่มที่อยู่จัดส่ง</a>';
                    html += '</div>';
                } else {
                    html += '<table style="width:100%">';
                    $.each(res.data, function (i, v) {
                        if (i == 0) {
                            var checked = 'checked';
                        } else {
                            var checked = '';
                        }
                        html += '<tr class="bor12">';
                        html += '<td style="width:5%">';
                        html += '<input class="input-radio" type="radio" name="address_id" id="address_id' + (i + 1) + '" ' + checked + ' value="' + v.id + '">';
                        html += '</td>';
                        html += '<td style="width:75%"';
                        html += ' data-id="' + v.id + '"';
                        html += ' data-name="' + v.name + '"';
                        html += ' data-phone="' + v.phone + '"';
                        html += ' data-address="' + v.address_at + '"';
                        html += ' data-province="' + v.province_id + '"';
                        html += ' data-district="' + v.district_id + '"';
                        html += ' data-subdistrict="' + v.subdistrict_id + '"';
                        html += ' data-zipcode="' + v.zip_code + '">';
                        html += '<label class="form-check-label" for="address_id' + (i + 1) + '">';
                        html += '<b>' + v.name + ' ' + v.phone + '</b> ' + v.address_at + ' ตำบล' + v.subdistrict_name_in_thai + ' อำเภอ' + v.district_name_in_thai + ' จังหวัด' + v.province_name_in_thai + ' ' + v.zip_code;
                        html += '</label>';
                        html += '</td>';
                        html += '<td style="width:20%">';
                        html += '<div class="flex-w m-r--5">';
                        html += '<a href="#" class="flex-c-m stext-107 cl6 size-301 bor7 p-lr-15 hov-tag1 trans-04 m-r-5 m-b-5 edit-address">แก้ไข</a>';
                        html += '<a href="#" class="flex-c-m stext-107 cl6 size-301 bor7 p-lr-15 hov-tag1 trans-04 m-r-5 m-b-5 delete-address" data-id="' + v.id + '">ลบ</a>';
                        html += '</div>';
                        html += '</td>';
                        html += '</tr>';
                    });
                    html += '</table>';
                    html += '<div class="flex-w m-r--5 m-t-5">';
                    html += '<a href="#" class="flex-c-m stext-107 cl6 size-301 bor7 p-lr-15 hov-tag1 trans-04 m-r-5 m-b-5 add-address">เพิ่มที่อยู่จัดส่ง</a>';
                    html += '</div>';
                }
                $('.form-out').html(html);
            }
        });
    }

    $('.form-out').on('click', '.add-address', function (e) {
        e.preventDefault();

        $('#addModalLabel').text('ที่อยู่ใหม่');
        $('#addModal').modal('show');
    });

    $(document).on('click', '.edit-address', function (e) {
        e.preventDefault();

        // ดึงข้อมูลจาก <td> ที่อยู่ถัดไป (หรือใช้ .closest('tr').find('td:eq(1)')
        var td = $(this).closest('tr').find('td:eq(1)');

        var data = {
            id: td.data('id'),
            name: td.data('name'),
            phone: td.data('phone'),
            address: td.data('address'),
            province: td.data('province'),
            district: td.data('district'),
            subdistrict: td.data('subdistrict'),
            zipcode: td.data('zipcode')
        };

        // ใส่ค่าในฟอร์ม
        $('#address_id').val(data.id);
        $('#name').val(data.name);
        $('#phone').val(data.phone);
        $('#address_at').val(data.address);
        $('#zip_code').val(data.zipcode);

        // โหลด province / district / subdistrict พร้อมเลือกค่าเดิม
        $('#province_id').val(data.province).trigger('change');
        select_districts(data.province, data.district);
        select_subdistricts(data.district, data.subdistrict);

        // เปลี่ยนปุ่มและโหมด
        $('#addModalLabel').text('แก้ไขที่อยู่จัดส่ง');
        $('#btn_submit_address').text('บันทึกการแก้ไข');
        $('#form_address').attr('data-mode', 'edit');

        $('#addModal').modal('show');
    });


    select_provinces();

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
                    dropdownParent: $('#addModal'),
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

    $('#btn_submit_address').click(function (e) {
        e.preventDefault(); // ป้องกันการ submit ปกติ

        var form = $('#form_address');
        var mode = form.attr('data-mode'); // 'add' หรือ 'edit'
        var fn = (mode === 'edit') ? 'update_address' : 'insert_address';

        $.ajax({
            type: "post",
            url: "api/address.php",
            data: form.serialize() + '&fn=' + fn,
            dataType: "json",
            success: function (res) {
                swal({
                    title: res.title,
                    text: res.message,
                    icon: res.icon,
                    button: "ตกลง",
                }).then(() => {
                    if (res.status === "success") {
                        $('#addModal').modal('hide'); // ปิด modal
                        form.trigger('reset').attr('data-mode', 'add');
                        $('#btn_submit_address').text('ยืนยัน');
                        select_address();
                    }
                });
            },
            error: function (xhr, status, error) {
                swal({
                    title: "เกิดข้อผิดพลาด!",
                    text: error,
                    icon: "error",
                    button: "ตกลง",
                });
            }
        });
    });

    $(document).on('click', '.delete-address', function (e) {
        e.preventDefault();

        // ดึงค่า address_id จาก radio ที่อยู่ใน row เดียวกัน
        var address_id = $(this).data('id');

        swal({
            title: "ยืนยันการลบ?",
            text: "คุณต้องการลบที่อยู่นี้ใช่หรือไม่",
            icon: "warning",
            buttons: ["ยกเลิก", "ลบเลย"],
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    type: "post",
                    url: "api/address.php",
                    data: {
                        fn: "delete_address",
                        address_id: address_id
                    },
                    dataType: "json",
                    success: function (res) {
                        swal({
                            title: res.title,
                            text: res.message,
                            icon: res.icon,
                            timer: 1500,
                            buttons: false
                        });

                        // โหลดรายการที่อยู่ใหม่
                        select_address();
                    },
                    error: function () {
                        swal("เกิดข้อผิดพลาด", "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้", "error");
                    }
                });
            }
        });
    });



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
            if (shipping_name == "") {
                swal("กรุณากรอกข้อมูลให้ครบ", "กรุณากรอกข้อมูลก่อนยืนยันสั่งซื้อ", "warning");
                return;
            }
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
            var shipping_name = "";
            var shipping_phone = "";
            var shipping_department = "";
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