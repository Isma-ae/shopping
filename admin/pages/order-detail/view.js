$(document).ready(function () {
    $('#update-shipping').click(function (e) {
        e.preventDefault();
        $('#shippingModal').modal('show');
    });
    $('#shippingModal').on('hidden.bs.modal', function () {
        var transported_now = $('#transported_now').val();
        var parcel_now = $('#parcel_now').val();
        if (transported_now == '' || transported_now == 0) {
            $('#transported_id').val(0);
        } else {
            $('#transported_id').val(transported_now);
        }
        $('#parcel_number').val(parcel_now);
    });

    $('#update-receipt').click(function (e) {
        e.preventDefault();
        $('#receiptModal').modal('show');
    });
    $('#receiptModal').on('hidden.bs.modal', function () {
        var receipt_now = $('#receipt_now').val();
        $('#receipt_link').val(receipt_now);
    });

    $('#update-status').click(function (e) {
        e.preventDefault();
        $('#statusModal').modal('show');
    });
    $('#statusModal').on('hidden.bs.modal', function () {
        var status_now = $('#status_now').val();
        $('[name="status_id"][value="' + status_now + '"]').prop('checked', true);
    });

    $('#save-shipping').click(function (e) {
        e.preventDefault();
        var transported_id = $('#transported_id').val();
        var parcel_number = $('#parcel_number').val();
        var order_id = $('#order_id').val();
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
                if (res.status == "success") {
                    Swal.fire(res.title, res.message, res.icon).then((result) => {
                        location.reload();
                    });
                } else {
                    Swal.fire(res.title, res.message, res.icon);
                }
            }
        });
    });

    $('#save-receipt').click(function (e) {
        e.preventDefault();
        var receipt_link = $('#receipt_link').val();
        var order_id = $('#order_id').val();
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
                if (res.status == "success") {
                    Swal.fire(res.title, res.message, res.icon).then((result) => {
                        location.reload();
                    });
                } else {
                    Swal.fire(res.title, res.message, res.icon);
                }
            }
        });
    });

    $('#save-status').click(function (e) {
        e.preventDefault();
        var status_id = $('input[name="status_id"]:checked').val();
        var order_id = $('#order_id').val();
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
                if (res.status == "success") {
                    Swal.fire(res.title, res.message, res.icon).then((result) => {
                        location.reload();
                    });
                } else {
                    Swal.fire(res.title, res.message, res.icon);
                }
            }
        });
    });
});