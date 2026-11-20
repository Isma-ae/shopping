$(document).ready(function () {
    $('#update-shipping').on('click', function (e) {
        e.preventDefault();
        $('#shippingModal').modal('show');
    });

    $('#shippingModal').on('hidden.bs.modal', function () {
        var transported_now = $('#transported_now').val();
        var parcel_now = $('#parcel_now').val();
        $('#transported_id').val(transported_now && transported_now !== '0' ? transported_now : '0');
        $('#parcel_number').val(parcel_now);
    });

    $('#update-receipt').on('click', function (e) {
        e.preventDefault();
        $('#receiptModal').modal('show');
    });

    $('#receiptModal').on('hidden.bs.modal', function () {
        $('#receipt_link').val($('#receipt_now').val());
    });

    $('#update-status').on('click', function (e) {
        e.preventDefault();
        $('#statusModal').modal('show');
    });

    $('#statusModal').on('hidden.bs.modal', function () {
        var status_now = $('#status_now').val();
        $('[name="status_id"][value="' + status_now + '"]').prop('checked', true);
    });

    $('#save-shipping').on('click', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var transported_id = $('#transported_id').val();
        var parcel_number = $.trim($('#parcel_number').val());
        var order_id = $('#order_id').val();

        if (!transported_id || transported_id === '0') {
            Swal.fire('กรุณาเลือกบริษัทขนส่ง', 'กรุณาเลือกบริษัทขนส่งก่อนบันทึก', 'warning');
            return;
        }
        if (parcel_number === '') {
            Swal.fire('กรุณากรอกหมายเลขพัสดุ', 'กรุณาระบุหมายเลขพัสดุก่อนบันทึก', 'warning');
            $('#parcel_number').trigger('focus');
            return;
        }

        $btn.prop('disabled', true);
        $.ajax({
            type: "post",
            url: "api/orders.php",
            data: {
                fn: "update_shipping",
                transported_id: transported_id,
                parcel_number: parcel_number,
                order_id: order_id
            },
            dataType: "json",
            success: function (res) {
                if (res.status === "success") {
                    Swal.fire(res.title, res.message, res.icon).then(function () {
                        location.reload();
                    });
                } else {
                    Swal.fire(res.title || "ไม่สำเร็จ", res.message || "", res.icon || "error");
                }
            },
            error: function () {
                Swal.fire("เกิดข้อผิดพลาด", "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้", "error");
            },
            complete: function () {
                $btn.prop('disabled', false);
            }
        });
    });

    $('#save-receipt').on('click', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var receipt_link = $.trim($('#receipt_link').val());
        var order_id = $('#order_id').val();

        $btn.prop('disabled', true);
        $.ajax({
            type: "post",
            url: "api/orders.php",
            data: {
                fn: "update_receipt",
                receipt_link: receipt_link,
                order_id: order_id
            },
            dataType: "json",
            success: function (res) {
                if (res.status === "success") {
                    Swal.fire(res.title, res.message, res.icon).then(function () {
                        location.reload();
                    });
                } else {
                    Swal.fire(res.title || "ไม่สำเร็จ", res.message || "", res.icon || "error");
                }
            },
            error: function () {
                Swal.fire("เกิดข้อผิดพลาด", "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้", "error");
            },
            complete: function () {
                $btn.prop('disabled', false);
            }
        });
    });

    $('#save-status').on('click', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var status_id = $('input[name="status_id"]:checked').val();
        var order_id = $('#order_id').val();

        $btn.prop('disabled', true);
        $.ajax({
            type: "post",
            url: "api/orders.php",
            data: {
                fn: "update_status",
                status_id: status_id,
                order_id: order_id
            },
            dataType: "json",
            success: function (res) {
                if (res.status === "success") {
                    Swal.fire(res.title, res.message, res.icon).then(function () {
                        location.reload();
                    });
                } else {
                    Swal.fire(res.title || "ไม่สำเร็จ", res.message || "", res.icon || "error");
                }
            },
            error: function () {
                Swal.fire("เกิดข้อผิดพลาด", "ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้", "error");
            },
            complete: function () {
                $btn.prop('disabled', false);
            }
        });
    });

    // Zoom slip modal
    $(document).on('click', '.js-zoom-slip', function (e) {
        e.preventDefault();
        var url = $(this).attr('data-slip-url') || '';
        if (!url) return;
        $('#slipZoomImg').attr('src', url);
        $('#slipZoomLink').attr('href', url);
        var modalEl = document.getElementById('slipZoomModal');
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    });

    // Copy helper for order no, ref1, parcel number
    $(document).on('click', '.js-copy-text', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var text = $btn.attr('data-copy') || '';
        if (!text) return;

        function markCopied() {
            var $icon = $btn.find('i');
            $icon.removeClass('fa-copy').addClass('fa-check text-success');
            setTimeout(function () {
                $icon.removeClass('fa-check text-success').addClass('fa-copy');
            }, 1200);
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(markCopied);
        } else {
            var $tmp = $('<input>').val(text).appendTo('body').select();
            document.execCommand('copy');
            $tmp.remove();
            markCopied();
        }
    });
});
