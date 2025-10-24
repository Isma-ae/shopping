$(document).ready(function () {
    load_qty();

    $('#logout').click(function (e) {
        e.preventDefault();
        $.ajax({
            type: "post",
            url: "login/action.php",
            data: {
                fn: "logout"
            },
            dataType: "json",
            success: function (response) {
                if (response.data == 't') {
                    location.reload();
                } else {
                    alert('error');
                }
            }
        });
    });
});

function load_qty() {
    $.ajax({
        type: "post",
        url: "api/load_qty.php",
        data: {
            user_id: $('#user_id').val()
        },
        dataType: "json",
        success: function (res) {
            if (res.data == 'f') {
                $('.js-show-cart').removeClass('icon-header-noti');
            } else {
                if (res.data[0].cart_qty > 0) {
                    $('.js-show-cart').addClass('icon-header-noti');
                    $('.js-show-cart').attr('data-notify', res.data[0].cart_qty);
                } else {
                    $('.js-show-cart').removeClass('icon-header-noti');
                }
            }
        }
    });
}