$(document).ready(function () {
    $('form').submit(function (e) {
        e.preventDefault();
        let formData = new FormData(this);

        $.ajax({
            url: "api/evidence.php",
            type: "POST",
            data: formData,
            processData: false, // สำคัญ
            contentType: false, // สำคัญ
            dataType: "json",
            success: function (res) {
                if (res.status == 'success') {
                    swal(res.title, res.message, res.icon).then((result) => {
                        window.location.href = '?page=shipping_list';
                    });
                } else {
                    swal(res.title, res.message, res.icon);
                }
            }
        });
    });
});