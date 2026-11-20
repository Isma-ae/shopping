$(function () {
    var grandTotal = 0;
    var totalQty = 0;
    var currentShippingPrice = 0;
    var currentTotalPrice = 0;

    $('.form-in').hide();
    $('.form-out').hide();
    $('.receipt_form').hide();

    $('.js-example-basic-single').select2({
        dropdownParent: $('#addModal'),
        width: '100%'
    });

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
                var items = Array.isArray(res.data) ? res.data : [];
                if (items.length === 0) {
                    window.location.href = '?page=shipping_list';
                    return;
                }

                var html = '';
                grandTotal = 0;
                totalQty = 0;

                $.each(items, function (i, v) {
                    var unitPrice = parseFloat(v.variant_sale || 0);
                    var qty = parseInt(v.cart_qty || 1, 10);
                    if (isNaN(qty) || qty < 1) qty = 1;
                    var itemTotal = unitPrice * qty;
                    grandTotal += itemTotal;
                    totalQty += qty;

                    // Match pages/detail & pages/cart: hide color/size if single option
                    var colorCount = parseInt(v.color_count || 0, 10);
                    var sizeCount = parseInt(v.size_count || 0, 10);
                    var colorVal = $.trim(v.variant_color || '');
                    var sizeVal = $.trim(v.variant_size || '');
                    var sizeDetail = $.trim(v.size_detail || '');

                    var variantHtml = '';
                    if (colorCount > 1) {
                        var colorLabel = colorVal !== '' ? colorVal : 'มาตรฐาน';
                        variantHtml += '<span class="checkout-variant-pill"><strong>สี:</strong>' + escapeHtml(colorLabel) + '</span>';
                    }
                    if (sizeCount > 1) {
                        var sizeLabel = sizeVal !== '' ? sizeVal : 'มาตรฐาน';
                        if (sizeDetail !== '') {
                            sizeLabel += ' (' + sizeDetail + ')';
                        }
                        variantHtml += '<span class="checkout-variant-pill"><strong>ขนาด:</strong>' + escapeHtml(sizeLabel) + '</span>';
                    }
                    if (Number(v.cart_type) === 2) {
                        variantHtml += '<span class="checkout-status-pill">สินค้าสั่งจอง</span>';
                    }

                    var imgPath = (v.img_name && $.trim(v.img_name) !== '')
                        ? 'file/product/' + encodeURIComponent(v.img_name)
                        : 'images/icons/OARShopping.png';
                    var detailUrl = v.product_hash
                        ? '?page=detail&product=' + encodeURIComponent(v.product_hash)
                        : '?page=product';

                    html += '<article class="checkout-item">';
                    html += '  <div class="checkout-item-main">';
                    html += '    <a href="' + detailUrl + '" class="checkout-item-thumb">';
                    html += '      <img src="' + imgPath + '" alt="' + escapeHtml(v.product_name) + '" onerror="this.onerror=null;this.src=\'images/icons/OARShopping.png\';this.classList.add(\'is-fallback-img\');">';
                    html += '    </a>';
                    html += '    <div class="checkout-item-info">';
                    html += '      <a href="' + detailUrl + '" class="checkout-item-title">' + escapeHtml(v.product_name) + '</a>';
                    if (variantHtml !== '') {
                        html += '      <div class="checkout-item-variants">' + variantHtml + '</div>';
                    }
                    html += '      <div class="checkout-item-mobile-meta">' + formatBaht(unitPrice) + ' &times; ' + qty + ' ชิ้น</div>';
                    html += '    </div>';
                    html += '  </div>';
                    html += '  <div class="checkout-item-unit">' + formatBaht(unitPrice) + '</div>';
                    html += '  <div class="checkout-item-qty-col"><span class="checkout-item-qty-badge">' + qty + '</span></div>';
                    html += '  <div class="checkout-item-subtotal">' + formatBaht(itemTotal) + '</div>';
                    html += '</article>';
                });

                $('.cart_data').html(html);
                $('#checkoutHeadMeta').removeClass('d-none');
                $('#checkoutItemCount').text(items.length);
                $('#checkoutQtyCount').text(totalQty);
                $('#checkoutSummaryQty').text(items.length + ' รายการ (' + totalQty + ' ชิ้น)');
                $('.cart-total').text(formatBaht(grandTotal));

                shipping_price(grandTotal, totalQty);
            }
        });
    }

    function shipping_price(totalPrice, qtySum) {
        var price_shipping = 0;

        if ($('#shipping-type-3').is(':checked')) {
            var shipping_main = 50;
            var qty_add = Math.max(0, parseInt(qtySum, 10) - 1);
            price_shipping = shipping_main + (10 * qty_add);
            $('#shippingRateNote').removeClass('d-none');
        } else {
            $('#shippingRateNote').addClass('d-none');
        }

        currentShippingPrice = parseFloat(price_shipping);
        currentTotalPrice = parseFloat(totalPrice) + currentShippingPrice;

        if (currentShippingPrice <= 0) {
            $('.shipping-price').text('ฟรี').addClass('is-free');
        } else {
            $('.shipping-price').text(formatBaht(currentShippingPrice)).removeClass('is-free');
        }
        $('.total-price').text(formatBaht(currentTotalPrice));
    }

    $('[name="receipt_id"]').on('change', function () {
        if ($(this).is(':checked')) {
            $('.receipt_form').slideDown(200);
        } else {
            $('.receipt_form').slideUp(200);
        }
    });

    // เปลี่ยนประเภทการจัดส่ง
    $('[name="shipping_type"]').on('change', function () {
        if ($('#shipping-type-1').is(':checked')) {
            $('.form-front').show();
            $('.form-in').hide();
            $('.form-out').hide();
        } else if ($('#shipping-type-2').is(':checked')) {
            $('.form-front').hide();
            $('.form-in').show();
            $('.form-out').hide();
        } else if ($('#shipping-type-3').is(':checked')) {
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
                var rows = Array.isArray(res.data) ? res.data : [];
                var html = '';

                if (rows.length > 0) {
                    html += '<div class="checkout-address-list">';
                    $.each(rows, function (i, v) {
                        var isFirst = (i === 0);
                        html += '<div class="checkout-address-card' + (isFirst ? ' is-selected' : '') + '"' +
                            ' data-id="' + escapeHtml(v.id) + '"' +
                            ' data-name="' + escapeHtml(v.name) + '"' +
                            ' data-phone="' + escapeHtml(v.phone) + '"' +
                            ' data-address="' + escapeHtml(v.address_at) + '"' +
                            ' data-province="' + escapeHtml(v.province_id) + '"' +
                            ' data-district="' + escapeHtml(v.district_id) + '"' +
                            ' data-subdistrict="' + escapeHtml(v.subdistrict_id) + '"' +
                            ' data-zipcode="' + escapeHtml(v.zip_code) + '">';
                        html += '  <label class="checkout-address-main" for="address_id' + (i + 1) + '">';
                        html += '    <input class="checkout-address-radio" type="radio" name="address_id" id="address_id' + (i + 1) + '"' + (isFirst ? ' checked' : '') + ' value="' + escapeHtml(v.id) + '">';
                        html += '    <div class="checkout-address-text">';
                        html += '      <strong>' + escapeHtml(v.name) + '<span>' + escapeHtml(v.phone) + '</span></strong>';
                        html += '      <p>' + escapeHtml(v.address_at) + ' ตำบล' + escapeHtml(v.subdistrict_name_in_thai) + ' อำเภอ' + escapeHtml(v.district_name_in_thai) + ' จังหวัด' + escapeHtml(v.province_name_in_thai) + ' ' + escapeHtml(v.zip_code) + '</p>';
                        html += '    </div>';
                        html += '  </label>';
                        html += '  <div class="checkout-address-actions">';
                        html += '    <a href="#" class="checkout-addr-btn edit-address"><i class="fa fa-pencil mr-1" aria-hidden="true"></i>แก้ไข</a>';
                        html += '    <a href="#" class="checkout-addr-btn checkout-addr-btn--delete delete-address" data-id="' + escapeHtml(v.id) + '"><i class="fa fa-trash-o mr-1" aria-hidden="true"></i>ลบ</a>';
                        html += '  </div>';
                        html += '</div>';
                    });
                    html += '</div>';
                }

                html += '<a href="#" class="checkout-add-addr-btn add-address"><i class="fa fa-plus mr-2" aria-hidden="true"></i>เพิ่มที่อยู่จัดส่งใหม่</a>';
                $('.form-out').html(html);
            }
        });
    }

    $(document).on('change', 'input[name="address_id"]', function () {
        $('.checkout-address-card').removeClass('is-selected');
        $(this).closest('.checkout-address-card').addClass('is-selected');
    });

    function resetAddressForm() {
        var $form = $('#form_address');
        $form.trigger('reset').attr('data-mode', 'add');
        $('#address_id').val('');
        $('#province_id').val('').trigger('change.select2');
        $('#district_id').html('<option value="">-- เลือกอำเภอ --</option>').prop('disabled', true).trigger('change.select2');
        $('#subdistrict_id').html('<option value="">-- เลือกตำบล --</option>').prop('disabled', true).trigger('change.select2');
        $('#zip_code').val('');
    }

    $(document).on('click', '.add-address', function (e) {
        e.preventDefault();
        resetAddressForm();
        $('#addModalLabel').text('เพิ่มที่อยู่จัดส่งใหม่');
        $('#btn_submit_address').text('บันทึกที่อยู่');
        $('#addModal').modal('show');
    });

    $(document).on('click', '.edit-address', function (e) {
        e.preventDefault();
        var $card = $(this).closest('.checkout-address-card');

        var data = {
            id: $card.attr('data-id'),
            name: $card.attr('data-name'),
            phone: $card.attr('data-phone'),
            address: $card.attr('data-address'),
            province: $card.attr('data-province'),
            district: $card.attr('data-district'),
            subdistrict: $card.attr('data-subdistrict'),
            zipcode: $card.attr('data-zipcode')
        };

        $('#address_id').val(data.id);
        $('#name').val(data.name);
        $('#phone').val(data.phone);
        $('#address_at').val(data.address);
        $('#zip_code').val(data.zipcode);

        $('#province_id').val(data.province).trigger('change.select2');
        select_districts(data.province, data.district);
        select_subdistricts(data.district, data.subdistrict);

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
                    html += '<option value="' + v.province_id + '">' + escapeHtml(v.province_name_in_thai) + '</option>';
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

    $('#province_id').on('change', function () {
        var province_id = $(this).val();
        if (province_id) {
            $('#district_id').prop('disabled', false);
            select_districts(province_id);
            $('#subdistrict_id').html('<option value="">-- เลือกตำบล --</option>').prop('disabled', true).trigger('change.select2');
            $('#zip_code').val('');
        } else {
            $('#district_id').html('<option value="">-- เลือกอำเภอ --</option>').prop('disabled', true).trigger('change.select2');
            $('#subdistrict_id').html('<option value="">-- เลือกตำบล --</option>').prop('disabled', true).trigger('change.select2');
            $('#zip_code').val('');
        }
    });

    $('#district_id').on('change', function () {
        var district_id = $(this).val();
        if (district_id) {
            $('#subdistrict_id').prop('disabled', false);
            select_subdistricts(district_id);
            $('#zip_code').val('');
        } else {
            $('#subdistrict_id').html('<option value="">-- เลือกตำบล --</option>').prop('disabled', true).trigger('change.select2');
            $('#zip_code').val('');
        }
    });

    $('#subdistrict_id').on('change', function () {
        var subdistrict_id = $(this).val();
        if (subdistrict_id) {
            select_zip_code(subdistrict_id);
        } else {
            $('#zip_code').val('');
        }
    });

    function select_districts(province_id, selected_id) {
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
                    var selected = (selected_id && String(selected_id) === String(v.district_id)) ? 'selected' : '';
                    html += '<option value="' + v.district_id + '" ' + selected + '>' + escapeHtml(v.district_name_in_thai) + '</option>';
                });
                $('#district_id').html(html).prop('disabled', false).trigger('change.select2');
            }
        });
    }

    function select_subdistricts(district_id, selected_id) {
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
                    var selected = (selected_id && String(selected_id) === String(v.subdistrict_id)) ? 'selected' : '';
                    html += '<option value="' + v.subdistrict_id + '" ' + selected + '>' + escapeHtml(v.subdistrict_name_in_thai) + '</option>';
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

    $('#btn_submit_address').on('click', function (e) {
        e.preventDefault();

        var form = $('#form_address');
        var mode = form.attr('data-mode');
        var fn = (mode === 'edit') ? 'update_address' : 'insert_address';

        var shipping_name = $.trim($('#name').val());
        var shipping_phone = $.trim($('#phone').val());
        var address_at = $.trim($('#address_at').val());
        var subdistrict_id = $('#subdistrict_id').val();
        var district_id = $('#district_id').val();
        var province_id = $('#province_id').val();
        var zip_code = $.trim($('#zip_code').val());

        if (
            shipping_name === "" ||
            shipping_phone === "" ||
            address_at === "" ||
            !subdistrict_id ||
            !district_id ||
            !province_id ||
            zip_code === ""
        ) {
            swal({
                title: "กรุณากรอกข้อมูลให้ครบ",
                text: "กรุณากรอกชื่อ เบอร์โทร ที่อยู่ และเลือกตำบล อำเภอ จังหวัด ให้ครบก่อนบันทึก",
                icon: "warning",
            });
            return;
        }

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
                }).then(function () {
                    if (res.status === "success") {
                        $('#addModal').modal('hide');
                        resetAddressForm();
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
        var address_id = $(this).data('id');

        swal({
            title: "ยืนยันการลบ?",
            text: "คุณต้องการลบที่อยู่นี้ใช่หรือไม่",
            icon: "warning",
            buttons: ["ยกเลิก", "ลบเลย"],
            dangerMode: true,
        }).then(function (willDelete) {
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
                        select_address();
                    },
                    error: function () {
                        swal("เกิดข้อผิดพลาด", "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้", "error");
                    }
                });
            }
        });
    });

    $('#check-out').on('click', function (e) {
        e.preventDefault();
        var $btn = $(this);

        var receipt_id = 0;
        var receipt_name = '';
        var receipt_number = '';
        var receipt_address = '';

        if ($('#receipt_id').is(':checked')) {
            receipt_id = $('input[name="receipt_id"]:checked').val();
            receipt_name = $.trim($('#receipt_name').val());
            receipt_number = $.trim($('#receipt_number').val());
            receipt_address = $.trim($('#receipt_address').val());

            if (receipt_name === '' || receipt_number === '' || receipt_address === '') {
                swal("กรุณากรอกข้อมูลใบเสร็จให้ครบ", "กรุณาระบุนามออกใบเสร็จ เลขประจำตัวผู้เสียภาษี และที่อยู่", "warning");
                return;
            }
        }

        var shipping_type = $('input[name="shipping_type"]:checked').val();
        var shipping_name = '';
        var shipping_phone = '';
        var shipping_department = '';
        var address_id = 0;

        if ($('#shipping-type-1').is(':checked')) {
            shipping_name = $.trim($('[name="shipping_name"]').val());
            shipping_phone = $.trim($('[name="shipping_phone"]').val());
            shipping_department = $.trim($('[name="shipping_department"]').val());
            if (shipping_name === "") {
                swal("กรุณากรอกข้อมูลให้ครบ", "กรุณากรอกชื่อ-นามสกุลก่อนยืนยันสั่งซื้อ", "warning");
                return;
            }
        } else if ($('#shipping-type-2').is(':checked')) {
            shipping_name = $.trim($('[name="shipping_name2"]').val());
            shipping_phone = $.trim($('[name="shipping_phone2"]').val());
            shipping_department = $.trim($('[name="shipping_department2"]').val());
            if (shipping_name === "" || shipping_phone === "" || shipping_department === "") {
                swal("กรุณากรอกข้อมูลให้ครบ", "กรุณากรอกชื่อ หมายเลขโทรศัพท์ และหน่วยงานให้ครบก่อนยืนยันสั่งซื้อ", "warning");
                return;
            }
        } else if ($('#shipping-type-3').is(':checked')) {
            address_id = $('input[name="address_id"]:checked').val();
            if (!address_id) {
                swal("กรุณาเลือกที่อยู่จัดส่ง", "คุณยังไม่ได้เลือกที่อยู่สำหรับการจัดส่งสินค้า", "warning");
                return;
            }
        }

        $btn.prop('disabled', true);
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
                shipping_price: currentShippingPrice.toFixed(2),
                total_price: currentTotalPrice.toFixed(2)
            },
            dataType: "json",
            success: function (res) {
                if (res.status == "success") {
                    window.location.href = "payment/?order_no=" + encodeURIComponent(res.order_no);
                } else {
                    $btn.prop('disabled', false);
                    swal("เกิดข้อผิดพลาด", res.msg || "ไม่สามารถสร้างคำสั่งซื้อได้", "error");
                }
            },
            error: function () {
                $btn.prop('disabled', false);
                swal("เกิดข้อผิดพลาด", "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้", "error");
            }
        });
    });
});
