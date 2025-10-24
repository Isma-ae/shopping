$(function () {
    $('#add-address').click(function (e) {
        e.preventDefault();
        $.ajax({
            type: "post",
            url: "api/address.php",
            data: {
                fn: "add_address",
                address_name: $('#address_name').val(),
                address_phone: $('#address_phone').val(),
                address_at: $('#address_at').val()
            },
            dataType: "json",
            success: function (res) {
                if (res.data == 't') {
                    alert('เพิ่มที่อยู่เรียบร้อย');
                    window.location.href = '?page=checkout';
                }
            }
        });
    });

})